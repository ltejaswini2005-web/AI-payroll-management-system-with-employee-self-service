<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    // Attendance Report
    public function attendance(Request $request): View
    {
        $employees = Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('first_name')
            ->get();

        $filters = [
            'employee_id' => (int) $request->input('employee_id', 0),
            'status'      => (string) $request->input('status', ''),
            'from_date'   => (string) $request->input('from_date', now()->startOfMonth()->format('Y-m-d')),
            'to_date'     => (string) $request->input('to_date', now()->format('Y-m-d')),
            'per_page'    => max(10, min(100, (int) $request->input('per_page', 20))),
        ];

        $logs = AttendanceLog::query()
            ->with([
                'employee:id,employee_code,first_name,last_name,department_id,designation_id',
                'employee.department:id,name',
                'employee.designation:id,name',
            ])
            ->when($filters['employee_id'] > 0, fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when($filters['status'] !== '', fn ($q) => $q->where('status', $filters['status']))
            ->whereDate('attendance_date', '>=', $filters['from_date'])
            ->whereDate('attendance_date', '<=', $filters['to_date'])
            ->orderByDesc('attendance_date')
            ->orderBy('employee_id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('hr.reports.attendance', [
            'logs'      => $logs,
            'employees' => $employees,
            'filters'   => $filters,
            'statuses'  => ['present', 'absent', 'late', 'half_day', 'leave', 'holiday'],
        ]);
    }

    // Payroll Report
    public function payroll(Request $request): View
    {
        $employees = Employee::query()
            ->select(['id', 'employee_code', 'first_name', 'last_name'])
            ->orderBy('first_name')
            ->get();

        $payrollRuns = PayrollRun::query()
            ->select(['id', 'period_label', 'payroll_year', 'payroll_month', 'period_start', 'status'])
            ->orderByDesc('period_start')
            ->get();

        $filters = [
            'employee_id'    => (int) $request->input('employee_id', 0),
            'payroll_run_id' => (int) $request->input('payroll_run_id', 0),
            'payment_status' => (string) $request->input('payment_status', ''),
            'per_page'       => max(10, min(100, (int) $request->input('per_page', 20))),
        ];

        $items = PayrollItem::query()
            ->with([
                'employee:id,employee_code,first_name,last_name,department_id,designation_id',
                'employee.department:id,name',
                'employee.designation:id,name',
                'payrollRun:id,period_label,payroll_year,payroll_month,pay_date,status,period_start',
            ])
            ->when($filters['employee_id'] > 0, fn ($q) => $q->where('employee_id', $filters['employee_id']))
            ->when($filters['payroll_run_id'] > 0, fn ($q) => $q->where('payroll_run_id', $filters['payroll_run_id']))
            ->when($filters['payment_status'] !== '', fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->orderByDesc('payroll_run_id')
            ->orderBy('employee_id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('hr.reports.payroll', [
            'items'           => $items,
            'employees'       => $employees,
            'payrollRuns'     => $payrollRuns,
            'filters'         => $filters,
            'paymentStatuses' => ['pending', 'paid', 'cancelled'],
        ]);
    }

    // Employee Report
    public function employees(Request $request): View
    {
        $departments  = Department::query()->select(['id', 'name'])->orderBy('name')->get();
        $designations = Designation::query()->select(['id', 'name'])->orderBy('name')->get();

        $filters = [
            'department_id'     => (int) $request->input('department_id', 0),
            'designation_id'    => (int) $request->input('designation_id', 0),
            'employment_status' => (string) $request->input('employment_status', ''),
            'employment_type'   => (string) $request->input('employment_type', ''),
            'search'            => (string) $request->input('search', ''),
            'per_page'          => max(10, min(100, (int) $request->input('per_page', 20))),
        ];

        $employees = Employee::query()
            ->with([
                'department:id,name',
                'designation:id,name',
                'salaryGrade:id,grade_name,grade_code',
            ])
            ->select([
                'id', 'employee_code', 'first_name', 'last_name',
                'gender', 'phone', 'work_email',
                'employment_type', 'employment_status',
                'date_of_joining', 'department_id', 'designation_id', 'salary_grade_id',
            ])
            ->when($filters['department_id'] > 0, fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when($filters['designation_id'] > 0, fn ($q) => $q->where('designation_id', $filters['designation_id']))
            ->when($filters['employment_status'] !== '', fn ($q) => $q->where('employment_status', $filters['employment_status']))
            ->when($filters['employment_type'] !== '', fn ($q) => $q->where('employment_type', $filters['employment_type']))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', $term)
                          ->orWhere('last_name', 'like', $term)
                          ->orWhere('employee_code', 'like', $term)
                          ->orWhere('work_email', 'like', $term);
                });
            })
            ->orderBy('first_name')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('hr.reports.employees', [
            'employees'          => $employees,
            'departments'        => $departments,
            'designations'       => $designations,
            'filters'            => $filters,
            'employmentStatuses' => ['active', 'inactive', 'terminated', 'resigned', 'on_leave'],
            'employmentTypes'    => ['full_time', 'part_time', 'contract', 'intern'],
        ]);
    }
}
