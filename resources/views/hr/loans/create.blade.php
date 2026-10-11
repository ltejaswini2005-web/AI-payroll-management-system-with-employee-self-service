@extends('layouts.backend')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0edfe; color: #1d72f2; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-plus"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Add Loan Request</h4>
                <p class="text-muted mb-0 small">Submit a new employee loan request for approval.</p>
            </div>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <i class="icon-arrow-left"></i> Back to Loan List
        </a>
    </div>

    @include('partials.flash')

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h5 class="fw-bold text-dark mb-0">Loan Request Details</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('loans.store') }}">
                @csrf

                <div class="row g-3">
                    {{-- Employee --}}
                    <div class="col-md-6">
                        <label for="employee_id" class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select id="employee_id" name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    [{{ $emp->employee_code }}] {{ $emp->first_name }} {{ $emp->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Loan Type --}}
                    <div class="col-md-6">
                        <label for="loan_type" class="form-label fw-semibold">Loan Type <span class="text-danger">*</span></label>
                        <select id="loan_type" name="loan_type" class="form-select @error('loan_type') is-invalid @enderror" required>
                            @foreach($loanTypes as $val => $label)
                                <option value="{{ $val }}" {{ old('loan_type', 'personal') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('loan_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Principal Amount --}}
                    <div class="col-md-4">
                        <label for="principal_amount" class="form-label fw-semibold">Requested Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="principal_amount" name="principal_amount" class="form-control @error('principal_amount') is-invalid @enderror"
                                   value="{{ old('principal_amount') }}" min="1" step="0.01" required placeholder="e.g. 50000">
                            @error('principal_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Installment Count --}}
                    <div class="col-md-4">
                        <label for="installment_count" class="form-label fw-semibold">Tenure (Months) <span class="text-danger">*</span></label>
                        <input type="number" id="installment_count" name="installment_count" class="form-control @error('installment_count') is-invalid @enderror"
                               value="{{ old('installment_count') }}" min="1" max="360" required placeholder="e.g. 12">
                        @error('installment_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Interest Rate --}}
                    <div class="col-md-4">
                        <label for="interest_rate_percent" class="form-label fw-semibold">Interest Rate (% per year)</label>
                        <div class="input-group">
                            <input type="number" id="interest_rate_percent" name="interest_rate_percent" class="form-control @error('interest_rate_percent') is-invalid @enderror"
                                   value="{{ old('interest_rate_percent', 0) }}" min="0" max="100" step="0.01" placeholder="0.00">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Leave 0 for interest-free loan.</small>
                        @error('interest_rate_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Issued Date --}}
                    <div class="col-md-4">
                        <label for="issued_date" class="form-label fw-semibold">Request Date <span class="text-danger">*</span></label>
                        <input type="date" id="issued_date" name="issued_date" class="form-control @error('issued_date') is-invalid @enderror"
                               value="{{ old('issued_date', date('Y-m-d')) }}" required>
                        @error('issued_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- First Installment Date --}}
                    <div class="col-md-4">
                        <label for="first_installment_date" class="form-label fw-semibold">First EMI Date</label>
                        <input type="date" id="first_installment_date" name="first_installment_date" class="form-control @error('first_installment_date') is-invalid @enderror"
                               value="{{ old('first_installment_date') }}">
                        <small class="text-muted">Optional. Used to generate installment schedule on approval.</small>
                        @error('first_installment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label for="remarks" class="form-label fw-semibold">Reason / Remarks</label>
                        <textarea id="remarks" name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Provide a reason for the loan request...">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('loans.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="icon-check me-1"></i> Submit Loan Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
