@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 2px 6px rgba(29, 114, 242, 0.15);">
                <i class="icon-shield"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.35rem;">Provident Fund (EPF & EPS)</h4>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">Track employee & employer PF contributions, passbook transactions, and advance withdrawal claims.</p>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size: 0.86rem;">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item text-muted">Payroll</li>
                <li class="breadcrumb-item active fw-semibold text-primary" aria-current="page">Provident Fund</li>
            </ol>
        </nav>
    </div>

    @include('partials.flash')

    <!-- Top Action Filters & Buttons -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff;">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <form method="GET" action="{{ route('payroll.provident-fund') }}" class="d-flex flex-wrap align-items-center gap-3">
                    @if($employees && $employees->count() > 1)
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fw-semibold small">Employee:</span>
                            <select name="employee_id" class="form-select form-select-sm" style="width: auto; min-width: 200px; border-radius: 6px;" onchange="this.form.submit()">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ ($employee?->id == $emp->id) ? 'selected' : '' }}>
                                        {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-semibold small">Year:</span>
                        <select name="year" class="form-select form-select-sm" style="width: auto; border-radius: 6px;" onchange="this.form.submit()">
                            <option value="all" {{ ($yearFilter === 'all') ? 'selected' : '' }}>All Years</option>
                            <option value="2025" {{ ($yearFilter === '2025') ? 'selected' : '' }}>2025</option>
                            <option value="2024" {{ ($yearFilter === '2024') ? 'selected' : '' }}>2024</option>
                        </select>
                    </div>
                </form>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('payroll.provident-fund.export', ['employee_id' => $employee?->id]) }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 px-3 py-2 fw-semibold" style="border-radius: 6px;">
                        <i class="icon-cloud-download"></i> Export Passbook (CSV)
                    </a>
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2 px-3 py-2 fw-semibold" style="background-color: #1d72f2; border-color: #1d72f2; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#claimModal">
                        <i class="icon-plus"></i> Apply for PF Advance
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total PF Balance -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="background: linear-gradient(135deg, #1d72f2 0%, #1752b0 100%); color: #ffffff;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-white-50 fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Total EPF Balance</span>
                        <span class="badge bg-white text-primary rounded-pill px-2 py-1 small">Active</span>
                    </div>
                    <h3 class="fw-bold mb-1">₹ {{ number_format($totalBalance) }}</h3>
                    <div class="small text-white-50">Accumulated Employee & Employer Share</div>
                </div>
            </div>
        </div>

        <!-- Card 2: Pension EPS Balance -->
        <div class="col-xl-2 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">EPS Pension</span>
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                            <i class="icon-wallet"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">₹ {{ number_format($epsBalance) }}</h4>
                    <div class="small text-muted">8.33% Pension Corpus</div>
                </div>
            </div>
        </div>

        <!-- Card 3: Employee Monthly Contribution -->
        <div class="col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">Employee Share</span>
                        <span class="badge bg-light text-primary border">12% Basic</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">₹ 3,000 <span class="small text-muted fw-normal">/ mo</span></h4>
                    <div class="small text-muted">₹ {{ number_format($totalEmployeeShare) }} total deposited</div>
                </div>
            </div>
        </div>

        <!-- Card 4: Employer Monthly Contribution -->
        <div class="col-xl-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">Employer Share</span>
                        <span class="badge bg-light text-success border">12% Total</span>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">₹ 3,000 <span class="small text-muted fw-normal">/ mo</span></h4>
                    <div class="small text-muted">EPF: ₹918 | EPS: ₹2,082</div>
                </div>
            </div>
        </div>

        <!-- Card 5: Annual Interest -->
        <div class="col-xl-3 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">Current PF Interest</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Govt Rate</span>
                    </div>
                    <h4 class="fw-bold text-success mb-1">{{ $annualInterestRate }}% <span class="small text-muted fw-normal">p.a.</span></h4>
                    <div class="small text-muted">~ ₹ {{ number_format($estimatedAnnualInterest) }} annual yield</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Details & Compliance Box -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff;">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="icon-user text-primary fs-5"></i>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">EPFO Member Profile & Compliance Status</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="icon-check"></i> Aadhaar Linked</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="icon-check"></i> PAN Linked</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="icon-check"></i> Bank KYC Verified</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4" style="font-size: 0.92rem;">
                <div class="col-md-3 col-sm-6 border-end">
                    <div class="text-muted small mb-1">Universal Account Number (UAN)</div>
                    <div class="fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <span>{{ $pfAccount?->uan_number ?: '101234567890' }}</span>
                        <button class="btn btn-sm btn-light p-0 text-muted" title="Copy UAN" onclick="navigator.clipboard.writeText('{{ $pfAccount?->uan_number ?: '101234567890' }}'); alert('UAN copied!');">
                            <i class="icon-docs"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 border-end">
                    <div class="text-muted small mb-1">PF Account / Member ID</div>
                    <div class="fw-bold text-dark fs-6">{{ $pfAccount?->pf_account_number ?: 'KN/BNG/0012345/000/0012345' }}</div>
                </div>
                <div class="col-md-3 col-sm-6 border-end">
                    <div class="text-muted small mb-1">Scheme Joining Date</div>
                    <div class="fw-bold text-dark fs-6">{{ $pfAccount?->effective_from ? \Carbon\Carbon::parse($pfAccount->effective_from)->format('d-m-Y') : '01-07-2023' }}</div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="text-muted small mb-1">Registered Nominee</div>
                    <div class="fw-bold text-dark fs-6">{{ $pfAccount?->nominee_name ?: 'Suresh Kumar' }} <span class="badge bg-light text-dark fw-normal">({{ $pfAccount?->nominee_relation ?: 'Father' }} - 100%)</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Passbook Statement Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: #ffffff;">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="icon-layers text-primary fs-5"></i>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">EPF Passbook - Monthly Contribution Statement</h5>
            </div>
            <span class="badge bg-light text-muted border px-2 py-1">Showing 12 Recent Deposits</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead style="background-color: #f8fafc; border-bottom: 2px solid #edf2f7;">
                        <tr>
                            <th class="ps-4 py-3 fw-bold text-muted">Transaction Date</th>
                            <th class="py-3 fw-bold text-muted">Reference / Challan</th>
                            <th class="py-3 fw-bold text-muted">Wage & Details</th>
                            <th class="py-3 text-end fw-bold text-primary">Employee Share (12%)</th>
                            <th class="py-3 text-end fw-bold text-success">Employer Share</th>
                            <th class="py-3 text-end fw-bold text-muted">Withdrawal</th>
                            <th class="py-3 text-end fw-bold text-dark">Closing Balance (₹)</th>
                            <th class="pe-4 py-3 text-center fw-bold text-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                            <tr>
                                <td class="ps-4 py-3 text-dark fw-medium">
                                    {{ $txn->transaction_date ? $txn->transaction_date->format('d-m-Y') : '-' }}
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border">{{ $txn->reference_no }}</span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold text-dark">{{ $txn->reason }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">{{ $txn->comments }}</div>
                                </td>
                                <td class="py-3 text-end fw-bold text-primary">
                                    ₹ {{ number_format($txn->employee_contribution) }}
                                </td>
                                <td class="py-3 text-end fw-bold text-success">
                                    ₹ {{ number_format($txn->employer_contribution) }}
                                </td>
                                <td class="py-3 text-end text-muted">
                                    {{ $txn->withdrawal_amount > 0 ? '₹ '.number_format($txn->withdrawal_amount) : '-' }}
                                </td>
                                <td class="py-3 text-end fw-bold text-dark">
                                    ₹ {{ number_format($txn->balance_after) }}
                                </td>
                                <td class="pe-4 py-3 text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                        Credited
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No passbook records found for this employee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Claims & Advance Requests -->
    <div class="card border-0 shadow-sm rounded-3" style="background: #ffffff;">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="icon-refresh text-primary fs-5"></i>
                <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">PF Advance & Withdrawal Claims History</h5>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#claimModal">
                New Claim Request
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th class="ps-4 py-3 text-muted">Claim Reference</th>
                            <th class="py-3 text-muted">Claim Form</th>
                            <th class="py-3 text-muted">Purpose</th>
                            <th class="py-3 text-end text-muted">Claim Amount</th>
                            <th class="py-3 text-muted">Submission Date</th>
                            <th class="py-3 text-muted">Remarks</th>
                            <th class="pe-4 py-3 text-center text-muted">Claim Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                            <tr>
                                <td class="ps-4 py-3 fw-bold text-dark">{{ $claim->claim_reference }}</td>
                                <td class="py-3"><span class="badge bg-light text-primary border">{{ $claim->claim_type }}</span></td>
                                <td class="py-3 text-dark">{{ $claim->purpose }}</td>
                                <td class="py-3 text-end fw-bold text-dark">₹ {{ number_format($claim->amount) }}</td>
                                <td class="py-3 text-muted">{{ $claim->submission_date ? $claim->submission_date->format('d-m-Y') : '-' }}</td>
                                <td class="py-3 text-muted small">{{ $claim->remarks ?: '-' }}</td>
                                <td class="pe-4 py-3 text-center">
                                    @if($claim->status === 'Settled' || $claim->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">{{ $claim->status }}</span>
                                    @elseif($claim->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">{{ $claim->status }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">{{ $claim->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No claims filed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Apply for PF Advance -->
<div class="modal fade" id="claimModal" tabindex="-1" aria-labelledby="claimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('payroll.provident-fund.claim') }}">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee?->id }}">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="claimModalLabel">Apply for PF Advance / Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Claim Type</label>
                        <select name="claim_type" class="form-select" required>
                            <option value="Form 31 (PF Advance)">Form 31 - Non-refundable PF Advance</option>
                            <option value="Form 19 (PF Settlement)">Form 19 - Full & Final Settlement</option>
                            <option value="Form 10C (EPS Pension Scheme)">Form 10C - Scheme Certificate / EPS Pension</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Purpose of Advance</label>
                        <select name="purpose" class="form-select" required>
                            <option value="Illness / Medical Emergency for Self/Family">Illness / Medical Emergency for Self/Family</option>
                            <option value="Higher Education of Children">Higher Education of Children</option>
                            <option value="Purchase/Construction of House or Flat">Purchase/Construction of House or Flat</option>
                            <option value="Marriage of Self/Children/Sibling">Marriage of Self/Children/Sibling</option>
                            <option value="Special Pandemic/Natural Calamity Advance">Special Relief Advance</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Requested Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" placeholder="e.g. 25000" min="1000" max="{{ $totalBalance }}" required>
                        <div class="form-text small">Maximum eligible advance from your EPF balance: ₹ {{ number_format($totalBalance) }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Remarks / Medical Hospital Details (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Mention hospital or college details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background-color: #1d72f2; border-color: #1d72f2;">
                        Submit Claim Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
