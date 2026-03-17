<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_relations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->unsignedBigInteger('related_ticket_id');
            $table->string('relation_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_relations');
    }
};
