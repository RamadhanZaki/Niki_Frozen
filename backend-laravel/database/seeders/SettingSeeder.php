<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'profit_margin',    'value' => '25',             'label' => 'Estimasi Margin Laba (%)',    'type' => 'number'],
            ['key' => 'store_name',       'value' => 'Niki Frozen',    'label' => 'Nama Toko',                   'type' => 'text'],
            ['key' => 'store_address',    'value' => '',               'label' => 'Alamat Toko',                 'type' => 'text'],
            ['key' => 'store_phone',      'value' => '',               'label' => 'No. Telepon Toko',            'type' => 'text'],
            ['key' => 'low_stock_threshold', 'value' => '10',          'label' => 'Batas Stok Menipis',          'type' => 'number'],
            ['key' => 'expiry_warning_days', 'value' => '7',           'label' => 'Peringatan Expired (hari)',   'type' => 'number'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->updateOrInsert(['key' => $s['key']], array_merge($s, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
