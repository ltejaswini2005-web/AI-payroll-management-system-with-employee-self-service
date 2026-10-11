<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeProvidentFund;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\ProvidentFundClaim;
use App\Models\ProvidentFundTransaction;
use App\Models\SalaryGrade;
use App\Models\SalaryTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollAndProvidentFundSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Company Settings
        SystemSetting::query()->updateOrCreate(
            ['key' => 'company_name'],
            ['group_name' => 'general', 'value' => 'ABC Technologies Pvt. Ltd.', 'type' => 'string', 'autoload' => 1]
        );
        SystemSetting::query()->updateOrCreate(
            ['key' => 'company_address'],
            ['group_name' => 'general', 'value' => '#123, Tech Park, Bengaluru - 560001', 'type' => 'string', 'autoload' => 1]
        );
        SystemSetting::query()->updateOrCreate(
            ['key' => 'company_website'],
            ['group_name' => 'general', 'value' => 'www.abctech.com', 'type' => 'string', 'autoload' => 1]
        );

        // 2. Department CSE
        $dept = Department::query()->firstOrCreate(
            ['name' => 'CSE'],
            ['description' => 'Computer Science & Engineering', 'is_active' => true]
        );

        // 3. Designation Software Engineer
        $desig = Designation::query()->firstOrCreate(
            ['name' => 'Software Engineer'],
            ['department_id' => $dept->id, 'code' => 'SE', 'description' => 'Software Development', 'is_active' => true]
        );

        // 4. Salary Grade
        $salaryGrade = SalaryGrade::query()->firstOrCreate(
            ['grade_code' => 'SE-L1'],
            [
                'grade_name' => 'Software Engineer Level 1',
                'band_name' => 'Engineering Band A',
                'min_salary' => 35000,
                'max_salary' => 60000,
                'description' => 'Standard engineering grade',
                'is_active' => true,
            ]
        );

        // 5. Salary Template
        $salaryTemplate = SalaryTemplate::query()->firstOrCreate(
            ['code' => 'TPL-SE-STD'],
            [
                'name' => 'Software Engineer Standard Template',
                'pay_frequency' => 'monthly',
                'basic_salary' => 25000,
                'house_rent' => 10000,
                'conveyance_allowance' => 2000,
                'medical_allowance' => 1500,
                'other_allowance' => 3500,
                'provident_fund_percent' => 12.00,
                'tax_percent' => 9.52,
                'is_active' => true,
            ]
        );

        // 6. Employee Varsha (Code 12345)
        $employee = Employee::query()->where('employee_code', '12345')->first();
        if (! $employee) {
            $employee = Employee::query()->create([
                'employee_code' => '12345',
                'first_name' => 'Varsha',
                'last_name' => '',
                'date_of_birth' => '1998-05-14',
                'phone' => '9876543210',
                'work_email' => 'varsha@abctech.com',
                'date_of_joining' => '2023-06-15',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
                'department_id' => $dept->id,
                'designation_id' => $desig->id,
                'salary_grade_id' => $salaryGrade->id,
                'tax_id' => 'ABCPV1234F',
            ]);
        } else {
            $employee->update([
                'first_name' => 'Varsha',
                'last_name' => '',
                'department_id' => $dept->id,
                'designation_id' => $desig->id,
                'salary_grade_id' => $salaryGrade->id,
                'date_of_joining' => '2023-06-15',
                'tax_id' => 'ABCPV1234F',
            ]);
        }

        // Also update employee 1 if different, so either user sees rich data
        $firstEmployee = Employee::query()->find(1);
        if ($firstEmployee && $firstEmployee->id !== $employee->id) {
            $firstEmployee->update([
                'department_id' => $dept->id,
                'designation_id' => $desig->id,
                'salary_grade_id' => $salaryGrade->id,
                'tax_id' => 'ABCPV1234F',
                'date_of_joining' => $firstEmployee->date_of_joining ?: '2023-06-15',
            ]);
        }

        // 7. Bank Account
        EmployeeBankAccount::query()->updateOrCreate(
            ['employee_id' => $employee->id, 'account_number' => '501001234567'],
            [
                'bank_name' => 'HDFC Bank',
                'branch_name' => 'Tech Park, Bengaluru',
                'account_holder_name' => 'Varsha',
                'routing_number' => 'HDFC0001234',
                'account_type' => 'Savings',
                'is_primary' => true,
            ]
        );

        // 8. Generate Payroll Runs & Items for 2025 & 2026 months
        $admin = User::first();
        $adminId = $admin?->id ?? 1;

        $months = [
            ['year' => 2025, 'month' => 9, 'label' => 'September 2025', 'start' => '2025-09-01', 'end' => '2025-09-30', 'pay' => '2025-09-30'],
            ['year' => 2025, 'month' => 8, 'label' => 'August 2025', 'start' => '2025-08-01', 'end' => '2025-08-31', 'pay' => '2025-08-31'],
            ['year' => 2025, 'month' => 7, 'label' => 'July 2025', 'start' => '2025-07-01', 'end' => '2025-07-31', 'pay' => '2025-07-31'],
            ['year' => 2025, 'month' => 6, 'label' => 'June 2025', 'start' => '2025-06-01', 'end' => '2025-06-30', 'pay' => '2025-06-30'],
            ['year' => 2025, 'month' => 5, 'label' => 'May 2025', 'start' => '2025-05-01', 'end' => '2025-05-31', 'pay' => '2025-05-31'],
            ['year' => 2025, 'month' => 4, 'label' => 'April 2025', 'start' => '2025-04-01', 'end' => '2025-04-30', 'pay' => '2025-04-30'],
            ['year' => 2025, 'month' => 3, 'label' => 'March 2025', 'start' => '2025-03-01', 'end' => '2025-03-31', 'pay' => '2025-03-31'],
            ['year' => 2025, 'month' => 2, 'label' => 'February 2025', 'start' => '2025-02-01', 'end' => '2025-02-28', 'pay' => '2025-02-28'],
            ['year' => 2025, 'month' => 1, 'label' => 'January 2025', 'start' => '2025-01-01', 'end' => '2025-01-31', 'pay' => '2025-01-31'],
            ['year' => 2025, 'month' => 10, 'label' => 'October 2025', 'start' => '2025-10-01', 'end' => '2025-10-31', 'pay' => '2025-10-31'],
            ['year' => 2025, 'month' => 11, 'label' => 'November 2025', 'start' => '2025-11-01', 'end' => '2025-11-30', 'pay' => '2025-11-30'],
            ['year' => 2025, 'month' => 12, 'label' => 'December 2025', 'start' => '2025-12-01', 'end' => '2025-12-31', 'pay' => '2025-12-31'],
        ];

        foreach ($months as $m) {
            $run = PayrollRun::query()->firstOrCreate(
                [
                    'pay_frequency' => 'monthly',
                    'period_start' => $m['start'],
                    'period_end' => $m['end'],
                ],
                [
                    'payroll_year' => $m['year'],
                    'payroll_month' => $m['month'],
                    'period_label' => $m['label'],
                    'pay_date' => $m['pay'],
                    'status' => 'completed',
                    'gross_total' => 42000,
                    'deduction_total' => 9700,
                    'net_total' => 32300,
                    'processed_by' => $adminId,
                    'processed_at' => Carbon::parse($m['pay'])->setTime(10, 45),
                ]
            );

            // Payroll item for Varsha
            PayrollItem::query()->updateOrCreate(
                [
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'pay_frequency' => 'monthly',
                    'basic_salary' => 25000,
                    'allowance_total' => 17000, // 10000 HRA + 2000 Conveyance + 1500 Medical + 3500 Special
                    'bonus_total' => 0,
                    'loan_deduction' => 2000,
                    'other_deduction' => 700, // 500 ESI + 200 PT
                    'provident_fund_deduction' => 3000,
                    'tax_deduction' => 4000,
                    'total_deduction' => 9700,
                    'net_payable' => 32300,
                    'payment_status' => 'paid',
                    'payment_reference' => 'PAY-TXN-' . $m['year'] . str_pad($m['month'], 2, '0', STR_PAD_LEFT) . '-' . $employee->id,
                ]
            );
        }

        // 9. Employee Provident Fund Record
        $pf = EmployeeProvidentFund::query()->updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'uan_number' => '101234567890',
                'pf_account_number' => 'KN/BNG/0012345/000/0012345',
                'employee_contribution_percent' => 12.00,
                'employer_contribution_percent' => 12.00,
                'opening_balance' => 70800.00,
                'effective_from' => '2023-07-01',
                'kyc_status' => 'Verified',
                'nominee_name' => 'Suresh Kumar',
                'nominee_relation' => 'Father',
                'interest_rate_percent' => 8.25,
                'current_balance' => 142800.00,
                'pension_eps_balance' => 25400.00,
            ]
        );

        // 10. PF Monthly Passbook Transactions (12 months leading up to September 2025)
        ProvidentFundTransaction::query()->where('employee_id', $employee->id)->delete();

        $runningBal = 70800.00;
        $monthlyBasic = 25000.00;
        $eeShare = 3000.00; // 12%
        $erEpf = 918.00;    // 3.67%
        $erEps = 2082.00;   // 8.33%
        $totalDep = $eeShare + $erEpf; // EPF balance increases by EE + ER EPF

        $passbookMonths = [
            ['year' => 2024, 'month' => 10, 'label' => 'October 2024', 'date' => '2024-11-05'],
            ['year' => 2024, 'month' => 11, 'label' => 'November 2024', 'date' => '2024-12-05'],
            ['year' => 2024, 'month' => 12, 'label' => 'December 2024', 'date' => '2025-01-05'],
            ['year' => 2025, 'month' => 1, 'label' => 'January 2025', 'date' => '2025-02-05'],
            ['year' => 2025, 'month' => 2, 'label' => 'February 2025', 'date' => '2025-03-05'],
            ['year' => 2025, 'month' => 3, 'label' => 'March 2025', 'date' => '2025-04-05'],
            ['year' => 2025, 'month' => 4, 'label' => 'April 2025', 'date' => '2025-05-05'],
            ['year' => 2025, 'month' => 5, 'label' => 'May 2025', 'date' => '2025-06-05'],
            ['year' => 2025, 'month' => 6, 'label' => 'June 2025', 'date' => '2025-07-05'],
            ['year' => 2025, 'month' => 7, 'label' => 'July 2025', 'date' => '2025-08-05'],
            ['year' => 2025, 'month' => 8, 'label' => 'August 2025', 'date' => '2025-09-05'],
            ['year' => 2025, 'month' => 9, 'label' => 'September 2025', 'date' => '2025-10-05'],
        ];

        foreach ($passbookMonths as $pm) {
            $runningBal += ($eeShare + $erEpf);
            ProvidentFundTransaction::query()->create([
                'employee_id' => $employee->id,
                'payroll_run_id' => null,
                'transaction_date' => $pm['date'],
                'transaction_type' => 'monthly_contribution',
                'employee_contribution' => $eeShare,
                'employer_contribution' => $erEpf + $erEps,
                'withdrawal_amount' => 0,
                'adjustment_amount' => 0,
                'balance_after' => round($runningBal, 2),
                'reference_no' => 'PF-DEP-' . $pm['year'] . str_pad($pm['month'], 2, '0', STR_PAD_LEFT) . '-' . $employee->id,
                'reason' => $pm['label'] . ' Monthly PF Contribution',
                'comments' => 'Wage: ₹' . number_format($monthlyBasic) . ' | EE: ₹' . number_format($eeShare) . ' | ER EPF: ₹' . number_format($erEpf) . ' | ER EPS: ₹' . number_format($erEps),
                'recorded_by' => $adminId,
            ]);
        }

        // 11. Sample PF Claims
        ProvidentFundClaim::query()->where('employee_id', $employee->id)->delete();

        ProvidentFundClaim::query()->create([
            'employee_id' => $employee->id,
            'claim_reference' => 'PF-CLM-2025-0812',
            'claim_type' => 'Form 31 (PF Advance)',
            'purpose' => 'Illness / Medical Treatment for Family',
            'amount' => 25000.00,
            'status' => 'Settled',
            'submission_date' => '2025-04-10',
            'approved_date' => '2025-04-18',
            'remarks' => 'Amount credited to HDFC Bank A/C ending 4567.',
        ]);

        ProvidentFundClaim::query()->create([
            'employee_id' => $employee->id,
            'claim_reference' => 'PF-CLM-2025-0941',
            'claim_type' => 'Form 31 (PF Advance)',
            'purpose' => 'Higher Education Course Fee',
            'amount' => 20000.00,
            'status' => 'Approved',
            'submission_date' => '2025-09-12',
            'approved_date' => '2025-09-20',
            'remarks' => 'Approved by Regional PF Commissioner. Under dispatch.',
        ]);
    }
}
