@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 2px 6px rgba(29, 114, 242, 0.15);">
                <i class="icon-doc"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.35rem;">Payslip Generation</h4>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">Generate and view your monthly payslip. You can download or print the payslip for your records.</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 0.86rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item text-muted">Payroll</li>
                <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Payslip Generation</li>
            </ol>
        </nav>
    </div>

    @include('partials.flash')

    <div class="row g-4">
        <!-- Left Sidebar Column: Selection & Employee Details -->
        <div class="col-lg-4 col-md-5">
            <!-- Card 1: Select Month & Year -->
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4" style="font-size: 1.05rem;">Select Month & Year</h5>

                    <form method="GET" action="{{ route('payroll.payslip') }}" id="payslip-filter-form">
                        <!-- Employee select for HR / Admin -->
                        @if($employees && $employees->count() > 1)
                            <div class="mb-3">
                                <label for="employee_id" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Employee</label>
                                <select name="employee_id" id="employee_id" class="form-select form-select-lg" style="font-size: 0.95rem; border-color: #dbe2ea; border-radius: 8px;">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ ($employee?->id == $emp->id) ? 'selected' : '' }}>
                                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="employee_id" value="{{ $employee?->id }}">
                        @endif

                        <!-- Month Select -->
                        <div class="mb-3">
                            <label for="month" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Month</label>
                            <div class="position-relative">
                                <select name="month" id="month" class="form-select form-select-lg" style="font-size: 0.95rem; border-color: #dbe2ea; border-radius: 8px;">
                                    @foreach($monthNames as $num => $name)
                                        <option value="{{ $name }}" {{ ($selectedMonthName === $name) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Year Select -->
                        <div class="mb-4">
                            <label for="year" class="form-label text-muted fw-semibold" style="font-size: 0.85rem;">Year</label>
                            <div class="position-relative">
                                <select name="year" id="year" class="form-select form-select-lg" style="font-size: 0.95rem; border-color: #dbe2ea; border-radius: 8px;">
                                    @foreach($years as $yr)
                                        <option value="{{ $yr }}" {{ ($selectedYear == $yr) ? 'selected' : '' }}>
                                            {{ $yr }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Generate Button -->
                        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-semibold" style="background-color: #1d72f2; border-color: #1d72f2; border-radius: 8px; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(29, 114, 242, 0.25);">
                            <i class="icon-doc"></i> Generate Payslip
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card 2: Employee Details -->
            <div class="card border-0 shadow-sm rounded-3" style="background: #ffffff;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width: 26px; height: 26px; border-radius: 50%; background: #1d72f2; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold;">
                            i
                        </div>
                        <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Employee Details</h5>
                    </div>

                    <div class="employee-details-list" style="font-size: 0.92rem;">
                        <div class="row py-2 border-bottom border-light">
                            <div class="col-5 text-muted">Name</div>
                            <div class="col-7 fw-semibold text-dark">: {{ $employee?->first_name ?: 'Varsha' }} {{ $employee?->last_name }}</div>
                        </div>
                        <div class="row py-2 border-bottom border-light">
                            <div class="col-5 text-muted">Employee ID</div>
                            <div class="col-7 fw-semibold text-dark">: {{ $employee?->employee_code ?: '12345' }}</div>
                        </div>
                        <div class="row py-2 border-bottom border-light">
                            <div class="col-5 text-muted">Department</div>
                            <div class="col-7 fw-semibold text-dark">: {{ $employee?->department?->name ?: 'CSE' }}</div>
                        </div>
                        <div class="row py-2">
                            <div class="col-5 text-muted">Designation</div>
                            <div class="col-7 fw-semibold text-dark">: {{ $employee?->designation?->name ?: 'Software Engineer' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Main Column: Payslip Preview & Actions -->
        <div class="col-lg-8 col-md-7">
            <!-- Top Action Buttons -->
            <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
                <a href="{{ route('payroll.payslip.download-pdf', ['month' => $selectedMonthName, 'year' => $selectedYear, 'employee_id' => $employee?->id]) }}" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-semibold" style="background-color: #1d72f2; border-color: #1d72f2; border-radius: 8px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(29, 114, 242, 0.2);">
                    <i class="icon-cloud-download"></i> Download PDF
                </a>
                <a href="{{ route('payroll.payslip.print', ['month' => $selectedMonthName, 'year' => $selectedYear, 'employee_id' => $employee?->id]) }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center gap-2 px-3 py-2 fw-semibold" style="border-radius: 8px; font-size: 0.9rem; background: #ffffff; color: #495057; border-color: #ced4da;">
                    <i class="icon-printer"></i> Print
                </a>
            </div>

            <!-- Payslip Document Card -->
            <div class="card border-0 shadow-sm rounded-3" id="printable-payslip" style="background: #ffffff; padding: 15px;">
                <div class="card-body p-4">
                    <!-- Payslip Header: Company Info & Payslip Title -->
                    <div class="d-flex justify-content-between align-items-start pb-4 mb-4 border-bottom border-light">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 58px; height: 58px; border-radius: 12px; background: #f0f7ff; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid #d0e4ff;">
                                <i class="icon-organization"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1" style="font-size: 1.25rem; color: #0f172a;">{{ $companyName }}</h4>
                                <div class="text-muted" style="font-size: 0.85rem;">{{ $companyAddress }}</div>
                                <div class="text-muted" style="font-size: 0.85rem;">{{ $companyWebsite }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-bold mb-1" style="letter-spacing: 0.5px; color: #0f172a; font-size: 1.55rem;">PAYSLIP</h3>
                            <div class="text-muted fw-medium" style="font-size: 0.88rem;">For the month of {{ $selectedMonthName }} {{ $selectedYear }}</div>
                        </div>
                    </div>

                    <!-- Employee Details Grid Box (Light Blue) -->
                    <div class="p-3 mb-4 rounded-3" style="background-color: #f1f7fe; border: 1px solid #e1effe;">
                        <div class="row g-3" style="font-size: 0.89rem;">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Employee Name</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->first_name ?: 'Varsha' }} {{ $employee?->last_name }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Employee ID</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->employee_code ?: '12345' }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Department</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->department?->name ?: 'CSE' }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Designation</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->designation?->name ?: 'Software Engineer' }}</div>
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Date of Joining</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('d-m-Y') : '15-06-2023' }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">PAN No.</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $employee?->tax_id ?: 'ABCPV1234F' }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Bank Account No.</div>
                                    <div class="col-7 text-dark fw-bold">: {{ $primaryBank?->account_number ?: '501001234567' }}</div>
                                </div>
                                <div class="row py-1">
                                    <div class="col-5 text-muted fw-semibold">Payment Mode</div>
                                    <div class="col-7 text-dark fw-bold">: Bank Transfer</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings and Deductions Tables -->
                    <div class="row g-4 mb-4">
                        <!-- Earnings Table -->
                        <div class="col-md-6">
                            <div class="border rounded-2 overflow-hidden" style="border-color: #e2e8f0 !important;">
                                <table class="table table-bordered mb-0 align-middle" style="font-size: 0.88rem;">
                                    <thead>
                                        <tr style="background-color: #ebf4ff; color: #1e40af;">
                                            <th class="py-2 px-3 fw-bold" style="border-bottom: 1px solid #dbeafe;">Particulars</th>
                                            <th class="py-2 px-3 text-end fw-bold" style="border-bottom: 1px solid #dbeafe;">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($earnings as $item)
                                            <tr>
                                                <td class="py-2 px-3 text-dark">{{ $item['particular'] }}</td>
                                                <td class="py-2 px-3 text-end text-dark">{{ number_format($item['amount']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background-color: #ebf4ff; color: #1e40af;">
                                            <th class="py-2 px-3 fw-bold">Total Earnings</th>
                                            <th class="py-2 px-3 text-end fw-bold">{{ number_format($totalEarnings) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Deductions Table -->
                        <div class="col-md-6">
                            <div class="border rounded-2 overflow-hidden" style="border-color: #e2e8f0 !important;">
                                <table class="table table-bordered mb-0 align-middle" style="font-size: 0.88rem;">
                                    <thead>
                                        <tr style="background-color: #ebf4ff; color: #1e40af;">
                                            <th class="py-2 px-3 fw-bold" style="border-bottom: 1px solid #dbeafe;">Particulars</th>
                                            <th class="py-2 px-3 text-end fw-bold" style="border-bottom: 1px solid #dbeafe;">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deductions as $item)
                                            <tr>
                                                <td class="py-2 px-3 text-dark">{{ $item['particular'] }}</td>
                                                <td class="py-2 px-3 text-end text-dark">{{ number_format($item['amount']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background-color: #ebf4ff; color: #1e40af;">
                                            <th class="py-2 px-3 fw-bold">Total Deductions</th>
                                            <th class="py-2 px-3 text-end fw-bold">{{ number_format($totalDeductions) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Net Salary Green Banner -->
                    <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded-3" style="background-color: #eefcf3; border: 1px solid #d1fae5;">
                        <span class="fw-bold" style="font-size: 1.15rem; color: #15803d;">Net Salary</span>
                        <span class="fw-bold" style="font-size: 1.35rem; color: #15803d;">₹ {{ number_format($netSalary) }}</span>
                    </div>

                    <!-- Footer Note -->
                    <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                        <div>This is a system generated payslip and does not require a signature.</div>
                        <div>Generated on: {{ $generatedTimestamp }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
