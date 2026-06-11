<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('total_expense', 12, 2)->default(0);
            $table->decimal('net_profit', 12, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('financial_reports');
    }
};
