@extends('layouts.backend')
@section('title', 'Attendance')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-clock"></i> Attendance</h1>
        <div class="d-flex gap-2">
            @if($canManageAttendance && $hasAllAccess)
                <a href="{{ route('attendance.template-download') }}" class="btn btn-custom-default">
                    <i class="icon-doc"></i> Template Download
                </a>
            @endif
            <a href="{{ route('attendance.export', ['from_date' => $filters['from_date'], 'to_date' => $filters['to_date'], 'employee_id' => $filters['employee_id']]) }}"
                class="btn btn-custom-default">
                <i class="icon-cloud-download"></i> Export Excel (CSV)
            </a>
        </div>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">

            {{-- Attendance Tabs Navigation --}}
            <ul class="nav nav-tabs mb-4" id="attendanceTabs" role="tablist" style="border-bottom: 2px solid #e9ecef; gap: 10px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="normal-attendance-tab" data-bs-toggle="tab" data-bs-target="#normal-attendance" type="button" role="tab" aria-controls="normal-attendance" aria-selected="true" style="border: none; background: none; padding: 10px 0; border-bottom: 3px solid #4f46e5; color: #4f46e5; font-weight: 600;">
                        <i class="icon-check"></i> Normal Attendance
                    </button>
                </li>
                @if($faceEnabled && ($canManageAttendance || $currentEmployeeId))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="daily-face-tab" data-bs-toggle="tab" data-bs-target="#daily-face" type="button" role="tab" aria-controls="daily-face" aria-selected="false" style="border: none; background: none; padding: 10px 0; border-bottom: 3px solid transparent; color: #666; font-weight: 600; transition: all 0.2s;">
                        <i class="icon-camera"></i> Daily Face Attendance
                    </button>
                </li>
                @endif
                @if($canManageAttendance)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="face-enrollment-tab" data-bs-toggle="tab" data-bs-target="#face-enrollment" type="button" role="tab" aria-controls="face-enrollment" aria-selected="false" style="border: none; background: none; padding: 10px 0; border-bottom: 3px solid transparent; color: #666; font-weight: 600; transition: all 0.2s;">
                        <i class="icon-user"></i> Face Enrollment
                    </button>
                </li>
                @endif
                @if($canAttendanceApiIntegration)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="api-integration-tab" data-bs-toggle="tab" data-bs-target="#api-integration" type="button" role="tab" aria-controls="api-integration" aria-selected="false" style="border: none; background: none; padding: 10px 0; border-bottom: 3px solid transparent; color: #666; font-weight: 600; transition: all 0.2s;">
                        <i class="icon-settings"></i> API Integration
                    </button>
                </li>
                @endif
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="attendanceTabContent">

                {{-- TAB 1: Normal Attendance --}}
                <div class="tab-pane fade show active" id="normal-attendance" role="tabpanel" aria-labelledby="normal-attendance-tab">
                    {{-- Manual Attendance Entry --}}
                    <div class="card no-border mb-3" id="attendance-entry-form">
                        <div class="content_wrapper" style="padding:20px;">
                            <h5 class="table_banner_title mb-3">Manual Attendance Entry</h5>
                            <form method="POST" action="{{ route('attendance.store') }}" class="row g-2">
                                @csrf
                                @if($canManageAttendance && $hasAllAccess)
                                    <div class="col-md-3">
                                        <label>Employee</label>
                                        <select name="employee_id" class="form-control js-example-basic-single">
                                            @foreach($employees as $employee)
                                                @php($name = trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')))
                                                <option value="{{ $employee->id }}" {{ (int) old('employee_id', $currentEmployeeId) === (int) $employee->id ? 'selected' : '' }}>
                                                    {{ $name !== '' ? $name : 'Employee' }} ({{ $employee->employee_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                     <label>Date</label>
                                    <input type="text"
                                        name="attendance_date"
                                        class="form-control attendance-date-picker" placeholder="YYYY-MM-DD"
                                        autocomplete="off"
                                        value="{{ old('attendance_date', now()->format('Y-m-d')) }}"
                                        required
                                    >
                                </div>
                                <div class="col-md-2">
                                    <label>Entry Type</label>
                                    @php($entryType = old('entry_type', 'checkin'))
                                    <select name="entry_type" class="form-control" required>
                                        <option value="checkin" {{ $entryType === 'checkin' ? 'selected' : '' }}>Check-in</option>
                                        <option value="checkout" {{ $entryType === 'checkout' ? 'selected' : '' }}>Check-out</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Time (hh:mm AM/PM)</label>
                                    <input type="text" name="entry_time"
                                        class="form-control attendance-time-picker"
                                        placeholder="09:01 AM"
                                        autocomplete="off"
                                        value="{{ old('entry_time') }}"
                                        required
                                    >
                                </div>
                                <div class="col-md-2">
                                        <label>Remarks</label>
                                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Optional">
                                    </div>
                                <div class="col-md-12 mt-2">
                                    <button type="submit" class="btn btn-custom"><i class="icon-plus"></i> Add Attendance</button>
                                    </div>
                            </form>
                        </div>
                    </div>

                    {{-- CSV Import --}}
                    @if($canManageAttendance && $hasAllAccess)
                        <div class="card no-border mb-3">
                            <div class="content_wrapper" style="padding:20px;">
                                <h5 class="table_banner_title mb-3">Excel Import (CSV)</h5>
                                <form method="POST" action="{{ route('attendance.import') }}" enctype="multipart/form-data" class="row g-2">
                                    @csrf
                                    <div class="col-md-6">
                                        <label>Upload CSV File</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input id="attendance_file" type="file" name="attendance_file" accept=".csv,text/csv" required>
                                            <label for="attendance_file" class="btn btn-custom mb-0">Choose CSV</label>
                                            <span id="attendance_file_name" class="text-muted">No file selected</span>
                                            </div>
                                                <small class="text-muted">Use template columns: employee_code, attendance_date, entry_type, entry_time, remarks.</small>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" class="btn btn-custom"><i class="icon-cloud-upload"></i> Import Attendance</button>
                                        </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Attendance History --}}
                    <div class="card no-border">
                        <div class="content_wrapper" style="padding:20px;">
                            <h5 class="table_banner_title mb-3">Attendance History</h5>

                            <form method="GET" class="row g-2 mb-3">
                                <div class="col-md-2">
                                    <input type="text" name="from_date" class="form-control attendance-date-picker" value="{{ $filters['from_date'] }}" placeholder="From date">
                                    </div>
                                <div class="col-md-2">
                                    <input type="text" name="to_date" class="form-control attendance-date-picker" value="{{ $filters['to_date'] }}" placeholder="To date">
                                    </div>
                                <div class="col-md-3">
                                    <select name="employee_id" class="form-control js-example-basic-single">
                                        <option value="0">All Employees</option>
                                        @foreach($employees as $employee)
                                            @php($name = trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')))
                                            <option value="{{ $employee->id }}" {{ (int) $filters['employee_id'] === (int) $employee->id ? 'selected' : '' }}>
                                                {{ $name !== '' ? $name : 'Employee' }} ({{ $employee->employee_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="per_page" class="form-control">
                                        @foreach([10,20,50,100] as $size)
                                            <option value="{{ $size }}" {{ (int) $filters['per_page'] === $size ? 'selected' : '' }}>{{ $size }} / page</option>
                                        @endforeach
                                        </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-custom"><i class="icon-magnifier"></i> Filter</button>
                                        <a href="{{ route('attendance.index') }}" class="btn btn-custom-default"><i class="icon-refresh"></i></a>
                                    </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Employee</th>
                                            <th>Code</th>
                                            <th>First Check-in</th>
                                            <th>Last Check-out</th>
                                            <th>Duration</th>
                                            <th>Entries (Same Day)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attendanceRows as $row)
                                            <tr>
                                                <td>{{ \Illuminate\Support\Carbon::parse($row->attendance_date)->format('Y-m-d') }}</td>
                                                <td>{{ trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: '-' }}</td>
                                                <td>{{ $row->employee_code ?: '-' }}</td>
                                                <td>{{ $row->first_check_in_at ? \Illuminate\Support\Carbon::parse($row->first_check_in_at)->format('H:i') : '-' }}</td>
                                                <td>{{ $row->last_check_out_at ? \Illuminate\Support\Carbon::parse($row->last_check_out_at)->format('H:i') : '-' }}</td>
                                                <td>
                                                    @php($stayMinutes = is_numeric($row->stay_minutes ?? null) ? max(0, (int) $row->stay_minutes) : null)
                                                    @if($stayMinutes === null)
                                                        -
                                                    @else
                                                        {{ intdiv($stayMinutes, 60) }}h {{ $stayMinutes % 60 }}m
                                                    @endif
                                                </td>
                                                <td>{{ (int) $row->total_entries }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No attendance data found for selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $attendanceRows->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

                {{-- TAB 2: Daily Face Attendance (Kiosk) --}}
                @if($faceEnabled && ($canManageAttendance || $currentEmployeeId))
                <div class="tab-pane fade" id="daily-face" role="tabpanel" aria-labelledby="daily-face-tab">
                    @include('hr.attendance.sections.daily-face-attendance')
                </div>
                @endif

                {{-- TAB 3: Face Enrollment --}}
                @if($canManageAttendance)
                <div class="tab-pane fade" id="face-enrollment" role="tabpanel" aria-labelledby="face-enrollment-tab">
                    <div class="card no-border">
                        <div class="content_wrapper" style="padding:20px;">
                            <h5 class="table_banner_title mb-3"><i class="icon-user"></i> Face Enrollment Management</h5>
                            <p class="text-muted mb-3" style="font-size:13px;">View and manage employee face enrollments.</p>
                            <a href="{{ route('face-enrollment.index') }}" class="btn btn-custom-default">
                                <i class="icon-user"></i> Manage Face Enrollments
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TAB 4: API Integration --}}
                @if($canAttendanceApiIntegration)
                <div class="tab-pane fade" id="api-integration" role="tabpanel" aria-labelledby="api-integration-tab">
                    <div class="card no-border">
                        <div class="content_wrapper" style="padding:20px;">
                            <p class="text-muted">API Integration documentation is available in the admin section.</p>
                            <a href="{{ route('attendance.api-docs') }}" class="btn btn-custom">View API Documentation</a>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<style>
    .nav-link.active {
        border-bottom-color: #4f46e5 !important;
        color: #4f46e5 !important;
    }
    .nav-link {
        cursor: pointer;
        transition: all 0.2s;
    }
    .nav-link:hover {
        color: #4f46e5 !important;
    }
</style>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>

<script>
    (function () {
        if (!$.fn.datepicker) {
            return;
        }

        $('.attendance-date-picker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        if ($.fn.timepicker) {
            $('.attendance-time-picker').timepicker({
                timeFormat: 'h:mm p',
                step: 5,
                scrollDefault: 'now',
                forceRoundTime: true,
                dropdown: true
            });
        }

        var attendanceFile = document.getElementById('attendance_file');
        var attendanceFileName = document.getElementById('attendance_file_name');
        if (attendanceFile && attendanceFileName) {
            attendanceFile.addEventListener('change', function () {
                if (attendanceFile.files && attendanceFile.files.length > 0) {
                    attendanceFileName.textContent = attendanceFile.files[0].name;
                } else {
                    attendanceFileName.textContent = 'No file selected';
                }
            });
        }

        // Tab switching - update URL without reload
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function() {
                const target = this.getAttribute('data-bs-target');
                window.history.replaceState({}, '', window.location.pathname + '#' + target.substring(1));
            });
        });
    })();
</script>
@endpush
