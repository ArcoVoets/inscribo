<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('host');
            $table->unsignedSmallInteger('port');
            $table->string('username');
            $table->text('password');
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->string('reply_to_address')->nullable();
            $table->timestamps();
        });
    }
};
