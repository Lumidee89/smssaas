<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Tenancy\TenantSchemaManager;
use Illuminate\Console\Command;

class ProvisionTenantSchemas extends Command
{
    protected $signature = 'schoolos:provision-tenants {--school=}';
    protected $description = 'Create isolated PostgreSQL schemas for SchoolOS tenants';
    public function handle(TenantSchemaManager $schemas): int
    {
        School::query()->when($this->option('school'), fn ($query, $id) => $query->whereKey($id))->eachById(function ($school) use ($schemas) {
            $schemas->provision($school); $this->info("Provisioned {$school->name} [{$school->tenant_schema}]");
        });
        return self::SUCCESS;
    }
}
