<?php

namespace App\Modules\Attendance\Services;

use App\Models\Employee;
use App\Models\FaceEnrollment;

class FaceRecognitionService
{
    /**
     * Find best matching employee from live face descriptor.
     *
     * @param  array<float>  $liveDescriptor
     * @return array{employee: Employee|null, distance: float|null, matched_enrollment: FaceEnrollment|null}
     */
    public function findBestMatch(array $liveDescriptor): array
    {
        if (empty($liveDescriptor)) {
            \Log::warning('FIND_BEST_MATCH: Empty live descriptor');
            return [
                'employee' => null,
                'distance' => null,
                'matched_enrollment' => null,
            ];
        }

        $threshold = config('face-attendance.matching_threshold');
        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;

        // DEBUG: Log live descriptor info
        \Log::info('FIND_BEST_MATCH_START', [
            'live_descriptor_length' => count($liveDescriptor),
            'live_descriptor_type' => gettype($liveDescriptor),
            'threshold' => $threshold,
        ]);

        // Get all active enrollments with related data
        $enrollments = FaceEnrollment::query()
            ->where('is_active', true)
            ->with(['employee', 'employee.user'])
            ->get();

        \Log::info('FIND_BEST_MATCH_ENROLLMENTS', [
            'active_enrollment_count' => $enrollments->count(),
        ]);

        foreach ($enrollments as $enrollment) {
            $storedDescriptor = $enrollment->getDescriptorAsArray();

            if (empty($storedDescriptor)) {
                \Log::warning('FIND_BEST_MATCH_EMPTY_STORED', [
                    'enrollment_id' => $enrollment->id,
                    'employee_id' => $enrollment->employee_id,
                ]);
                continue;
            }

            // DEBUG: Log stored descriptor info
            \Log::info('FIND_BEST_MATCH_COMPARING', [
                'enrollment_id' => $enrollment->id,
                'employee_id' => $enrollment->employee_id,
                'stored_descriptor_length' => count($storedDescriptor),
                'stored_descriptor_type' => gettype($storedDescriptor),
            ]);

            $distance = $this->euclideanDistance($liveDescriptor, $storedDescriptor);

            \Log::info('FIND_BEST_MATCH_DISTANCE', [
                'enrollment_id' => $enrollment->id,
                'distance' => $distance,
                'within_threshold' => $distance <= $threshold,
            ]);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $enrollment;
            }
        }

        // Check if best match is within threshold
        if ($bestMatch && $bestDistance <= $threshold) {
            \Log::info('FIND_BEST_MATCH_SUCCESS', [
                'employee_id' => $bestMatch->employee_id,
                'distance' => $bestDistance,
                'threshold' => $threshold,
            ]);

            return [
                'employee' => $bestMatch->employee,
                'distance' => $bestDistance,
                'matched_enrollment' => $bestMatch,
            ];
        }

        \Log::info('FIND_BEST_MATCH_FAIL', [
            'best_distance' => $bestDistance !== PHP_FLOAT_MAX ? $bestDistance : null,
            'threshold' => $threshold,
            'reason' => $bestDistance === PHP_FLOAT_MAX ? 'no_enrollments' : 'threshold_exceeded',
        ]);

        return [
            'employee' => null,
            'distance' => $bestDistance !== PHP_FLOAT_MAX ? $bestDistance : null,
            'matched_enrollment' => null,
        ];
    }

    /**
     * Validate employee eligibility for attendance.
     *
     * @param  int  $employeeId
     * @return array{valid: bool, reason: string|null}
     */
    public function validateEmployeeEligibility(int $employeeId): array
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return [
                'valid' => false,
                'reason' => 'Employee not found',
            ];
        }

        // Check if employee has associated user
        if (!$employee->user) {
            return [
                'valid' => false,
                'reason' => 'Employee does not have associated user account',
            ];
        }

        // Check if user is active
        if (!$employee->user->isActive()) {
            return [
                'valid' => false,
                'reason' => 'Employee user account is inactive',
            ];
        }

        // Check if employee can access portal
        if (!$employee->user->canAccessPortal()) {
            $status = $employee->user->blockedEmploymentStatus();
            return [
                'valid' => false,
                'reason' => 'Employee is ' . ($status ?? 'inactive'),
            ];
        }

        // Check if employee has active enrollment
        if (!$this->hasActiveEnrollment($employeeId)) {
            return [
                'valid' => false,
                'reason' => 'Employee does not have active face enrollment',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
        ];
    }

    /**
     * Check if multiple matches are too close (ambiguous).
     *
     * @param  array<float>  $liveDescriptor
     * @return array{ambiguous: bool, top_two_distance_diff: float|null}
     */
    public function checkForAmbiguousMatches(array $liveDescriptor): array
    {
        if (empty($liveDescriptor)) {
            return ['ambiguous' => false, 'top_two_distance_diff' => null];
        }

        $threshold = config('face-attendance.matching_threshold');
        $distances = [];

        $enrollments = FaceEnrollment::query()
            ->where('is_active', true)
            ->get();

        foreach ($enrollments as $enrollment) {
            $storedDescriptor = $enrollment->getDescriptorAsArray();
            if (empty($storedDescriptor)) {
                continue;
            }

            $distance = $this->euclideanDistance($liveDescriptor, $storedDescriptor);
            if ($distance <= $threshold) {
                $distances[] = $distance;
            }
        }

        if (count($distances) >= 2) {
            // Multiple valid matches found - too close to distinguish
            sort($distances);
            $diff = abs($distances[0] - $distances[1]);

            // If top two matches are very similar (within 0.05), consider ambiguous
            if ($diff < 0.05) {
                return [
                    'ambiguous' => true,
                    'top_two_distance_diff' => $diff,
                ];
            }
        }

        return ['ambiguous' => false, 'top_two_distance_diff' => null];
    }

    /**
     * Calculate Euclidean distance between two descriptors.
     *
     * @param  array<float>  $descriptor1
     * @param  array<float>  $descriptor2
     * @return float
     */
    private function euclideanDistance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            \Log::error('EUCLIDEAN_DISTANCE_MISMATCH', [
                'descriptor1_length' => count($descriptor1),
                'descriptor2_length' => count($descriptor2),
            ]);
            return PHP_FLOAT_MAX;
        }

        $sum = 0;
        foreach ($descriptor1 as $i => $value1) {
            $value2 = $descriptor2[$i] ?? 0;
            $sum += ($value1 - $value2) ** 2;
        }

        $distance = sqrt($sum);
        return $distance;
    }

    /**
     * Check if employee has active face enrollment.
     *
     * @param  int  $employeeId
     * @return bool
     */
    private function hasActiveEnrollment(int $employeeId): bool
    {
        return FaceEnrollment::query()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->exists();
    }
}
