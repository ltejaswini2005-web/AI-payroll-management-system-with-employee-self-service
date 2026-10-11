@extends('layouts.backend')
@section('title', 'Employee Report')

@section('content')
<div class="wrapper-page" style="background-color: #f4f6fa; min-height: 100vh; padding: 25px;">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="icon-people"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Employee Report</h4>
                <p class="text-muted mb-0 small">View and filter all employee records by department, designation, status, and type.</p>
            </div>
        </div>
    </div>

    @include('partials.flash')

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Name, Code, Email...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="0">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (int)$filters['department_id'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Designation</label>
                    <select name="designation_id" class="form-select">
                        <option value="0">All Designations</option>
                        @foreach($designations as $des)
                            <option value="{{ $des->id }}" {{ (int)$filters['designation_id'] === (int)$des->id ? 'selected' : '' }}>{{ $des->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Status</label>
                    <select name="employment_status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($employmentStatuses as $es)
                            <option value="{{ $es }}" {{ $filters['employment_status'] === $es ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $es)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Type</label>
                    <select name="employment_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($employmentTypes as $et)
                            <option value="{{ $et }}" {{ $filters['employment_type'] === $et ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $et)) }}</option>
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
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="icon-magnifier"></i> Filter</button>
                    <a href="{{ route('reports.employees') }}" class="btn btn-outline-secondary"><i class="icon-refresh"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">Employees</h5>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">{{ $employees->total() }} Records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Employee Code</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Salary Grade</th>
                            <th>Type</th>
                            <th class="text-center">Status</th>
                            <th>Joining Date</th>
                            <th>Work Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            @php
                                $empName = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
                                $statusClass = match($emp->employment_status) {
                                    'active'     => 'bg-success-subtle text-success border-success-subtle',
                                    'inactive'   => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                    'terminated' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    'resigned'   => 'bg-warning-subtle text-warning border-warning-subtle',
                                    default        => 'bg-light text-dark border',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4"><span class="badge bg-light text-dark border fw-semibold">{{ $emp->employee_code }}</span></td>
                                <td class="fw-semibold text-dark">{{ $empName !== '' ? $empName : '-' }}</td>
                                <td>{{ $emp->department?->name ?? '-' }}</td>
                                <td>{{ $emp->designation?->name ?? '-' }}</td>
                                <td>{{ $emp->salaryGrade?->grade_name ?? '-' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $emp->employment_type)) }}</span></td>
                                <td class="text-center">
                                    <span class="badge border {{ $statusClass }} rounded-pill px-3 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $emp->employment_status)) }}
                                    </span>
                                </td>
                                <td>{{ $emp->date_of_joining ? \Carbon\Carbon::parse($emp->date_of_joining)->format('d-m-Y') : '-' }}</td>
                                <td class="text-muted small">{{ $emp->work_email ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No employee records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employees->hasPages())
        <div class="card-footer bg-transparent px-4 py-3">
            {{ $employees->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
