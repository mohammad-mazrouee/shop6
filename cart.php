<?php
session_start();
require_once 'config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sample cart items (در حالت واقعی از دیتابیس می‌خوانیم)
$cartItems = [
    [
        'id' => 1,
        'name' => 'پیراهن مشکی لوکس',
        'price' => 2500000,
        'discount_price' => 1900000,
        'image' => 'assets/images/products/product1.jpg',
        'color' => 'مشکی',
        'size' => 'L',
        'quantity' => 2,
        'stock' => 5,
        'savings' => 600000
    ],
    [
        'id' => 2,
        'name' => 'شلوار جین کلاسیک',
        'price' => 3200000,
        'discount_price' => 2800000,
        'image' => 'assets/images/products/product2.jpg',
        'color' => 'آبی تیره',
        'size' => 'XL',
        'quantity' => 1,
        'stock' => 3,
        'savings' => 400000
    ]
];

// محاسبات
$subtotal = 0;
$totalSavings = 0;
$shippingCost = 200000;
$freeShippingThreshold = 5000000;

foreach ($cartItems as $item) {
    $subtotal += $item['discount_price'] * $item['quantity'];
    $totalSavings += $item['savings'] * $item['quantity'];
}

$remainingForFreeShipping = max(0, $freeShippingThreshold - $subtotal);
if ($remainingForFreeShipping == 0) {
    $shippingCost = 0;
}

$total = $subtotal + $shippingCost;

$pageTitle = 'سبد خرید';
include 'header.php';
?>
<div class="cart-page">
    <!-- Breadcrumb -->
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">سبد خرید</li>
            </ol>
        </nav>
    </div>

    <div class="container">
        <div class="cart-header">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart"></i>
                سبد خرید شما
            </h1>
            <span class="cart-count"><?php echo count($cartItems); ?> محصول</span>
        </div>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart State -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <h2>سبد خرید شما خالی است</h2>
                <p>برای شروع خرید، محصولات مورد نظر خود را انتخاب کنید</p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-left"></i>
                    بازگشت به فروشگاه
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Content -->
            <div class="cart-layout">
                <!-- Main Column: Cart Items -->
                <div class="cart-items-section">
                    <!-- Progress Bar for Free Shipping -->
                    <?php if ($remainingForFreeShipping > 0): ?>
                        <div class="shipping-progress-card">
                            <div class="shipping-info">
                                <i class="fas fa-shipping-fast"></i>
                                <span>
                                    تنها <strong><?php echo number_format($remainingForFreeShipping); ?> تومان</strong> 
                                    تا ارسال رایگان!
                                </span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: <?php echo ($subtotal / $freeShippingThreshold) * 100; ?>%"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="shipping-progress-card success">
                            <div class="shipping-info">
                                <i class="fas fa-check-circle"></i>
                                <span>تبریک! شما از ارسال رایگان بهره‌مند می‌شوید</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Cart Items -->
                    <div class="cart-items-list">
                        <?php foreach ($cartItems as $index => $item): ?>
                            <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
                                <div class="cart-item-image">
                                    <img src="<?php echo $item['image']; ?>" 
                                         alt="<?php echo $item['name']; ?>"
                                         loading="lazy">
                                </div>

                                <div class="cart-item-details">
                                    <h3 class="cart-item-title"><?php echo $item['name']; ?></h3>
                                    
                                    <div class="cart-item-variants">
                                        <span class="variant-badge">
                                            <i class="fas fa-palette"></i>
                                            <?php echo $item['color']; ?>
                                        </span>
                                        <span class="variant-badge">
                                            <i class="fas fa-ruler"></i>
                                            <?php echo $item['size']; ?>
                                        </span>
                                    </div>

                                    <?php if ($item['stock'] < 5): ?>
                                        <div class="stock-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            تنها <?php echo $item['stock']; ?> عدد در انبار باقی مانده
                                        </div>
                                    <?php endif; ?>

                                    <div class="cart-item-actions-mobile">
                                        <div class="quantity-selector">
                                            <button class="qty-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="qty-input" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="<?php echo $item['stock']; ?>"
                                                   readonly>
                                            <button class="qty-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>

                                        <button class="btn-icon btn-save" onclick="saveForLater(<?php echo $item['id']; ?>)" title="ذخیره برای بعد">
                                            <i class="far fa-heart"></i>
                                        </button>

                                        <button class="btn-icon btn-remove" onclick="removeItem(<?php echo $item['id']; ?>)" title="حذف از سبد">
                                            <i class="far fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-item-quantity">
                                    <div class="quantity-selector">
                                        <button class="qty-btn minus" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="qty-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock']; ?>"
                                               readonly>
                                        <button class="qty-btn plus" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-item-price">
                                    <?php if ($item['price'] > $item['discount_price']): ?>
                                        <div class="original-price"><?php echo number_format($item['price']); ?> تومان</div>
                                    <?php endif; ?>
                                    <div class="current-price"><?php echo number_format($item['discount_price']); ?> تومان</div>
                                    <?php if ($item['savings'] > 0): ?>
                                        <div class="savings-badge">
                                            <i class="fas fa-tag"></i>
                                            <?php echo number_format($item['savings']); ?> تومان سود
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="cart-item-total">
                                    <div class="item-total-price">
                                        <?php echo number_format($item['discount_price'] * $item['quantity']); ?> تومان
                                    </div>
                                </div>

                                <div class="cart-item-actions">
                                    <button class="btn-icon btn-save" onclick="saveForLater(<?php echo $item['id']; ?>)" title="ذخیره برای بعد">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button class="btn-icon btn-remove" onclick="removeItem(<?php echo $item['id']; ?>)" title="حذف از سبد">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="continue-shopping">
                        <a href="products.php" class="btn btn-outline">
                            <i class="fas fa-arrow-right"></i>
                            ادامه خرید
                        </a>
                    </div>
                </div>

                <!-- Sidebar: Order Summary -->
                <div class="cart-sidebar">
                    <div class="order-summary sticky-sidebar">
                        <h2 class="summary-title">خلاصه سفارش</h2>

                        <div class="summary-details">
                            <div class="summary-row">
                                <span>جمع کل کالاها:</span>
                                <span><?php echo number_format($subtotal); ?> تومان</span>
                            </div>

                            <?php if ($totalSavings > 0): ?>
                                <div class="summary-row savings">
                                    <span>
                                        <i class="fas fa-gift"></i>
                                        سود شما از این خرید:
                                    </span>
                                    <span class="savings-amount">
                                        <?php echo number_format($totalSavings); ?> تومان
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="summary-row">
                                <span>هزینه ارسال:</span>
                                <span class="<?php echo $shippingCost == 0 ? 'free-shipping' : ''; ?>">
                                    <?php echo $shippingCost == 0 ? 'رایگان' : number_format($shippingCost) . ' تومان'; ?>
                                </span>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-row total">
                                <span>مبلغ قابل پرداخت:</span>
                                <span class="total-price"><?php echo number_format($total); ?> تومان</span>
                            </div>
                        </div>

                        <!-- Coupon Code Section -->
                        <div class="coupon-section">
                            <div class="coupon-header">
                                <i class="fas fa-ticket-alt"></i>
                                <span>کد تخفیف دارید؟</span>
                            </div>
                            <form id="couponForm" class="coupon-form">
                                <input type="text" 
                                       id="couponCode" 
                                       class="coupon-input" 
                                       placeholder="کد تخفیف را وارد کنید">
                                <button type="submit" class="btn-apply-coupon">
                                    اعمال
                                </button>
                            </form>
                            <div id="couponMessage" class="coupon-message"></div>
                        </div>

                        <!-- Checkout Button -->
                        <button class="btn btn-checkout" onclick="proceedToCheckout()">
                            <i class="fas fa-lock"></i>
                            تکمیل سفارش و پرداخت
                            <i class="fas fa-arrow-left"></i>
                        </button>

                        <!-- Trust Badges -->
                        <div class="trust-badges">
                            <div class="trust-badge">
                                <i class="fas fa-shield-alt"></i>
                                <span>پرداخت امن</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-undo"></i>
                                <span>۷ روز ضمانت بازگشت</span>
                            </div>
                            <div class="trust-badge">
                                <i class="fas fa-headset"></i>
                                <span>پشتیبانی ۲۴/۷</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recommended Products -->
                    <div class="recommended-section">
                        <h3 class="recommended-title">
                            <i class="fas fa-star"></i>
                            محصولات پیشنهادی
                        </h3>
                        <div class="recommended-items">
                            <!-- Sample recommended product -->
                            <div class="mini-product-card">
                                <img src="assets/images/products/recommended1.jpg" alt="محصول پیشنهادی">
                                <div class="mini-product-info">
                                    <h4>کمربند چرم طبیعی</h4>
                                    <div class="mini-product-price">
                                        <span class="price">۸۵۰,۰۰۰ تومان</span>
                                    </div>
                                    <button class="btn-add-mini" onclick="addToCart(10)">
                                        <i class="fas fa-plus"></i>
                                        افزودن
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Undo Toast -->
<div id="undoToast" class="undo-toast">
    <div class="toast-content">
        <span id="toastMessage">محصول از سبد حذف شد</span>
        <button class="btn-undo" onclick="undoRemove()">
            <i class="fas fa-undo"></i>
            بازگردانی
        </button>
    </div>
    <button class="toast-close" onclick="closeToast()">
        <i class="fas fa-times"></i>
    </button>
</div>

<script>
let removedItem = null;
let undoTimeout = null;

// Update quantity
function updateQuantity(itemId, change) {
    const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
    const input = cartItem.querySelector('.qty-input');
    const currentValue = parseInt(input.value);
    const maxStock = parseInt(input.max);
    
    let newValue = currentValue + change;
    
    if (newValue < 1) newValue = 1;
    if (newValue > maxStock) {
        showNotification('موجودی انبار کافی نیست', 'warning');
        return;
    }
    
    input.value = newValue;
    
    // Update cart via AJAX
    updateCartItem(itemId, newValue);
    
    // Add animation
    cartItem.classList.add('updating');
    setTimeout(() => cartItem.classList.remove('updating'), 300);
}

// Remove item from cart
function removeItem(itemId) {
    const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
    removedItem = {
        id: itemId,
        element: cartItem.cloneNode(true)
    };
    
    // Animate removal
    cartItem.style.opacity = '0';
    cartItem.style.transform = 'translateX(100px)';
    
    setTimeout(() => {
        cartItem.remove();
        showUndoToast('محصول از سبد حذف شد');
        updateCartTotals();
    }, 300);
    
    // Send AJAX request
    removeFromCart(itemId);
}

// Undo remove
function undoRemove() {
    if (removedItem) {
        const cartList = document.querySelector('.cart-items-list');
        cartList.insertAdjacentHTML('beforeend', removedItem.element.outerHTML);
        
        // Re-add to cart via AJAX
        addBackToCart(removedItem.id);
        
        removedItem = null;
        closeToast();
        updateCartTotals();
    }
}

// Save for later
function saveForLater(itemId) {
    showNotification('محصول به لیست علاقه‌مندی‌ها اضافه شد', 'success');
    removeItem(itemId);
    // Send AJAX request to move to wishlist
}

// Show undo toast
function showUndoToast(message) {
    const toast = document.getElementById('undoToast');
    const messageEl = document.getElementById('toastMessage');
    
    messageEl.textContent = message;
    toast.classList.add('show');
    
    // Auto hide after 5 seconds
    if (undoTimeout) clearTimeout(undoTimeout);
    undoTimeout = setTimeout(closeToast, 5000);
}

// Close toast
function closeToast() {
    document.getElementById('undoToast').classList.remove('show');
    removedItem = null;
}

// Apply coupon
document.getElementById('couponForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const couponCode = document.getElementById('couponCode').value.trim();
    const messageEl = document.getElementById('couponMessage');
    const applyBtn = this.querySelector('.btn-apply-coupon');
    
    if (!couponCode) {
        messageEl.className = 'coupon-message error';
        messageEl.innerHTML = '<i class="fas fa-times-circle"></i> لطفاً کد تخفیف را وارد کنید';
        return;
    }
    
    // Show loading
    applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    applyBtn.disabled = true;
    
    // Simulate AJAX call
    setTimeout(() => {
        // Success simulation
        if (couponCode.toUpperCase() === 'DISCOUNT20') {
            messageEl.className = 'coupon-message success';
            messageEl.innerHTML = '<i class="fas fa-check-circle"></i> کد تخفیف با موفقیت اعمال شد! ۲۰٪ تخفیف';
            
            // Animate discount application
            const totalElement = document.querySelector('.total-price');
            totalElement.style.transform = 'scale(1.1)';
            setTimeout(() => totalElement.style.transform = 'scale(1)', 300);
            
            // Update total (should be done via AJAX)
            updateCartTotals();
        } else {
            messageEl.className = 'coupon-message error';
            messageEl.innerHTML = '<i class="fas fa-times-circle"></i> کد تخفیف نامعتبر است';
        }
        
        applyBtn.innerHTML = 'اعمال';
        applyBtn.disabled = false;
    }, 1000);
});

// Proceed to checkout
function proceedToCheckout() {
    window.location.href = 'checkout.php';
}

// Update cart totals (should be done via AJAX)
function updateCartTotals() {
    // This would make an AJAX call to recalculate totals
    console.log('Updating cart totals...');
}

// AJAX functions (to be implemented with actual backend)
function updateCartItem(itemId, quantity) {
    // AJAX call to update cart
    console.log(`Updating item ${itemId} to quantity ${quantity}`);
}

function removeFromCart(itemId) {
    // AJAX call to remove item
    console.log(`Removing item ${itemId}`);
}

function addBackToCart(itemId) {
    // AJAX call to add item back
    console.log(`Adding back item ${itemId}`);
}

function addToCart(productId) {
    // AJAX call to add recommended product
    showNotification('محصول به سبد خرید اضافه شد', 'success');
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Sticky sidebar on scroll
window.addEventListener('scroll', function() {
    const sidebar = document.querySelector('.sticky-sidebar');
    if (!sidebar) return;
    
    const sidebarTop = sidebar.getBoundingClientRect().top;
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 200) {
        sidebar.classList.add('fixed');
    } else {
        sidebar.classList.remove('fixed');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart page loaded');
    
    // Add subtle animations to cart items
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

<?php include 'footer.php'; ?>
