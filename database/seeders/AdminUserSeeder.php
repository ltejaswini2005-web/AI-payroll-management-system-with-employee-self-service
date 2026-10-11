<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed default admin user.
     */
    public function run(): void
    {
        $initialAdminPassword = config('app.initial_admin_password');
        if (! is_string($initialAdminPassword) || strlen($initialAdminPassword) < 12) {
            throw new RuntimeException('Set ADMIN_INITIAL_PASSWORD to a private value with at least 12 characters before running this seeder.');
        }

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@hr-payroll.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make($initialAdminPassword),
                'account_status' => 'active',
                'approved_at' => now(),
                'rejected_reason' => null,
            ]
        );

        $superAdminRole = Role::query()->where('slug', 'super-admin')->first();

        if ($superAdminRole) {
            $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
