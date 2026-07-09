@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('content')

<div class="d-flex justify-content-end mb-2">
    <span id="connStatus" class="badge rounded-pill text-bg-success">
        <i class="bi bi-wifi"></i> Online
    </span>
    <span id="pendingStatus" class="badge rounded-pill text-bg-warning ms-2" style="display:none;">
        <i class="bi bi-cloud-arrow-up"></i> <span id="pendingCount">0</span> transaksi belum sinkron
    </span>
</div>

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
                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}"
                             class="rounded-3 mx-auto mb-2 d-block"
                             style="width:64px;height:64px;object-fit:cover;">
                        <div class="fw-semibold small mb-1">{{ $p->name }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $p->category }}</div>
                        <div class="fw-bold text-primary mb-2">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
                        <div class="small text-muted mb-2">Stok: {{ $qty }}</div>

                        <button type="button" class="btn btn-sm btn-dark w-100"
                            {{ $qty <= 0 ? 'disabled' : '' }}
                            data-product='{{ json_encode(["id" => $p->id, "name" => $p->name, "price" => (float)$p->price, "stock" => $qty], JSON_HEX_APOS | JSON_HEX_QUOT) }}'
                            onclick="addToCart(JSON.parse(this.dataset.product))">
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
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Subtotal</span>
                    <span class="small" id="cartSubtotal">Rp 0</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Kode Diskon</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="discountCodeInput" class="form-control"
                               placeholder="Contoh: PROMO10" style="text-transform:uppercase;">
                        <button type="button" class="btn btn-outline-danger" id="btnRemoveDiscount"
                                style="display:none;" onclick="removeDiscountCode()" title="Batalkan diskon">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted" style="font-size:.7rem;">
                        Ketik kode, lalu tekan <kbd class="px-1">Enter</kbd> untuk menerapkan.
                    </div>
                    <div id="discountAppliedInfo" class="small text-success mt-1" style="display:none;"></div>
                </div>

                <div class="d-flex justify-content-between mb-1" id="cartDiscountRow" style="display:none;">
                    <span class="text-muted small">Diskon</span>
                    <span class="small text-success" id="cartDiscount">- Rp 0</span>
                </div>

                @if($taxPercent > 0)
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Pajak ({{ rtrim(rtrim(number_format($taxPercent, 2, ',', '.'), '0'), ',') }}%)</span>
                    <span class="small" id="cartTax">Rp 0</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-1" id="cartRoundingRow" style="display:none;">
                    <span class="text-muted small">Pembulatan (Tunai)</span>
                    <span class="small" id="cartRounding">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Bayar</span>
                    <span class="fw-bold fs-5" id="cartTotal">Rp 0</span>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Metode Pembayaran</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="paymentMethod" id="pmCash" value="cash" checked onchange="selectPaymentMethod('cash')">
                        <label class="btn btn-outline-primary" for="pmCash"><i class="bi bi-cash-coin me-1"></i>Tunai</label>

                        <input type="radio" class="btn-check" name="paymentMethod" id="pmQris" value="qris" onchange="selectPaymentMethod('qris')">
                        <label class="btn btn-outline-primary" for="pmQris"><i class="bi bi-qr-code me-1"></i>QRIS</label>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Bayar</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" inputmode="numeric" id="paymentInput" class="form-control" placeholder="0">
                    </div>
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

@endsection

@push('scripts')
<script>
    let cart = []; // [{id, name, price, stock, qty}]
    let paymentMethod = 'cash'; // 'cash' | 'qris'
    let appliedDiscount = null; // { code, discount_amount } | null
    const TAX_PERCENT = {{ (float) $taxPercent }};

    // Hitung subtotal (pra-pajak), diskon, nominal pajak, dan total akhir yang
    // harus dibayar. Perhitungan final tetap dilakukan ulang di server
    // (checkout()), ini hanya untuk tampilan & validasi awal di sisi kasir.
    //
    // Diskon dipotong dari subtotal SEBELUM pajak dihitung — konsisten dengan
    // logic di server (KasirWebController::checkout()).
    //
    // Untuk Tunai, total dibulatkan ke kelipatan Rp500 terdekat (sama seperti
    // di server) supaya kasir tidak perlu menghitung kembalian recehan hasil
    // pajak (mis. Rp250/Rp750) yang tidak punya pecahan uang fisik. QRIS tetap
    // presisi karena nominal digital tidak masalah dibayar berapa pun.
    function calcTotals() {
        const subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
        const discount = appliedDiscount ? Math.min(appliedDiscount.discount_amount, subtotal) : 0;
        const taxableBase = subtotal - discount;
        const tax      = Math.round(taxableBase * TAX_PERCENT / 100);
        const preRound = taxableBase + tax;
        const grandTotal = paymentMethod === 'cash' ? Math.round(preRound / 500) * 500 : preRound;
        const rounding = grandTotal - preRound;
        return { subtotal, discount, tax, rounding, grandTotal };
    }

    function selectPaymentMethod(method) {
        paymentMethod = method;
        const input = document.getElementById('paymentInput');

        if (method === 'qris') {
            // QRIS selalu dibayar pas sejumlah total (termasuk pajak), tidak
            // ada kembalian tunai.
            const { grandTotal } = calcTotals();
            input.value = grandTotal > 0 ? grandTotal.toLocaleString('id-ID') : '';
            input.disabled = true;
        } else {
            input.disabled = false;
        }
        updateTotals();
    }

    function formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    }

    // Ambil angka murni dari input Bayar (buang semua titik/karakter non-digit)
    function getPaymentValue() {
        const raw = document.getElementById('paymentInput').value.replace(/\D/g, '');
        return raw ? parseInt(raw, 10) : 0;
    }

    // Format input Bayar dengan titik ribuan sambil diketik, tanpa
    // mengganggu posisi kursor (dihitung dari kanan, bukan kiri).
    function formatPaymentInput() {
        const input = document.getElementById('paymentInput');
        const digitsBeforeCursor = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;

        const rawDigits = input.value.replace(/\D/g, '');
        const formatted = rawDigits ? Number(rawDigits).toLocaleString('id-ID') : '';
        input.value = formatted;

        // Kembalikan posisi kursor ke jumlah digit yang sama dari kiri,
        // dengan menghitung ulang termasuk titik yang baru ditambahkan.
        let seenDigits = 0;
        let pos = formatted.length;
        for (let i = 0; i < formatted.length; i++) {
            if (/\d/.test(formatted[i])) seenDigits++;
            if (seenDigits === digitsBeforeCursor) {
                pos = i + 1;
                break;
            }
        }
        input.setSelectionRange(pos, pos);
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
        invalidateDiscountIfCartChanged();
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
        invalidateDiscountIfCartChanged();
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        invalidateDiscountIfCartChanged();
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
        const { subtotal, discount, tax, rounding, grandTotal } = calcTotals();

        document.getElementById('cartSubtotal').textContent = formatRp(subtotal);
        const taxEl = document.getElementById('cartTax');
        if (taxEl) taxEl.textContent = formatRp(tax);
        document.getElementById('cartTotal').textContent = formatRp(grandTotal);

        // Baris "Diskon" cuma muncul kalau ada kode diskon yang diterapkan.
        const discountRow = document.getElementById('cartDiscountRow');
        const discountEl  = document.getElementById('cartDiscount');
        if (discount > 0) {
            discountRow.style.display = 'flex';
            discountEl.textContent = '- ' + formatRp(discount);
        } else {
            discountRow.style.display = 'none';
        }

        // Baris "Pembulatan" hanya muncul kalau ada selisih pembulatan (Tunai +
        // hasilnya bukan kelipatan Rp500 pas sebelum dibulatkan).
        const roundingRow = document.getElementById('cartRoundingRow');
        const roundingEl  = document.getElementById('cartRounding');
        if (rounding !== 0) {
            roundingRow.style.display = 'flex';
            roundingEl.textContent = (rounding > 0 ? '+ ' : '- ') + formatRp(Math.abs(rounding));
        } else {
            roundingRow.style.display = 'none';
        }

        if (paymentMethod === 'qris') {
            document.getElementById('paymentInput').value = grandTotal > 0 ? grandTotal.toLocaleString('id-ID') : '';
        }

        const payment = getPaymentValue();
        const change  = payment - grandTotal;
        document.getElementById('cartChange').textContent = formatRp(change > 0 ? change : 0);
    }

    // ── Kode Diskon ──────────────────────────────────────────────────
    const APPLY_DISCOUNT_URL = "{{ route('kasir.pos.applyDiscount') }}";

    async function applyDiscountCode() {
        const codeInput = document.getElementById('discountCodeInput');
        const code = codeInput.value.trim();
        if (!code) return;

        const { subtotal } = calcTotals();
        if (subtotal <= 0) {
            Swal.fire({ icon: 'warning', title: 'Keranjang masih kosong', text: 'Tambahkan produk dulu sebelum menerapkan kode diskon.' });
            return;
        }

        // Tombol "Terapkan" sengaja dihapus — kasir cukup ketik kode lalu
        // tekan Enter, supaya tidak ada langkah "klik tombol" yang gampang
        // kelupaan dan bikin diskon tidak jadi terpakai pas checkout.
        // Input dikunci sementara selagi request jalan (mencegah Enter
        // dobel-tekan kirim dua request sekaligus); kalau gagal dibuka lagi
        // di blok catch, kalau berhasil tetap terkunci sebagai tanda diskon
        // sedang aktif (dibuka lagi lewat tombol "Batalkan diskon").
        codeInput.disabled = true;

        try {
            const res = await fetch(APPLY_DISCOUNT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ code, subtotal }),
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Kode diskon tidak valid.');
            }

            appliedDiscount = { code: data.code, discount_amount: data.discount_amount };

            const info = document.getElementById('discountAppliedInfo');
            info.textContent = `Kode "${data.code}" diterapkan — potongan ${formatRp(data.discount_amount)}`;
            info.style.display = 'block';

            document.getElementById('btnRemoveDiscount').style.display = 'inline-block';

            updateTotals();
        } catch (err) {
            // Kosongkan & buka lagi input kode diskon begitu popup error ditutup
            // (baik lewat tombol OK, klik di luar popup, atau tombol Esc) —
            // supaya kasir tidak bingung antara kode yang gagal tadi dengan
            // kode baru yang mau dicoba, dan bisa langsung ketik ulang + Enter.
            Swal.fire({ icon: 'error', title: 'Kode diskon gagal diterapkan', text: err.message }).then(() => {
                codeInput.value = '';
                codeInput.disabled = false;
                codeInput.focus();
            });
        }
    }

    // Terapkan kode diskon saat kasir menekan Enter di kolom kode diskon —
    // gantinya tombol "Terapkan" yang dihapus.
    document.getElementById('discountCodeInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyDiscountCode();
        }
    });

    function removeDiscountCode() {
        appliedDiscount = null;

        const codeInput = document.getElementById('discountCodeInput');
        codeInput.value = '';
        codeInput.disabled = false;

        document.getElementById('discountAppliedInfo').style.display = 'none';
        document.getElementById('btnRemoveDiscount').style.display = 'none';

        updateTotals();
    }

    // Kode diskon yang sudah diterapkan dihitung berdasarkan subtotal SAAT itu.
    // Kalau isi keranjang berubah setelahnya, potongan yang ditampilkan jadi
    // tidak akurat lagi — jadi otomatis dibatalkan dan kasir diminta menerapkan
    // ulang. Server tetap jadi sumber kebenaran akhir di checkout() apa pun
    // yang terjadi di sisi tampilan ini.
    function invalidateDiscountIfCartChanged() {
        if (appliedDiscount) {
            removeDiscountCode();
            Swal.fire({
                icon: 'info',
                title: 'Diskon dibatalkan',
                text: 'Keranjang berubah, silakan terapkan ulang kode diskon jika masih ingin dipakai.',
                timer: 4000,
                showConfirmButton: false,
            });
        }
    }

    document.getElementById('paymentInput').addEventListener('input', function () {
        formatPaymentInput();
        updateTotals();
    });

    // Dipisah jadi fungsi tersendiri (bukan cuma listener inline) karena harus
    // dipanggil ulang setiap kali productsPoll() me-render ulang #productGrid
    // — supaya kata kunci pencarian yang sedang diketik kasir tidak hilang
    // begitu daftar produk ter-update otomatis di latar belakang.
    function applySearchFilter() {
        const keyword = document.getElementById('searchProduct').value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(el => {
            el.style.display = el.dataset.name.includes(keyword) ? '' : 'none';
        });
    }

    document.getElementById('searchProduct').addEventListener('input', applySearchFilter);

    // ═══════════════════════════════════════════════════════════════
    // REALTIME PRODUK — polling ringan supaya daftar produk & stok di POS
    // otomatis ter-update kalau Owner tambah/edit/hapus produk atau ubah
    // stok, TANPA kasir perlu reload manual. Pola sama persis dengan polling
    // notifikasi lonceng di layouts/app.blade.php (fetch berkala, bukan
    // WebSocket/Pusher/Reverb — konsisten & tidak butuh infrastruktur baru).
    //
    // Ini juga jaring pengaman utama untuk race condition "kasir sedang buka
    // POS, Owner hapus produk yang lagi ada di keranjang kasir": begitu poll
    // berikutnya jalan, produk yang hilang otomatis dikeluarkan dari
    // keranjang SEBELUM kasir sempat checkout, jadi tidak akan kena error
    // validasi "items.*.id: exists:products,id" yang membingungkan di server.
    // ═══════════════════════════════════════════════════════════════
    const PRODUCTS_POLL_URL = "{{ route('kasir.pos.productsPoll') }}";
    const PRODUCTS_POLL_INTERVAL_MS = 10000; // 10 detik — lebih rapat dari poll notifikasi (15 detik) karena stok/harga langsung memengaruhi transaksi yang sedang berjalan

    function escapeHtmlPos(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // Escape JSON supaya aman disisipkan ke dalam atribut HTML `data-product='...'`.
    // Urutan penting: "&" WAJIB paling awal, kalau tidak entity yang baru
    // dibuat di langkah berikutnya (mis. &#39;) akan ikut ter-escape ulang
    // jadi &amp;#39; yang salah. Ini padanan JS dari flag
    // JSON_HEX_APOS | JSON_HEX_QUOT yang dipakai versi Blade (server-side).
    function escapeForAttr(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/'/g, '&#39;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function productCardHtml(p) {
        const outOfStock = p.stock <= 0;
        const productJson = escapeForAttr(JSON.stringify({ id: p.id, name: p.name, price: p.price, stock: p.stock }));
        return `
            <div class="col-6 col-md-4 product-item" data-name="${escapeHtmlPos(p.name.toLowerCase())}" id="product-card-${p.id}">
                <div class="card border-0 shadow-sm h-100 ${outOfStock ? 'opacity-50' : ''}">
                    <div class="card-body text-center p-3">
                        <img src="${escapeHtmlPos(p.image_url)}" alt="${escapeHtmlPos(p.name)}"
                             class="rounded-3 mx-auto mb-2 d-block"
                             style="width:64px;height:64px;object-fit:cover;">
                        <div class="fw-semibold small mb-1">${escapeHtmlPos(p.name)}</div>
                        <div class="text-muted" style="font-size:.7rem;">${escapeHtmlPos(p.category)}</div>
                        <div class="fw-bold text-primary mb-2">${formatRp(p.price)}</div>
                        <div class="small text-muted mb-2">Stok: ${p.stock}</div>

                        <button type="button" class="btn btn-sm btn-dark w-100"
                            ${outOfStock ? 'disabled' : ''}
                            data-product='${productJson}'
                            onclick="addToCart(JSON.parse(this.dataset.product))">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </div>
            </div>`;
    }

    function renderProducts(products) {
        const grid = document.getElementById('productGrid');

        if (products.length === 0) {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="text-center text-muted py-5">Belum ada produk untuk cabang ini.</div>
                </div>`;
            return;
        }

        grid.innerHTML = products.map(productCardHtml).join('');
        applySearchFilter();
    }

    /**
     * Bandingkan isi keranjang kasir saat ini dengan daftar produk terbaru
     * hasil poll. Produk yang sudah dihapus Owner otomatis dibuang dari
     * keranjang; produk yang stoknya berkurang (tapi belum 0) otomatis
     * di-clamp qty-nya supaya tidak melebihi stok terbaru. Kasir diberi tahu
     * lewat satu toast ringkas kalau ada perubahan yang memengaruhi keranjangnya.
     */
    function reconcileCartWithProducts(products) {
        if (cart.length === 0) return;

        const productMap = new Map(products.map(p => [p.id, p]));
        const removedNames = [];
        const clampedNames = [];

        const nextCart = [];
        for (const item of cart) {
            const latest = productMap.get(item.id);

            if (!latest) {
                removedNames.push(item.name);
                continue; // produk sudah dihapus Owner → buang dari keranjang
            }

            let qty = item.qty;
            if (latest.stock <= 0) {
                removedNames.push(item.name);
                continue; // stok baru saja habis → buang dari keranjang
            }
            if (qty > latest.stock) {
                qty = latest.stock;
                clampedNames.push(item.name);
            }

            nextCart.push({ ...item, price: latest.price, stock: latest.stock, qty });
        }

        if (removedNames.length === 0 && clampedNames.length === 0) return;

        cart = nextCart;
        invalidateDiscountIfCartChanged();
        renderCart();

        const parts = [];
        if (removedNames.length) parts.push(`Dihapus dari keranjang: ${removedNames.join(', ')}.`);
        if (clampedNames.length) parts.push(`Jumlah disesuaikan (stok berkurang): ${clampedNames.join(', ')}.`);

        Swal.fire({
            icon: 'warning',
            title: 'Keranjang diperbarui otomatis',
            text: parts.join(' '),
            timer: 7000,
            showConfirmButton: true,
            confirmButtonText: 'Mengerti',
        });
    }

    let isFirstProductsPoll = true;

    async function pollProducts() {
        // Sama seperti poll notifikasi: jangan polling kalau tab tidak
        // aktif, hemat request — begitu tab aktif lagi ada listener terpisah
        // di bawah yang langsung poll ulang.
        if (document.visibilityState !== 'visible') return;

        try {
            const res = await fetch(PRODUCTS_POLL_URL, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;

            const data = await res.json();
            const products = data.products ?? [];

            // Poll pertama sengaja TIDAK me-render ulang grid — halaman sudah
            // di-render server (SSR) saat load, jadi tidak perlu ditimpa JS
            // kalau datanya sama persis. Ini hanya dipakai untuk sinkronkan
            // keranjang kalau ternyata ada perubahan yang sudah terjadi
            // tepat sebelum halaman selesai dimuat.
            if (!isFirstProductsPoll) {
                renderProducts(products);
            }
            reconcileCartWithProducts(products);

            isFirstProductsPoll = false;
        } catch (e) {
            // Diam-diam gagal (koneksi putus sesaat) — poll berikutnya coba lagi.
        }
    }

    pollProducts();
    setInterval(pollProducts, PRODUCTS_POLL_INTERVAL_MS);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') pollProducts();
    });

    // ═══════════════════════════════════════════════════════════════
    // OFFLINE SYNC — IndexedDB queue untuk transaksi saat koneksi putus
    // ═══════════════════════════════════════════════════════════════
    const CHECKOUT_URL = "{{ route('kasir.pos.checkout') }}";
    const DB_NAME    = 'nikifrozen_offline_db';
    const STORE_NAME = 'pending_transactions';

    function openOfflineDB() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, 1);
            req.onupgradeneeded = () => {
                const db = req.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'client_txn_id' });
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => reject(req.error);
        });
    }

    async function queuePendingTransaction(payload) {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            tx.objectStore(STORE_NAME).put(payload);
            tx.oncomplete = () => resolve();
            tx.onerror    = () => reject(tx.error);
        });
    }

    async function getPendingTransactions() {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const req = tx.objectStore(STORE_NAME).getAll();
            req.onsuccess = () => resolve(req.result || []);
            req.onerror   = () => reject(req.error);
        });
    }

    async function removePendingTransaction(clientTxnId) {
        const db = await openOfflineDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            tx.objectStore(STORE_NAME).delete(clientTxnId);
            tx.oncomplete = () => resolve();
            tx.onerror    = () => reject(tx.error);
        });
    }

    function makeClientTxnId() {
        return 'txn-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
    }

    // Error khusus: request tidak pernah sampai/dibalas server (offline / DNS gagal /
    // timeout). Beda dengan error validasi (stok kurang, dsb) yang HARUS ditampilkan
    // ke kasir, bukan ditumpuk ke antrian offline.
    class NetworkUnreachableError extends Error {}

    async function sendCheckout(payload) {
        let res;
        try {
            res = await fetch(CHECKOUT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });
        } catch (networkErr) {
            // fetch() sendiri gagal (bukan response error) → benar-benar putus koneksi
            throw new NetworkUnreachableError('Tidak dapat menghubungi server.');
        }

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || data.success === false) {
            const message = data?.message || 'Transaksi ditolak server.';
            throw new Error(message);
        }
        return data;
    }

    async function refreshPendingBadge() {
        const pending = await getPendingTransactions();
        const badge = document.getElementById('pendingStatus');
        const count = document.getElementById('pendingCount');
        count.textContent = pending.length;
        badge.style.display = pending.length > 0 ? 'inline-flex' : 'none';
    }

    async function syncPendingTransactions() {
        const pending = await getPendingTransactions();
        for (const payload of pending) {
            try {
                await sendCheckout(payload);
                await removePendingTransaction(payload.client_txn_id);
            } catch (err) {
                if (err instanceof NetworkUnreachableError) {
                    // Masih putus di tengah proses sync — hentikan, sisanya dicoba lagi nanti.
                    break;
                }
                // Error validasi (mis. stok berubah selama offline) — buang dari antrian
                // supaya tidak macet, tapi tetap kasih tahu kasir untuk dicek manual.
                await removePendingTransaction(payload.client_txn_id);
                Swal.fire({
                    icon: 'error',
                    title: 'Transaksi offline gagal disinkronkan',
                    text: err.message,
                });
            }
        }
        await refreshPendingBadge();
    }

    function updateConnBadge() {
        const badge = document.getElementById('connStatus');
        if (navigator.onLine) {
            badge.className = 'badge rounded-pill text-bg-success';
            badge.innerHTML = '<i class="bi bi-wifi"></i> Online';
        } else {
            badge.className = 'badge rounded-pill text-bg-danger';
            badge.innerHTML = '<i class="bi bi-wifi-off"></i> Offline';
        }
    }

    window.addEventListener('online', () => {
        updateConnBadge();
        syncPendingTransactions();
    });
    window.addEventListener('offline', updateConnBadge);

    document.addEventListener('DOMContentLoaded', () => {
        updateConnBadge();
        refreshPendingBadge();
        syncPendingTransactions();
    });

    async function submitCheckout() {
        const { grandTotal } = calcTotals();
        const payment = getPaymentValue();

        if (cart.length === 0) {
            alert('Keranjang masih kosong.');
            return;
        }
        if (payment < grandTotal) {
            alert('Jumlah pembayaran kurang dari total belanja.');
            return;
        }

        const payload = {
            client_txn_id: makeClientTxnId(),
            items: cart.map(i => ({ id: i.id, qty: i.qty })),
            payment: payment,
            payment_method: paymentMethod,
            discount_code: appliedDiscount ? appliedDiscount.code : null,
        };

        const btn = document.getElementById('btnCheckout');
        btn.disabled = true;

        try {
            const data = await sendCheckout(payload);
            Swal.fire({
                icon: 'success',
                title: 'Transaksi tersimpan',
                text: data.message,
                showCancelButton: true,
                confirmButtonText: '🖨️ Cetak Struk',
                cancelButtonText: 'Tutup',
            }).then((result) => {
                if (result.isConfirmed && data.receipt_url) {
                    window.open(data.receipt_url, '_blank');
                }
                window.location.reload(); // reload otomatis reset toggle ke Tunai (default HTML)
            });
        } catch (err) {
            if (err instanceof NetworkUnreachableError) {
                // Koneksi putus → simpan ke antrian lokal, jangan blokir kasir.
                await queuePendingTransaction(payload);
                await refreshPendingBadge();
                cart = [];
                renderCart();
                document.getElementById('paymentInput').value = '';
                document.getElementById('paymentInput').disabled = false;
                document.getElementById('pmCash').checked = true;
                paymentMethod = 'cash';
                removeDiscountCode();
                Swal.fire({
                    icon: 'warning',
                    title: 'Tersimpan offline',
                    text: 'Koneksi internet terputus. Transaksi disimpan di perangkat ini dan akan otomatis disinkronkan saat koneksi kembali.',
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Transaksi gagal', text: err.message });
            }
        } finally {
            btn.disabled = cart.length === 0;
        }
    }
</script>
@endpush
