<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table): void {
            $table->foreignId('dependency_field_id')
                ->nullable()
                ->after('default_option_id')
                ->constrained('form_fields')
                ->nullOnDelete();

            $table->foreignId('dependency_option_id')
                ->nullable()
                ->after('dependency_field_id')
                ->constrained('form_field_options')
                ->nullOnDelete();

            $table->boolean('dependency_equals')
                ->nullable()
                ->after('dependency_option_id');
        });
    }
};
