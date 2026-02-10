<?php
require_once 'config.php';

// دریافت ID محصول
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// دریافت اطلاعات محصول
$product = $db->fetchOne("
    SELECT p.*, c.name as category_name, c.id as category_id
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = :id AND p.status = 'active'
", [':id' => $product_id]);

if (!$product) {
    header('Location: products.php');
    exit;
}

// افزایش تعداد بازدید
$db->execute("UPDATE products SET view_count = view_count + 1 WHERE id = :id", [':id' => $product_id]);

// دریافت گالری تصاویر
$images = json_decode($product['images'], true) ?: [];
array_unshift($images, $product['main_image']); // اضافه کردن تصویر اصلی به ابتدا

// دریافت رنگ‌ها و سایزها
$colors = json_decode($product['colors'], true) ?: [];
$sizes = json_decode($product['sizes'], true) ?: [];

// دریافت نظرات
$reviews = $db->query("
    SELECT r.*, u.name as user_name, u.avatar,
           o.id as order_id, o.created_at as purchase_date,
           oi.size, oi.color
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN order_items oi ON r.order_item_id = oi.id
    LEFT JOIN orders o ON oi.order_id = o.id
    WHERE r.product_id = :product_id AND r.status = 'approved'
    ORDER BY r.created_at DESC
    LIMIT 10
", [':product_id' => $product_id]);

// محاسبه میانگین رتبه
$avg_rating = $db->fetchOne("
    SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(*) as review_count
    FROM reviews
    WHERE product_id = :product_id AND status = 'approved'
", [':product_id' => $product_id]);

// دریافت محصولات پیشنهادی (Cross-sell)
$cross_sell_products = $db->query("
    SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.id IN (
        SELECT cross_sell_id FROM product_cross_sells WHERE product_id = :product_id
    )
    GROUP BY p.id
    LIMIT 4
", [':product_id' => $product_id]);

// دریافت محصولات مشابه (Related Products)
$related_products = $db->query("
    SELECT p.*, COALESCE(AVG(r.rating), 0) as avg_rating
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.category_id = :category_id 
      AND p.id != :product_id 
      AND p.status = 'active'
    GROUP BY p.id
    ORDER BY p.sales_count DESC
    LIMIT 8
", [
    ':category_id' => $product['category_id'],
    ':product_id' => $product_id
]);

$page_title = $product['name'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Helper::sanitize($page_title); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo Helper::sanitize($product['short_description']); ?>">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* این استایل‌ها باید به style.css اضافه شوند */
    .product-detail-page { padding: 2rem 0 4rem; }
    
    /* Breadcrumb */
    .breadcrumb-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 1.5rem 0;
        margin-bottom: 2rem;
    }
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #64748b;
    }
    .breadcrumb a { color: #6366f1; text-decoration: none; transition: color 0.3s; }
    .breadcrumb a:hover { color: #4f46e5; }
    .breadcrumb .separator { color: #cbd5e1; margin: 0 0.3rem; }
    
    /* Social Share */
    .social-share {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .social-share .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 1.1rem;
        color: white;
    }
    .share-btn.telegram { background: #0088cc; }
    .share-btn.whatsapp { background: #25d366; }
    .share-btn.instagram { background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); }
    .share-btn:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
    
    /* Sticky Buy Bar */
    .sticky-buy-bar {
        position: fixed;
        bottom: -100px;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        padding: 1rem 0;
        z-index: 999;
        transition: bottom 0.3s;
    }
    .sticky-buy-bar.show { bottom: 0; }
    .sticky-buy-bar .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .sticky-buy-bar .product-mini {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .sticky-buy-bar .product-mini img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    .sticky-buy-bar .product-mini .info h4 {
        font-size: 0.95rem;
        margin-bottom: 0.3rem;
    }
    .sticky-buy-bar .product-mini .price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #6366f1;
    }
    .sticky-buy-bar .buy-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> خانه</a>
                <span class="separator">/</span>
                <a href="products.php?category=<?php echo $product['category_id']; ?>">
                    <?php echo Helper::sanitize($product['category_name']); ?>
                </a>
                <span class="separator">/</span>
                <span><?php echo Helper::sanitize($product['name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="product-detail-page">
        <div class="container">
            <div class="product-detail-layout">
                
                <!-- Left: Image Gallery -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img id="main-image" src="assets/images/products/<?php echo $images[0]; ?>" 
                             alt="<?php echo Helper::sanitize($product['name']); ?>">
                        <?php if ($product['discount_percent'] > 0): ?>
                            <span class="discount-badge"><?php echo $product['discount_percent']; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="thumbnail-gallery">
                        <?php foreach ($images as $index => $image): ?>
                            <img src="assets/images/products/<?php echo $image; ?>" 
                                 onclick="changeMainImage(this.src)"
                                 class="<?php echo $index === 0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Product Info -->
                <div class="product-info-section">
                    <h1 class="product-title"><?php echo Helper::sanitize($product['name']); ?></h1>
                    
                    <!-- Rating & Reviews -->
                    <div class="product-meta">
                        <div class="rating-display">
                            <?php
                            $rating = $avg_rating['avg_rating'];
                            for ($i = 1; $i <= 5; $i++):
                                if ($i <= $rating):
                                    echo '<i class="fas fa-star"></i>';
                                elseif ($i - 0.5 <= $rating):
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                else:
                                    echo '<i class="far fa-star"></i>';
                                endif;
                            endfor;
                            ?>
                            <span class="rating-number"><?php echo number_format($rating, 1); ?></span>
                            <a href="#reviews" class="review-count">(<?php echo $avg_rating['review_count']; ?> نظر)</a>
                        </div>
                        <div class="product-stats">
                            <span><i class="fas fa-eye"></i> <?php echo number_format($product['view_count']); ?> بازدید</span>
                            <span><i class="fas fa-shopping-cart"></i> <?php echo number_format($product['sales_count']); ?> فروش</span>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="price-section">
                        <?php if ($product['discount_percent'] > 0): ?>
                            <div class="price-wrapper">
                                <span class="old-price"><?php echo Helper::formatPrice($product['price']); ?></span>
                                <span class="price"><?php echo Helper::formatPrice($product['final_price']); ?> <small>تومان</small></span>
                                <span class="save-amount">
                                    شما <?php echo Helper::formatPrice($product['price'] - $product['final_price']); ?> تومان صرفه‌جویی می‌کنید
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="price"><?php echo Helper::formatPrice($product['final_price']); ?> <small>تومان</small></span>
                        <?php endif; ?>
                    </div>

                    <!-- Short Description -->
                    <div class="short-description">
                        <p><?php echo nl2br(Helper::sanitize($product['short_description'])); ?></p>
                    </div>

                    <!-- Color Selection -->
                    <?php if (count($colors) > 0): ?>
                        <div class="variant-section">
                            <label class="variant-label">
                                <i class="fas fa-palette"></i> رنگ: <span id="selected-color-name"><?php echo $colors[0]['name']; ?></span>
                            </label>
                            <div class="color-options">
                                <?php foreach ($colors as $index => $color): ?>
                                    <button class="color-option <?php echo $index === 0 ? 'active' : ''; ?>" 
                                            data-color="<?php echo Helper::sanitize($color['name']); ?>"
                                            onclick="selectColor(this)"
                                            style="background: <?php echo $color['code']; ?>;">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Size Selection -->
                    <?php if (count($sizes) > 0): ?>
                        <div class="variant-section">
                            <label class="variant-label">
                                <i class="fas fa-ruler"></i> سایز: <span id="selected-size-name"><?php echo $sizes[0]; ?></span>
                            </label>
                            <div class="size-options">
                                <?php foreach ($sizes as $index => $size): ?>
                                    <button class="size-option <?php echo $index === 0 ? 'active' : ''; ?>" 
                                            data-size="<?php echo $size; ?>"
                                            onclick="selectSize(this)">
                                        <?php echo $size; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <a href="#size-guide" class="size-guide-link">
                                <i class="fas fa-question-circle"></i> راهنمای سایز
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Quantity & Add to Cart -->
                    <div class="purchase-section">
                        <div class="quantity-selector">
                            <button onclick="decreaseQty()"><i class="fas fa-minus"></i></button>
                            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button onclick="increaseQty()"><i class="fas fa-plus"></i></button>
                        </div>
                        <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product_id; ?>)">
                            <i class="fas fa-shopping-cart"></i>
                            افزودن به سبد خرید
                        </button>
                        <button class="btn-wishlist" onclick="toggleWishlist(<?php echo $product_id; ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>

                    <!-- Stock Status -->
                    <div class="stock-status">
                        <?php if ($product['stock'] > 10): ?>
                            <span class="in-stock"><i class="fas fa-check-circle"></i> موجود در انبار</span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="low-stock"><i class="fas fa-exclamation-circle"></i> تنها <?php echo $product['stock']; ?> عدد باقی مانده</span>
                        <?php else: ?>
                            <span class="out-of-stock"><i class="fas fa-times-circle"></i> ناموجود</span>
                        <?php endif; ?>
                    </div>

                    <!-- Features -->
                    <div class="product-features">
                        <div class="feature-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <strong>ارسال رایگان</strong>
                                <span>برای خریدهای بالای 500 هزار تومان</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>گارانتی اصالت کالا</strong>
                                <span>تضمین کیفیت و اصالت محصول</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-undo"></i>
                            <div>
                                <strong>7 روز ضمانت بازگشت</strong>
                                <span>بدون هیچ سوال و شرطی</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Share -->
                    <div class="social-share-section">
                        <label><i class="fas fa-share-alt"></i> اشتراک‌گذاری:</label>
                        <div class="social-share">
                            <button class="share-btn telegram" onclick="shareToTelegram()">
                                <i class="fab fa-telegram-plane"></i>
                            </button>
                            <button class="share-btn whatsapp" onclick="shareToWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button class="share-btn instagram" onclick="shareToInstagram()">
                                <i class="fab fa-instagram"></i>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Tabs Section -->
            <div class="product-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" onclick="openTab('description')">
                        <i class="fas fa-align-right"></i> توضیحات
                    </button>
                    <button class="tab-btn" onclick="openTab('specifications')">
                        <i class="fas fa-list"></i> مشخصات
                    </button>
                    <button class="tab-btn" onclick="openTab('reviews')">
                        <i class="fas fa-comments"></i> نظرات (<?php echo $avg_rating['review_count']; ?>)
                    </button>
                </div>

                <div class="tab-contents">
                    <!-- Description Tab -->
                    <div id="description" class="tab-content active">
                        <div class="description-content">
                            <?php echo nl2br(Helper::sanitize($product['description'])); ?>
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div id="specifications" class="tab-content">
                        <table class="specifications-table">
                            <?php
                            $specs = json_decode($product['specifications'], true) ?: [];
                            foreach ($specs as $key => $value):
                            ?>
                                <tr>
                                    <td class="spec-key"><?php echo Helper::sanitize($key); ?></td>
                                    <td class="spec-value"><?php echo Helper::sanitize($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <!-- Reviews Tab -->
                    <div id="reviews" class="tab-content">
                        <div class="reviews-section">
                            <div class="reviews-header">
                                <div class="rating-summary">
                                    <div class="rating-score">
                                        <span class="score"><?php echo number_format($avg_rating['avg_rating'], 1); ?></span>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++):
                                                echo $i <= $avg_rating['avg_rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                            endfor; ?>
                                        </div>
                                        <span class="count">از <?php echo $avg_rating['review_count']; ?> نظر</span>
                                    </div>
                                </div>
                                <button class="btn-write-review" onclick="openReviewModal()">
                                    <i class="fas fa-pen"></i> ثبت نظر
                                </button>
                            </div>

                            <div class="reviews-list">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <img src="assets/images/avatars/<?php echo $review['avatar'] ?: 'default.png'; ?>" 
                                                     alt="<?php echo Helper::sanitize($review['user_name']); ?>"
                                                     class="reviewer-avatar">
                                                <div>
                                                    <strong><?php echo Helper::sanitize($review['user_name']); ?></strong>
                                                    <?php if ($review['order_id']): ?>
                                                        <span class="verified-purchase">
                                                            <i class="fas fa-check-circle"></i> خریدار محصول
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="review-meta">
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++):
                                                        echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                    endfor; ?>
                                                </div>
                                                <span class="review-date">
                                                    <?php echo Helper::timeAgo($review['created_at']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if ($review['size'] || $review['color']): ?>
                                            <div class="review-purchase-info">
                                                <?php if ($review['size']): ?>
                                                    <span><i class="fas fa-ruler"></i> سایز: <?php echo $review['size']; ?></span>
                                                <?php endif; ?>
                                                <?php if ($review['color']): ?>
                                                    <span><i class="fas fa-palette"></i> رنگ: <?php echo $review['color']; ?></span>
                                                <?php endif; ?>
                                                <?php if ($review['purchase_date']): ?>
                                                    <span><i class="fas fa-calendar"></i> خریداری شده در: <?php echo date('Y/m/d', strtotime($review['purchase_date'])); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <p class="review-text"><?php echo nl2br(Helper::sanitize($review['comment'])); ?></p>

                                        <?php if ($review['images']): 
                                            $review_images = json_decode($review['images'], true);
                                            if ($review_images):
                                        ?>
                                            <div class="review-images">
                                                <?php foreach ($review_images as $img): ?>
                                                    <img src="assets/images/reviews/<?php echo $img; ?>" 
                                                         onclick="openImageModal(this.src)">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; endif; ?>

                                        <div class="review-actions">
                                            <button class="review-action-btn" onclick="likeReview(<?php echo $review['id']; ?>)">
                                                <i class="far fa-thumbs-up"></i> مفید بود (<?php echo $review['likes']; ?>)
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (count($reviews) === 0): ?>
                                    <div class="no-reviews">
                                        <i class="fas fa-comment-slash"></i>
                                        <p>هنوز نظری برای این محصول ثبت نشده است.</p>
                                        <button class="btn-primary" onclick="openReviewModal()">
                                            اولین نفری باشید که نظر می‌دهد
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cross-sell Products -->
            <?php if (count($cross_sell_products) > 0): ?>
                <div class="cross-sell-section section">
                    <div class="section-header">
                        <h2><i class="fas fa-gift"></i> محصولات پیشنهادی ما</h2>
                        <p>این محصولات را با هم بخرید و تخفیف بگیرید</p>
                    </div>
                    <div class="products-grid-4">
                        <?php foreach ($cross_sell_products as $product): ?>
                            <?php include 'product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Related Products -->
            <?php if (count($related_products) > 0): ?>
                <div class="related-products-section section">
                    <div class="section-header">
                        <h2><i class="fas fa-th"></i> محصولات مشابه</h2>
                        <p>محصولات دیگری که ممکن است بپسندید</p>
                    </div>
                    <div class="products-slider-container">
                        <button class="slider-nav prev" onclick="slideProducts('related', -1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="products-slider" id="related-slider">
                            <?php foreach ($related_products as $product): ?>
                                <?php include 'product-card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                        <button class="slider-nav next" onclick="slideProducts('related', 1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Sticky Buy Bar -->
    <div class="sticky-buy-bar" id="sticky-buy-bar">
        <div class="container">
            <div class="product-mini">
                <img src="assets/images/products/<?php echo $product['main_image']; ?>" 
                     alt="<?php echo Helper::sanitize($product['name']); ?>">
                <div class="info">
                    <h4><?php echo Helper::sanitize($product['name']); ?></h4>
                    <span class="price"><?php echo Helper::formatPrice($product['final_price']); ?> تومان</span>
                </div>
            </div>
            <div class="buy-actions">
                <div class="quantity-selector">
                    <button onclick="decreaseQty()"><i class="fas fa-minus"></i></button>
                    <input type="number" id="sticky-quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    <button onclick="increaseQty()"><i class="fas fa-plus"></i></button>
                </div>
                <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product_id; ?>)">
                    <i class="fas fa-shopping-cart"></i>
                    افزودن به سبد
                </button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Image Gallery
    function changeMainImage(src) {
        document.getElementById('main-image').src = src;
        document.querySelectorAll('.thumbnail-gallery img').forEach(img => {
            img.classList.remove('active');
            if (img.src === src) img.classList.add('active');
        });
    }

    // Color Selection
    function selectColor(element) {
        document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('selected-color-name').textContent = element.dataset.color;
    }

    // Size Selection
    function selectSize(element) {
        document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('selected-size-name').textContent = element.dataset.size;
    }

    // Quantity
    function increaseQty() {
        const input = document.getElementById('quantity');
        const stickyInput = document.getElementById('sticky-quantity');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
            stickyInput.value = input.value;
        }
    }

    function decreaseQty() {
        const input = document.getElementById('quantity');
        const stickyInput = document.getElementById('sticky-quantity');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            stickyInput.value = input.value;
        }
    }

    // Tabs
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    // Sticky Buy Bar
    window.addEventListener('scroll', function() {
        const buyBar = document.getElementById('sticky-buy-bar');
        const reviewsSection = document.getElementById('reviews');
        
        if (reviewsSection && window.scrollY > reviewsSection.offsetTop - 100) {
            buyBar.classList.add('show');
        } else {
            buyBar.classList.remove('show');
        }
    });

    // Social Share
    function shareToTelegram() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.querySelector('.product-title').textContent);
        window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
    }

    function shareToWhatsApp() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.querySelector('.product-title').textContent);
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    }

    function shareToInstagram() {
        alert('لطفاً لینک را کپی کرده و در استوری اینستاگرام خود به اشتراک بگذارید:\n' + window.location.href);
    }

    // Add to Cart (این تابع در فایل script.js اصلی پیاده‌سازی می‌شود)
    function addToCart(productId) {
        const quantity = document.getElementById('quantity').value;
        const color = document.querySelector('.color-option.active')?.dataset.color || '';
        const size = document.querySelector('.size-option.active')?.dataset.size || '';
        
        console.log('Adding to cart:', { productId, quantity, color, size });
        // اینجا باید درخواست AJAX به سرور ارسال شود
    }

    // Wishlist Toggle
    function toggleWishlist(productId) {
        console.log('Toggle wishlist:', productId);
        // اینجا باید درخواست AJAX به سرور ارسال شود
    }

    // Like Review
    function likeReview(reviewId) {
        console.log('Like review:', reviewId);
        // اینجا باید درخواست AJAX به سرور ارسال شود
    }

    // Open Review Modal
    function openReviewModal() {
        console.log('Open review modal');
        // اینجا باید مودال نظر باز شود
    }

    // Open Image Modal
    function openImageModal(src) {
        console.log('Open image:', src);
        // اینجا باید مودال تصویر باز شود
    }

    // Products Slider
    function slideProducts(sliderId, direction) {
        const slider = document.getElementById(sliderId + '-slider');
        const scrollAmount = 300;
        slider.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
    </script>
</body>
</html>
