<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('accent_color_title_and_button')->nullable();
            $table->string('accent_color_required_and_hover')->nullable();
            $table->string('accent_color_label_and_radio')->nullable();
            $table->string('accent_color_section_title')->nullable();
        });
    }
};
