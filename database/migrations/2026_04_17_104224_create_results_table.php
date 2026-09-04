<?php
// database/migrations/2024_01_01_000006_create_results_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->enum('exam_type', ['test', 'exam']);
            $table->string('term');
            $table->integer('academic_year');
            $table->decimal('score', 5, 2);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Prevent duplicate results
            $table->unique(['student_id', 'subject_id', 'term', 'academic_year', 'exam_type'], 'unique_result');
        });
    }

    public function down()
    {
        Schema::dropIfExists('results');
    }
};