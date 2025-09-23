<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detail_points_ledger', function (Blueprint $table) {
            // ubah source_type jadi string 32
            $table->string('source_type', 32)->default('challenge')->change();

            // pastikan points signed (bukan unsigned)
            $table->integer('points')->change();
        });
    }

    public function down(): void
    {
        Schema::table('detail_points_ledger', function (Blueprint $table) {
            // rollback ke enum kalau sebelumnya enum
            // SESUAIKAN dengan skema awal kamu. Contoh:
            // $table->enum('source_type', ['challenge'])->default('challenge')->change();

            // points balik (kalau awalnya unsignedInteger)
            // $table->unsignedInteger('points')->change();
        });
    }
};
