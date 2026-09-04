<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('users', fn (Blueprint $table) => $table->foreignId('student_id')->nullable()->unique()->after('school_id')->constrained()->nullOnDelete()); }
    public function down(): void { Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('student_id')); }
};
