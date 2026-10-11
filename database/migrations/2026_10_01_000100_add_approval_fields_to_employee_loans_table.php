<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add approval workflow fields to employee_loans table.
     * Also change default status to 'pending' to support the approval workflow.
     */
    public function up(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {
            // Add loan_type for categorisation (personal, education, medical, etc.)
            $table->string('loan_type', 50)->default('personal')->after('employee_id');
            // requested_by: the user who submitted the loan request
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete()->after('remarks');
            // Approval fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('requested_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_remarks')->nullable()->after('approved_at');
            // The amount the admin actually approves (may differ from requested principal_amount)
            $table->decimal('approved_amount', 14, 2)->nullable()->after('approval_remarks');
        });

        // Update existing 'active' records to keep them as 'active' (already disbursed loans).
        // New loans submitted via Add Loan form will have status = 'pending'.
        // This migration does NOT reset existing data.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['loan_type', 'approved_at', 'approval_remarks', 'approved_amount']);
        });
    }
};
