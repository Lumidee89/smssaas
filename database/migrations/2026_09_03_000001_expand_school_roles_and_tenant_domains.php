<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('role', 40)->default('parent')->change());
        Schema::table('schools', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->unique()->after('subdomain');
            $table->string('tenant_schema')->nullable()->unique()->after('custom_domain');
            $table->timestamp('domain_verified_at')->nullable()->after('tenant_schema');
            $table->timestamp('provisioned_at')->nullable()->after('domain_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('schools', fn (Blueprint $table) => $table->dropColumn([
            'custom_domain', 'tenant_schema', 'domain_verified_at', 'provisioned_at',
        ]));
    }
};
