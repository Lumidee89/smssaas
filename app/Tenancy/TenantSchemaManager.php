<?php

namespace App\Tenancy;

use App\Models\School;
use Illuminate\Support\Facades\DB;

class TenantSchemaManager
{
    /** Tables whose rows are physically isolated for each institution. */
    public const TABLES = [
        'campuses', 'faculties', 'departments', 'courses', 'course_registrations',
        'classes', 'subjects', 'teacher_subject', 'class_subject', 'students', 'parent_student',
        'academic_years', 'academic_terms', 'attendance_records', 'announcements',
        'grading_policies', 'grading_bands', 'assessments', 'assessment_scores', 'results', 'transcripts',
        'fee_invoices', 'fee_invoice_items', 'installment_schedules', 'scholarships', 'scholarship_student',
        'expenses', 'payroll_runs', 'payroll_items', 'payments', 'conversations', 'conversation_user', 'messages',
        'device_tokens', 'cbt_exams', 'cbt_questions', 'cbt_exam_question', 'cbt_attempts', 'cbt_answers',
        'staff_attendance_records', 'student_development_signals', 'student_success_scores', 'student_interventions',
        'ai_generation_requests', 'school_events', 'homework_assignments', 'audit_logs',
        'hostel_buildings', 'hostel_rooms', 'hostel_allocations', 'library_items', 'library_loans',
        'transport_routes', 'transport_vehicles', 'transport_assignments', 'wallets', 'wallet_transactions',
        'marketplace_vendors', 'marketplace_products', 'marketplace_orders', 'marketplace_order_items',
    ];

    public function provision(School $school): void
    {
        if (config('tenancy.mode') === 'shared') {
            $school->forceFill(['tenant_schema' => null, 'provisioned_at' => now()])->save();
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            throw new \RuntimeException('TENANCY_MODE=schema requires PostgreSQL. Use TENANCY_MODE=shared for MySQL.');
        }

        $schema = $school->tenant_schema ?: config('tenancy.schema_prefix').$school->id;
        $this->assertIdentifier($schema);
        DB::statement(sprintf('CREATE SCHEMA IF NOT EXISTS "%s"', $schema));
        foreach (self::TABLES as $table) {
            if (! $this->publicTableExists($table)) continue;
            DB::statement(sprintf('CREATE TABLE IF NOT EXISTS "%s"."%s" (LIKE public."%s" INCLUDING ALL)', $schema, $table, $table));
        }
        $school->forceFill(['tenant_schema' => $schema, 'provisioned_at' => now()])->save();
    }

    public function activate(School $school): void
    {
        if (config('tenancy.mode') !== 'schema') return;
        abort_unless(DB::getDriverName() === 'pgsql', 503, 'Schema tenancy requires PostgreSQL; configure shared tenancy for MySQL.');
        abort_unless($school->tenant_schema && $school->provisioned_at, 503, 'Tenant workspace is not provisioned.');
        $this->assertIdentifier($school->tenant_schema);
        DB::statement(sprintf('SET search_path TO "%s", public', $school->tenant_schema));
    }

    public function reset(): void
    {
        if (DB::getDriverName() === 'pgsql') DB::statement('SET search_path TO public');
    }

    private function publicTableExists(string $table): bool
    {
        return (bool) DB::selectOne('SELECT to_regclass(?) AS name', ['public.'.$table])?->name;
    }

    private function assertIdentifier(string $schema): void
    {
        if (! preg_match('/^[a-z][a-z0-9_]{1,62}$/', $schema)) throw new \InvalidArgumentException('Invalid tenant schema identifier.');
    }
}
