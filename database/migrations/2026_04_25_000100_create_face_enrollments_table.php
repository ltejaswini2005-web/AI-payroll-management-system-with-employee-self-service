<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Face enrollment table for face recognition attendance system.
     */
    public function up(): void
    {
        Schema::create('face_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->longText('face_descriptor'); // Stores face descriptor as JSON
            $table->string('photo_path')->nullable(); // Path to enrollment reference photo
            $table->boolean('is_active')->default(true);
            $table->foreignId('enrolled_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamps();

            // Ensure one active enrollment per employee
            $table->unique(['employee_id', 'is_active'], 'face_enrollments_employee_active_unique');
            $table->index(['employee_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_enrollments');
    }
};
