<?php

namespace App\Modules\Loans\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\LoanInstallment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Loan List — shows all loans (all statuses) for super-admin / hr-manager.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user?->hasPermission('loan.view'), 403);

        $loans = EmployeeLoan::with(['employee', 'approvedBy'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('employee_id'), fn ($q, $id) => $q->where('employee_id', $id))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $statusLabels = EmployeeLoan::$statusLabels;

        return view('hr.loans.index', compact('loans', 'statusLabels'));
    }

    /**
     * Add Loan form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user?->hasPermission('loan.create'), 403);

        $employees  = Employee::where('employment_status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'employee_code']);

        $loanTypes  = EmployeeLoan::$loanTypes;

        return view('hr.loans.create', compact('employees', 'loanTypes'));
    }

    /**
     * Store a new loan request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user?->hasPermission('loan.create'), 403);

        $validated = $request->validate([
            'employee_id'       => ['required', 'exists:employees,id'],
            'loan_type'         => ['required', 'string', 'max:50'],
            'principal_amount'  => ['required', 'numeric', 'min:1'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:360'],
            'interest_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'issued_date'       => ['required', 'date'],
            'first_installment_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $interestRate  = (float) ($validated['interest_rate_percent'] ?? 0);
        $principal     = (float) $validated['principal_amount'];
        $installments  = (int) $validated['installment_count'];

        // Simple EMI calculation (flat interest)
        if ($interestRate > 0) {
            $totalInterest = ($principal * $interestRate / 100) * ($installments / 12);
            $installmentAmount = round(($principal + $totalInterest) / $installments, 2);
        } else {
            $installmentAmount = round($principal / $installments, 2);
        }

        $loan = EmployeeLoan::create([
            'employee_id'           => $validated['employee_id'],
            'loan_type'             => $validated['loan_type'],
            'loan_reference'        => EmployeeLoan::generateReference(),
            'principal_amount'      => $principal,
            'interest_rate_percent' => $interestRate,
            'installment_count'     => $installments,
            'installment_amount'    => $installmentAmount,
            'issued_date'           => $validated['issued_date'],
            'first_installment_date' => $validated['first_installment_date'] ?? null,
            'status'                => EmployeeLoan::STATUS_PENDING,
            'remarks'               => $validated['remarks'] ?? null,
            'requested_by'          => $user->id,
        ]);

        return redirect()
            ->route('loans.index')
            ->with('success', "Loan request [{$loan->loan_reference}] submitted for approval.");
    }

    /**
     * Loan Approval page — shows only PENDING loans.
     */
    public function approvals(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user?->hasPermission('loan.approve'), 403);

        $pendingLoans = EmployeeLoan::with(['employee', 'requestedBy'])
            ->where('status', EmployeeLoan::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $loanTypes = EmployeeLoan::$loanTypes;

        return view('hr.loans.approvals', compact('pendingLoans', 'loanTypes'));
    }

    /**
     * Process approval or rejection of a pending loan.
     */
    public function processApproval(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user?->hasPermission('loan.approve'), 403);

        if ($loan->status !== EmployeeLoan::STATUS_PENDING) {
            return back()->with('error', 'Only pending loans can be processed.');
        }

        $validated = $request->validate([
            'action'           => ['required', 'in:approve,reject'],
            'approved_amount'  => ['nullable', 'numeric', 'min:1'],
            'approval_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['action'] === 'approve') {
            $approvedAmount = (float) ($validated['approved_amount'] ?? $loan->principal_amount);

            // Recalculate installment amount based on approved amount
            $interestRate = (float) $loan->interest_rate_percent;
            $installments = (int) $loan->installment_count;

            if ($interestRate > 0) {
                $totalInterest = ($approvedAmount * $interestRate / 100) * ($installments / 12);
                $installmentAmount = round(($approvedAmount + $totalInterest) / $installments, 2);
            } else {
                $installmentAmount = round($approvedAmount / $installments, 2);
            }

            $loan->update([
                'status'           => EmployeeLoan::STATUS_APPROVED,
                'approved_amount'  => $approvedAmount,
                'installment_amount' => $installmentAmount,
                'approved_by'      => $user->id,
                'approved_at'      => now(),
                'approval_remarks' => $validated['approval_remarks'] ?? null,
            ]);

            // Generate installment schedule if a first_installment_date is set
            if ($loan->first_installment_date && $loan->installments()->count() === 0) {
                $this->generateInstallmentSchedule($loan, $installmentAmount);
            }

            return redirect()
                ->route('loans.approvals')
                ->with('success', "Loan [{$loan->loan_reference}] approved successfully.");
        } else {
            $loan->update([
                'status'           => EmployeeLoan::STATUS_REJECTED,
                'approved_by'      => $user->id,
                'approved_at'      => now(),
                'approval_remarks' => $validated['approval_remarks'] ?? 'Rejected by approver.',
            ]);

            return redirect()
                ->route('loans.approvals')
                ->with('success', "Loan [{$loan->loan_reference}] has been rejected.");
        }
    }

    /**
     * Generate installment schedule for an approved loan.
     */
    private function generateInstallmentSchedule(EmployeeLoan $loan, float $installmentAmount): void
    {
        $dueDate = Carbon::parse($loan->first_installment_date);

        for ($i = 1; $i <= $loan->installment_count; $i++) {
            LoanInstallment::create([
                'employee_loan_id' => $loan->id,
                'installment_no'   => $i,
                'due_date'         => $dueDate->copy(),
                'amount'           => $installmentAmount,
                'paid_amount'      => 0,
                'paid_date'        => null,
                'status'           => 'pending',
            ]);
            $dueDate->addMonth();
        }
    }
}
