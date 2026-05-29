<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('mollie_payment_id')->unique();
            $table->string('status');
            $table->timestamp('occured_at')->nullable();

            $table->timestamps();

            $table->index(['registration_id', 'created_at']);
        });
    }
};
