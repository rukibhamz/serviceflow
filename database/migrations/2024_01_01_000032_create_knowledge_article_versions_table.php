<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_article_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_versions');
    }
};
