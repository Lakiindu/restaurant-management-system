@php
    $panel = $panel ?? strtolower(auth()->user()->role->role_name ?? 'admin');
    $existingCart = $activeOrder
        ? $activeOrder->items
            ->map(function ($item) {
                return [
                    'id' => $item->menu_item_id,
                    'name' => $item->menuItem->name,
                    'price' => (float) $item->price,
                    'qty' => $item->quantity,
                ];
            })
            ->toJson()
        : '[]';
@endphp
@extends('layouts.' . $panel)

@section('content')
    <!-- Full Screen POS Workspace -->
    <div class="pos-layout d-flex flex-column">

        <!-- TOP HEADER (Fixed Height) -->
        <div class="pos-header shrink-0 d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-start">
                <!-- Big Square Back Button -->
                <a href="{{ url($panel . '/pos') }}" class="btn-back-square me-3 shadow-sm transition-hover"
                    title="Back to Floor Plan">
                    <span style="font-size: 1.4rem; font-weight: 700; color: #111;">←</span>
                </a>
                <div>
                    <div class="text-uppercase fw-bold text-muted tracking-wide fs-9 mb-1">Restaurant POS &bull; Order
                        Workspace</div>
                    <h2 class="fw-bolder text-dark mb-1" style="letter-spacing: -1px;">Table {{ $table->table_number }}</h2>
                    <div class="d-flex align-items-center gap-2 text-muted fs-8">
                        <span
                            class="status-dot @if ($activeOrder) bg-warning @else bg-secondary @endif"></span>
                        @if ($activeOrder)
                            Active Order: {{ $activeOrder->order_no }}
                        @else
                            New order
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex gap-3">
                <div class="pill-badge text-success fw-bold shadow-sm">
                    <span class="status-dot bg-success me-2"></span> LIVE
                </div>
                <div class="pill-badge fw-bold text-dark shadow-sm" id="clock">
                    00:00:00 PM
                </div>
            </div>
        </div>

        <!-- 3 COLUMN BODY (Fills remaining height) -->
        <div class="pos-body row g-3 grow min-vh-0">

            <!-- COLUMN 1: Categories -->
            <div class="col-lg-2 h-100 d-flex flex-column">
                <div class="pos-panel d-flex flex-column h-100 overflow-hidden">
                    <div class="shrink-0 d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="text-uppercase fw-bold text-muted tracking-wide fs-9 mb-1">Browse Menu</div>
                            <h5 class="fw-bolder text-dark mb-0">Categories</h5>
                        </div>
                        <span class="badge bg-light text-dark rounded-pill border">{{ $categories->count() + 1 }}</span>
                    </div>

                    <!-- Scrollable Category List -->
                    <div class="pos-scroll grow pe-2 pb-3" id="category-list">
                        <div class="category-item active" data-id="all">
                            <div class="cat-icon bg-white shadow-sm"></div>
                            <span class="fw-bold">All items</span>
                        </div>
                        @foreach ($categories as $cat)
                            <div class="category-item" data-id="{{ $cat->id }}">
                                <div class="cat-icon shadow-sm" style="background: {{ $cat->color ?? '#f0f0f0' }}20;"></div>
                                <span class="fw-semibold">{{ $cat->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: Menu Items -->
            <div class="col-lg-6 h-100 d-flex flex-column">
                <div class="pos-panel d-flex flex-column h-100 overflow-hidden">
                    <div class="shrink-0">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <div class="text-uppercase fw-bold text-muted tracking-wide fs-9 mb-1">Freshly Prepared
                                </div>
                                <h4 class="fw-bolder text-dark mb-1">Explore the menu</h4>
                                <div class="text-muted fs-8">Choose items to build your table's order.</div>
                            </div>
                            <div class="search-bar-wrapper">
                                <input type="text" class="form-control pos-search" id="searchItem"
                                    placeholder="Search dishes...">
                                <i class="fas fa-search text-muted fs-8"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-bold text-dark fs-8">All menu items</div>
                            <div class="text-muted fs-9">Tap a card to add</div>
                        </div>
                    </div>

                    <!-- Scrollable Menu Grid -->
                    <div class="row g-3 pos-scroll grow pe-2 pb-3 align-content-start" id="menu-items-grid">
                        @foreach ($menuItems as $item)
                            <div class="col-xl-4 col-lg-6 col-md-6 menu-item-card" data-category="{{ $item->category_id }}"
                                data-name="{{ strtolower($item->name) }}">
                                <div class="food-card"
                                    onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})">

                                    <!-- Image Top Half (Dynamic with URL check) -->
                                    @php
                                        $img = !empty($item->image)
                                            ? (str_starts_with($item->image, 'http')
                                                ? $item->image
                                                : asset('storage/' . $item->image))
                                            : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80';
                                    @endphp
                                    <div class="food-card-graphic position-relative"
                                        style="background-image: url('{{ $img }}'); background-size: cover; background-position: center;">
                                        <div class="img-overlay"></div>
                                        <span class="food-badge">{{ $item->category->name ?? 'Menu' }}</span>
                                    </div>

                                    <!-- Text Bottom Half -->
                                    <div class="food-card-body">
                                        <h6 class="fw-bolder text-dark mb-1 lh-sm fs-7">{{ $item->name }}</h6>
                                        @if ($item->is_vegetarian)
                                            <div class="mb-2"><span class="badge bg-success bg-opacity-10 text-success"
                                                    style="font-size: 0.55rem;">VEG</span></div>
                                        @else
                                            <div class="mb-2"></div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <!-- Fixed price wrapping -->
                                            <span class="fw-bolder pos-text-green fs-7 text-nowrap">Rs
                                                {{ number_format($item->price, 2) }}</span>
                                            <span class="text-muted fw-bold text-nowrap" style="font-size: 0.7rem;">Add <i
                                                    class="fas fa-plus-circle ms-1 text-success"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- COLUMN 3: Cart -->
            <div class="col-lg-4 h-100 d-flex flex-column">
                <div class="pos-panel d-flex flex-column h-100 p-0 overflow-hidden">
                    <!-- Cart Header (Fixed) -->
                    <div class="shrink-0 p-3 border-bottom border-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-light shadow-sm d-flex align-items-center justify-content-center"
                                    style="width: 36px; height: 36px;">
                                    <i class="fas fa-shopping-basket text-success"></i>
                                </div>
                                <div>
                                    <div class="text-uppercase fw-bold text-muted tracking-wide fs-9 mb-0">Your Selection
                                    </div>
                                    <h5 class="fw-bolder text-dark mb-0">Current order</h5>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark rounded-pill fw-semibold border" id="cart-count">0
                                items</span>
                        </div>
                        <div class="d-flex gap-2 text-muted fs-8 fw-medium ms-5">
                            <span>Table {{ $table->table_number }}</span>
                            <span>|</span>
                            <span>Dine-in</span>
                        </div>
                    </div>

                    <!-- Scrollable Cart Items -->
                    <div class="pos-scroll grow p-3" id="cart-container" style="background-color: #ffffff;">
                        <!-- JS will populate this -->
                    </div>

                    <!-- Totals & Actions (Fixed at Bottom) -->
                    <div class="shrink-0 p-3 border-top border-light shadow-sm"
                        style="background-color: #ffffff; z-index: 10;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted fw-medium fs-7">Subtotal</span>
                            <span class="fw-bolder text-dark fs-7" id="cart-subtotal">Rs 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2">
                            <span class="text-muted fw-medium fs-7">VAT (18%)</span>
                            <span class="fw-bolder text-dark fs-7" id="cart-vat">Rs 0.00</span>
                        </div>

                        <div class="total-box d-flex justify-content-between align-items-center mb-3 p-3">
                            <div>
                                <div class="fw-bolder text-dark fs-6 mb-0">Total amount</div>
                                <div class="text-muted" style="font-size: 0.7rem;">VAT included</div>
                            </div>
                            <div class="fw-bolder pos-text-green fs-4 text-nowrap" id="cart-total">Rs 0.00</div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <button class="pos-btn btn-hold w-100" onclick="submitOrder('Parked')" id="btnHold">
                                    Hold order
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="pos-btn btn-kitchen w-100" onclick="submitOrder('Pending')"
                                    id="btnSend">
                                    Send to kitchen
                                </button>
                            </div>
                        </div>
                        <button class="pos-btn btn-checkout w-100 shadow-sm" onclick="openCheckout()" id="btnCheckout"
                            disabled>
                            Pay / Checkout
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Checkout Payment Modal (Split Bill Ready) -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title fw-bolder fs-4 mb-0">Complete Payment</h5>
                            <div class="text-muted fs-8">Split across Cash / Card / Online</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Totals row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="total-box text-center p-3 h-100">
                                    <div class="text-muted fw-bold mb-1 fs-9">TOTAL DUE</div>
                                    <div class="fw-bolder pos-text-green fs-3" id="modal-total-due">Rs 0.00</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 text-center p-3 h-100 border">
                                    <div class="text-muted fw-bold mb-1 fs-9">PAID SO FAR</div>
                                    <div class="fw-bolder text-dark fs-3" id="modal-paid-so-far">Rs 0.00</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 text-center p-3 h-100 border">
                                    <div class="text-muted fw-bold mb-1 fs-9">REMAINING</div>
                                    <div class="fw-bolder text-danger fs-3" id="modal-remaining">Rs 0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment lines -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-dark mb-0">Payment lines</label>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3"
                                onclick="addPaymentRow()">
                                + Add split
                            </button>
                        </div>
                        <div id="payment-rows" class="mb-3">
                            <!-- rows injected by JS -->
                        </div>

                        <div class="form-text mb-4">
                            Tip: Add one line per method (e.g. Card Rs 100 + Cash Rs 136). Change is calculated on Cash.
                        </div>

                        <button onclick="processPayment()" id="btnProcessPayment"
                            class="pos-btn btn-checkout w-100 shadow-sm" style="padding: 16px; font-size: 1.1rem;">
                            Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* Perfect Screen Fit CSS */
        .pos-layout {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f7f7f9;
            height: calc(100vh - 65px);
            /* Exact height subtraction for header */
            padding: 15px;
            margin: -1.5rem -1.5rem 0 -1.5rem;
            overflow: hidden;
            /* Prevents whole page scrolling */
        }

        .pos-body {
            min-height: 0;
        }

        /* Crucial for flexbox scrolling inside */

        /* Typography Overrides */
        .fs-8 {
            font-size: 0.8rem;
        }

        .fs-9 {
            font-size: 0.7rem;
        }

        .tracking-wide {
            letter-spacing: 0.5px;
        }

        .pos-text-green {
            color: #117854;
        }

        /* Components */
        .btn-back-square {
            width: 60px;
            height: 60px;
            background: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .transition-hover {
            transition: all 0.2s ease;
        }

        .transition-hover:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05) !important;
        }

        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .pill-badge {
            background: #ffffff;
            border: 1px solid #eaeaea;
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
        }

        .pos-panel {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid #f8f9fa;
        }

        /* Scrollbars */
        .pos-scroll {
            overflow-y: auto;
            overflow-x: hidden;
        }

        .pos-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .pos-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .pos-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Categories */
        .category-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .category-item:hover {
            background: #f8fafc;
        }

        .category-item.active {
            background: #eaf5f0;
            color: #117854;
            border-left: 4px solid #117854;
        }

        .cat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        /* Search */
        .search-bar-wrapper {
            position: relative;
            width: 200px;
        }

        .pos-search {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 50px;
            padding: 10px 16px 10px 36px;
            font-size: 0.8rem;
        }

        .search-bar-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Food Cards with Images */
        .food-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            height: 100%;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: all 0.2s;
            overflow: hidden;
        }

        .food-card:hover {
            border-color: #e2e8f0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            transform: translateY(-3px);
        }

        .food-card-graphic {
            height: 110px;
            position: relative;
            padding: 12px;
        }

        .img-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.4) 100%);
        }

        .food-badge {
            background: #ffffff;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
            color: #333;
            position: relative;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .food-card-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        /* Cart Styling */
        .cart-item-row {
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .cart-item-row:last-child {
            border-bottom: none;
        }

        .qty-control {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 2px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border: none;
            background: #ffffff;
            color: #475569;
            font-weight: bold;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: 0.1s;
        }

        .qty-btn:hover {
            background: #e2e8f0;
        }

        .qty-val {
            width: 24px;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #0f172a;
        }

        .total-box {
            background: #eaf5f0;
            border-radius: 12px;
            border: 1px solid #d1fae5;
        }

        /* Action Buttons */
        .pos-btn {
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .pos-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-hold {
            background: #fef3c7;
            color: #b45309;
        }

        .btn-hold:hover:not(:disabled) {
            background: #fde68a;
        }

        .btn-kitchen {
            background: #eaf5f0;
            color: #117854;
        }

        .btn-kitchen:hover:not(:disabled) {
            background: #d1fae5;
        }

        .btn-checkout {
            background: #157353;
            color: #ffffff;
            font-size: 1rem;
        }

        .btn-checkout:hover:not(:disabled) {
            background: #0f5c42;
            transform: translateY(-2px);
        }
    </style>

    <script>
        let cart = {!! $existingCart !!};

        document.addEventListener('DOMContentLoaded', function() {
            updateCartUI();
            startClock();

            // Category Filter
            document.querySelectorAll('.category-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-item').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.add('active');

                    const catId = this.dataset.id;
                    document.querySelectorAll('.menu-item-card').forEach(card => {
                        card.style.display = (catId === 'all' || card.dataset.category ===
                            catId) ? 'block' : 'none';
                    });
                });
            });

            // Search Filter
            document.getElementById('searchItem').addEventListener('input', function(e) {
                const text = e.target.value.toLowerCase();
                document.querySelectorAll('.menu-item-card').forEach(card => {
                    card.style.display = card.dataset.name.includes(text) ? 'block' : 'none';
                });
            });
        });

        function addToCart(id, name, price) {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    qty: 1
                });
            }
            updateCartUI();
        }

        function updateQty(id, change) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) cart = cart.filter(i => i.id !== id);
            }
            updateCartUI();
        }

        function updateCartUI() {
            const container = document.getElementById('cart-container');
            container.innerHTML = '';

            let subtotal = 0,
                qtyCount = 0;

            if (cart.length === 0) {
                container.innerHTML = `
            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50 pb-5">
                <i class="fas fa-shopping-basket mb-3" style="font-size: 3rem;"></i>
                <div class="fw-semibold fs-5">Cart is empty</div>
            </div>`;
            } else {
                cart.forEach(item => {
                    const itemTotal = item.price * item.qty;
                    subtotal += itemTotal;
                    qtyCount += item.qty;

                    container.innerHTML += `
                <div class="cart-item-row d-flex justify-content-between align-items-start">
                    <div class="pe-2" style="flex-grow: 1;">
                        <div class="fw-bold text-dark lh-sm mb-1 fs-7">${item.name}</div>
                        <div class="text-muted fw-semibold mb-2" style="font-size: 0.7rem;">Rs ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="d-flex flex-column align-items-end shrink-0">
                        <div class="fw-bolder text-dark mb-2 fs-7 text-nowrap">Rs ${itemTotal.toFixed(2)}</div>
                        <div class="qty-control">
                            <button class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                            <span class="qty-val">${item.qty}</span>
                            <button class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                        </div>
                    </div>
                </div>
            `;
                });
            }

            const vat = subtotal * 0.18;
            const total = subtotal + vat;

            document.getElementById('cart-count').innerText = qtyCount + (qtyCount === 1 ? ' item' : ' items');
            document.getElementById('cart-subtotal').innerText = 'Rs ' + subtotal.toFixed(2);
            document.getElementById('cart-vat').innerText = 'Rs ' + vat.toFixed(2);
            document.getElementById('cart-total').innerText = 'Rs ' + total.toFixed(2);

            const hasItems = cart.length > 0;
            document.getElementById('btnCheckout').disabled = !hasItems;
            document.getElementById('btnHold').disabled = !hasItems;
            document.getElementById('btnSend').disabled = !hasItems;
        }

        function startClock() {
            setInterval(() => {
                document.getElementById('clock').innerText = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }, 1000);
        }

        // Setup Global Variables for Backend
        let currentOrderId = {{ $activeOrder ? $activeOrder->id : 'null' }};
        let currentTableId = {{ $table->id }};
        let panelUrl = "{{ url($panel) }}";
        let finalTotalDue = 0;

        // 1. Send Order to Kitchen OR Hold Order
        async function submitOrder(status, openCheckoutAfter = false) {
            if (cart.length === 0) return Swal.fire('Oops', 'Cart is empty!', 'warning');

            // Set button to loading state
            const btn = status === 'Parked' ? document.getElementById('btnHold') : document.getElementById('btnSend');
            const originalText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;

            try {
                const response = await fetch(`${panelUrl}/pos/save-order`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        table_id: currentTableId,
                        status: status,
                        items: cart
                    })
                });

                const data = await response.json();

                if (data.success) {
                    currentOrderId = data.order_id; // Save ID in case they click checkout next
                    if (openCheckoutAfter) {
                        // Order saved behind the scenes, now open modal
                        btn.innerText = originalText;
                        btn.disabled = false;
                        showCheckoutModal();
                    } else {
                        Swal.fire('Success!', data.message, 'success').then(() => {
                            window.location.href = data.redirect; // Back to floor plan
                        });
                    }
                } else {
                    Swal.fire('Error', data.message || 'Failed to save order', 'error');
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Network error. Try again.', 'error');
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        // 2. Open the Checkout Modal
        function openCheckout() {
            if (cart.length === 0) return Swal.fire('Oops', 'Cart is empty!', 'warning');

            // IMPORTANT: If this is a brand new order, it has no ID in the database yet.
            // We must auto-save it as 'Pending' first, THEN open the payment modal.
            if (!currentOrderId) {
                submitOrder('Pending', true);
            } else {
                showCheckoutModal();
            }
        }

        // ===== SPLIT BILL STATE =====
        let paymentRows = []; // { id, method, amount }

        function showCheckoutModal() {
            let subtotal = 0;
            cart.forEach(item => subtotal += (item.price * item.qty));
            finalTotalDue = Math.round((subtotal + (subtotal * 0.18)) * 100) / 100;
            document.getElementById('modal-total-due').innerText = 'Rs ' + finalTotalDue.toFixed(2);

            // Start with one full-amount Cash row
            paymentRows = [{
                id: Date.now(),
                method: 'Cash',
                amount: finalTotalDue
            }];

            renderPaymentRows();

            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }

        function addPaymentRow() {
            const remaining = getRemaining();
            paymentRows.push({
                id: Date.now() + Math.random(),
                method: 'Card',
                amount: remaining > 0 ? remaining : 0
            });
            renderPaymentRows();
        }

        function removePaymentRow(id) {
            if (paymentRows.length === 1) {
                return Swal.fire('Oops', 'Keep at least one payment line.', 'warning');
            }
            paymentRows = paymentRows.filter(r => r.id !== id);
            renderPaymentRows();
        }

        function updatePaymentRow(id, field, value) {
            const row = paymentRows.find(r => r.id === id);
            if (!row) return;

            if (field === 'method') row.method = value;
            if (field === 'amount') row.amount = parseFloat(value) || 0;

            renderPaymentRows(false); // don't rebuild inputs focus
            updateSplitTotals();
        }

        function getPaidSoFar() {
            return paymentRows.reduce((sum, r) => sum + (parseFloat(r.amount) || 0), 0);
        }

        function getRemaining() {
            return Math.round((finalTotalDue - getPaidSoFar()) * 100) / 100;
        }

        function updateSplitTotals() {
            const paid = getPaidSoFar();
            const remaining = getRemaining();

            document.getElementById('modal-paid-so-far').innerText = 'Rs ' + paid.toFixed(2);
            document.getElementById('modal-remaining').innerText = 'Rs ' + remaining.toFixed(2);
            document.getElementById('modal-remaining').className =
                'fw-bolder fs-3 ' + (remaining > 0.009 ? 'text-danger' : 'text-success');
        }

        function renderPaymentRows(full = true) {
            const wrap = document.getElementById('payment-rows');
            if (full) {
                wrap.innerHTML = paymentRows.map(r => `
                    <div class="row g-2 align-items-end mb-2 payment-row" data-id="${r.id}">
                        <div class="col-5">
                            <label class="form-label fs-9 text-muted mb-1">Method</label>
                            <select class="form-select shadow-none border-light"
                                onchange="updatePaymentRow(${r.id}, 'method', this.value)">
                                <option value="Cash" ${r.method === 'Cash' ? 'selected' : ''}>Cash</option>
                                <option value="Card" ${r.method === 'Card' ? 'selected' : ''}>Card</option>
                                <option value="Online" ${r.method === 'Online' ? 'selected' : ''}>Online</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label fs-9 text-muted mb-1">Amount (Rs)</label>
                            <input type="number" step="0.01" min="0" class="form-control fw-bold shadow-none border-light"
                                value="${(r.amount || 0).toFixed(2)}"
                                oninput="updatePaymentRow(${r.id}, 'amount', this.value)">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-light border w-100 text-danger"
                                onclick="removePaymentRow(${r.id})" title="Remove">×</button>
                        </div>
                    </div>
                `).join('');
            }
            updateSplitTotals();
        }

        async function processPayment() {
            const paid = getPaidSoFar();

            if (paymentRows.length === 0) {
                return Swal.fire('Error', 'Add at least one payment.', 'error');
            }

            if (paid < finalTotalDue - 0.01) {
                return Swal.fire('Insufficient', `Still remaining Rs ${getRemaining().toFixed(2)}`, 'warning');
            }

            const btn = document.getElementById('btnProcessPayment');
            btn.innerText = 'Processing...';
            btn.disabled = true;

            // Build payload for backend (already supports array)
            const paymentsPayload = paymentRows.map(r => ({
                method: r.method,
                amount: parseFloat(r.amount) || 0
            }));

            try {
                const response = await fetch(`${panelUrl}/pos/checkout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        order_id: currentOrderId,
                        discount_amount: 0,
                        payments: paymentsPayload
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // hide modal first
                    bootstrap.Modal.getInstance(document.getElementById('checkoutModal'))?.hide();

                    Swal.fire({
                        title: 'Payment Successful!',
                        html: `<h4 class="text-success mt-2 mb-0">Change Due: Rs ${Number(data.change || 0).toFixed(2)}</h4>
                               <div class="text-muted fs-8 mt-2">${paymentsPayload.length} payment line(s) recorded</div>`,
                        icon: 'success',
                        confirmButtonText: 'Back to Floor Plan',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire('Payment Failed', data.message || 'Unknown error', 'error');
                    btn.innerText = 'Confirm Payment';
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Network error.', 'error');
                btn.innerText = 'Confirm Payment';
                btn.disabled = false;
            }
        }
    </script>
@endsection
