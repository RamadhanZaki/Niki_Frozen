<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['owner', 'kasir'])->default('kasir')->after('password');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete()->after('role');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('branch_id');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['role', 'branch_id', 'status']);
        });
    }
};
