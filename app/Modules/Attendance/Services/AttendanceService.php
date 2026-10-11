<?php

namespace App\Modules\Attendance\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private readonly AttendanceRepository $attendanceRepository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function addManualLog(int $employeeId, array $payload, ?int $approvedBy = null, string $sourcePrefix = 'manual'): AttendanceLog
    {
        $attendanceDate = (string) $payload['attendance_date'];
        $entryType = (string) $payload['entry_type'];
        $entryTime = (string) $payload['entry_time'];
        $entryAt = $this->parseEntryDateTime($attendanceDate, $entryTime);
        $checkInAt = $entryType === 'checkin' ? $entryAt : null;
        $checkOutAt = $entryType === 'checkout' ? $entryAt : null;
        $source = substr($sourcePrefix . '-' . $entryType, 0, 40);

        return DB::transaction(function () use ($employeeId, $attendanceDate, $checkInAt, $checkOutAt, $source, $payload, $approvedBy, $entryType): AttendanceLog {
            $existing = AttendanceLog::query()
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->first();

            if ($existing) {
                if ($entryType === 'checkout') {
                    $existing->check_out_at = $checkOutAt;
                    if ($existing->check_in_at) {
                        $checkIn = Carbon::parse($existing->check_in_at);
                        $existing->worked_minutes = max(0, (int) $checkIn->diffInMinutes($checkOutAt));
                    }
                } else {
                    $existing->check_in_at = $checkInAt;
                }
                $existing->source = $source;
                if (!empty($payload['remarks'])) {
                    $existing->remarks = $payload['remarks'];
                }
                if ($approvedBy) {
                    $existing->approved_by = $approvedBy;
                    $existing->approved_at = now();
                }
                $existing->save();
                return $existing;
            }

            return $this->attendanceRepository->create([
                'employee_id' => $employeeId,
                'attendance_date' => $attendanceDate,
                'check_in_at' => $checkInAt,
                'check_out_at' => $checkOutAt,
                'worked_minutes' => 0,
                'status' => 'present',
                'source' => $source,
                'remarks' => $payload['remarks'] ?? null,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedBy ? now() : null,
            ]);
        });
    }

    /**
     * @return array{status: 'recorded'|'invalid_action'|'completed', attendanceLog: AttendanceLog, timestamp: ?Carbon}
     */
    public function recordFaceAttendance(int $employeeId, string $action, ?string $remarks = null): array
    {
        return DB::transaction(function () use ($employeeId, $action, $remarks): array {
            Employee::query()
                ->whereKey($employeeId)
                ->lockForUpdate()
                ->firstOrFail();

            $now = now();
            $attendanceDate = $now->toDateString();
            $attendanceLog = AttendanceLog::query()
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($attendanceLog?->check_in_at && $attendanceLog->check_out_at) {
                return [
                    'status' => 'completed',
                    'attendanceLog' => $attendanceLog,
                    'timestamp' => null,
                ];
            }

            $expectedAction = $attendanceLog?->check_in_at ? 'checkout' : 'checkin';
            if ($action !== $expectedAction) {
                return [
                    'status' => 'invalid_action',
                    'attendanceLog' => $attendanceLog ?? new AttendanceLog(),
                    'timestamp' => null,
                ];
            }

            if (! $attendanceLog) {
                $attendanceLog = new AttendanceLog([
                    'employee_id' => $employeeId,
                    'attendance_date' => $attendanceDate,
                    'worked_minutes' => 0,
                    'status' => 'present',
                ]);
            }

            if ($action === 'checkin') {
                $attendanceLog->check_in_at = $now;
            } else {
                $attendanceLog->check_out_at = $now;
                $attendanceLog->worked_minutes = max(
                    0,
                    (int) Carbon::parse($attendanceLog->check_in_at)->diffInMinutes($now)
                );
            }

            $attendanceLog->source = 'face-' . $action;
            if ($remarks !== null) {
                $attendanceLog->remarks = $remarks;
            }
            $attendanceLog->save();

            return [
                'status' => 'recorded',
                'attendanceLog' => $attendanceLog,
                'timestamp' => $now,
            ];
        });
    }

    public function isEntryDateTimeValid(string $attendanceDate, string $entryTime): bool
    {
        $value = trim($attendanceDate . ' ' . trim($entryTime));
        $formats = ['Y-m-d H:i', 'Y-m-d h:i A', 'Y-m-d h:i a'];

        foreach ($formats as $format) {
            try {
                Carbon::createFromFormat($format, $value);
                return true;
            } catch (\Throwable) {
                // Try next format.
            }
        }

        return false;
    }
    // Parse the provided attendance date and entry time into a Carbon instance, trying multiple formats to accommodate different time input styles.
    private function parseEntryDateTime(string $attendanceDate, string $entryTime): Carbon
    {
        $value = trim($attendanceDate . ' ' . trim($entryTime));
        $formats = ['Y-m-d H:i', 'Y-m-d h:i A', 'Y-m-d h:i a'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
 
            }
        }

        return Carbon::parse($value);
    }
}
