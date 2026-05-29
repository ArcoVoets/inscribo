<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('field_id')
                ->constrained('form_fields')
                ->restrictOnDelete();

            $table->foreignId('option_id')
                ->nullable()
                ->constrained('form_field_options')
                ->nullOnDelete();

            $table->integer('option_price_cents')->default(0);
            $table->text('value')->nullable();

            $table->timestamps();

            $table->unique(['registration_id', 'field_id']);
        });
    }
};
