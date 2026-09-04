<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();$table->string('code')->unique();$table->string('name');$table->decimal('monthly_price',14,2)->nullable();$table->char('currency',3)->default('NGN');$table->unsignedInteger('max_students')->nullable();$table->unsignedInteger('max_staff')->nullable();$table->json('features');$table->boolean('is_active')->default(true);$table->unsignedSmallInteger('sort_order')->default(0);$table->timestamps();
        });
        Schema::create('school_subscriptions', function (Blueprint $table): void {
            $table->id();$table->foreignId('school_id')->constrained()->cascadeOnDelete();$table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();$table->enum('status',['trialing','active','past_due','suspended','cancelled','expired'])->default('trialing');$table->dateTime('starts_at');$table->dateTime('trial_ends_at')->nullable();$table->dateTime('current_period_ends_at')->nullable();$table->dateTime('cancelled_at')->nullable();$table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();$table->timestamps();$table->index(['school_id','status']);
        });
        Schema::create('subscription_payments', function (Blueprint $table): void {
            $table->id();$table->foreignId('school_id')->constrained()->cascadeOnDelete();$table->foreignId('school_subscription_id')->constrained()->cascadeOnDelete();$table->string('reference')->unique();$table->decimal('amount',14,2);$table->char('currency',3)->default('NGN');$table->enum('status',['pending','paid','failed','refunded'])->default('pending');$table->string('provider')->nullable();$table->string('provider_reference')->nullable();$table->dateTime('paid_at')->nullable();$table->json('metadata')->nullable();$table->timestamps();$table->index(['school_id','status','created_at']);
        });
        $now=now();
        DB::table('subscription_plans')->insert([
            ['code'=>'starter','name'=>'Starter','monthly_price'=>25000,'currency'=>'NGN','max_students'=>300,'max_staff'=>30,'features'=>json_encode(['academics','finance','attendance','messaging']),'is_active'=>1,'sort_order'=>1,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'growth','name'=>'Growth','monthly_price'=>60000,'currency'=>'NGN','max_students'=>1500,'max_staff'=>150,'features'=>json_encode(['academics','finance','attendance','messaging','parent_app','analytics','cbt']),'is_active'=>1,'sort_order'=>2,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'university','name'=>'University','monthly_price'=>150000,'currency'=>'NGN','max_students'=>10000,'max_staff'=>1000,'features'=>json_encode(['academics','finance','attendance','messaging','parent_app','analytics','cbt','ai','api']),'is_active'=>1,'sort_order'=>3,'created_at'=>$now,'updated_at'=>$now],
            ['code'=>'enterprise','name'=>'Enterprise','monthly_price'=>null,'currency'=>'NGN','max_students'=>null,'max_staff'=>null,'features'=>json_encode(['all','white_label','priority_support']),'is_active'=>1,'sort_order'=>4,'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
    public function down(): void {Schema::dropIfExists('subscription_payments');Schema::dropIfExists('school_subscriptions');Schema::dropIfExists('subscription_plans');}
};
