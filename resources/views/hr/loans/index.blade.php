@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-credit-card"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Loan List</h4>
                <p class="text-muted mb-0 small">All employee loan records — pending, approved, active and closed.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()?->hasPermission('loan.approve'))
                <a href="{{ route('loans.approvals') }}" class="btn btn-outline-warning d-flex align-items-center gap-2">
                    <i class="icon-check"></i> Pending Approvals
                </a>
            @endif
            @if(auth()->user()?->hasPermission('loan.create'))
                <a href="{{ route('loans.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="icon-plus"></i> Add Loan
                </a>
            @endif
        </div>
    </div>

    @include('partials.flash')

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('loans.index') }}" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
        <select name="status" class="form-select form-select-sm" style="max-width: 160px;">
            <option value="">All Statuses</option>
            @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
        @if(request('status'))
            <a href="{{ route('loans.index') }}" class="btn btn-sm btn-light">Clear</a>
        @endif
    </form>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Employee Loans</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                Total: {{ $loans->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ref #</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th class="text-end">Principal (₹)</th>
                            <th class="text-end">Approved (₹)</th>
                            <th class="text-end">EMI (₹)</th>
                            <th class="text-center">Installments</th>
                            <th>Issued Date</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-center">Approver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td class="ps-4 fw-bold text-dark font-monospace">{{ $loan->loan_reference }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $loan->employee?->first_name }} {{ $loan->employee?->last_name }}</div>
                                    <small class="text-muted">{{ $loan->employee?->employee_code }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ \App\Models\EmployeeLoan::$loanTypes[$loan->loan_type] ?? ucfirst($loan->loan_type) }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">₹ {{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="text-end fw-semibold text-success">
                                    {{ $loan->approved_amount ? '₹ ' . number_format($loan->approved_amount, 2) : '—' }}
                                </td>
                                <td class="text-end">₹ {{ number_format($loan->installment_amount, 2) }}</td>
                                <td class="text-center">{{ $loan->installment_count }} months</td>
                                <td>{{ $loan->issued_date ? $loan->issued_date->format('d-m-Y') : '—' }}</td>
                                <td class="text-center">
                                    @php
                                        $statusColors = [
                                            'pending'  => 'bg-warning-subtle text-warning border-warning-subtle',
                                            'approved' => 'bg-success-subtle text-success border-success-subtle',
                                            'rejected' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            'active'   => 'bg-primary-subtle text-primary border-primary-subtle',
                                            'closed'   => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                        ];
                                        $cls = $statusColors[$loan->status] ?? 'bg-light text-dark';
                                    @endphp
                                    <span class="badge {{ $cls }} border rounded-pill px-3 py-1">
                                        {{ \App\Models\EmployeeLoan::$statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center text-muted small">
                                    {{ $loan->approvedBy?->name ?? '—' }}
                                    @if($loan->approved_at)
                                        <br><small>{{ \Carbon\Carbon::parse($loan->approved_at)->format('d-m-Y') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="icon-credit-card" style="font-size: 2rem; opacity: 0.3;"></i>
                                    <div class="mt-2">No loan records found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($loans->hasPages())
            <div class="card-footer bg-transparent border-top px-4 py-3">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
