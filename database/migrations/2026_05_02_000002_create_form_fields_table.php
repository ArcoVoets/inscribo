<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_id')
                ->constrained('forms')
                ->cascadeOnDelete();

            $table->foreignId('section_id')
                ->constrained('form_sections')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->string('type', 20);
            $table->foreignId('default_option_id')->nullable();
            $table->boolean('hide_option_price');
            $table->unsignedTinyInteger('width')->default(100);
            $table->boolean('required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['form_id', 'name']);
        });
    }
};
