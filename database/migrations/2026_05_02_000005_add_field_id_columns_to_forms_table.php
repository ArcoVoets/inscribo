<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table
                ->foreignId('email_field_id')
                ->nullable()
                ->constrained('form_fields')
                ->nullOnDelete()
                ->after('description');

            $table
                ->foreignId('name_field_id')
                ->nullable()
                ->constrained('form_fields')
                ->nullOnDelete()
                ->after('email_field_id');
        });
    }
};
