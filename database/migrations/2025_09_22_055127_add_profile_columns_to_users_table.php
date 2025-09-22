<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('full_name')->after('email')->default('');
            $t->enum('role', ['admin','member'])->default('member')->after('full_name');
            $t->enum('status', ['pending','active','suspended'])->default('active')->after('role');
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['full_name','role','status']);
        });
    }
};
