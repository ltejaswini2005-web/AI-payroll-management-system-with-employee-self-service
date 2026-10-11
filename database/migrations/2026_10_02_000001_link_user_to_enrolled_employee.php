<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Link a user account to the enrolled employee (ID=1) if not already linked.
     * 
     * The face enrollment exists but the employee doesn't have an associated user,
     * which prevents liveness verification from completing.
     * 
     * This migration:
     * 1. Checks if employee ID=1 has an active face enrollment
     * 2. If yes and employee has no user, creates a test user and links it
     * 3. Or links to existing admin user if available
     */
    public function up(): void
    {
        // Find the enrolled employee
        $enrollment = DB::table('face_enrollments')
            ->where('is_active', true)
            ->where('employee_id', 1)
            ->first();

        if ($enrollment) {
            $employee = Employee::find(1);
            
            // If employee exists and has no user
            if ($employee && !$employee->user_id) {
                // Try to link to admin user first
                $admin = User::where('email', 'admin@hr-payroll.local')->first();
                
                if ($admin) {
                    // Link employee to admin user
                    DB::table('employees')
                        ->where('id', 1)
                        ->update(['user_id' => $admin->id]);
                } else {
                    $initialEmployeePassword = config('app.initial_employee_password');
                    if (! is_string($initialEmployeePassword) || strlen($initialEmployeePassword) < 12) {
                        throw new RuntimeException('Set EMPLOYEE_INITIAL_PASSWORD to a private value with at least 12 characters before this migration creates an account.');
                    }

                    // Create a test user for this employee
                    $user = User::create([
                        'name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                        'email' => $employee->work_email ?? 'employee' . $employee->id . '@hr-payroll.local',
                        'password' => Hash::make($initialEmployeePassword),
                        'account_status' => 'active',
                        'approved_at' => now(),
                    ]);
                    
                    // Link employee to this user
                    DB::table('employees')
                        ->where('id', 1)
                        ->update(['user_id' => $user->id]);
                }
            }
        }
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Don't undo user linking as it might remove legitimate account associations
        // If needed, manually fix the user_id field
    }
};
