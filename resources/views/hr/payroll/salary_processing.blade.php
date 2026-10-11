@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-wallet"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Salary Processing</h4>
                <p class="text-muted mb-0 small">Process monthly payroll runs, generate payouts, and view completed disbursements.</p>
            </div>
        </div>
        <a href="{{ route('payroll.payslip') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="icon-doc"></i> View Payslip Generation
        </a>
    </div>

    @include('partials.flash')

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Monthly Payroll Runs</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Automated Processing</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Period</th>
                            <th>Frequency</th>
                            <th>Pay Date</th>
                            <th class="text-end">Gross Total (₹)</th>
                            <th class="text-end">Deductions (₹)</th>
                            <th class="text-end">Net Payout (₹)</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($runs as $run)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $run->period_label }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($run->pay_frequency) }}</span></td>
                                <td>{{ $run->pay_date ? \Carbon\Carbon::parse($run->pay_date)->format('d-m-Y') : '-' }}</td>
                                <td class="text-end fw-semibold">₹ {{ number_format($run->gross_total) }}</td>
                                <td class="text-end text-danger fw-semibold">₹ {{ number_format($run->deduction_total) }}</td>
                                <td class="text-end text-success fw-bold">₹ {{ number_format($run->net_total) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        {{ ucfirst($run->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <a href="{{ route('payroll.payslip', ['month' => $run->payroll_month, 'year' => $run->payroll_year]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="icon-eye"></i> View Payslip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payroll runs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
