<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teams') && ! Schema::hasColumn('teams', 'team_lead_id')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->unsignedBigInteger('team_lead_id')->nullable()->after('tenant_id');
                $table->index('team_lead_id');
                $table->foreign('team_lead_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teams') && Schema::hasColumn('teams', 'team_lead_id')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropForeign(['team_lead_id']);
                $table->dropIndex(['team_lead_id']);
                $table->dropColumn('team_lead_id');
            });
        }
    }
};

