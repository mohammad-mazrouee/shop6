<?php
require_once 'config.php';

// دریافت پارامترهای فیلتر و مرتب‌سازی
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000000;
$color = isset($_GET['color']) ? $_GET['color'] : '';
$size = isset($_GET['size']) ? $_GET['size'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// ساخت کوئری پایه
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($min_price > 0) {
    $where_conditions[] = "p.final_price >= :min_price";
    $params[':min_price'] = $min_price;
}

if ($max_price < 10000000) {
    $where_conditions[] = "p.final_price <= :max_price";
    $params[':max_price'] = $max_price;
}

if (!empty($color)) {
    $where_conditions[] = "p.colors LIKE :color";
    $params[':color'] = "%$color%";
}

if (!empty($size)) {
    $where_conditions[] = "p.sizes LIKE :size";
    $params[':size'] = "%$size%";
}

$where_sql = implode(' AND ', $where_conditions);

// تعیین نوع مرتب‌سازی
switch ($sort) {
    case 'expensive':
        $order_by = 'p.final_price DESC';
        break;
    case 'cheap':
        $order_by = 'p.final_price ASC';
        break;
    case 'popular':
        $order_by = 'p.view_count DESC, p.sales_count DESC';
        break;
    case 'newest':
    default:
        $order_by = 'p.created_at DESC';
        break;
}

// شمارش کل محصولات
$count_query = "SELECT COUNT(*) as total FROM products p WHERE $where_sql";
$count_result = $db->query($count_query, $params);
$total_products = $count_result[0]['total'];

// دریافت محصولات
$products_query = "
    SELECT p.*, c.name as category_name,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(r.id) as review_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE $where_sql
    GROUP BY p.id
    ORDER BY $order_by
    LIMIT :limit OFFSET :offset
";
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$products = $db->query($products_query, $params);

// دریافت دسته‌بندی‌ها برای فیلتر
$categories = $db->query("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY sort_order");

// دریافت رنگ‌های موجود
$available_colors = $db->query("
    SELECT DISTINCT color_code, color_name 
    FROM (
        SELECT 
            JSON_EXTRACT(colors, '$[*].code') as color_codes,
            JSON_EXTRACT(colors, '$[*].name') as color_names
        FROM products 
        WHERE status = 'active' AND colors IS NOT NULL
    ) as color_data
");

// اگر درخواست AJAX باشد، فقط محصولات را برگردان
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'products' => $products,
        'total' => $total_products,
        'has_more' => ($offset + $per_page) < $total_products
    ]);
    exit;
}

// دریافت اطلاعات دسته‌بندی فعلی
$current_category = null;
if ($category_id > 0) {
    $current_category = $db->fetchOne("SELECT * FROM categories WHERE id = :id", [':id' => $category_id]);
}

$page_title = $current_category ? $current_category['name'] : 'تمام محصولات';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> خانه</a>
                <?php if ($current_category): ?>
                    <span class="separator">/</span>
                    <span><?php echo Helper::sanitize($current_category['name']); ?></span>
                <?php else: ?>
                    <span class="separator">/</span>
                    <span>محصولات</span>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Products Page -->
    <div class="products-page">
        <div class="container">
            <div class="products-layout">
                
                <!-- Sidebar Filters -->
                <aside class="products-sidebar">
                    <div class="filter-section">
                        <div class="filter-header">
                            <h3><i class="fas fa-filter"></i> فیلترها</h3>
                            <button class="clear-filters" onclick="clearFilters()">
                                <i class="fas fa-times"></i> پاک کردن
                            </button>
                        </div>

                        <!-- دسته‌بندی -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-th-large"></i> دسته‌بندی
                            </h4>
                            <div class="filter-options">
                                <?php foreach ($categories as $cat): ?>
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="<?php echo $cat['id']; ?>" 
                                               <?php echo $category_id == $cat['id'] ? 'checked' : ''; ?>
                                               onchange="applyFilters()">
                                        <span><?php echo Helper::sanitize($cat['name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- محدوده قیمت -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-dollar-sign"></i> محدوده قیمت
                            </h4>
                            <div class="price-range-slider">
                                <div class="price-inputs">
                                    <input type="number" id="min-price" value="<?php echo $min_price; ?>" 
                                           placeholder="از (تومان)">
                                    <span>-</span>
                                    <input type="number" id="max-price" value="<?php echo $max_price; ?>" 
                                           placeholder="تا (تومان)">
                                </div>
                                <div class="range-slider">
                                    <input type="range" id="price-range-min" min="0" max="10000000" 
                                           value="<?php echo $min_price; ?>" step="50000">
                                    <input type="range" id="price-range-max" min="0" max="10000000" 
                                           value="<?php echo $max_price; ?>" step="50000">
                                </div>
                                <button class="apply-price-btn" onclick="applyPriceFilter()">
                                    <i class="fas fa-check"></i> اعمال
                                </button>
                            </div>
                        </div>

                        <!-- فیلتر رنگ -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-palette"></i> رنگ
                            </h4>
                            <div class="color-filter">
                                <div class="color-option" data-color="" 
                                     onclick="selectColor(this, '')" 
                                     <?php echo empty($color) ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">همه</span>
                                </div>
                                <div class="color-option" data-color="black" 
                                     onclick="selectColor(this, 'black')"
                                     <?php echo $color == 'black' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #000;"></span>
                                </div>
                                <div class="color-option" data-color="white" 
                                     onclick="selectColor(this, 'white')"
                                     <?php echo $color == 'white' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #fff; border: 1px solid #ddd;"></span>
                                </div>
                                <div class="color-option" data-color="red" 
                                     onclick="selectColor(this, 'red')"
                                     <?php echo $color == 'red' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #ef4444;"></span>
                                </div>
                                <div class="color-option" data-color="blue" 
                                     onclick="selectColor(this, 'blue')"
                                     <?php echo $color == 'blue' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #3b82f6;"></span>
                                </div>
                                <div class="color-option" data-color="green" 
                                     onclick="selectColor(this, 'green')"
                                     <?php echo $color == 'green' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #10b981;"></span>
                                </div>
                                <div class="color-option" data-color="yellow" 
                                     onclick="selectColor(this, 'yellow')"
                                     <?php echo $color == 'yellow' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #fbbf24;"></span>
                                </div>
                                <div class="color-option" data-color="pink" 
                                     onclick="selectColor(this, 'pink')"
                                     <?php echo $color == 'pink' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #ec4899;"></span>
                                </div>
                                <div class="color-option" data-color="gray" 
                                     onclick="selectColor(this, 'gray')"
                                     <?php echo $color == 'gray' ? 'class="color-option active"' : 'class="color-option"'; ?>>
                                    <span style="background: #6b7280;"></span>
                                </div>
                            </div>
                        </div>

                        <!-- فیلتر سایز -->
                        <div class="filter-group">
                            <h4 class="filter-title">
                                <i class="fas fa-ruler"></i> سایز
                            </h4>
                            <div class="size-filter">
                                <button class="size-option <?php echo $size == '' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, '')">همه</button>
                                <button class="size-option <?php echo $size == 'XS' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'XS')">XS</button>
                                <button class="size-option <?php echo $size == 'S' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'S')">S</button>
                                <button class="size-option <?php echo $size == 'M' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'M')">M</button>
                                <button class="size-option <?php echo $size == 'L' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'L')">L</button>
                                <button class="size-option <?php echo $size == 'XL' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'XL')">XL</button>
                                <button class="size-option <?php echo $size == 'XXL' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, 'XXL')">XXL</button>
                                <button class="size-option <?php echo $size == '38' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, '38')">38</button>
                                <button class="size-option <?php echo $size == '39' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, '39')">39</button>
                                <button class="size-option <?php echo $size == '40' ? 'active' : ''; ?>" 
                                        onclick="selectSize(this, '40')">40</button>
                            </div>
                        </div>

                    </div>
                </aside>

                <!-- Main Products Area -->
                <main class="products-main">
                    
                    <!-- Toolbar -->
                    <div class="products-toolbar">
                        <div class="toolbar-left">
                            <div class="results-count">
                                <i class="fas fa-box-open"></i>
                                نمایش <strong><?php echo $offset + 1; ?></strong> تا 
                                <strong><?php echo min($offset + $per_page, $total_products); ?></strong> 
                                از <strong><?php echo $total_products; ?></strong> محصول
                            </div>
                        </div>
                        <div class="toolbar-right">
                            <!-- Mobile Filter Button -->
                            <button class="mobile-filter-btn" onclick="toggleMobileFilters()">
                                <i class="fas fa-sliders-h"></i> فیلترها
                            </button>
                            
                            <!-- Sort Dropdown -->
                            <div class="sort-dropdown">
                                <button class="sort-btn" onclick="toggleSortMenu()">
                                    <i class="fas fa-sort-amount-down"></i>
                                    <span id="current-sort">
                                        <?php
                                        $sort_labels = [
                                            'newest' => 'جدیدترین',
                                            'expensive' => 'گران‌ترین',
                                            'cheap' => 'ارزان‌ترین',
                                            'popular' => 'محبوب‌ترین'
                                        ];
                                        echo $sort_labels[$sort];
                                        ?>
                                    </span>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="sort-menu" id="sort-menu">
                                    <button onclick="changeSort('newest')" 
                                            class="<?php echo $sort == 'newest' ? 'active' : ''; ?>">
                                        <i class="fas fa-clock"></i> جدیدترین
                                    </button>
                                    <button onclick="changeSort('expensive')" 
                                            class="<?php echo $sort == 'expensive' ? 'active' : ''; ?>">
                                        <i class="fas fa-arrow-up"></i> گران‌ترین
                                    </button>
                                    <button onclick="changeSort('cheap')" 
                                            class="<?php echo $sort == 'cheap' ? 'active' : ''; ?>">
                                        <i class="fas fa-arrow-down"></i> ارزان‌ترین
                                    </button>
                                    <button onclick="changeSort('popular')" 
                                            class="<?php echo $sort == 'popular' ? 'active' : ''; ?>">
                                        <i class="fas fa-fire"></i> محبوب‌ترین
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="products-grid" id="products-grid">
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <?php include 'product-card.php'; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-products">
                                <i class="fas fa-box-open"></i>
                                <h3>محصولی یافت نشد</h3>
                                <p>متأسفانه محصولی با فیلترهای انتخابی شما وجود ندارد.</p>
                                <button onclick="clearFilters()" class="btn-primary">
                                    <i class="fas fa-redo"></i> حذف فیلترها
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Loading Spinner -->
                    <div class="loading-spinner" id="loading-spinner" style="display: none;">
                        <div class="spinner"></div>
                        <p>در حال بارگذاری محصولات...</p>
                    </div>

                    <!-- End Message -->
                    <div class="end-message" id="end-message" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <p>همه محصولات نمایش داده شد</p>
                    </div>

                </main>

            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Global Variables
    let currentPage = <?php echo $page; ?>;
    let isLoading = false;
    let hasMore = <?php echo ($offset + $per_page) < $total_products ? 'true' : 'false'; ?>;
    let selectedColor = '<?php echo $color; ?>';
    let selectedSize = '<?php echo $size; ?>';

    // Infinite Scroll
    window.addEventListener('scroll', function() {
        if (isLoading || !hasMore) return;

        const scrollHeight = document.documentElement.scrollHeight;
        const scrollTop = document.documentElement.scrollTop;
        const clientHeight = document.documentElement.clientHeight;

        if (scrollTop + clientHeight >= scrollHeight - 500) {
            loadMoreProducts();
        }
    });

    function loadMoreProducts() {
        if (isLoading || !hasMore) return;

        isLoading = true;
        currentPage++;

        const spinner = document.getElementById('loading-spinner');
        spinner.style.display = 'block';

        const params = new URLSearchParams(window.location.search);
        params.set('page', currentPage);
        params.set('ajax', '1');

        fetch('products.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                const grid = document.getElementById('products-grid');
                
                data.products.forEach(product => {
                    const card = createProductCard(product);
                    grid.insertAdjacentHTML('beforeend', card);
                });

                hasMore = data.has_more;
                isLoading = false;
                spinner.style.display = 'none';

                if (!hasMore) {
                    document.getElementById('end-message').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                isLoading = false;
                spinner.style.display = 'none';
            });
    }

    function createProductCard(product) {
        const discount = product.discount_percent > 0 ? 
            `<span class="badge badge-discount">${product.discount_percent}%</span>` : '';
        
        const isNew = (Date.now() - new Date(product.created_at).getTime()) < 7 * 24 * 60 * 60 * 1000;
        const newBadge = isNew ? '<span class="badge badge-new">جدید</span>' : '';

        return `
            <div class="product-card" onclick="window.location='product-detail.php?id=${product.id}'">
                <div class="product-image">
                    <img src="assets/images/products/${product.main_image}" alt="${product.name}">
                    ${product.second_image ? 
                        `<img src="assets/images/products/${product.second_image}" alt="${product.name}" class="hover-image">` 
                        : ''}
                    <div class="product-badges">
                        ${newBadge}
                        ${discount}
                    </div>
                    <div class="product-actions">
                        <button class="action-btn" onclick="event.stopPropagation(); addToWishlist(${product.id})">
                            <i class="far fa-heart"></i>
                        </button>
                        <button class="action-btn" onclick="event.stopPropagation(); quickView(${product.id})">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-rating">
                        ${renderStars(product.avg_rating)}
                        <span>(${product.review_count})</span>
                    </div>
                    <h3 class="product-name">${product.name}</h3>
                    <div class="product-price">
                        ${product.discount_percent > 0 ? 
                            `<span class="old-price">${formatPrice(product.price)}</span>` 
                            : ''}
                        <span class="price">${formatPrice(product.final_price)} <small>تومان</small></span>
                    </div>
                </div>
            </div>
        `;
    }

    function renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star"></i>';
            } else if (i - 0.5 <= rating) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            } else {
                stars += '<i class="far fa-star"></i>';
            }
        }
        return stars;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('fa-IR').format(price);
    }

    // Filter Functions
    function applyFilters() {
        const params = new URLSearchParams();
        
        const category = document.querySelector('input[name="category"]:checked');
        if (category && category.value) {
            params.set('category', category.value);
        }

        const minPrice = document.getElementById('min-price').value;
        const maxPrice = document.getElementById('max-price').value;
        if (minPrice) params.set('min_price', minPrice);
        if (maxPrice) params.set('max_price', maxPrice);

        if (selectedColor) params.set('color', selectedColor);
        if (selectedSize) params.set('size', selectedSize);

        const sort = new URLSearchParams(window.location.search).get('sort') || 'newest';
        params.set('sort', sort);

        window.location.href = 'products.php?' + params.toString();
    }

    function applyPriceFilter() {
        applyFilters();
    }

    function selectColor(element, color) {
        document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        selectedColor = color;
        applyFilters();
    }

    function selectSize(element, size) {
        document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        selectedSize = size;
        applyFilters();
    }

    function clearFilters() {
        window.location.href = 'products.php';
    }

    // Sort Functions
    function toggleSortMenu() {
        const menu = document.getElementById('sort-menu');
        menu.classList.toggle('show');
    }

    function changeSort(sortType) {
        const params = new URLSearchParams(window.location.search);
        params.set('sort', sortType);
        window.location.href = 'products.php?' + params.toString();
    }

    // Mobile Filter Toggle
    function toggleMobileFilters() {
        document.querySelector('.products-sidebar').classList.toggle('show');
    }

    // Price Range Slider
    const minPriceInput = document.getElementById('min-price');
    const maxPriceInput = document.getElementById('max-price');
    const rangeMin = document.getElementById('price-range-min');
    const rangeMax = document.getElementById('price-range-max');

    rangeMin.addEventListener('input', function() {
        const value = parseInt(this.value);
        const maxValue = parseInt(rangeMax.value);
        
        if (value > maxValue - 100000) {
            this.value = maxValue - 100000;
        }
        
        minPriceInput.value = this.value;
    });

    rangeMax.addEventListener('input', function() {
        const value = parseInt(this.value);
        const minValue = parseInt(rangeMin.value);
        
        if (value < minValue + 100000) {
            this.value = minValue + 100000;
        }
        
        maxPriceInput.value = this.value;
    });

    minPriceInput.addEventListener('change', function() {
        rangeMin.value = this.value;
    });

    maxPriceInput.addEventListener('change', function() {
        rangeMax.value = this.value;
    });

    // Close sort menu when clicking outside
    document.addEventListener('click', function(event) {
        const sortDropdown = document.querySelector('.sort-dropdown');
        if (!sortDropdown.contains(event.target)) {
            document.getElementById('sort-menu').classList.remove('show');
        }
    });

    // Wishlist & Quick View (placeholder functions)
    function addToWishlist(productId) {
        // این تابع در فایل script.js اصلی پیاده‌سازی می‌شود
        console.log('Add to wishlist:', productId);
    }

    function quickView(productId) {
        // این تابع در فایل script.js اصلی پیاده‌سازی می‌شود
        console.log('Quick view:', productId);
    }
    </script>
</body>
</html>
