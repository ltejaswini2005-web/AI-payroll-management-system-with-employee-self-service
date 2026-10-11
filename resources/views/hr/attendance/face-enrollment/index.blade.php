@extends('layouts.backend')
@section('title', 'Face Enrollment Management')

@section('content')
<div class="wrapper-page">
    <div class="page-title d-flex justify-content-between align-items-center">
        <h1><i class="icon-face"></i> Face Enrollment Management</h1>
    </div>

    @include('partials.flash')

    <div class="page-content">
        <div class="container-fluid">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="content_wrapper" style="padding:20px; text-align:center;">
                            <div style="font-size:32px; font-weight:bold; color:#667eea;">{{ $stats['total_active_enrollments'] }}</div>
                            <div style="color:#999; margin-top:8px;">Active Enrollments</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="content_wrapper" style="padding:20px; text-align:center;">
                            <div style="font-size:32px; font-weight:bold; color:#28a745;">{{ $stats['total_employees'] }}</div>
                            <div style="color:#999; margin-top:8px;">Total Employees</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="content_wrapper" style="padding:20px; text-align:center;">
                            <div style="font-size:32px; font-weight:bold; color:#ff6b6b;">{{ $stats['enrollment_rate_percent'] }}%</div>
                            <div style="color:#999; margin-top:8px;">Enrollment Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employees List -->
            <div class="card no-border">
                <div class="content_wrapper" style="padding:20px;">
                    <h5 class="table_banner_title mb-3">Enrollment Status</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Code</th>
                                    <th>Status</th>
                                    <th>Enrolled Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>
                                            <strong>{{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</strong>
                                        </td>
                                        <td>{{ $employee->employee_code }}</td>
                                        <td>
                                            @if($employee->faceEnrollment)
                                                <span class="badge bg-success">✓ Enrolled</span>
                                            @else
                                                <span class="badge bg-secondary">Not Enrolled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->faceEnrollment)
                                                {{ $employee->faceEnrollment->enrolled_at->format('M d, Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('face-enrollment.form', $employee->id) }}" class="btn btn-sm btn-custom">
                                                <i class="icon-edit"></i> Enroll/Update
                                            </a>
                                            @if($employee->faceEnrollment)
                                                <form action="{{ route('face-enrollment.deactivate', $employee->faceEnrollment->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate enrollment?')">
                                                        <i class="icon-trash"></i> Deactivate
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align:center; padding:30px;">No employees found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="margin-top:20px;">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
