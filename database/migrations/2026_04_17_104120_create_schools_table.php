<?php
// database/migrations/2024_01_01_000001_create_schools_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->string('logo')->nullable();
            $table->string('theme_color')->default('#6366f1');
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscription_end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schools');
    }
};