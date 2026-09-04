<?php
// database/migrations/2024_01_01_000015_rename_classes_id_to_class_id_in_students_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            // Check if columns exist before renaming
            if (Schema::hasColumn('students', 'classes_id')) {
                $table->renameColumn('classes_id', 'class_id');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'class_id')) {
                $table->renameColumn('class_id', 'classes_id');
            }
        });
    }
};