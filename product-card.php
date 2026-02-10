<?php
/**
 * ==========================================
 * کامپوننت کارت محصول
 * ==========================================
 * فایل: components/product-card.php
 * ==========================================
 */

// محاسبه درصد تخفیف
$discountPercent = 0;
if ($product['discount_price'] > 0) {
    $discountPercent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
}

$finalPrice = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
?>

<style>
    /* ==========================================
       Product Card - کارت محصول
       ========================================== */
    .product-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: var(--transition-smooth);
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    }
    
    .product-image-wrapper {
        position: relative;
        padding-top: 125%;
        overflow: hidden;
        background: #f5f5f5;
    }
    
    .product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition-smooth);
    }
    
    .product-image.main {
        opacity: 1;
    }
    
    .product-image.hover {
        opacity: 0;
    }
    
    .product-card:hover .product-image.main {
        opacity: 0;
    }
    
    .product-card:hover .product-image.hover {
        opacity: 1;
    }
    
    .product-badges {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 2;
    }
    
    .product-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-new {
        background: #22c55e;
        color: white;
    }
    
    .badge-sale {
        background: #ef4444;
        color: white;
    }
    
    .badge-limited {
        background: var(--color-secondary);
        color: var(--color-primary);
    }
    
    .product-actions {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        opacity: 0;
        transform: translateX(-10px);
        transition: var(--transition-smooth);
    }
    
    .product-card:hover .product-actions {
        opacity: 1;
        transform: translateX(0);
    }
    
    .product-action-btn {
        width: 40px;
        height: 40px;
        background: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition-fast);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-action-btn:hover {
        background: var(--color-secondary);
        color: white;
        transform: scale(1.1);
    }
    
    .product-info {
        padding: 20px;
    }
    
    .product-category {
        font-size: 12px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-name a {
        color: inherit;
        text-decoration: none;
        transition: var(--transition-fast);
    }
    
    .product-name a:hover {
        color: var(--color-secondary);
    }
    
    .product-rating {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }
    
    .product-stars {
        display: flex;
        gap: 2px;
    }
    
    .product-stars i {
        font-size: 12px;
        color: #fbbf24;
    }
    
    .product-rating-count {
        font-size: 12px;
        color: #999;
    }
    
    .product-price {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .price-current {
        font-family: var(--font-display);
        font-size: 22px;
        font-weight: 700;
        color: var(--color-primary);
    }
    
    .price-original {
        font-size: 16px;
        color: #999;
        text-decoration: line-through;
    }
    
    .add-to-cart-btn {
        width: 100%;
        padding: 12px;
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition-smooth);
    }
    
    .add-to-cart-btn:hover {
        background: var(--color-secondary);
        color: var(--color-primary);
        transform: translateY(-2px);
    }
</style>

<div class="product-card" data-product-id="<?php echo $product['id']; ?>">
    <div class="product-image-wrapper">
        <img src="<?php echo $product['main_image']; ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             class="product-image main">
        <?php if ($product['hover_image']): ?>
            <img src="<?php echo $product['hover_image']; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="product-image hover">
        <?php endif; ?>
        
        <!-- Badges -->
        <div class="product-badges">
            <?php if ($product['is_new']): ?>
                <span class="product-badge badge-new">جدید</span>
            <?php endif; ?>
            
            <?php if ($discountPercent > 0): ?>
                <span class="product-badge badge-sale"><?php echo $discountPercent; ?>% تخفیف</span>
            <?php endif; ?>
            
            <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                <span class="product-badge badge-limited">محدود</span>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="product-actions">
            <button class="product-action-btn" 
                    onclick="toggleWishlist(<?php echo $product['id']; ?>)"
                    title="افزودن به علاقه‌مندی‌ها">
                <i class="far fa-heart"></i>
            </button>
            <button class="product-action-btn" 
                    onclick="quickView(<?php echo $product['id']; ?>)"
                    title="مشاهده سریع">
                <i class="far fa-eye"></i>
            </button>
        </div>
    </div>
    
    <div class="product-info">
        <p class="product-category">
            <?php 
            $category = Database::fetchOne("SELECT name FROM categories WHERE id = ?", [$product['category_id']]);
            echo htmlspecialchars($category['name']);
            ?>
        </p>
        
        <h3 class="product-name">
            <a href="product.php?id=<?php echo $product['id']; ?>">
                <?php echo htmlspecialchars($product['name']); ?>
            </a>
        </h3>
        
        <div class="product-rating">
            <div class="product-stars">
                <?php
                $avgRating = Database::fetchOne(
                    "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ? AND status = 'approved'",
                    [$product['id']]
                )['avg_rating'] ?? 0;
                
                for ($i = 1; $i <= 5; $i++):
                ?>
                    <i class="<?php echo $i <= round($avgRating) ? 'fas' : 'far'; ?> fa-star"></i>
                <?php endfor; ?>
            </div>
            <span class="product-rating-count">(<?php echo round($avgRating, 1); ?>)</span>
        </div>
        
        <div class="product-price">
            <span class="price-current"><?php echo formatPrice($finalPrice); ?></span>
            <?php if ($discountPercent > 0): ?>
                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
            <?php endif; ?>
        </div>
        
        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">
            <i class="fas fa-shopping-cart"></i>
            افزودن به سبد خرید
        </button>
    </div>
</div>
