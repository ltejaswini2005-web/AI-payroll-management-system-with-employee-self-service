<?php

namespace App\Modules\Attendance\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class FaceAttendanceApiController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceRecognitionService,
        private readonly AttendanceService $attendanceService
    ) {
    }

    /**
     * Recognize face and return employee info if matched.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function recognize(Request $request): JsonResponse
    {
        // Face attendance kiosk is public - no auth required
        $validated = $request->validate([
            'face_descriptor' => ['required', 'array', 'min:1'],
            'liveness_verified' => ['nullable', 'boolean'],
        ]);

        $descriptor = (array) $validated['face_descriptor'];

        // DEBUG: Log the incoming descriptor
        \Log::info('KIOSK_RECOGNIZE', [
            'descriptor_length' => count($descriptor),
            'descriptor_type' => gettype($descriptor),
            'first_value' => $descriptor[0] ?? null,
            'sample_values' => array_slice($descriptor, 0, 5),
        ]);

        // Check for ambiguous matches
        $ambiguityCheck = $this->faceRecognitionService->checkForAmbiguousMatches($descriptor);
        if ($ambiguityCheck['ambiguous']) {
            return response()->json([
                'success' => false,
                'error' => 'ambiguous',
                'message' => 'Unable to identify the employee. Please try again.',
            ], 400);
        }

        // Find best match
        $match = $this->faceRecognitionService->findBestMatch($descriptor);

        // DEBUG: Log match result
        \Log::info('KIOSK_MATCH_RESULT', [
            'found_employee' => $match['employee'] ? $match['employee']->id : null,
            'distance' => $match['distance'],
            'threshold' => config('face-attendance.matching_threshold'),
            'within_threshold' => $match['employee'] !== null,
        ]);

        if (!$match['employee']) {
            return response()->json([
                'success' => false,
                'error' => 'not_recognized',
                'message' => 'Face not recognized',
            ], 400);
        }

        $employee = $match['employee'];

        // Validate employee eligibility
        $eligibility = $this->faceRecognitionService->validateEmployeeEligibility($employee->id);
        
        // DEBUG: Log eligibility result
        \Log::info('KIOSK_ELIGIBILITY', [
            'employee_id' => $employee->id,
            'valid' => $eligibility['valid'],
            'reason' => $eligibility['reason'],
        ]);

        if (!$eligibility['valid']) {
            return response()->json([
                'success' => false,
                'error' => 'not_eligible',
                'message' => $eligibility['reason'],
            ], 403);
        }

        // Determine attendance state (check-in or check-out)
        $attendanceState = $this->determineAttendanceState($employee->id);

        if ($attendanceState['action'] === 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'already_completed',
                'message' => "Today's attendance is already completed.",
            ], 409);
        }

        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'code' => $employee->employee_code,
                'name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
            ],
            'match_distance' => round($match['distance'], 4),
            'attendance_action' => $attendanceState['action'],
            'has_open_attendance' => $attendanceState['has_open'],
        ]);
    }

    /**
     * Record face attendance.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function recordAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'face_descriptor' => ['required', 'array', 'min:1'],
            'action' => ['required', 'in:checkin,checkout'],
            'photo_data' => ['nullable', 'string'],
            'liveness_verified' => ['nullable', 'boolean'],
        ]);

        $employeeId = (int) $validated['employee_id'];
        $descriptor = (array) $validated['face_descriptor'];
        $action = (string) $validated['action'];

        // Re-verify the face match (security: don't trust browser-sent employee_id)
        $match = $this->faceRecognitionService->findBestMatch($descriptor);

        if (!$match['employee'] || $match['employee']->id !== $employeeId) {
            return response()->json([
                'success' => false,
                'error' => 'verification_failed',
                'message' => 'Face verification failed',
            ], 403);
        }

        $employee = $match['employee'];

        // Re-validate eligibility
        $eligibility = $this->faceRecognitionService->validateEmployeeEligibility($employee->id);
        if (!$eligibility['valid']) {
            return response()->json([
                'success' => false,
                'error' => 'not_eligible',
                'message' => $eligibility['reason'],
            ], 403);
        }

        try {
            $attendanceResult = $this->attendanceService->recordFaceAttendance(
                $employeeId,
                $action,
                'Face recognition - Kiosk' . (!empty($validated['liveness_verified']) ? ' (AI Liveness Verified)' : '')
            );

            if ($attendanceResult['status'] === 'completed') {
                return response()->json([
                    'success' => false,
                    'error' => 'already_completed',
                    'message' => "Today's attendance is already completed.",
                ], 409);
            }

            if ($attendanceResult['status'] === 'invalid_action') {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_action',
                    'message' => 'Attendance state changed. Please scan again.',
                ], 409);
            }

            $attendanceLog = $attendanceResult['attendanceLog'];
            $timestamp = $attendanceResult['timestamp'];

            if (!empty($validated['photo_data'])) {
                $photoPath = $this->saveAttendancePhoto($validated['photo_data'], $employeeId);
                if ($photoPath) {
                    $attendanceLog->update(['photo_path' => $photoPath]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'attendance' => [
                    'employee_name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                    'employee_code' => $employee->employee_code,
                    'action' => $action,
                    'timestamp' => $timestamp?->format('h:i A'),
                    'date' => $attendanceLog->attendance_date->format('Y-m-d'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'recording_failed',
                'message' => 'Failed to record attendance',
            ], 500);
        }
    }

    /**
     * Determine if employee should check-in or check-out.
     *
     * @param  int  $employeeId
     * @return array{action: 'checkin'|'checkout'|'completed', has_open: bool}
     */
    private function determineAttendanceState(int $employeeId): array
    {
        $today = now()->format('Y-m-d');

        // Find today's attendance records for this employee
        $lastRecord = AttendanceLog::query()
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $today)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastRecord) {
            // No attendance today - should check in
            return ['action' => 'checkin', 'has_open' => false];
        }

        if ($lastRecord->check_in_at && !$lastRecord->check_out_at) {
            // Open check-in, should check out
            return ['action' => 'checkout', 'has_open' => true];
        }

        if ($lastRecord->check_in_at && $lastRecord->check_out_at) {
            return ['action' => 'completed', 'has_open' => false];
        }

        // Default to check-in
        return ['action' => 'checkin', 'has_open' => false];
    }

    /**
     * Save attendance photo from base64 data.
     *
     * @param  string  $base64Data
     * @param  int  $employeeId
     * @return string|null
     */
    private function saveAttendancePhoto(string $base64Data, int $employeeId): ?string
    {
        try {
            // Parse data URL
            if (strpos($base64Data, 'data:image') === 0) {
                preg_match('/data:image\/(\w+);base64,/', $base64Data, $matches);
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
            } else {
                $data = $base64Data;
            }

            $decoded = base64_decode($data, true);
            if ($decoded === false) {
                return null;
            }

            $config = config('face-attendance');
            $disk = $config['storage']['disk'];
            $path = $config['storage']['attendance_path'];
            $filename = 'emp_' . $employeeId . '_' . now()->timestamp . '.jpg';

            Storage::disk($disk)->put($path . '/' . $filename, $decoded);

            return $path . '/' . $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
