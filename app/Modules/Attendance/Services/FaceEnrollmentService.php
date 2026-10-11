<?php

namespace App\Modules\Attendance\Services;

use App\Models\Employee;
use App\Models\FaceEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FaceEnrollmentService
{
    /**
     * Enroll a new face for an employee.
     *
     * @param  int  $employeeId
     * @param  array<string, mixed>  $faceData
     * @param  UploadedFile|null  $photo
     * @param  int  $enrolledById
     * @return FaceEnrollment
     */
    public function enrollFace(
        int $employeeId,
        array $faceData,
        ?UploadedFile $photo,
        int $enrolledById
    ): FaceEnrollment {
        $descriptor = (array) ($faceData['descriptor'] ?? []);
        if (empty($descriptor)) {
            throw new \InvalidArgumentException('Face descriptor is required');
        }

        return DB::transaction(function () use ($employeeId, $descriptor, $photo, $enrolledById) {
            // Deactivate any existing active enrollment
            FaceEnrollment::query()
                ->where('employee_id', $employeeId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $photoPath = null;
            if ($photo) {
                $photoPath = $this->storeEnrollmentPhoto($photo, $employeeId);
            }

            return FaceEnrollment::create([
                'employee_id' => $employeeId,
                'face_descriptor' => json_encode($descriptor),
                'photo_path' => $photoPath,
                'is_active' => true,
                'enrolled_by' => $enrolledById,
                'enrolled_at' => now(),
            ]);
        });
    }

    /**
     * Get active enrollment for an employee.
     *
     * @param  int  $employeeId
     * @return FaceEnrollment|null
     */
    public function getActiveEnrollment(int $employeeId): ?FaceEnrollment
    {
        return FaceEnrollment::query()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active enrollments (for face recognition matching).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, FaceEnrollment>
     */
    public function getAllActiveEnrollments()
    {
        return FaceEnrollment::query()
            ->where('is_active', true)
            ->with(['employee', 'employee.user'])
            ->get();
    }

    /**
     * Deactivate an enrollment.
     *
     * @param  int  $enrollmentId
     * @return bool
     */
    public function deactivateEnrollment(int $enrollmentId): bool
    {
        $enrollment = FaceEnrollment::find($enrollmentId);
        if (!$enrollment) {
            return false;
        }

        return (bool) $enrollment->update(['is_active' => false]);
    }

    /**
     * Check if employee has active enrollment.
     *
     * @param  int  $employeeId
     * @return bool
     */
    public function hasActiveEnrollment(int $employeeId): bool
    {
        return FaceEnrollment::query()
            ->where('employee_id', $employeeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Store enrollment photo.
     *
     * @param  UploadedFile  $photo
     * @param  int  $employeeId
     * @return string
     */
    private function storeEnrollmentPhoto(UploadedFile $photo, int $employeeId): string
    {
        $config = config('face-attendance');
        $disk = $config['storage']['disk'];
        $path = $config['storage']['enrollment_path'];
        $filename = 'employee_' . $employeeId . '_' . time() . '.jpg';

        return Storage::disk($disk)->putFileAs($path, $photo, $filename);
    }

    /**
     * Delete enrollment (including photo).
     *
     * @param  int  $enrollmentId
     * @return bool
     */
    public function deleteEnrollment(int $enrollmentId): bool
    {
        $enrollment = FaceEnrollment::find($enrollmentId);
        if (!$enrollment) {
            return false;
        }

        // Delete photo if it exists
        if ($enrollment->photo_path) {
            try {
                $disk = config('face-attendance.storage.disk');
                Storage::disk($disk)->delete($enrollment->photo_path);
            } catch (\Throwable) {
                // Continue even if photo delete fails
            }
        }

        return (bool) $enrollment->delete();
    }

    /**
     * Get enrollment statistics.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $totalEnrollments = FaceEnrollment::query()->where('is_active', true)->count();
        $totalEmployees = Employee::query()->count();
        $enrollmentRate = $totalEmployees > 0 ? round(($totalEnrollments / $totalEmployees) * 100, 2) : 0;

        return [
            'total_active_enrollments' => $totalEnrollments,
            'total_employees' => $totalEmployees,
            'enrollment_rate_percent' => $enrollmentRate,
        ];
    }
}
