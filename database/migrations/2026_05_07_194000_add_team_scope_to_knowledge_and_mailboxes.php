<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('article_categories', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('id');
                $table->index('team_id');
            }
        });

        Schema::table('knowledge_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('knowledge_articles', 'team_id')) {
                $table->unsignedBigInteger('team_id')->nullable()->after('id');
                $table->index('team_id');
            }
        });

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'inbound_email')) {
                $table->string('inbound_email')->nullable()->after('description');
                $table->boolean('inbound_email_enabled')->default(false)->after('inbound_email');
                $table->index('inbound_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (Schema::hasColumn('teams', 'inbound_email_enabled')) {
                $table->dropColumn('inbound_email_enabled');
            }
            if (Schema::hasColumn('teams', 'inbound_email')) {
                $table->dropIndex(['inbound_email']);
                $table->dropColumn('inbound_email');
            }
        });

        Schema::table('knowledge_articles', function (Blueprint $table) {
            if (Schema::hasColumn('knowledge_articles', 'team_id')) {
                $table->dropIndex(['team_id']);
                $table->dropColumn('team_id');
            }
        });

        Schema::table('article_categories', function (Blueprint $table) {
            if (Schema::hasColumn('article_categories', 'team_id')) {
                $table->dropIndex(['team_id']);
                $table->dropColumn('team_id');
            }
        });
    }
};

