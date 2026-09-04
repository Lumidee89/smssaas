<?php
// database/migrations/2024_01_01_000004_create_subjects_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('credit_hours')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Create teacher_subject pivot table
        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate entries
            $table->unique(['teacher_id', 'subject_id']);
        });

        // Create class_subject pivot table
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate entries
            $table->unique(['class_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_subject');
        Schema::dropIfExists('teacher_subject');
        Schema::dropIfExists('subjects');
    }
};