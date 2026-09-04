<?php
// database/migrations/2024_01_01_000011_add_price_columns_to_schools_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->enum('subscription_plan', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->decimal('monthly_price', 10, 2)->default(49.00);
            $table->decimal('yearly_price', 10, 2)->default(39.00);
            $table->integer('max_students')->default(200);
            $table->integer('max_teachers')->default(10);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_advanced_analytics')->default(false);
            $table->boolean('has_priority_support')->default(false);
        });
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan', 'monthly_price', 'yearly_price',
                'max_students', 'max_teachers', 'has_api_access',
                'has_advanced_analytics', 'has_priority_support'
            ]);
        });
    }
};