<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $selectedMonthName }} {{ $selectedYear }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .company-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }
        .payslip-title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            color: #0f172a;
        }
        .payslip-period {
            font-size: 12px;
            color: #64748b;
            text-align: right;
            margin-top: 4px;
        }
        .emp-box {
            background-color: #f1f7fe;
            border: 1px solid #d0e4ff;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .emp-table {
            width: 100%;
            border-collapse: collapse;
        }
        .emp-table td {
            padding: 4px 6px;
            font-size: 11px;
        }
        .emp-label {
            color: #64748b;
            font-weight: 600;
            width: 25%;
        }
        .emp-val {
            color: #0f172a;
            font-weight: bold;
            width: 25%;
        }
        .tables-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .section-table {
            width: 100%;
            border-collapse: collapse;
        }
        .section-table th {
            background-color: #ebf4ff;
            color: #1e40af;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #dbeafe;
        }
        .section-table td {
            padding: 7px 10px;
            font-size: 11px;
            border: 1px solid #e2e8f0;
        }
        .section-table tfoot th {
            background-color: #ebf4ff;
            color: #1e40af;
            font-size: 11px;
            padding: 8px 10px;
            border: 1px solid #dbeafe;
        }
        .net-salary-box {
            background-color: #eefcf3;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 25px;
        }
        .net-label {
            font-size: 14px;
            font-weight: bold;
            color: #15803d;
        }
        .net-amount {
            font-size: 18px;
            font-weight: bold;
            color: #15803d;
            text-align: right;
        }
        .footer-note {
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="company-title">{{ $companyName }}</div>
                <div class="company-sub">{{ $companyAddress }}</div>
                <div class="company-sub">{{ $companyWebsite }}</div>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div class="payslip-title">PAYSLIP</div>
                <div class="payslip-period">For the month of {{ $selectedMonthName }} {{ $selectedYear }}</div>
            </td>
        </tr>
    </table>

    <div class="emp-box">
        <table class="emp-table">
            <tr>
                <td class="emp-label">Employee Name:</td>
                <td class="emp-val">{{ $employee?->first_name ?: 'Varsha' }} {{ $employee?->last_name }}</td>
                <td class="emp-label">Date of Joining:</td>
                <td class="emp-val">{{ $employee?->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('d-m-Y') : '15-06-2023' }}</td>
            </tr>
            <tr>
                <td class="emp-label">Employee ID:</td>
                <td class="emp-val">{{ $employee?->employee_code ?: '12345' }}</td>
                <td class="emp-label">PAN No.:</td>
                <td class="emp-val">{{ $employee?->tax_id ?: 'ABCPV1234F' }}</td>
            </tr>
            <tr>
                <td class="emp-label">Department:</td>
                <td class="emp-val">{{ $employee?->department?->name ?: 'CSE' }}</td>
                <td class="emp-label">Bank Account No.:</td>
                <td class="emp-val">{{ $primaryBank?->account_number ?: '501001234567' }}</td>
            </tr>
            <tr>
                <td class="emp-label">Designation:</td>
                <td class="emp-val">{{ $employee?->designation?->name ?: 'Software Engineer' }}</td>
                <td class="emp-label">Payment Mode:</td>
                <td class="emp-val">Bank Transfer</td>
            </tr>
        </table>
    </div>

    <table class="tables-container">
        <tr>
            <!-- Earnings Table -->
            <td style="width: 49%; vertical-align: top;">
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>Particulars</th>
                            <th style="text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $item)
                            <tr>
                                <td>{{ $item['particular'] }}</td>
                                <td style="text-align: right;">{{ number_format($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Earnings</th>
                            <th style="text-align: right;">{{ number_format($totalEarnings) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <td style="width: 2%;"></td>

            <!-- Deductions Table -->
            <td style="width: 49%; vertical-align: top;">
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>Particulars</th>
                            <th style="text-align: right;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $item)
                            <tr>
                                <td>{{ $item['particular'] }}</td>
                                <td style="text-align: right;">{{ number_format($item['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Deductions</th>
                            <th style="text-align: right;">{{ number_format($totalDeductions) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td class="net-salary-box">
                <table style="width: 100%;">
                    <tr>
                        <td class="net-label">Net Salary</td>
                        <td class="net-amount">₹ {{ number_format($netSalary) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%;" class="footer-note">
        <tr>
            <td>This is a system generated payslip and does not require a signature.</td>
            <td style="text-align: right;">Generated on: {{ $generatedTimestamp }}</td>
        </tr>
    </table>
</body>
</html>
