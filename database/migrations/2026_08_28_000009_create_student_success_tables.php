<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->time('scheduled_at')->nullable();
            $table->time('checked_in_at')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'attendance_date']);
            $table->index(['school_id', 'attendance_date', 'status']);
        });

        Schema::create('student_development_signals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('category', ['behaviour', 'leadership']);
            $table->unsignedTinyInteger('score');
            $table->text('note');
            $table->date('occurred_on');
            $table->timestamps();
            $table->index(['school_id', 'student_id', 'category', 'occurred_on'], 'development_signals_lookup');
        });

        Schema::create('student_success_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('calculated_on');
            $table->decimal('score', 5, 2);
            $table->decimal('academic_score', 5, 2);
            $table->decimal('attendance_score', 5, 2);
            $table->decimal('assignment_score', 5, 2);
            $table->decimal('behaviour_score', 5, 2);
            $table->decimal('leadership_score', 5, 2);
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->decimal('trend', 6, 2)->default(0);
            $table->json('risk_factors');
            $table->json('recommendations');
            $table->json('data_quality');
            $table->timestamps();
            $table->unique(['student_id', 'calculated_on']);
            $table->index(['school_id', 'risk_level', 'score']);
        });

        Schema::create('student_interventions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('action_plan');
            $table->date('due_on')->nullable();
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_interventions');
        Schema::dropIfExists('student_success_scores');
        Schema::dropIfExists('student_development_signals');
        Schema::dropIfExists('staff_attendance_records');
    }
};
