<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->foreignId('installment_schedule_id')->nullable()->after('fee_invoice_id')->constrained()->nullOnDelete());

        Schema::create('school_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title'); $table->text('description')->nullable();
            $table->string('audience', 30)->default('all');
            $table->dateTime('starts_at'); $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable(); $table->timestamps();
            $table->index(['school_id', 'starts_at']);
        });

        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->text('instructions');
            $table->dateTime('due_at'); $table->timestamp('published_at')->nullable();
            $table->timestamps(); $table->index(['school_id', 'class_id', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
        Schema::dropIfExists('school_events');
        Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('installment_schedule_id'));
    }
};
