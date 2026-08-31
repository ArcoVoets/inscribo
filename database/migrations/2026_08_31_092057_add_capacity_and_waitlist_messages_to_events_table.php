<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('capacity_full_title')->nullable();
            $table->text('capacity_full_description')->nullable();
            $table->string('waitlist_active_title')->nullable();
            $table->text('waitlist_active_description')->nullable();
        });

        DB::table('events')->update([
            'capacity_full_title' => __('admin.events.form.default_messages.capacity_full_title'),
            'capacity_full_description' => __('admin.events.form.default_messages.capacity_full_description'),
            'waitlist_active_title' => __('admin.events.form.default_messages.waitlist_active_title'),
            'waitlist_active_description' => __('admin.events.form.default_messages.waitlist_active_description'),
        ]);

        Schema::table('events', function (Blueprint $table): void {
            $table->string('capacity_full_title')->change();
            $table->text('capacity_full_description')->change();
            $table->string('waitlist_active_title')->change();
            $table->text('waitlist_active_description')->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn([
                'capacity_full_title',
                'capacity_full_description',
                'waitlist_active_title',
                'waitlist_active_description',
            ]);
        });
    }
};
