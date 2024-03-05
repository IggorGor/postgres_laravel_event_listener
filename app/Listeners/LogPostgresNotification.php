<?php

namespace App\Listeners;

use App\Events\PostgresNotificationReceived;
use Illuminate\Support\Facades\Log;

class LogPostgresNotification
{

    public function handle(PostgresNotificationReceived $event): void
    {
        Log::info('Received Postgres notification: ', $event->notification);
    }
}
