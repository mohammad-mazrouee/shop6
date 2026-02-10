<?php
session_start();
require_once 'config.php';

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Sample wishlist items (در حالت واقعی از دیتابیس می‌خوانیم)
$wishlistItems = [
    [
        'id' => 1,
        'name' => 'کت تک مشکی لوکس',
        'price' => 4500000,
        'discount_price' => 3200000,
        'discount_percent' => 29,
        'image' => 'assets/images/products/product1.jpg',
        'category' => 'کت و شلوار',
        'rating' => 4.8,
        'stock_status' => 'in_stock', // in_stock, low_stock, out_of_stock, on_sale
        'stock_count' => 15,
        'price_dropped' => false, // آیا قیمت کاهش یافته؟
        'ribbon_text' => null
    ],
    [
        'id' => 2,
        'name' => 'پیراهن رسمی سفید',
        'price' => 2800000,
        'discount_price' => 2240000,
        'discount_percent' => 20,
        'image' => 'assets/images/products/product2.jpg',
        'category' => 'پیراهن',
        'rating' => 4.9,
        'stock_status' => 'on_sale',
        'stock_count' => 8,
        'price_dropped' => true,
        'ribbon_text' => '20% تخفیف فقط برای شما!'
    ],
    [
        'id' => 3,
        'name' => 'شلوار جین کلاسیک',
        'price' => 3200000,
        'discount_price' => 3200000,
        'discount_percent' => 0,
        'image' => 'assets/images/products/product3.jpg',
        'category' => 'شلوار',
        'rating' => 4.7,
        'stock_status' => 'low_stock',
        'stock_count' => 3,
        'price_dropped' => false,
        'ribbon_text' => 'موجودی محدود!'
    ],
    [
        'id' => 4,
        'name' => 'کفش چرم دست‌دوز',
        'price' => 5500000,
        'discount_price' => 5500000,
        'discount_percent' => 0,
        'image' => 'assets/images/products/product4.jpg',
        'category' => 'کفش',
        'rating' => 5.0,
        'stock_status' => 'out_of_stock',
        'stock_count' => 0,
        'price_dropped' => false,
        'ribbon_text' => 'ناموجود - شارژ شد!'
    ]
];

$totalItems = count($wishlistItems);
$pageTitle = 'علاقه‌مندی‌های من';
include 'header.php';

?>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<div class="wishlist-page">
    <!-- Breadcrumb -->
    <div class="container">
        <nav class="breadcrumb-nav" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">خانه</a></li>
                <li class="breadcrumb-item active" aria-current="page">علاقه‌مندی‌ها</li>
            </ol>
        </nav>
    </div>

    <div class="container">
        <?php if (empty($wishlistItems)): ?>
            <!-- Empty Wishlist State -->
            <div class="empty-wishlist">
                <div class="empty-wishlist-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h1 class="empty-title">لیست علاقه‌مندی‌های شما خالی است</h1>
                <p class="empty-description">
                    محصولات مورد علاقه خود را کشف کنید و با کلیک روی آیکون قلب، آن‌ها را به این لیست اضافه کنید
                </p>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag"></i>
                    شروع گشت و گذار در فروشگاه
                </a>
                <div class="empty-features">
                    <div class="empty-feature">
                        <i class="fas fa-bell"></i>
                        <span>اطلاع‌رسانی کاهش قیمت</span>
                    </div>
                    <div class="empty-feature">
                        <i class="fas fa-sync-alt"></i>
                        <span>ذخیره خودکار</span>
                    </div>
                    <div class="empty-feature">
                        <i class="fas fa-heart"></i>
                        <span>دسترسی همه‌جا</span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Wishlist Header -->
            <div class="wishlist-header">
                <div class="wishlist-header-left">
                    <h1 class="page-title">
                        <i class="fas fa-heart"></i>
                        علاقه‌مندی‌های من
                    </h1>
                    <span class="wishlist-count"><?php echo $totalItems; ?> محصول</span>
                </div>
                <div class="wishlist-header-actions">
                    <button class="btn btn-outline-primary" onclick="shareWishlist()">
                        <i class="fas fa-share-alt"></i>
                        اشتراک‌گذاری لیست
                    </button>
                    <div class="bulk-actions-dropdown">
                        <button class="btn btn-outline" onclick="toggleBulkActions()">
                            <i class="fas fa-tasks"></i>
                            عملیات گروهی
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="bulk-actions-menu" id="bulkActionsMenu">
                            <button onclick="selectAllItems()">
                                <i class="far fa-check-square"></i>
                                انتخاب همه
                            </button>
                            <button onclick="addAllToCart()">
                                <i class="fas fa-cart-plus"></i>
                                افزودن همه به سبد
                            </button>
                            <button onclick="removeAllItems()" class="danger">
                                <i class="far fa-trash-alt"></i>
                                حذف همه
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guest Warning Banner (برای کاربران مهمان) -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="guest-warning-banner">
                    <div class="warning-content">
                        <i class="fas fa-info-circle"></i>
                        <div class="warning-text">
                            <strong>توجه:</strong>
                            لیست علاقه‌مندی شما تا 48 ساعت ذخیره می‌شود. برای ذخیره دائمی، لطفاً وارد حساب کاربری خود شوید.
                        </div>
                    </div>
                    <div class="warning-actions">
                        <a href="login.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            ورود / ثبت‌نام
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wishlist Grid -->
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-card" data-product-id="<?php echo $item['id']; ?>">
                        <!-- Quick Remove Button -->
                        <button class="btn-quick-remove" onclick="removeFromWishlist(<?php echo $item['id']; ?>)" title="حذف از لیست">
                            <i class="fas fa-times"></i>
                        </button>

                        <!-- Selection Checkbox (for bulk actions) -->
                        <div class="selection-checkbox">
                            <input type="checkbox" id="select-<?php echo $item['id']; ?>" class="item-checkbox">
                            <label for="select-<?php echo $item['id']; ?>"></label>
                        </div>

                        <!-- Status Ribbon -->
                        <?php if ($item['ribbon_text']): ?>
                            <div class="status-ribbon <?php echo $item['stock_status']; ?>">
                                <?php echo $item['ribbon_text']; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($item['price_dropped']): ?>
                            <div class="price-drop-badge">
                                <i class="fas fa-arrow-down"></i>
                                کاهش قیمت!
                            </div>
                        <?php endif; ?>

                        <!-- Product Image -->
                        <a href="product-detail.php?id=<?php echo $item['id']; ?>" class="wishlist-card-image">
                            <img src="<?php echo $item['image']; ?>" 
                                 alt="<?php echo $item['name']; ?>"
                                 loading="lazy">
                            
                            <!-- Quick View Overlay -->
                            <div class="quick-view-overlay">
                                <button class="btn-quick-view" onclick="quickView(<?php echo $item['id']; ?>); event.preventDefault();">
                                    <i class="fas fa-eye"></i>
                                    نمایش سریع
                                </button>
                            </div>
                        </a>

                        <!-- Product Info -->
                        <div class="wishlist-card-body">
                            <div class="product-category"><?php echo $item['category']; ?></div>
                            
                            <h3 class="product-title">
                                <a href="product-detail.php?id=<?php echo $item['id']; ?>">
                                    <?php echo $item['name']; ?>
                                </a>
                            </h3>

                            <!-- Rating -->
                            <div class="product-rating">
                                <div class="stars">
                                    <?php
                                    $fullStars = floor($item['rating']);
                                    $hasHalfStar = ($item['rating'] - $fullStars) >= 0.5;
                                    
                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    if ($hasHalfStar) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    }
                                    for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                                <span class="rating-number"><?php echo $item['rating']; ?></span>
                            </div>

                            <!-- Price Section -->
                            <div class="product-price-section">
                                <?php if ($item['discount_percent'] > 0): ?>
                                    <div class="price-row">
                                        <span class="original-price"><?php echo number_format($item['price']); ?> تومان</span>
                                        <span class="discount-badge">٪<?php echo $item['discount_percent']; ?></span>
                                    </div>
                                    <div class="current-price"><?php echo number_format($item['discount_price']); ?> تومان</div>
                                <?php else: ?>
                                    <div class="current-price"><?php echo number_format($item['price']); ?> تومان</div>
                                <?php endif; ?>
                            </div>

                            <!-- Stock Status -->
                            <div class="stock-status-section">
                                <?php if ($item['stock_status'] === 'in_stock'): ?>
                                    <div class="stock-badge in-stock">
                                        <i class="fas fa-check-circle"></i>
                                        موجود در انبار
                                    </div>
                                <?php elseif ($item['stock_status'] === 'low_stock'): ?>
                                    <div class="stock-badge low-stock">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        تنها <?php echo $item['stock_count']; ?> عدد باقی‌مانده!
                                    </div>
                                <?php elseif ($item['stock_status'] === 'out_of_stock'): ?>
                                    <div class="stock-badge out-of-stock">
                                        <i class="fas fa-times-circle"></i>
                                        ناموجود
                                    </div>
                                <?php elseif ($item['stock_status'] === 'on_sale'): ?>
                                    <div class="stock-badge on-sale">
                                        <i class="fas fa-fire"></i>
                                        در حال تخفیف ویژه!
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card-actions">
                                <?php if ($item['stock_status'] !== 'out_of_stock'): ?>
                                    <button class="btn btn-add-to-cart" onclick="addToCartFromWishlist(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i>
                                        افزودن به سبد خرید
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-notify-me" onclick="notifyWhenAvailable(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-bell"></i>
                                        اطلاع به من
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-icon-action" onclick="shareProduct(<?php echo $item['id']; ?>)" title="اشتراک‌گذاری">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Added Date -->
                        <div class="card-footer">
                            <span class="added-date">
                                <i class="far fa-clock"></i>
                                افزوده شده 3 روز پیش
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bottom Actions Bar -->
            <div class="bottom-actions-bar">
                <button class="btn btn-outline-danger" onclick="clearAllWishlist()">
                    <i class="far fa-trash-alt"></i>
                    پاک کردن همه
                </button>
                <button class="btn btn-primary btn-lg" onclick="addSelectedToCart()">
                    <i class="fas fa-cart-plus"></i>
                    افزودن انتخاب‌شده‌ها به سبد
                </button>
            </div>

            <!-- Price Alert Section -->
            <div class="price-alert-section">
                <div class="alert-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="alert-content">
                    <h3>هوشمند باش، کمتر بپرداز!</h3>
                    <p>وقتی قیمت محصولات علاقه‌مندی شما کاهش یابد، به شما اطلاع می‌دهیم</p>
                </div>
                <div class="alert-status">
                    <span class="status-active">
                        <i class="fas fa-check-circle"></i>
                        فعال
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick View Modal -->
<div id="quickViewModal" class="modal">
    <div class="modal-overlay" onclick="closeQuickView()"></div>
    <div class="modal-content">
        <button class="modal-close" onclick="closeQuickView()">
            <i class="fas fa-times"></i>
        </button>
        <div id="quickViewContent">
            <!-- محتوا از طریق AJAX بارگذاری می‌شود -->
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div id="shareModal" class="modal">
    <div class="modal-overlay" onclick="closeShareModal()"></div>
    <div class="modal-content modal-small">
        <button class="modal-close" onclick="closeShareModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="share-content">
            <h3>اشتراک‌گذاری لیست</h3>
            <div class="share-options">
                <button class="share-btn telegram" onclick="shareOn('telegram')">
                    <i class="fab fa-telegram"></i>
                    تلگرام
                </button>
                <button class="share-btn whatsapp" onclick="shareOn('whatsapp')">
                    <i class="fab fa-whatsapp"></i>
                    واتساپ
                </button>
                <button class="share-btn instagram" onclick="shareOn('instagram')">
                    <i class="fab fa-instagram"></i>
                    اینستاگرام
                </button>
                <button class="share-btn link" onclick="copyLink()">
                    <i class="fas fa-link"></i>
                    کپی لینک
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="toast-notification">
    <div class="toast-content">
        <i id="toastIcon" class="fas fa-check-circle"></i>
        <span id="toastMessage">عملیات با موفقیت انجام شد</span>
    </div>
    <button class="toast-close" onclick="closeToast()">
        <i class="fas fa-times"></i>
    </button>
</div>

<script>
// Wishlist Functions
function removeFromWishlist(productId) {
    const card = document.querySelector(`[data-product-id="${productId}"]`);
    
    // انیمیشن حذف
    card.style.opacity = '0';
    card.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        card.remove();
        updateWishlistCount();
        showToast('محصول از لیست علاقه‌مندی حذف شد', 'success');
        
        // بررسی اینکه آیا لیست خالی شده؟
        checkEmptyWishlist();
    }, 300);
    
    // ارسال درخواست AJAX به سرور
    // $.ajax({ ... });
}

function addToCartFromWishlist(productId) {
    const card = document.querySelector(`[data-product-id="${productId}"]`);
    const cartIcon = document.querySelector('.cart-icon'); // فرض بر وجود آیکون سبد در هدر
    
    // کلون کردن تصویر محصول برای انیمیشن
    const productImage = card.querySelector('img');
    const imageClone = productImage.cloneNode(true);
    const imageRect = productImage.getBoundingClientRect();
    
    imageClone.style.position = 'fixed';
    imageClone.style.top = imageRect.top + 'px';
    imageClone.style.left = imageRect.left + 'px';
    imageClone.style.width = imageRect.width + 'px';
    imageClone.style.height = imageRect.height + 'px';
    imageClone.style.zIndex = '9999';
    imageClone.style.transition = 'all 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
    imageClone.style.pointerEvents = 'none';
    
    document.body.appendChild(imageClone);
    
    // انیمیشن پرواز به سبد خرید
    setTimeout(() => {
        const cartRect = cartIcon ? cartIcon.getBoundingClientRect() : { top: 0, left: window.innerWidth };
        imageClone.style.top = cartRect.top + 'px';
        imageClone.style.left = cartRect.left + 'px';
        imageClone.style.width = '30px';
        imageClone.style.height = '30px';
        imageClone.style.opacity = '0';
    }, 100);
    
    setTimeout(() => {
        imageClone.remove();
        
        // محو کردن کارت
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        
        setTimeout(() => {
            card.remove();
            updateWishlistCount();
            updateCartCount();
            showToast('محصول به سبد خرید اضافه شد', 'success');
            checkEmptyWishlist();
        }, 300);
    }, 900);
    
    // ارسال درخواست AJAX
    // $.ajax({ ... });
}

function notifyWhenAvailable(productId) {
    showToast('شما از موجود شدن این محصول مطلع خواهید شد', 'info');
    // ارسال درخواست AJAX
}

function shareProduct(productId) {
    document.getElementById('shareModal').classList.add('show');
}

function shareWishlist() {
    document.getElementById('shareModal').classList.add('show');
}

function quickView(productId) {
    const modal = document.getElementById('quickViewModal');
    modal.classList.add('show');
    
    // بارگذاری محتوا از طریق AJAX
    // در اینجا باید محتوای محصول را از سرور بگیریم
}

function closeQuickView() {
    document.getElementById('quickViewModal').classList.remove('show');
}

function closeShareModal() {
    document.getElementById('shareModal').classList.remove('show');
}

function shareOn(platform) {
    const url = window.location.href;
    let shareUrl = '';
    
    switch(platform) {
        case 'telegram':
            shareUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(url)}`;
            break;
        case 'instagram':
            showToast('لینک کپی شد! در اینستاگرام به اشتراک بگذارید', 'info');
            copyLink();
            return;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank');
    }
}

function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        showToast('لینک کپی شد!', 'success');
    });
}

// Bulk Actions
function toggleBulkActions() {
    const menu = document.getElementById('bulkActionsMenu');
    menu.classList.toggle('show');
}

function selectAllItems() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });
}

function addAllToCart() {
    const cards = document.querySelectorAll('.wishlist-card');
    let delay = 0;
    
    cards.forEach(card => {
        const productId = card.getAttribute('data-product-id');
        const stockStatus = card.querySelector('.stock-badge').classList;
        
        if (!stockStatus.contains('out-of-stock')) {
            setTimeout(() => {
                addToCartFromWishlist(productId);
            }, delay);
            delay += 200; // فاصله بین انیمیشن‌ها
        }
    });
}

function addSelectedToCart() {
    const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        showToast('لطفاً حداقل یک محصول را انتخاب کنید', 'warning');
        return;
    }
    
    let delay = 0;
    selectedCheckboxes.forEach(checkbox => {
        const card = checkbox.closest('.wishlist-card');
        const productId = card.getAttribute('data-product-id');
        
        setTimeout(() => {
            addToCartFromWishlist(productId);
        }, delay);
        delay += 200;
    });
}

function removeAllItems() {
    if (confirm('آیا مطمئن هستید که می‌خواهید تمام محصولات را حذف کنید؟')) {
        clearAllWishlist();
    }
}

function clearAllWishlist() {
    if (confirm('آیا مطمئن هستید؟ این عملیات قابل بازگشت نیست.')) {
        const cards = document.querySelectorAll('.wishlist-card');
        
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    card.remove();
                    
                    if (index === cards.length - 1) {
                        location.reload(); // نمایش صفحه خالی
                    }
                }, 300);
            }, index * 100);
        });
        
        // ارسال درخواست AJAX
    }
}

// Helper Functions
function updateWishlistCount() {
    const remainingCards = document.querySelectorAll('.wishlist-card').length;
    const countElement = document.querySelector('.wishlist-count');
    
    if (countElement) {
        countElement.textContent = `${remainingCards} محصول`;
    }
    
    // به‌روزرسانی شمارنده در هدر
    const headerWishlistCount = document.querySelector('.wishlist-badge-count');
    if (headerWishlistCount) {
        headerWishlistCount.textContent = remainingCards;
    }
}

function updateCartCount() {
    const cartCount = document.querySelector('.cart-badge-count');
    if (cartCount) {
        const currentCount = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = currentCount + 1;
        
        // انیمیشن
        cartCount.style.transform = 'scale(1.3)';
        setTimeout(() => {
            cartCount.style.transform = 'scale(1)';
        }, 300);
    }
}

function checkEmptyWishlist() {
    const remainingCards = document.querySelectorAll('.wishlist-card').length;
    
    if (remainingCards === 0) {
        setTimeout(() => {
            location.reload();
        }, 500);
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    // تنظیم آیکون
    let iconClass = 'fas fa-check-circle';
    if (type === 'error') iconClass = 'fas fa-times-circle';
    if (type === 'warning') iconClass = 'fas fa-exclamation-triangle';
    if (type === 'info') iconClass = 'fas fa-info-circle';
    
    icon.className = iconClass;
    messageEl.textContent = message;
    toast.className = `toast-notification ${type}`;
    
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function closeToast() {
    document.getElementById('toast').classList.remove('show');
}

// بستن منوهای dropdown با کلیک خارج
document.addEventListener('click', function(event) {
    if (!event.target.closest('.bulk-actions-dropdown')) {
        document.getElementById('bulkActionsMenu')?.classList.remove('show');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Wishlist page loaded');
    
    // انیمیشن ورود کارت‌ها
    const cards = document.querySelectorAll('.wishlist-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>

<?php include 'footer.php'; ?>
