<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->json('confirmation_mail_addresses')->nullable();
        });

        DB::table('events')
            ->whereNotNull('confirmation_mail_address')
            ->orderBy('id')
            ->chunkById(100, function (Collection $events): void {
                foreach ($events as $event) {
                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'confirmation_mail_addresses' => json_encode(
                                [$event->confirmation_mail_address],
                                JSON_THROW_ON_ERROR,
                            ),
                        ]);
                }
            });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('confirmation_mail_address');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('confirmation_mail_address')->nullable();
        });

        DB::table('events')
            ->whereNotNull('confirmation_mail_addresses')
            ->orderBy('id')
            ->chunkById(100, function (Collection $events): void {
                foreach ($events as $event) {
                    $addresses = json_decode($event->confirmation_mail_addresses, true, 512, JSON_THROW_ON_ERROR);

                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'confirmation_mail_address' => $addresses[0] ?? null,
                        ]);
                }
            });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('confirmation_mail_addresses');
        });
    }
};
