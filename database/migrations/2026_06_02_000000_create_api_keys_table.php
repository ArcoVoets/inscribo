<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('key');
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
