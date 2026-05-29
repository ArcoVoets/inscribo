<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_states', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained()
                ->cascadeOnDelete();

            // Used by Parental for single-table inheritance.
            $table->string('type');

            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('amount_cents')->nullable();

            $table->timestamps();

            $table->index(['registration_id', 'created_at']);
        });
    }
};
