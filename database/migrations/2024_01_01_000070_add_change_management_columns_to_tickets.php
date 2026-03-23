<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('change_type')->nullable()->after('type');       // normal, standard, emergency
            $table->string('risk_level')->nullable()->after('change_type'); // low, medium, high
            $table->boolean('cab_approval_required')->default(false)->after('risk_level');
            $table->timestamp('scheduled_at')->nullable()->after('cab_approval_required');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['change_type', 'risk_level', 'cab_approval_required', 'scheduled_at']);
        });
    }
};
