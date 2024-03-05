<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::unprepared('
            CREATE OR REPLACE FUNCTION send_notify(data json) RETURNS VOID AS $$
            BEGIN
                PERFORM pg_notify(\'my_event\', data::text);
            END;
            $$ LANGUAGE plpgsql;
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS send_notify(json);');
    }
};
