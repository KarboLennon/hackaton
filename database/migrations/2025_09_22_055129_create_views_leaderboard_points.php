<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement(<<<SQL
CREATE OR REPLACE VIEW v_user_points AS
SELECT user_id, COALESCE(SUM(points),0) AS points_balance
FROM detail_points_ledger
GROUP BY user_id;
SQL);

        DB::statement(<<<SQL
CREATE OR REPLACE VIEW v_leaderboard AS
SELECT u.id, u.full_name, v.points_balance
FROM users u
LEFT JOIN v_user_points v ON v.user_id = u.id
WHERE u.status = 'active'
ORDER BY v.points_balance DESC;
SQL);
    }

    public function down(): void {
        DB::statement('DROP VIEW IF EXISTS v_leaderboard;');
        DB::statement('DROP VIEW IF EXISTS v_user_points;');
    }
};
