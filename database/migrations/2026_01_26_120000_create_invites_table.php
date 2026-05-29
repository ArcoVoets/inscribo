<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('email');

            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            // Stored encrypted so admins can copy the invite URL later.
            $table->text('token');

            $table->dateTime('expires_at')->nullable()->index();

            $table->dateTime('revoked_at')->nullable()->index();

            $table->dateTime('used_at')->nullable()->index();

            $table->foreignId('used_registration_id')
                ->nullable()
                ->constrained('registrations')
                ->nullOnDelete();

            $table->timestamps();
        });
    }
};
