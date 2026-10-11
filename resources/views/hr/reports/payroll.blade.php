@extends('layouts.backend')
@section('title', 'Payroll Report')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0f7ea; color: #198754; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-wallet"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Payroll Report</h4>
                <p class="text-muted mb-0 small">View payroll items per employee across all payroll runs, with detailed salary breakdowns.</p>
            </div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="0">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (int)$filters['employee_id'] === (int)$emp->id ? 'selected' : '' }}>
                                {{ trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Payroll Run / Period</label>
                    <select name="payroll_run_id" class="form-select">
                        <option value="0">All Periods</option>
                        @foreach($payrollRuns as $run)
                            <option value="{{ $run->id }}" {{ (int)$filters['payroll_run_id'] === (int)$run->id ? 'selected' : '' }}>
                                {{ $run->period_label ?? ($run->payroll_year . '-' . str_pad($run->payroll_month, 2, '0', STR_PAD_LEFT)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($paymentStatuses as $ps)
                            <option value="{{ $ps }}" {{ $filters['payment_status'] === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold text-muted">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" {{ (int)$filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="icon-magnifier"></i> Filter</button>
                    <a href="{{ route('reports.payroll') }}" class="btn btn-outline-secondary"><i class="icon-refresh"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Payroll Items</h5>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $items->total() }} Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Period</th>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th class="text-end">Basic (₹)</th>
                            <th class="text-end">Allowances (₹)</th>
                            <th class="text-end">Bonus (₹)</th>
                            <th class="text-end">Deductions (₹)</th>
                            <th class="text-end">Net Payable (₹)</th>
                            <th class="text-center">Pay Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $periodLabel = $item->payrollRun?->period_label ?? '-';
                                $empName = trim(($item->employee?->first_name ?? '') . ' ' . ($item->employee?->last_name ?? ''));
                                $payStatusClass = match($item->payment_status) {
                                    'paid'      => 'bg-success-subtle text-success border-success-subtle',
                                    'pending'   => 'bg-warning-subtle text-warning border-warning-subtle',
                                    'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    default     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $periodLabel }}</td>
                                <td>{{ $empName !== '' ? $empName : '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $item->employee?->employee_code ?? '-' }}</span></td>
                                <td>{{ $item->employee?->department?->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($item->basic_salary, 2) }}</td>
                                <td class="text-end">{{ number_format($item->allowance_total, 2) }}</td>
                                <td class="text-end">{{ number_format($item->bonus_total, 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($item->total_deduction, 2) }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($item->net_payable, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $payStatusClass }} rounded-pill px-3 py-1">
                                        {{ ucfirst($item->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No payroll records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($items->hasPages())
        <div class="card-footer bg-transparent px-4 py-3">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
