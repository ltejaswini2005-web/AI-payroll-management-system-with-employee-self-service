<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Support\Carbon;

class HRDataService
{
    /**
     * Build comprehensive HR context data for the authenticated user.
     *
     * @param  User  $user
     * @return array{
     *     employee: ?array,
     *     leaves: array,
     *     attendance: array,
     *     payroll: ?array,
     *     provident_fund: ?array,
     *     holidays: array,
     *     announcements: array,
     *     context_text: string,
     *     is_admin: bool
     * }
     */
    public function getHRContext(User $user): array
    {
        $employee = $user->employee()
            ->with([
                'department',
                'designation',
                'salaryGrade',
                'leaveBalances.leaveCategory',
                'leaveApplications.leaveCategory',
                'attendanceLogs' => fn ($q) => $q->latest('attendance_date')->limit(30),
                'payrollItems' => fn ($q) => $q->latest('id')->limit(3),
                'providentFund',
            ])
            ->first();

        if (!$employee && $user->email) {
            $employee = Employee::where('official_email', $user->email)
                ->orWhere('personal_email', $user->email)
                ->with([
                    'department',
                    'designation',
                    'salaryGrade',
                    'leaveBalances.leaveCategory',
                    'leaveApplications.leaveCategory',
                    'attendanceLogs' => fn ($q) => $q->latest('attendance_date')->limit(30),
                    'payrollItems' => fn ($q) => $q->latest('id')->limit(3),
                    'providentFund',
                ])
                ->first();
        }

        $isAdmin = $user->hasRole('super-admin') || $user->hasRole('hr-manager');
        $systemStats = null;
        if ($isAdmin) {
            $systemStats = [
                'total_employees' => Employee::count(),
                'active_employees' => Employee::where('employment_status', 'active')->count(),
                'today_attendance' => \App\Models\AttendanceLog::whereDate('attendance_date', now()->toDateString())->count(),
            ];
        }

        $employeeData = null;
        $leavesData = ['balances' => [], 'recent_applications' => []];
        $attendanceData = ['summary' => [], 'recent_logs' => []];
        $payrollData = null;
        $pfData = null;

        if ($employee) {
            $employeeData = [
                'id' => $employee->id,
                'name' => trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')),
                'employee_code' => $employee->employee_code,
                'email' => $employee->official_email ?? $user->email,
                'department' => $employee->department?->name ?? 'General',
                'designation' => $employee->designation?->name ?? 'Staff',
                'joining_date' => $employee->joining_date ? Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A',
                'status' => ucfirst($employee->employment_status ?? 'Active'),
            ];

            // 1. Leave Balances & Recent Applications
            if ($employee->leaveBalances) {
                foreach ($employee->leaveBalances as $balance) {
                    $categoryName = $balance->leaveCategory?->name ?? 'General Leave';
                    $leavesData['balances'][] = [
                        'category' => $categoryName,
                        'allocated' => (float) $balance->allocated,
                        'availed' => (float) $balance->availed,
                        'remaining' => (float) $balance->closing_balance,
                    ];
                }
            }

            if ($employee->leaveApplications) {
                $recentLeaves = $employee->leaveApplications->sortByDesc('created_at')->take(5);
                foreach ($recentLeaves as $app) {
                    $leavesData['recent_applications'][] = [
                        'category' => $app->leaveCategory?->name ?? 'Leave',
                        'start_date' => Carbon::parse($app->start_date)->format('d M Y'),
                        'end_date' => Carbon::parse($app->end_date)->format('d M Y'),
                        'days' => (float) $app->total_days,
                        'status' => ucfirst($app->status),
                        'reason' => $app->reason,
                    ];
                }
            }

            // 2. Attendance Summary (Last 30 records)
            $logs = $employee->attendanceLogs ?? collect();
            $presentCount = $logs->where('status', 'present')->count();
            $lateCount = $logs->where('status', 'late')->count();
            $halfDayCount = $logs->where('status', 'half_day')->count();
            $absentCount = $logs->where('status', 'absent')->count();
            $totalWorkedMinutes = $logs->sum('worked_minutes');
            $totalWorkedHours = round($totalWorkedMinutes / 60, 1);

            $attendanceData['summary'] = [
                'total_tracked_days' => $logs->count(),
                'present_days' => $presentCount,
                'late_days' => $lateCount,
                'half_days' => $halfDayCount,
                'absent_days' => $absentCount,
                'total_worked_hours' => $totalWorkedHours,
            ];

            foreach ($logs->take(7) as $log) {
                $attendanceData['recent_logs'][] = [
                    'date' => Carbon::parse($log->attendance_date)->format('d M Y'),
                    'status' => ucfirst($log->status),
                    'check_in' => $log->check_in_at ? Carbon::parse($log->check_in_at)->format('h:i A') : 'N/A',
                    'check_out' => $log->check_out_at ? Carbon::parse($log->check_out_at)->format('h:i A') : 'N/A',
                    'worked_hours' => round(($log->worked_minutes ?? 0) / 60, 1) . ' hrs',
                ];
            }

            // 3. Most Recent Payroll Item
            $latestPayroll = $employee->payrollItems?->first();
            if ($latestPayroll) {
                $payrollData = [
                    'pay_frequency' => ucfirst($latestPayroll->pay_frequency ?? 'Monthly'),
                    'basic_salary' => (float) $latestPayroll->basic_salary,
                    'allowances' => (float) $latestPayroll->allowance_total,
                    'bonuses' => (float) $latestPayroll->bonus_total,
                    'pf_deduction' => (float) $latestPayroll->provident_fund_deduction,
                    'tax_deduction' => (float) $latestPayroll->tax_deduction,
                    'other_deductions' => (float) ($latestPayroll->loan_deduction + $latestPayroll->other_deduction),
                    'total_deductions' => (float) $latestPayroll->total_deduction,
                    'net_payable' => (float) $latestPayroll->net_payable,
                    'payment_status' => ucfirst($latestPayroll->payment_status ?? 'Pending'),
                ];
            }

            // 4. Provident Fund Summary
            if ($employee->providentFund) {
                $pf = $employee->providentFund;
                $pfData = [
                    'account_number' => $pf->account_number ?? 'PF-' . $employee->employee_code,
                    'employee_contribution_rate' => $pf->employee_contribution_rate . '%',
                    'employer_contribution_rate' => $pf->employer_contribution_rate . '%',
                    'status' => ucfirst($pf->status ?? 'Active'),
                ];
            }
        }

        // 5. Upcoming Holidays
        $upcomingHolidays = Holiday::query()
            ->where('holiday_date', '>=', now()->toDateString())
            ->orderBy('holiday_date', 'asc')
            ->limit(5)
            ->get()
            ->map(fn ($h) => [
                'name' => $h->name,
                'date' => Carbon::parse($h->holiday_date)->format('d M Y (l)'),
                'is_optional' => (bool) $h->is_optional,
            ])
            ->toArray();

        // 6. Active Announcements
        $announcements = Announcement::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($a) => [
                'title' => $a->title,
                'content' => $a->content,
                'date' => Carbon::parse($a->created_at)->format('d M Y'),
            ])
            ->toArray();

        // 7. Structured Text Representation for AI Context
        $contextText = $this->buildContextText(
            $employeeData,
            $leavesData,
            $attendanceData,
            $payrollData,
            $pfData,
            $upcomingHolidays,
            $announcements,
            $isAdmin
        );

        return [
            'employee' => $employeeData,
            'leaves' => $leavesData,
            'attendance' => $attendanceData,
            'payroll' => $payrollData,
            'provident_fund' => $pfData,
            'holidays' => $upcomingHolidays,
            'announcements' => $announcements,
            'system_stats' => $systemStats,
            'context_text' => $contextText,
            'is_admin' => $isAdmin,
        ];
    }

    /**
     * Format prompt context text for AI LLM.
     */
    private function buildContextText(
        ?array $emp,
        array $leaves,
        array $att,
        ?array $payroll,
        ?array $pf,
        array $holidays,
        array $announcements,
        bool $isAdmin
    ): string {
        $lines = [];
        $lines[] = "--- CURRENT AUTHENTICATED USER & HR CONTEXT ---";

        if ($emp) {
            $lines[] = "EMPLOYEE: {$emp['name']} (Code: {$emp['employee_code']})";
            $lines[] = "DEPARTMENT: {$emp['department']} | DESIGNATION: {$emp['designation']} | STATUS: {$emp['status']}";
            $lines[] = "JOINING DATE: {$emp['joining_date']}";
        } else {
            $lines[] = "USER: Administrative / Non-employee user profile.";
        }

        // Leave Balances
        if (!empty($leaves['balances'])) {
            $lines[] = "\nLEAVE BALANCES:";
            foreach ($leaves['balances'] as $b) {
                $lines[] = "- {$b['category']}: Remaining: {$b['remaining']} days (Allocated: {$b['allocated']}, Used: {$b['availed']})";
            }
        }

        // Recent Leaves
        if (!empty($leaves['recent_applications'])) {
            $lines[] = "\nRECENT LEAVE REQUESTS:";
            foreach ($leaves['recent_applications'] as $r) {
                $lines[] = "- {$r['category']} from {$r['start_date']} to {$r['end_date']} ({$r['days']} days) - Status: {$r['status']}";
            }
        }

        // Attendance Summary
        if (!empty($att['summary'])) {
            $s = $att['summary'];
            $lines[] = "\nATTENDANCE SUMMARY (Recent):";
            $lines[] = "- Present: {$s['present_days']} days | Late: {$s['late_days']} days | Half-days: {$s['half_days']} days | Total Logged Hours: {$s['total_worked_hours']} hrs";
            if (!empty($att['recent_logs'])) {
                $lastLog = $att['recent_logs'][0];
                $lines[] = "- Latest Entry: {$lastLog['date']} [Status: {$lastLog['status']}, In: {$lastLog['check_in']}, Out: {$lastLog['check_out']}]";
            }
        }

        // Payroll / Payslip
        if ($payroll) {
            $lines[] = "\nLATEST PAYSLIP BREAKDOWN:";
            $lines[] = "- Basic Salary: {$payroll['basic_salary']}";
            $lines[] = "- Allowances: {$payroll['allowances']}";
            $lines[] = "- Bonuses: {$payroll['bonuses']}";
            $lines[] = "- Deductions: Total {$payroll['total_deductions']} (PF: {$payroll['pf_deduction']}, Tax: {$payroll['tax_deduction']})";
            $lines[] = "- Net Payable Salary: {$payroll['net_payable']}";
            $lines[] = "- Status: {$payroll['payment_status']}";
        }

        // Provident Fund
        if ($pf) {
            $lines[] = "\nPROVIDENT FUND (PF):";
            $lines[] = "- Account: {$pf['account_number']} | Employee Rate: {$pf['employee_contribution_rate']} | Employer Rate: {$pf['employer_contribution_rate']}";
        }

        // Holidays
        if (!empty($holidays)) {
            $lines[] = "\nUPCOMING HOLIDAYS:";
            foreach ($holidays as $h) {
                $lines[] = "- {$h['name']} on {$h['date']}" . ($h['is_optional'] ? " (Optional)" : "");
            }
        }

        // Announcements
        if (!empty($announcements)) {
            $lines[] = "\nCOMPANY ANNOUNCEMENTS:";
            foreach ($announcements as $a) {
                $lines[] = "- {$a['title']} ({$a['date']}): {$a['content']}";
            }
        }

        $lines[] = "\n--- STRICT SECURITY POLICIES ---";
        $lines[] = "1. You are talking directly with the above authenticated employee.";
        $lines[] = "2. PRIVACY RULE: You must ONLY answer queries about THIS user's records. If the user asks for another employee's salary, attendance, phone, or private data, politely refuse citing HR data privacy policies.";
        $lines[] = "3. Always be polite, professional, concise, and helpful. Use clear markdown formatting (bullet points, bold text).";

        return implode("\n", $lines);
    }
}
