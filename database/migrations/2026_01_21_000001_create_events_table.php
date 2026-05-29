<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('capacity');
            $table->dateTime('opens_at');
            $table->dateTime('closes_at')->nullable();

            $table->boolean('show_waitlist_position');
            $table->boolean('show_capacity_data');

            $table->string('home_url')->nullable();

            $table->boolean('wordpress_enabled')->default(false);
            $table->string('wordpress_form_page_url')->nullable();
            $table->string('wordpress_status_page_url')->nullable();

            $table->unsignedInteger('registration_expiration_minutes');

            $table->timestamps();
        });
    }
};
