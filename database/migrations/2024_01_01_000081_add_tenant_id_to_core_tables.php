<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tenant_id to all core tenant-scoped tables.
 * tenant_id is nullable to allow single-tenant deployments without migration changes.
 */
return new class extends Migration
{
    private array $tables = [
        'tickets',
        'ticket_comments',
        'sla_policies',
        'sla_timers',
        'knowledge_articles',
        'assets',
        'automations',
        'csat_surveys',
        'users',
        'teams',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $t->index('tenant_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropIndex(['tenant_id']);
                    $t->dropColumn('tenant_id');
                });
            }
        }
    }
};
