<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_provident_funds', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_provident_funds', 'uan_number')) {
                $table->string('uan_number', 30)->nullable()->after('employee_id');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'pf_account_number')) {
                $table->string('pf_account_number', 50)->nullable()->after('uan_number');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'kyc_status')) {
                $table->string('kyc_status', 30)->default('Verified')->after('opening_balance');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'nominee_name')) {
                $table->string('nominee_name', 100)->nullable()->after('kyc_status');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'nominee_relation')) {
                $table->string('nominee_relation', 50)->nullable()->after('nominee_name');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'interest_rate_percent')) {
                $table->decimal('interest_rate_percent', 5, 2)->default(8.25)->after('nominee_relation');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'current_balance')) {
                $table->decimal('current_balance', 14, 2)->default(0)->after('interest_rate_percent');
            }
            if (! Schema::hasColumn('employee_provident_funds', 'pension_eps_balance')) {
                $table->decimal('pension_eps_balance', 14, 2)->default(0)->after('current_balance');
            }
        });

        if (! Schema::hasTable('provident_fund_transactions')) {
            Schema::create('provident_fund_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('wage_month', 30);
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->decimal('wage_amount', 14, 2)->default(0);
                $table->decimal('employee_share', 14, 2)->default(0);
                $table->decimal('employer_epf_share', 14, 2)->default(0);
                $table->decimal('employer_eps_share', 14, 2)->default(0);
                $table->decimal('total_deposited', 14, 2)->default(0);
                $table->decimal('running_balance', 14, 2)->default(0);
                $table->string('status', 20)->default('Credited');
                $table->date('transaction_date');
                $table->timestamps();

                $table->index(['employee_id', 'year', 'month']);
            });
        }

        if (! Schema::hasTable('provident_fund_claims')) {
            Schema::create('provident_fund_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->string('claim_reference', 50)->unique();
                $table->string('claim_type', 80);
                $table->string('purpose', 150);
                $table->decimal('amount', 14, 2);
                $table->string('status', 30)->default('Under Review');
                $table->date('submission_date');
                $table->date('approved_date')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provident_fund_claims');
        Schema::dropIfExists('provident_fund_transactions');

        Schema::table('employee_provident_funds', function (Blueprint $table) {
            $table->dropColumn([
                'uan_number',
                'pf_account_number',
                'kyc_status',
                'nominee_name',
                'nominee_relation',
                'interest_rate_percent',
                'current_balance',
                'pension_eps_balance',
            ]);
        });
    }
};
