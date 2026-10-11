<?php

namespace App\Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Modules\Attendance\Services\FaceEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly FaceEnrollmentService $enrollmentService
    ) {
    }

    /**
     * Show face enrollment management page (HR/Admin only).
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $employees = Employee::query()
            ->with(['user', 'faceEnrollment' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(20);

        $stats = $this->enrollmentService->getStatistics();

        return view('hr.attendance.face-enrollment.index', [
            'employees' => $employees,
            'stats' => $stats,
        ]);
    }

    /**
     * Show enrollment form for a specific employee.
     */
    public function showEnrollmentForm(Request $request, Employee $employee): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('hr.attendance.face-enrollment.enroll', [
            'employee' => $employee,
            'modelsPath' => config('face-attendance.models_path'),
            'hasExistingEnrollment' => $this->enrollmentService->hasActiveEnrollment($employee->id),
        ]);
    }

    /**
     * Store or update face enrollment via API.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $validated = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'face_descriptor' => ['required', 'array', 'min:1'],
                'photo_data' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors as JSON instead of HTML
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $employeeId = (int) $validated['employee_id'];
            $descriptor = (array) $validated['face_descriptor'];

            // Prepare face data
            $faceData = [
                'descriptor' => $descriptor,
            ];

            // Handle photo if provided
            $photo = null;
            if (!empty($validated['photo_data'])) {
                $photo = $this->savePhotoFromBase64(
                    $validated['photo_data'],
                    $employeeId
                );
            }

            // Enroll the face
            $enrollment = $this->enrollmentService->enrollFace(
                $employeeId,
                $faceData,
                $photo,
                $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Face enrollment successful',
                'enrollment_id' => $enrollment->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deactivate an enrollment.
     */
    public function deactivate(Request $request, int $enrollmentId): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->enrollmentService->deactivateEnrollment($enrollmentId);

        return redirect()->route('face-enrollment.index')
            ->with('success', 'Face enrollment has been deactivated.');
    }

    /**
     * Delete an enrollment completely.
     */
    public function destroy(Request $request, int $enrollmentId): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->enrollmentService->deleteEnrollment($enrollmentId);

        return redirect()->route('face-enrollment.index')
            ->with('success', 'Face enrollment has been deleted.');
    }

    /**
     * Save photo from base64 data URL.
     *
     * @param  string  $base64Data
     * @param  int  $employeeId
     * @return \Illuminate\Http\UploadedFile|null
     */
    private function savePhotoFromBase64(string $base64Data, int $employeeId): ?\Illuminate\Http\UploadedFile
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

            $tmpFile = tempnam(sys_get_temp_dir(), 'face_');
            if ($tmpFile === false) {
                return null;
            }

            file_put_contents($tmpFile, $decoded);

            return new \Illuminate\Http\UploadedFile(
                $tmpFile,
                'enrollment_' . $employeeId . '.jpg',
                'image/jpeg',
                null,
                true
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
