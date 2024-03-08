<?php

namespace App\Console\Commands;

use App\Events\PostgresNotificationReceived;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PDO;

class ListenNotifyCommand extends Command
{
    protected $signature = 'listen:notify';

    protected $description = 'Listen to PostgreSQL notify events';

    protected bool $hasPcntl = false;
    protected bool $running = true;


    public function handle(): int
    {
        $this->hasPcntl = extension_loaded('pcntl');

        $pdo = DB::connection()->getPdo();
        $pdo->exec("LISTEN my_event");
        $this->info('Start listening');

        while ($this->running) {
            $notification = $pdo->pgsqlGetNotify(PDO::FETCH_ASSOC, 10000);

            if ($notification) {
                $this->info('Received notification: ' . json_encode($notification, JSON_THROW_ON_ERROR));
                $payload = json_decode($notification['payload'], true, 512, JSON_THROW_ON_ERROR);
                $this->info('Decoded payload: ' . print_r($payload, true));
                Event::dispatch(new PostgresNotificationReceived($payload));
            }

            if ($this->hasPcntl) {
                $this->trap([SIGINT, SIGTERM], function () {
                    $this->info( PHP_EOL . 'Received stop signal, shutting down...');
                    $this->running = false;
                });
            }
        }

        return 0;

    }

}
