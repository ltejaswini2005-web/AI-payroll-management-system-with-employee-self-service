@extends('layouts.backend')
@section('title', 'Attendance Report')
@section('content')
<div class="wrapper-page" style="background-color:#f4f6fa;min-height:100vh;padding:25px;">
  <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div class="d-flex align-items-center gap-3">
      <div style="width:48px;height:48px;border-radius:10px;background:#e0edfe;color:#1d72f2;display:flex;align-items:center;justify-content:center;font-size:24px;">
        <i class="icon-chart"></i>
      </div>
      <div>
        <h4 class="mb-0 fw-bold text-dark">Attendance Report</h4>
        <p class="text-muted mb-0 small">View and filter employee attendance records by date, employee, or status.</p>
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
              <option value="{{ $emp->id }}" {{ (int)$filters['employee_id']===(int)$emp->id ? 'selected' : '' }}>{{ trim(($emp->first_name ?? '').' '.($emp->last_name ?? '')) }} ({{ $emp->employee_code }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-muted">Status</label>
          <select name="status" class="form-select">
            <option value="">All Status</option>
            @foreach($statuses as $s)
              <option value="{{ $s }}" {{ $filters['status']===$s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-muted">From Date</label>
          <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] }}">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-muted">To Date</label>
          <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] }}">
        </div>
        <div class="col-md-1">
          <label class="form-label small fw-semibold text-muted">Per Page</label>
          <select name="per_page" class="form-select">
            @foreach([10,20,50,100] as $sz)
              <option value="{{ $sz }}" {{ (int)$filters['per_page']===$sz ? 'selected' : '' }}>{{ $sz }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1"><i class="icon-magnifier"></i> Filter</button>
          <a href="{{ route('reports.attendance') }}" class="btn btn-outline-secondary"><i class="icon-refresh"></i></a>
        </div>
      </form>
    </div>
  </div>
  <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold text-dark mb-0">Attendance Logs</h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $logs->total() }} Records</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Date</th><th>Employee</th><th>Code</th><th>Department</th>
              <th>Designation</th><th>Check In</th><th>Check Out</th>
              <th class="text-center">Hours</th><th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
              @php
                $en = trim(($log->employee?->first_name ?? '').' '.($log->employee?->last_name ?? ''));
                $wh = $log->worked_minutes > 0 ? number_format($log->worked_minutes / 60, 1) : '-';
                $sc = match($log->status) {
                  'present'  => 'bg-success-subtle text-success border-success-subtle',
                  'absent'   => 'bg-danger-subtle text-danger border-danger-subtle',
                  'late'     => 'bg-warning-subtle text-warning border-warning-subtle',
                  'half_day' => 'bg-info-subtle text-info border-info-subtle',
                  default    => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                };
              @endphp
              <tr>
                <td class="ps-4 fw-semibold">{{ $log->attendance_date?->format('d-m-Y') }}</td>
                <td>{{ $en !== '' ? $en : '-' }}</td>
                <td><span class="badge bg-light text-dark border">{{ $log->employee?->employee_code ?? '-' }}</span></td>
                <td>{{ $log->employee?->department?->name ?? '-' }}</td>
                <td>{{ $log->employee?->designation?->name ?? '-' }}</td>
                <td>{{ $log->check_in_at?->format('H:i') ?? '-' }}</td>
                <td>{{ $log->check_out_at?->format('H:i') ?? '-' }}</td>
                <td class="text-center fw-semibold">{{ $wh }}</td>
                <td class="text-center"><span class="badge border {{ $sc }} rounded-pill px-3 py-1">{{ ucfirst(str_replace('_',' ',$log->status)) }}</span></td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center py-4 text-muted">No attendance records found for the selected filters.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-transparent px-4 py-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
    @endif
  </div>
</div>
@endsection
