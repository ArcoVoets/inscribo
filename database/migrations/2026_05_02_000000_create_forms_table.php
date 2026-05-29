<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique('event_id');

            $table->unsignedInteger('base_price_cents');

            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }
};
