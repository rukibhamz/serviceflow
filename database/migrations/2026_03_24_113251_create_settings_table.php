<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        DB::table('settings')->insert([
            ['key' => 'brand_name',    'value' => 'ServiceFlow', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'brand_logo',    'value' => null,           'created_at' => now(), 'updated_at' => now()],
            ['key' => 'theme_preset',  'value' => 'blue',         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'theme_primary', 'value' => '#1a4fa0',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'theme_accent',  'value' => '#f97316',      'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
