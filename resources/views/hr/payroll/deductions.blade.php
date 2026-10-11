@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-pie-chart"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Payroll Deductions Management</h4>
                <p class="text-muted mb-0 small">Statutory & voluntary deduction rules (Provident Fund, ESI, Professional Tax, Loan EMI, TDS).</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('payroll.provident-fund') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="icon-shield"></i> View Provident Fund
            </a>
            <a href="{{ route('payroll.payslip') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="icon-doc"></i> View Payslip
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h5 class="fw-bold text-dark mb-0">Statutory & Non-Statutory Deductions Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Deduction Type</th>
                            <th>Category</th>
                            <th>Calculation Rule</th>
                            <th class="text-end">Standard Amount (₹)</th>
                            <th class="pe-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">Provident Fund (PF)</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Statutory</span></td>
                            <td>12% of Basic Salary</td>
                            <td class="text-end fw-bold">₹ 3,000</td>
                            <td class="pe-4 text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">Employee State Insurance (ESI)</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Statutory</span></td>
                            <td>0.75% of Gross Wages</td>
                            <td class="text-end fw-bold">₹ 500</td>
                            <td class="pe-4 text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">Professional Tax (PT)</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Statutory</span></td>
                            <td>State Govt Slab (Karnataka)</td>
                            <td class="text-end fw-bold">₹ 200</td>
                            <td class="pe-4 text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">Income Tax (TDS)</td>
                            <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Statutory</span></td>
                            <td>Section 192 Tax Slab</td>
                            <td class="text-end fw-bold">₹ 4,000</td>
                            <td class="pe-4 text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold text-dark">Company Loan EMI</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border">Voluntary</span></td>
                            <td>12 Month Personal Device Loan</td>
                            <td class="text-end fw-bold">₹ 2,000</td>
                            <td class="pe-4 text-center"><span class="badge bg-success-subtle text-success">Active</span></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="ps-4">Total Monthly Deductions</td>
                            <td class="text-end text-danger fs-6">₹ 9,700</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
