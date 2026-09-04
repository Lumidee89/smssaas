<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false', 'short_text']);
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->text('correct_answers');
            $table->text('explanation')->nullable();
            $table->decimal('default_points', 8, 2)->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['school_id', 'subject_id', 'is_active']);
        });

        Schema::create('cbt_exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->decimal('pass_percentage', 5, 2)->default(50);
            $table->string('access_code_hash')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'class_id', 'status']);
        });

        Schema::create('cbt_exam_question', function (Blueprint $table): void {
            $table->foreignId('cbt_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained()->cascadeOnDelete();
            $table->decimal('points', 8, 2);
            $table->unsignedSmallInteger('position');
            $table->primary(['cbt_exam_id', 'cbt_question_id']);
            $table->unique(['cbt_exam_id', 'position']);
        });

        Schema::create('cbt_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('token_hash', 64)->unique();
            $table->json('question_order');
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'pending_review', 'graded', 'expired'])->default('in_progress');
            $table->decimal('score', 10, 2)->default(0);
            $table->decimal('maximum_score', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['cbt_exam_id', 'student_id', 'attempt_number']);
        });

        Schema::create('cbt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->decimal('awarded_points', 8, 2)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('requires_review')->default(false);
            $table->text('review_comment')->nullable();
            $table->timestamps();
            $table->unique(['cbt_attempt_id', 'cbt_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_answers');
        Schema::dropIfExists('cbt_attempts');
        Schema::dropIfExists('cbt_exam_question');
        Schema::dropIfExists('cbt_exams');
        Schema::dropIfExists('cbt_questions');
    }
};
