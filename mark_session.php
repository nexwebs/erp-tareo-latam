<?php

use Illuminate\Support\Facades\DB;

DB::statement("INSERT INTO migrations (migration, batch) VALUES ('2026_04_22_192232_create_sessions_table', 9999) ON DUPLICATE KEY UPDATE batch = 9999");
echo "Done\n";
