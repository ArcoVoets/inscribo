<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_mail_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('subject');
            $table->json('content');

            $table->timestamps();

            $table->unique(['event_id', 'type']);
        });
    }
};
