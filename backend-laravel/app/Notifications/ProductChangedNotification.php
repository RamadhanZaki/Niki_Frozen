<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Notification;

class ProductChangedNotification extends Notification
{
    /**
     * @param string $action 'added' | 'updated' | 'deleted'
     * @param string $productName Disimpan sebagai string terpisah (bukan cuma
     *        $product->name) supaya notifikasi "deleted" tetap bisa menampilkan
     *        nama produk walau row Product-nya sudah benar-benar dihapus dari DB.
     */
    public function __construct(
        public string $action,
        public string $productName,
        public ?Product $product = null,
    ) {
    }

    /**
     * Hanya database channel — sama seperti CashDifferenceNotification &
     * QrisPaymentNotification. Kasir yang sedang buka POS akan otomatis
     * lihat ini lewat polling lonceng notifikasi yang sudah ada
     * (layouts/app.blade.php), tanpa perlu infrastruktur realtime baru.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = match ($this->action) {
            'added'   => 'Produk Baru Ditambahkan',
            'updated' => 'Produk Diperbarui',
            'deleted' => 'Produk Dihapus',
            default   => 'Perubahan Produk',
        };

        $message = match ($this->action) {
            'added'   => "Produk \"{$this->productName}\" baru saja ditambahkan oleh Owner.",
            'updated' => "Produk \"{$this->productName}\" baru saja diperbarui oleh Owner (harga/stok/data lain mungkin berubah).",
            'deleted' => "Produk \"{$this->productName}\" baru saja dihapus oleh Owner. Produk ini otomatis hilang dari daftar POS.",
            default   => "Ada perubahan pada produk \"{$this->productName}\".",
        };

        return [
            'title'        => $title,
            'message'      => $message,
            'action'       => $this->action,
            'product_id'   => $this->product?->id,
            'product_name' => $this->productName,
            'branch_id'    => $this->product?->branch_id,
        ];
    }
}
