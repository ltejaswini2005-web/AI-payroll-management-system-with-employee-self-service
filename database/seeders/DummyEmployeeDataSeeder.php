<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\LoanInstallment;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\SalaryGrade;
use App\Models\SalaryTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DummyEmployeeDataSeeder extends Seeder
{
    private const DEMO_NOTE = 'Dummy data for report and workflow testing.';

    /**
     * Seed five identifiable demo employees and related module data.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $departments = [
                'CSE' => $this->department('CSE', 'Computer Science and Engineering'),
                'Human Resources' => $this->department('Human Resources', 'Human Resources'),
                'Finance' => $this->department('Finance', 'Finance and Accounting'),
                'Operations' => $this->department('Operations', 'Business Operations'),
            ];

            $employees = [
                [
                    'employee_code' => 'EMP001',
                    'first_name' => 'Arjun',
                    'last_name' => 'Kumar',
                    'gender' => 'Male',
                    'date_of_birth' => '1996-03-12',
                    'phone' => '+91-90000-00001',
                    'work_email' => 'arjun.kumar@example.test',
                    'personal_email' => 'arjun.kumar.personal@example.test',
                    'marital_status' => 'single',
                    'date_of_joining' => '2022-06-13',
                    'department' => 'CSE',
                    'designation' => 'Software Engineer',
                    'designation_code' => 'SE',
                    'salary_grade_code' => 'DUMMY-ENG-1',
                    'salary_grade_name' => 'Demo Engineering Grade 1',
                    'salary' => 62000,
                ],
                [
                    'employee_code' => 'EMP002',
                    'first_name' => 'Priya',
                    'last_name' => 'Sharma',
                    'gender' => 'Female',
                    'date_of_birth' => '1994-11-08',
                    'phone' => '+91-90000-00002',
                    'work_email' => 'priya.sharma@example.test',
                    'personal_email' => 'priya.sharma.personal@example.test',
                    'marital_status' => 'married',
                    'date_of_joining' => '2021-09-20',
                    'department' => 'Human Resources',
                    'designation' => 'HR Specialist',
                    'designation_code' => 'HR-SPEC',
                    'salary_grade_code' => 'DUMMY-HR-2',
                    'salary_grade_name' => 'Demo HR Grade 2',
                    'salary' => 58000,
                ],
                [
                    'employee_code' => 'EMP003',
                    'first_name' => 'Rahul',
                    'last_name' => 'Patil',
                    'gender' => 'Male',
                    'date_of_birth' => '1993-07-24',
                    'phone' => '+91-90000-00003',
                    'work_email' => 'rahul.patil@example.test',
                    'personal_email' => 'rahul.patil.personal@example.test',
                    'marital_status' => 'single',
                    'date_of_joining' => '2020-02-10',
                    'department' => 'Finance',
                    'designation' => 'Finance Analyst',
                    'designation_code' => 'FIN-ANL',
                    'salary_grade_code' => 'DUMMY-FIN-3',
                    'salary_grade_name' => 'Demo Finance Grade 3',
                    'salary' => 68000,
                ],
                [
                    'employee_code' => 'EMP004',
                    'first_name' => 'Sneha',
                    'last_name' => 'Reddy',
                    'gender' => 'Female',
                    'date_of_birth' => '1997-01-17',
                    'phone' => '+91-90000-00004',
                    'work_email' => 'sneha.reddy@example.test',
                    'personal_email' => 'sneha.reddy.personal@example.test',
                    'marital_status' => 'single',
                    'date_of_joining' => '2023-04-03',
                    'department' => 'Operations',
                    'designation' => 'Operations Coordinator',
                    'designation_code' => 'OPS-COORD',
                    'salary_grade_code' => 'DUMMY-OPS-1',
                    'salary_grade_name' => 'Demo Operations Grade 1',
                    'salary' => 52000,
                ],
                [
                    'employee_code' => 'EMP005',
                    'first_name' => 'Kiran',
                    'last_name' => 'Mehta',
                    'gender' => 'Male',
                    'date_of_birth' => '1995-09-30',
                    'phone' => '+91-90000-00005',
                    'work_email' => 'kiran.mehta@example.test',
                    'personal_email' => 'kiran.mehta.personal@example.test',
                    'marital_status' => 'married',
                    'date_of_joining' => '2022-11-07',
                    'department' => 'CSE',
                    'designation' => 'Quality Assurance Engineer',
                    'designation_code' => 'QA-ENG',
                    'salary_grade_code' => 'DUMMY-ENG-2',
                    'salary_grade_name' => 'Demo Engineering Grade 2',
                    'salary' => 60000,
                ],
            ];

            $seededEmployees = [];
            foreach ($employees as $data) {
                $department = $departments[$data['department']];
                $designation = $this->designation($department, $data['designation'], $data['designation_code']);
                $salaryGrade = $this->salaryGrade($data);
                $salaryTemplate = $this->salaryTemplate($data);
                $employee = $this->employee($data, $department, $designation, $salaryGrade);

                $salaryAssignment = DB::table('employee_salary_templates')->where([
                    'employee_id' => $employee->id,
                    'salary_template_id' => $salaryTemplate->id,
                    'effective_from' => $data['date_of_joining'],
                ]);
                if (! $salaryAssignment->exists()) {
                    DB::table('employee_salary_templates')->insert([
                        'employee_id' => $employee->id,
                        'salary_template_id' => $salaryTemplate->id,
                        'effective_from' => $data['date_of_joining'],
                        'effective_to' => null,
                        'pay_frequency' => 'monthly',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $seededEmployees[$data['employee_code']] = [
                    'employee' => $employee,
                    'salary' => $data['salary'],
                    'salary_template' => $salaryTemplate,
                ];
            }

            $this->seedAttendance($seededEmployees);
            $this->seedLeaveApplications($seededEmployees);
            $this->seedPayroll($seededEmployees);
            $this->seedLoans($seededEmployees);
        });
    }

    private function department(string $name, string $description): Department
    {
        $department = Department::withTrashed()->where('name', $name)->first();
        if ($department?->trashed()) {
            throw new RuntimeException("Cannot seed department [{$name}] because it is soft-deleted.");
        }

        return $department ?? Department::query()->create([
            'name' => $name,
            'code' => 'DUMMY-' . strtoupper(substr(preg_replace('/[^a-z]/i', '', $name), 0, 12)),
            'description' => $description,
            'is_active' => true,
        ]);
    }

    private function designation(Department $department, string $name, string $code): Designation
    {
        $designation = Designation::withTrashed()
            ->where('department_id', $department->id)
            ->where('name', $name)
            ->first();
        if ($designation?->trashed()) {
            throw new RuntimeException("Cannot seed designation [{$name}] because it is soft-deleted.");
        }

        return $designation ?? Designation::query()->create([
            'department_id' => $department->id,
            'name' => $name,
            'code' => $code,
            'description' => self::DEMO_NOTE,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function salaryGrade(array $data): SalaryGrade
    {
        $grade = SalaryGrade::query()->where('grade_code', $data['salary_grade_code'])->first();
        if ($grade && $grade->grade_name !== $data['salary_grade_name']) {
            throw new RuntimeException("Salary grade code [{$data['salary_grade_code']}] is already in use.");
        }

        return $grade ?? SalaryGrade::query()->create([
            'grade_name' => $data['salary_grade_name'],
            'grade_code' => $data['salary_grade_code'],
            'band_name' => 'Demo ' . $data['department'],
            'min_salary' => $data['salary'],
            'max_salary' => $data['salary'],
            'description' => self::DEMO_NOTE,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function salaryTemplate(array $data): SalaryTemplate
    {
        $code = 'DUMMY-' . $data['employee_code'];
        $template = SalaryTemplate::withTrashed()->where('code', $code)->first();
        if ($template?->trashed()) {
            throw new RuntimeException("Cannot seed salary template [{$code}] because it is soft-deleted.");
        }
        if ($template && $template->name !== 'Demo Salary ' . $data['employee_code']) {
            throw new RuntimeException("Salary template code [{$code}] is already in use.");
        }

        return $template ?? SalaryTemplate::query()->create([
            'name' => 'Demo Salary ' . $data['employee_code'],
            'code' => $code,
            'pay_frequency' => 'monthly',
            'basic_salary' => $data['salary'],
            'house_rent' => round($data['salary'] * 0.4, 2),
            'medical_allowance' => 1500,
            'conveyance_allowance' => 2000,
            'other_allowance' => 2500,
            'provident_fund_percent' => 12,
            'tax_percent' => 5,
            'notes' => self::DEMO_NOTE,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function employee(array $data, Department $department, Designation $designation, SalaryGrade $salaryGrade): Employee
    {
        $byCode = Employee::withTrashed()->where('employee_code', $data['employee_code'])->first();
        $byEmail = Employee::withTrashed()->where('work_email', $data['work_email'])->first();
        if ($byCode && $byEmail && $byCode->id !== $byEmail->id) {
            throw new RuntimeException("Employee code and work email for [{$data['employee_code']}] belong to different records.");
        }

        $employee = $byCode ?? $byEmail;
        if ($employee) {
            if ($employee->employee_code !== $data['employee_code'] || $employee->work_email !== $data['work_email']) {
                throw new RuntimeException("Demo employee identifier conflict for [{$data['employee_code']}]; no employee data was changed.");
            }
            if ($employee->trashed()) {
                throw new RuntimeException("Demo employee [{$data['employee_code']}] is soft-deleted; it was not restored.");
            }

            return $employee;
        }

        return Employee::query()->create([
            'employee_code' => $data['employee_code'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'phone' => $data['phone'],
            'work_email' => $data['work_email'],
            'personal_email' => $data['personal_email'],
            'marital_status' => $data['marital_status'],
            'date_of_joining' => $data['date_of_joining'],
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'salary_grade_id' => $salaryGrade->id,
            'notes' => self::DEMO_NOTE,
        ]);
    }

    /**
     * @param array<string, array{employee: Employee, salary: int, salary_template: SalaryTemplate}> $employees
     */
    private function seedAttendance(array $employees): void
    {
        $workdays = [];
        $date = CarbonImmutable::today();
        while (count($workdays) < 5) {
            if ($date->isWeekday()) {
                $workdays[] = $date;
            }
            $date = $date->subDay();
        }

        foreach ($employees as $data) {
            foreach ($workdays as $index => $workday) {
                $checkIn = $workday->setTime(8, 50 + (($data['employee']->id + $index) % 20));
                $checkOut = $index === 0 && $workday->isToday()
                    ? null
                    : $workday->setTime(17, 0 + (($data['employee']->id + $index) % 30));

                $attendanceExists = AttendanceLog::query()
                    ->where('employee_id', $data['employee']->id)
                    ->whereDate('attendance_date', $workday->toDateString())
                    ->exists();

                if (! $attendanceExists) {
                    AttendanceLog::query()->create([
                        'employee_id' => $data['employee']->id,
                        'attendance_date' => $workday->toDateString(),
                        'check_in_at' => $checkIn,
                        'check_out_at' => $checkOut,
                        'worked_minutes' => $checkOut ? $checkIn->diffInMinutes($checkOut) : 0,
                        'status' => 'present',
                        'source' => 'web',
                        'remarks' => self::DEMO_NOTE,
                    ]);
                }
            }
        }
    }

    /**
     * @param array<string, array{employee: Employee, salary: int, salary_template: SalaryTemplate}> $employees
     */
    private function seedLeaveApplications(array $employees): void
    {
        $annualLeave = $this->leaveCategory('ANNUAL', 'Annual Leave', true);
        $sickLeave = $this->leaveCategory('SICK', 'Sick Leave', true);
        $personalLeave = $this->leaveCategory('PERSONAL', 'Personal Leave', false);
        $today = CarbonImmutable::today();
        $lastMonth = $today->subMonthNoOverflow();

        $applications = [
            ['EMP001', $annualLeave, $lastMonth->day(8), $lastMonth->day(9), 'approved', 'Family visit'],
            ['EMP002', $sickLeave, $lastMonth->day(14), $lastMonth->day(14), 'approved', 'Recovery day'],
            ['EMP003', $personalLeave, $today->addDays(7), $today->addDays(8), 'pending', 'Personal appointment'],
            ['EMP004', $annualLeave, $lastMonth->day(20), $lastMonth->day(21), 'rejected', 'Travel request'],
            ['EMP005', $annualLeave, $today->addDays(14), $today->addDays(16), 'pending', 'Planned family time'],
            ['EMP001', $sickLeave, $lastMonth->day(24), $lastMonth->day(24), 'approved', 'Medical appointment'],
        ];

        foreach ($applications as [$code, $category, $start, $end, $status, $reason]) {
            LeaveApplication::query()->firstOrCreate([
                'employee_id' => $employees[$code]['employee']->id,
                'leave_category_id' => $category->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'reason' => 'Dummy data: ' . $reason,
            ], [
                'total_days' => $start->diffInDays($end) + 1,
                'is_half_day' => false,
                'status' => $status,
                'approval_remarks' => $status === 'approved' ? 'Demo request approved.' : null,
            ]);
        }
    }

    private function leaveCategory(string $code, string $name, bool $paid): LeaveCategory
    {
        $category = LeaveCategory::withTrashed()
            ->where(fn ($query) => $query->where('code', $code)->orWhere('name', $name))
            ->first();
        if ($category?->trashed()) {
            throw new RuntimeException("Cannot seed leave category [{$code}] because it is soft-deleted.");
        }
        if ($category && ($category->code !== $code || $category->name !== $name)) {
            throw new RuntimeException("Leave category identifier conflict for [{$code}].");
        }

        return $category ?? LeaveCategory::query()->create([
            'name' => $name,
            'code' => $code,
            'is_paid' => $paid,
            'requires_attachment' => false,
            'description' => self::DEMO_NOTE,
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, array{employee: Employee, salary: int, salary_template: SalaryTemplate}> $employees
     */
    private function seedPayroll(array $employees): void
    {
        $periodStart = CarbonImmutable::today()->startOfMonth()->subMonthNoOverflow();
        $periodEnd = $periodStart->endOfMonth();
        $payDate = $periodEnd;

        $run = PayrollRun::query()->firstOrCreate([
            'pay_frequency' => 'monthly',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ], [
            'payroll_year' => $periodStart->year,
            'payroll_month' => $periodStart->month,
            'period_label' => $periodStart->format('F Y'),
            'pay_date' => $payDate->toDateString(),
            'status' => 'completed',
            'gross_total' => 0,
            'deduction_total' => 0,
            'net_total' => 0,
            'processed_at' => $payDate->setTime(10, 0),
        ]);

        foreach ($employees as $code => $data) {
            $basicSalary = $data['salary'];
            $allowanceTotal = round($basicSalary * 0.4, 2) + 1500 + 2000 + 2500;
            $gross = $basicSalary + $allowanceTotal;
            $providentFund = round($basicSalary * 0.12, 2);
            $tax = round($basicSalary * 0.05, 2);
            $loanDeduction = $code === 'EMP003' ? 2500 : 0;
            $otherDeduction = 200;
            $totalDeduction = $providentFund + $tax + $loanDeduction + $otherDeduction;

            PayrollItem::query()->firstOrCreate([
                'payroll_run_id' => $run->id,
                'employee_id' => $data['employee']->id,
            ], [
                'pay_frequency' => 'monthly',
                'basic_salary' => $basicSalary,
                'allowance_total' => $allowanceTotal,
                'bonus_total' => 0,
                'loan_deduction' => $loanDeduction,
                'other_deduction' => $otherDeduction,
                'provident_fund_deduction' => $providentFund,
                'tax_deduction' => $tax,
                'total_deduction' => $totalDeduction,
                'net_payable' => $gross - $totalDeduction,
                'payment_status' => 'paid',
                'payment_reference' => 'DUMMY-PAY-' . $periodStart->format('Ym') . '-' . $code,
            ]);
        }

        if ($run->wasRecentlyCreated) {
            $run->update([
                'gross_total' => $run->items()->sum(DB::raw('basic_salary + allowance_total + bonus_total')),
                'deduction_total' => $run->items()->sum('total_deduction'),
                'net_total' => $run->items()->sum('net_payable'),
            ]);
        }
    }

    /**
     * @param array<string, array{employee: Employee, salary: int, salary_template: SalaryTemplate}> $employees
     */
    private function seedLoans(array $employees): void
    {
        $activeLoan = $this->loan($employees['EMP003']['employee'], [
            'loan_reference' => 'DUMMY-LN-EMP003-01',
            'loan_type' => 'personal',
            'principal_amount' => 30000,
            'interest_rate_percent' => 0,
            'installment_count' => 12,
            'installment_amount' => 2500,
            'issued_date' => CarbonImmutable::today()->subMonths(8)->toDateString(),
            'first_installment_date' => CarbonImmutable::today()->subMonths(7)->startOfMonth()->toDateString(),
            'status' => EmployeeLoan::STATUS_ACTIVE,
            'remarks' => self::DEMO_NOTE,
        ]);
        $this->seedInstallments($activeLoan);

        $this->loan($employees['EMP002']['employee'], [
            'loan_reference' => 'DUMMY-LN-EMP002-01',
            'loan_type' => 'education',
            'principal_amount' => 45000,
            'interest_rate_percent' => 2.5,
            'installment_count' => 18,
            'installment_amount' => 2500,
            'issued_date' => CarbonImmutable::today()->toDateString(),
            'first_installment_date' => CarbonImmutable::today()->addMonth()->startOfMonth()->toDateString(),
            'status' => EmployeeLoan::STATUS_PENDING,
            'remarks' => self::DEMO_NOTE,
        ]);

        $this->loan($employees['EMP005']['employee'], [
            'loan_reference' => 'DUMMY-LN-EMP005-01',
            'loan_type' => 'medical',
            'principal_amount' => 20000,
            'interest_rate_percent' => 0,
            'installment_count' => 10,
            'installment_amount' => 2000,
            'issued_date' => CarbonImmutable::today()->toDateString(),
            'first_installment_date' => CarbonImmutable::today()->addMonth()->startOfMonth()->toDateString(),
            'status' => EmployeeLoan::STATUS_PENDING,
            'remarks' => self::DEMO_NOTE,
        ]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function loan(Employee $employee, array $attributes): EmployeeLoan
    {
        $loan = EmployeeLoan::query()->where('loan_reference', $attributes['loan_reference'])->first();
        if ($loan) {
            if ($loan->employee_id !== $employee->id) {
                throw new RuntimeException("Loan reference [{$attributes['loan_reference']}] is already in use.");
            }

            return $loan;
        }

        return EmployeeLoan::query()->create([
            ...$attributes,
            'employee_id' => $employee->id,
        ]);
    }

    private function seedInstallments(EmployeeLoan $loan): void
    {
        $firstDueDate = CarbonImmutable::parse($loan->first_installment_date);
        $today = CarbonImmutable::today();

        for ($number = 1; $number <= $loan->installment_count; $number++) {
            $dueDate = $firstDueDate->addMonths($number - 1);
            $isPaid = $dueDate->lt($today);

            LoanInstallment::query()->firstOrCreate([
                'employee_loan_id' => $loan->id,
                'installment_no' => $number,
            ], [
                'due_date' => $dueDate->toDateString(),
                'amount' => $loan->installment_amount,
                'paid_amount' => $isPaid ? $loan->installment_amount : 0,
                'paid_date' => $isPaid ? $dueDate->addDays(2)->toDateString() : null,
                'status' => $isPaid ? 'paid' : 'pending',
            ]);
        }
    }
}
