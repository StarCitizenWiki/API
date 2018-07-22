<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

/**
 * Class NotificationsSeeder
 */
class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notifications')->insert(
            [
                'level' => 0,
                'content' => '這是一個測試消息 你好，你好嗎',
                'output_email' => false,
                'output_status' => true,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYears(10),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 0,
                'content' => 'Wartungsarbeiten am 30.08',
                'output_email' => true,
                'output_status' => false,
                'created_at' => \Carbon\Carbon::now()->subHours(2),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYears(10),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 1,
                'content' => 'RSI Abfragen derzeit nicht möglich',
                'output_email' => true,
                'output_status' => true,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYears(10),
                'output_index' => true,
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 2,
                'content' => 'Probleme bei der Ausgabe von Statistik-Informationen.',
                'output_email' => true,
                'output_status' => false,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYears(10),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 3,
                'content' => 'Aufgrund eines Dateisystemfehlers konnten in den letzten Stunden keine Daten gespeichert werden. Ein Serverumzug erfolgt in Küze.',
                'output_email' => false,
                'output_status' => true,
                'created_at' => \Carbon\Carbon::now()->subMinutes(50),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now(),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 1,
                'content' => 'Leichter Verzögerungen bei API-Abfragen',
                'output_email' => false,
                'output_status' => true,
                'created_at' => \Carbon\Carbon::now()->subHour(),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYears(10),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );

        DB::table('notifications')->insert(
            [
                'level' => 0,
                'content' => 'Wartungsarbeiten abgeschlossen',
                'output_email' => true,
                'output_status' => true,
                'created_at' => \Carbon\Carbon::now()->subYear(),
                'updated_at' => \Carbon\Carbon::now(),
                'expired_at' => \Carbon\Carbon::now()->addYear(),
                'published_at' => \Carbon\Carbon::now(),
            ]
        );
    }
}
