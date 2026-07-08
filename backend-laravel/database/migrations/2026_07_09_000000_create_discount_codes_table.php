<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();

            // 'percentage' = persen dari subtotal (mis. 10 = 10%),
            // 'fixed'      = potongan nominal rupiah tetap (mis. 5000 = Rp5.000).
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 12, 2);

            // Minimal subtotal belanja supaya kode ini bisa dipakai.
            $table->decimal('min_purchase', 12, 2)->default(0);

            // Cap maksimal potongan — cuma relevan untuk type=percentage, supaya
            // "diskon 50%" tidak jebol kalau belanja jutaan rupiah. Null = tanpa cap.
            $table->decimal('max_discount', 12, 2)->nullable();

            // Null = tanpa batas kuota pemakaian.
            $table->unsignedInteger('quota')->nullable();
            $table->unsignedInteger('used_count')->default(0);

            // Null = berlaku untuk semua cabang.
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();

            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
