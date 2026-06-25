@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('content')

<div class="row g-3">
    {{-- ══ KIRI: DAFTAR PRODUK ══ --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <input type="text" id="searchProduct" class="form-control" placeholder="Cari produk...">
            </div>
        </div>

        <div class="row g-3" id="productGrid">
            @forelse($products as $p)
            @php $qty = $p->stock?->quantity ?? 0; @endphp
            <div class="col-6 col-md-4 product-item" data-name="{{ strtolower($p->name) }}">
                <div class="card border-0 shadow-sm h-100 {{ $qty <= 0 ? 'opacity-50' : '' }}">
                    <div class="card-body text-center p-3">
                        <div class="rounded-3 mx-auto mb-2 d-flex align-items-center justify-content-center"
                             style="width:48px;height:48px;background:#e0f7fc;">
                            <i class="bi bi-box-seam fs-5 text-primary"></i>
                        </div>
                        <div class="fw-semibold small mb-1">{{ $p->name }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $p->category }}</div>
                        <div class="fw-bold text-primary mb-2">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
                        <div class="small text-muted mb-2">Stok: {{ $qty }}</div>

                        <button type="button" class="btn btn-sm btn-dark w-100"
                            {{ $qty <= 0 ? 'disabled' : '' }}
                            onclick='addToCart({{ json_encode(["id" => $p->id, "name" => $p->name, "price" => (float)$p->price, "stock" => $qty]) }})'>
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">Belum ada produk untuk cabang ini.</div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ══ KANAN: KERANJANG ══ --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm" style="position:sticky; top:72px;">
            <div class="card-header bg-white border-0 pt-3">
                <span class="fw-semibold"><i class="bi bi-cart3 me-1"></i> Keranjang</span>
            </div>

            <div class="card-body p-0">
                <div id="cartEmpty" class="text-center text-muted py-5">
                    <i class="bi bi-cart-x fs-2 d-block mb-2"></i>
                    Keranjang masih kosong
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="cartTable" style="display:none;">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th style="width:90px;">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th style="width:36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total</span>
                    <span class="fw-bold fs-5" id="cartTotal">Rp 0</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Bayar</label>
                    <input type="number" id="paymentInput" class="form-control" placeholder="Masukkan jumlah uang" min="0">
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">Kembalian</span>
                    <span class="fw-semibold" id="cartChange">Rp 0</span>
                </div>

                <button class="btn btn-primary w-100" id="btnCheckout" onclick="submitCheckout()" disabled>
                    <i class="bi bi-check-circle me-1"></i> Proses Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Form tersembunyi untuk submit checkout --}}
<form id="checkoutForm" action="{{ route('kasir.pos.checkout') }}" method="POST" style="display:none;">
    @csrf
    <div id="checkoutFields"></div>
</form>

@endsection

@push('scripts')
<script>
    let cart = []; // [{id, name, price, stock, qty}]

    function formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    function addToCart(product) {
        const existing = cart.find(i => i.id === product.id);
        if (existing) {
            if (existing.qty >= product.stock) {
                alert('Stok tidak cukup.');
                return;
            }
            existing.qty++;
        } else {
            cart.push({ ...product, qty: 1 });
        }
        renderCart();
    }

    function changeQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;
        item.qty += delta;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (item.qty > item.stock) {
            item.qty = item.stock;
            alert('Stok tidak cukup.');
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function renderCart() {
        const empty = document.getElementById('cartEmpty');
        const table = document.getElementById('cartTable');
        const body  = document.getElementById('cartBody');
        const btn   = document.getElementById('btnCheckout');

        if (cart.length === 0) {
            empty.style.display = 'block';
            table.style.display = 'none';
            btn.disabled = true;
        } else {
            empty.style.display = 'none';
            table.style.display = 'table';
            btn.disabled = false;
        }

        body.innerHTML = cart.map(item => `
            <tr>
                <td>
                    <div class="small fw-semibold">${item.name}</div>
                    <div class="text-muted" style="font-size:.7rem;">${formatRp(item.price)}</div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty(${item.id}, -1)">-</button>
                        <span class="small">${item.qty}</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="changeQty(${item.id}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end small fw-semibold">${formatRp(item.price * item.qty)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        updateTotals();
    }

    function updateTotals() {
        const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
        document.getElementById('cartTotal').textContent = formatRp(total);

        const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
        const change  = payment - total;
        document.getElementById('cartChange').textContent = formatRp(change > 0 ? change : 0);
    }

    document.getElementById('paymentInput').addEventListener('input', updateTotals);

    document.getElementById('searchProduct').addEventListener('input', function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(el => {
            el.style.display = el.dataset.name.includes(keyword) ? '' : 'none';
        });
    });

    function submitCheckout() {
        const total   = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
        const payment = parseFloat(document.getElementById('paymentInput').value) || 0;

        if (cart.length === 0) {
            alert('Keranjang masih kosong.');
            return;
        }
        if (payment < total) {
            alert('Jumlah pembayaran kurang dari total belanja.');
            return;
        }

        const fields = document.getElementById('checkoutFields');
        fields.innerHTML = '';

        cart.forEach((item, idx) => {
            fields.innerHTML += `
                <input type="hidden" name="items[${idx}][id]" value="${item.id}">
                <input type="hidden" name="items[${idx}][qty]" value="${item.qty}">
            `;
        });
        fields.innerHTML += `<input type="hidden" name="payment" value="${payment}">`;

        document.getElementById('checkoutForm').submit();
    }
</script>
@endpush