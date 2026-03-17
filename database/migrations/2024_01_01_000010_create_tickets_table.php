<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ulid')->unique();
            $table->string('subject');
            $table->longText('description')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('medium');
            $table->string('type')->default('incident');
            $table->string('source')->default('web');
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedBigInteger('merged_into_id')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('type');
            $table->index('requester_id');
            $table->index('assignee_id');
            $table->index('team_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
