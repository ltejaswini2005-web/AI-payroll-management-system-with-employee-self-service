@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-calculator"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Income Tax & TDS Calculation</h4>
                <p class="text-muted mb-0 small">Tax computation slabs under New Tax Regime & Old Tax Regime with TDS projections.</p>
            </div>
        </div>
        <a href="{{ route('payroll.payslip') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
            <i class="icon-doc"></i> Payslip View
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="text-muted small fw-semibold text-uppercase">Annual Taxable Income</div>
                <h3 class="fw-bold text-dark mt-1">₹ 5,04,000</h3>
                <div class="small text-muted">Monthly Gross: ₹ 42,000</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="text-muted small fw-semibold text-uppercase">Monthly TDS Deduction</div>
                <h3 class="fw-bold text-primary mt-1">₹ 4,000</h3>
                <div class="small text-muted">Section 192 TDS deduction</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="text-muted small fw-semibold text-uppercase">Standard Deduction Claimed</div>
                <h3 class="fw-bold text-success mt-1">₹ 75,000</h3>
                <div class="small text-muted">Under Section 16(ia)</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h5 class="fw-bold text-dark mb-0">Income Tax Slab Structure (New Regime FY 2025-26)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Income Bracket</th>
                            <th>Tax Rate</th>
                            <th>TDS Deduction Status</th>
                            <th class="pe-4 text-end">Applicable Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">Up to ₹ 3,00,000</td>
                            <td><span class="badge bg-success-subtle text-success">Nil</span></td>
                            <td>Exempted</td>
                            <td class="pe-4 text-end">₹ 0</td>
                        </tr>
                        <tr>
                            <td class="ps-4">₹ 3,00,001 to ₹ 7,00,000</td>
                            <td><span class="badge bg-light text-primary border">5%</span></td>
                            <td>Covered under rebate 87A</td>
                            <td class="pe-4 text-end">₹ 20,000</td>
                        </tr>
                        <tr>
                            <td class="ps-4">₹ 7,00,001 to ₹ 10,00,000</td>
                            <td><span class="badge bg-light text-warning border">10%</span></td>
                            <td>Taxable</td>
                            <td class="pe-4 text-end">₹ 30,000</td>
                        </tr>
                        <tr>
                            <td class="ps-4">₹ 10,00,001 to ₹ 12,00,000</td>
                            <td><span class="badge bg-light text-danger border">15%</span></td>
                            <td>Taxable</td>
                            <td class="pe-4 text-end">₹ 30,000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
