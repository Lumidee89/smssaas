<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('otp_challenges', function(Blueprint $table){ $table->id(); $table->string('phone',32)->index(); $table->string('purpose',30); $table->string('code_hash'); $table->unsignedTinyInteger('attempts')->default(0); $table->timestamp('expires_at'); $table->timestamp('consumed_at')->nullable(); $table->timestamps(); $table->index(['phone','purpose','expires_at']); }); }
 public function down(): void { Schema::dropIfExists('otp_challenges'); }
};
