@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #fff3cd; color: #856404; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-check"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Loan Approvals</h4>
                <p class="text-muted mb-0 small">Review and approve or reject pending employee loan requests.</p>
            </div>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="icon-list"></i> All Loans
        </a>
    </div>

    @include('partials.flash')

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Pending Loan Requests</h5>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                {{ $pendingLoans->total() }} Pending
            </span>
        </div>
        <div class="card-body p-0">
            @forelse($pendingLoans as $loan)
                <div class="border-bottom px-4 py-4">
                    <div class="row align-items-start g-3">
                        {{-- Loan Info --}}
                        <div class="col-md-5">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                <span class="font-monospace fw-bold text-muted">{{ $loan->loan_reference }}</span>
                            </div>
                            <div class="fw-bold fs-6">{{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</div>
                            <small class="text-muted d-block">{{ $loan->employee?->employee_code }}</small>
                            <div class="mt-2">
                                <span class="badge bg-light text-dark border">
                                    {{ $loanTypes[$loan->loan_type] ?? ucfirst($loan->loan_type) }}
                                </span>
                            </div>
                        </div>

                        {{-- Loan Details --}}
                        <div class="col-md-4">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 py-1" style="width: 140px;">Requested Amount</td>
                                    <td class="fw-bold py-1">₹ {{ number_format($loan->principal_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Tenure</td>
                                    <td class="py-1">{{ $loan->installment_count }} months</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">EMI Amount</td>
                                    <td class="py-1">₹ {{ number_format($loan->installment_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Interest Rate</td>
                                    <td class="py-1">{{ $loan->interest_rate_percent }}%</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Request Date</td>
                                    <td class="py-1">{{ $loan->issued_date?->format('d-m-Y') }}</td>
                                </tr>
                                @if($loan->remarks)
                                    <tr>
                                        <td class="text-muted ps-0 py-1">Remarks</td>
                                        <td class="py-1 text-muted">{{ $loan->remarks }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>

                        {{-- Approval Actions --}}
                        <div class="col-md-3">
                            <form method="POST" action="{{ route('loans.approvals.process', $loan->id) }}" id="loan-form-{{ $loan->id }}">
                                @csrf
                                @method('PATCH')

                                <div class="mb-2">
                                    <label class="form-label fw-semibold small">Approved Amount (₹)</label>
                                    <input type="number" name="approved_amount"
                                           class="form-control form-control-sm"
                                           value="{{ number_format($loan->principal_amount, 2, '.', '') }}"
                                           min="1" step="0.01" placeholder="Approved amount">
                                    <small class="text-muted">Leave blank to use requested amount.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Remarks</label>
                                    <textarea name="approval_remarks" rows="2" class="form-control form-control-sm" placeholder="Optional remarks..."></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="action" value="approve"
                                            class="btn btn-success btn-sm flex-fill"
                                            onclick="document.getElementById('loan-form-{{ $loan->id }}').querySelector('[name=action]').value='approve';">
                                        <i class="icon-check me-1"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="reject"
                                            class="btn btn-danger btn-sm flex-fill"
                                            onclick="document.getElementById('loan-form-{{ $loan->id }}').querySelector('[name=action]').value='reject';">
                                        <i class="icon-close me-1"></i> Reject
                                    </button>
                                </div>

                                {{-- Hidden action field --}}
                                <input type="hidden" name="action" value="approve">
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="icon-check" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    <div class="mt-3 fs-6">No pending loan requests at this time.</div>
                    <p class="small text-muted mt-1">All loan requests have been processed.</p>
                </div>
            @endforelse
        </div>
        @if($pendingLoans->hasPages())
            <div class="card-footer bg-transparent border-top px-4 py-3">
                {{ $pendingLoans->links() }}
            </div>
        @endif
    </div>
</div>

<script>
// Fix the approve/reject button form submission to correctly set the action value
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-success, .btn-danger').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var form = this.closest('form');
            var hiddenInput = form.querySelector('input[name="action"]');
            if (hiddenInput) {
                hiddenInput.value = this.value;
            }
        });
    });
});
</script>
@endsection
