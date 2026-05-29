<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('field_id')
                ->constrained('form_fields')
                ->cascadeOnDelete();

            $table->string('label');
            $table->string('value');
            $table->integer('price_cents')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['field_id', 'value']);
        });
    }
};
