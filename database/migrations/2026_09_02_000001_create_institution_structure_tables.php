<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('campuses')) {
            Schema::create('campuses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 30);
                $table->string('email')->nullable();
                $table->string('phone', 40)->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_main')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['school_id', 'code']);
            });
        }

        if (! Schema::hasTable('faculties')) {
            Schema::create('faculties', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('code', 30);
                $table->foreignId('dean_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['school_id', 'code']);
            });
        }

        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('faculty_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('code', 30);
                $table->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['school_id', 'code']);
            });
        }

        if (! Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('code', 40);
                $table->unsignedSmallInteger('credit_units')->default(1);
                $table->unsignedSmallInteger('level')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['school_id', 'code']);
            });
        }

        if (! Schema::hasTable('course_registrations')) {
            Schema::create('course_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
                $table->foreignId('course_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
                $table->string('status', 20)->default('registered');
                $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
                $table->timestamp('registered_at');
                $table->timestamps();
                $table->unique(['student_id', 'course_id', 'academic_term_id'], 'cr_student_course_term_unique');
                $table->index(['school_id', 'academic_term_id'], 'cr_school_term_index');
            });
        } else {
            Schema::table('course_registrations', function (Blueprint $table) {
                if (! Schema::hasIndex('course_registrations', 'cr_student_course_term_unique')) {
                    $table->unique(['student_id', 'course_id', 'academic_term_id'], 'cr_student_course_term_unique');
                }
                if (! Schema::hasIndex('course_registrations', 'cr_school_term_index')) {
                    $table->index(['school_id', 'academic_term_id'], 'cr_school_term_index');
                }
            });
        }

        if (! Schema::hasColumn('users', 'campus_id')) {
            Schema::table('users', fn (Blueprint $table) => $table->foreignId('campus_id')->nullable()->after('school_id')->constrained()->nullOnDelete());
        }
        if (! Schema::hasColumn('classes', 'campus_id')) {
            Schema::table('classes', fn (Blueprint $table) => $table->foreignId('campus_id')->nullable()->after('school_id')->constrained()->nullOnDelete());
        }
        foreach (['campus_id' => 'school_id', 'faculty_id' => 'campus_id', 'department_id' => 'faculty_id'] as $column => $after) {
            if (! Schema::hasColumn('students', $column)) {
                Schema::table('students', fn (Blueprint $table) => $table->foreignId($column)->nullable()->after($after)->constrained()->nullOnDelete());
            }
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('faculty_id');
            $table->dropConstrainedForeignId('campus_id');
        });
        Schema::table('classes', fn (Blueprint $table) => $table->dropConstrainedForeignId('campus_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('campus_id'));
        Schema::dropIfExists('course_registrations');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('faculties');
        Schema::dropIfExists('campuses');
    }
};
