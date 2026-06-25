@extends('layouts.app')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Toko')

@section('content')

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold"><i class="bi bi-gear me-1"></i> Informasi Toko</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('owner.settings.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" name="store_name" class="form-control"
                               value="{{ old('store_name', $settings['store_name']) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Toko</label>
                        <textarea name="store_address" class="form-control" rows="2">{{ old('store_address', $settings['store_address']) }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Nomor Telepon</label>
                            <input type="text" name="store_phone" class="form-control"
                                   value="{{ old('store_phone', $settings['store_phone']) }}" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Pajak (%)</label>
                            <input type="number" name="tax_percent" class="form-control" min="0" max="100" step="0.1"
                                   value="{{ old('tax_percent', $settings['tax_percent']) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Catatan Struk</label>
                        <textarea name="receipt_note" class="form-control" rows="2"
                                  placeholder="Contoh: Terima kasih telah berbelanja!">{{ old('receipt_note', $settings['receipt_note']) }}</textarea>
                        <div class="form-text">Teks ini akan tampil di bagian bawah struk transaksi.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Info samping --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span class="fw-semibold small">Tentang Pengaturan</span>
                </div>
                <p class="text-muted small mb-0">
                    Pengaturan ini akan digunakan di seluruh sistem, termasuk pada struk transaksi
                    dan tampilan informasi toko. Pastikan data sudah benar sebelum disimpan.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection