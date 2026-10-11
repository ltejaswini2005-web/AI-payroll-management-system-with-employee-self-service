<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Payslip - {{ $selectedMonthName }} {{ $selectedYear }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 30px;
        }
        .payslip-paper {
            background: #ffffff;
            max-width: 850px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .payslip-paper {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="no-print d-flex justify-content-between align-items-center mb-4 max-w-850 mx-auto" style="max-width: 850px;">
        <a href="{{ route('payroll.payslip', ['month' => $selectedMonthName, 'year' => $selectedYear, 'employee_id' => $employee?->id]) }}" class="btn btn-outline-secondary">
            &larr; Back to Portal
        </a>
        <button onclick="window.print()" class="btn btn-primary px-4">
            Print Now
        </button>
    </div>

    <div class="payslip-paper">
        <!-- Payslip Header -->
        <div class="d-flex justify-content-between align-items-start pb-3 mb-4 border-bottom">
            <div>
                <h4 class="fw-bold mb-1">{{ $companyName }}</h4>
                <div class="text-muted small">{{ $companyAddress }}</div>
                <div class="text-muted small">{{ $companyWebsite }}</div>
            </div>
            <div class="text-end">
                <h3 class="fw-bold mb-0">PAYSLIP</h3>
                <div class="text-muted small">For the month of {{ $selectedMonthName }} {{ $selectedYear }}</div>
            </div>
        </div>

        <!-- Employee Info Box -->
        <div class="p-3 mb-4 rounded-3" style="background-color: #f1f7fe; border: 1px solid #d0e4ff;">
            <div class="row g-2 small">
                <div class="col-6">
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Employee Name:</div>
                        <div class="col-7 fw-bold">{{ $employee?->first_name ?: 'Varsha' }} {{ $employee?->last_name }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Employee ID:</div>
                        <div class="col-7 fw-bold">{{ $employee?->employee_code ?: '12345' }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Department:</div>
                        <div class="col-7 fw-bold">{{ $employee?->department?->name ?: 'CSE' }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Designation:</div>
                        <div class="col-7 fw-bold">{{ $employee?->designation?->name ?: 'Software Engineer' }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Date of Joining:</div>
                        <div class="col-7 fw-bold">{{ $employee?->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('d-m-Y') : '15-06-2023' }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">PAN No.:</div>
                        <div class="col-7 fw-bold">{{ $employee?->tax_id ?: 'ABCPV1234F' }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Bank Account No.:</div>
                        <div class="col-7 fw-bold">{{ $primaryBank?->account_number ?: '501001234567' }}</div>
                    </div>
                    <div class="row py-1">
                        <div class="col-5 text-muted fw-semibold">Payment Mode:</div>
                        <div class="col-7 fw-bold">Bank Transfer</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <table class="table table-bordered mb-0 small">
                    <thead class="table-primary">
                        <tr>
                            <th>Particulars</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $item)
                            <tr>
                                <td>{{ $item['particular'] }}</td>
                                <td class="text-end">{{ number_format($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-primary fw-bold">
                        <tr>
                            <td>Total Earnings</td>
                            <td class="text-end">{{ number_format($totalEarnings) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-bordered mb-0 small">
                    <thead class="table-primary">
                        <tr>
                            <th>Particulars</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $item)
                            <tr>
                                <td>{{ $item['particular'] }}</td>
                                <td class="text-end">{{ number_format($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-primary fw-bold">
                        <tr>
                            <td>Total Deductions</td>
                            <td class="text-end">{{ number_format($totalDeductions) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Net Salary -->
        <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded-3" style="background-color: #eefcf3; border: 1px solid #bbf7d0;">
            <h5 class="fw-bold mb-0 text-success">Net Salary</h5>
            <h4 class="fw-bold mb-0 text-success">₹ {{ number_format($netSalary) }}</h4>
        </div>

        <div class="d-flex justify-content-between align-items-center text-muted small border-top pt-2">
            <div>This is a system generated payslip and does not require a signature.</div>
            <div>Generated on: {{ $generatedTimestamp }}</div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Auto open print dialog after short delay
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
