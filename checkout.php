<?php
// checkout.php - صفحه تسویه حساب
require_once 'config.php';

// بررسی لاگین
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// دریافت اطلاعات کاربر
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch();

// دریافت محصولات سبد خرید (فرضی - باید از دیتابیس واقعی بیاید)
$cart_items = [
    [
        'id' => 1,
        'name' => 'پیراهن مردانه کلاسیک',
        'image' => 'https://via.placeholder.com/80x100/1a1a1a/DAA520?text=Product',
        'price' => 850000,
        'quantity' => 2,
        'size' => 'L',
        'color' => 'آبی'
    ],
    [
        'id' => 2,
        'name' => 'شلوار جین مردانه',
        'image' => 'https://via.placeholder.com/80x100/1a1a1a/DAA520?text=Product',
        'price' => 1200000,
        'quantity' => 1,
        'size' => 'XL',
        'color' => 'مشکی'
    ]
];

// محاسبه جمع کل
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// هزینه ارسال بر اساس استان (پیش‌فرض)
$shipping_cost = 50000;

// مالیات (9%)
$tax = $subtotal * 0.09;

// جمع نهایی
$total = $subtotal + $shipping_cost + $tax;

$page_title = 'تسویه حساب';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | فروشگاه پوشاک لوکس</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0f0f0f;
            color: #fff;
            font-family: 'Vazir', 'Segoe UI', sans-serif;
            line-height: 1.6;
        }

        /* Header */
        .checkout-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border-bottom: 1px solid rgba(218, 165, 32, 0.2);
            padding: 2rem 0;
            text-align: center;
        }

        .checkout-header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #DAA520, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        .step.active {
            color: #DAA520;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #DAA520, #FFD700);
            color: #000;
        }

        /* Main Container */
        .checkout-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        /* Form Section */
        .checkout-form {
            background: rgba(30, 30, 30, 0.8);
            border-radius: 20px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #DAA520;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
            font-size: 0.9rem;
        }

        .form-group label .required {
            color: #ef4444;
            margin-right: 0.25rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.9rem 1.2rem;
            background: rgba(40, 40, 40, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #DAA520;
            background: rgba(50, 50, 50, 0.8);
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
        }

        .form-group input:disabled {
            background: rgba(30, 30, 30, 0.5);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Validation States */
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: #ef4444;
        }

        .form-group.success input,
        .form-group.success select {
            border-color: #22c55e;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: none;
        }

        .form-group.error .error-message {
            display: block;
        }

        .success-icon {
            position: absolute;
            left: 1rem;
            top: 42px;
            color: #22c55e;
            display: none;
        }

        .form-group.success .success-icon {
            display: block;
        }

        /* Order Summary Sidebar */
        .order-summary {
            position: sticky;
            top: 2rem;
            background: rgba(30, 30, 30, 0.8);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: fit-content;
        }

        .summary-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cart-items {
            margin-bottom: 1.5rem;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .item-specs {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 0.5rem;
        }

        .item-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-quantity {
            color: #888;
            font-size: 0.9rem;
        }

        .item-total {
            color: #DAA520;
            font-weight: bold;
        }

        /* Price Breakdown */
        .price-breakdown {
            padding: 1.5rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #ccc;
        }

        .price-row.total {
            font-size: 1.3rem;
            font-weight: bold;
            color: #fff;
            padding-top: 1rem;
            border-top: 2px solid rgba(218, 165, 32, 0.3);
        }

        .price-row.total .value {
            color: #DAA520;
        }

        /* Submit Button */
        .submit-order {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #DAA520, #B8860B);
            color: #000;
            font-weight: bold;
            font-size: 1.1rem;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .submit-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(218, 165, 32, 0.4);
        }

        .submit-order:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Security Badge */
        .security-badge {
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #888;
            font-size: 0.85rem;
        }

        .security-badge i {
            color: #22c55e;
            margin-left: 0.3rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .checkout-container {
                padding: 0 1rem;
            }

            .checkout-form {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .checkout-steps {
                flex-direction: column;
                gap: 0.5rem;
            }
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(218, 165, 32, 0.2);
            border-top-color: #DAA520;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="checkout-header">
        <h1><i class="fas fa-shopping-cart"></i> تسویه حساب</h1>
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <span>اطلاعات</span>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <span>پرداخت</span>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <span>تکمیل</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="checkout-container">
        <!-- Form Section -->
        <div class="checkout-form">
            <form id="checkoutForm" method="POST" action="process_order.php">
                <!-- بخش 1: اطلاعات شخصی -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        اطلاعات شخصی و هویتی
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">
                                <span class="required">*</span>
                                نام
                            </label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                required
                            >
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">لطفاً نام خود را وارد کنید</div>
                        </div>
                        <div class="form-group">
                            <label for="last_name">
                                <span class="required">*</span>
                                نام خانوادگی
                            </label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                required
                            >
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">لطفاً نام خانوادگی خود را وارد کنید</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="national_code">
                                کد ملی (برای احراز هویت درگاه بانکی)
                            </label>
                            <input 
                                type="text" 
                                id="national_code" 
                                name="national_code" 
                                maxlength="10"
                                pattern="[0-9]{10}"
                            >
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">کد ملی باید 10 رقم باشد</div>
                        </div>
                        <div class="form-group">
                            <label for="mobile">
                                <span class="required">*</span>
                                شماره موبایل (تایید شده)
                            </label>
                            <input 
                                type="tel" 
                                id="mobile" 
                                name="mobile" 
                                value="<?php echo htmlspecialchars($user['mobile'] ?? '09123456789'); ?>"
                                disabled
                                title="شماره موبایل با OTP تایید شده و قابل تغییر نیست"
                            >
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="email">
                                ایمیل (برای ارسال خودکار فاکتور)
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                            >
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">لطفاً یک ایمیل معتبر وارد کنید</div>
                        </div>
                    </div>
                </div>

                <!-- بخش 2: سیستم هوشمند انتخاب مکان -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        آدرس تحویل
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="province">
                                <span class="required">*</span>
                                استان
                            </label>
                            <select id="province" name="province" required>
                                <option value="">انتخاب استان...</option>
                                <option value="tehran">تهران</option>
                                <option value="isfahan">اصفهان</option>
                                <option value="shiraz">فارس</option>
                                <option value="tabriz">آذربایجان شرقی</option>
                                <option value="mashhad">خراسان رضوی</option>
                                <option value="ahvaz">خوزستان</option>
                                <option value="qom">قم</option>
                                <option value="karaj">البرز</option>
                            </select>
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">لطفاً استان را انتخاب کنید</div>
                        </div>
                        <div class="form-group">
                            <label for="city">
                                <span class="required">*</span>
                                شهر (وابسته به استان)
                            </label>
                            <select id="city" name="city" disabled required>
                                <option value="">ابتدا استان را انتخاب کنید...</option>
                            </select>
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">لطفاً شهر را انتخاب کنید</div>
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="address">
                                <span class="required">*</span>
                                آدرس دقیق پستی (خیابان، کوچه، پلاک، واحد)
                            </label>
                            <textarea 
                                id="address" 
                                name="address" 
                                placeholder="مثال: خیابان ولیعصر، کوچه شهید احمدی، پلاک 15، واحد 3"
                                required
                            ></textarea>
                            <div class="error-message">لطفاً آدرس دقیق را وارد کنید</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="postal_code">
                                <span class="required">*</span>
                                کد پستی 10 رقمی (بدون خط تیره)
                            </label>
                            <input 
                                type="text" 
                                id="postal_code" 
                                name="postal_code" 
                                maxlength="10"
                                pattern="[0-9]{10}"
                                placeholder="1234567890"
                                required
                            >
                            <i class="fas fa-check success-icon"></i>
                            <div class="error-message">کد پستی باید دقیقاً 10 رقم باشد</div>
                        </div>
                        <div class="form-group">
                            <label for="phone">
                                تلفن ثابت (اختیاری)
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                maxlength="11"
                                placeholder="02112345678"
                            >
                        </div>
                    </div>
                </div>

                <!-- بخش 3: یادداشت سفارش -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-sticky-note"></i>
                        یادداشت سفارش (اختیاری)
                    </h2>
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="order_notes">
                                توضیحات تکمیلی
                            </label>
                            <textarea 
                                id="order_notes" 
                                name="order_notes" 
                                placeholder="مثال: لطفاً قبل از ارسال تماس بگیرید، زنگ واحد 3 خراب است، بسته به نگهبانی تحویل داده شود"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="submit_order" value="1">
            </form>
        </div>

        <!-- Order Summary Sidebar -->
        <aside class="order-summary">
            <h3 class="summary-title">خلاصه سفارش</h3>
            
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                    <div class="item-details">
                        <div class="item-name"><?php echo $item['name']; ?></div>
                        <div class="item-specs">
                            سایز: <?php echo $item['size']; ?> | رنگ: <?php echo $item['color']; ?>
                        </div>
                        <div class="item-price">
                            <span class="item-quantity">تعداد: <?php echo $item['quantity']; ?></span>
                            <span class="item-total"><?php echo number_format($item['price'] * $item['quantity']); ?> تومان</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="price-breakdown">
                <div class="price-row">
                    <span>جمع کل محصولات:</span>
                    <span><?php echo number_format($subtotal); ?> تومان</span>
                </div>
                <div class="price-row">
                    <span>هزینه ارسال:</span>
                    <span id="shipping-cost"><?php echo number_format($shipping_cost); ?> تومان</span>
                </div>
                <div class="price-row">
                    <span>مالیات (9%):</span>
                    <span><?php echo number_format($tax); ?> تومان</span>
                </div>
                <div class="price-row total">
                    <span>مبلغ قابل پرداخت:</span>
                    <span class="value" id="final-total"><?php echo number_format($total); ?> تومان</span>
                </div>
            </div>

            <button type="submit" form="checkoutForm" class="submit-order">
                <i class="fas fa-lock"></i>
                پرداخت امن
            </button>

            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                پرداخت از طریق درگاه بانکی امن
            </div>
        </aside>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script>
        // دیتای شهرها بر اساس استان
        const citiesData = {
            'tehran': ['تهران', 'ری', 'شمیرانات', 'ورامین', 'پاکدشت', 'دماوند'],
            'isfahan': ['اصفهان', 'کاشان', 'نجف‌آباد', 'خمینی‌شهر', 'شاهین‌شهر', 'گلپایگان'],
            'shiraz': ['شیراز', 'مرودشت', 'کازرون', 'جهرم', 'لار', 'فسا'],
            'tabriz': ['تبریز', 'مراغه', 'مرند', 'میانه', 'سراب', 'بناب'],
            'mashhad': ['مشهد', 'نیشابور', 'سبزوار', 'تربت حیدریه', 'قوچان', 'کاشمر'],
            'ahvaz': ['اهواز', 'دزفول', 'آبادان', 'خرمشهر', 'اندیمشک', 'بهبهان'],
            'qom': ['قم', 'سلفچگان', 'جعفریه', 'کهک'],
            'karaj': ['کرج', 'نظرآباد', 'اشتهارد', 'هشتگرد', 'طالقان']
        };

        // هزینه ارسال بر اساس استان
        const shippingCosts = {
            'tehran': 50000,
            'isfahan': 60000,
            'shiraz': 70000,
            'tabriz': 75000,
            'mashhad': 80000,
            'ahvaz': 85000,
            'qom': 45000,
            'karaj': 40000
        };

        // انتخاب استان و لود شهرها
        document.getElementById('province').addEventListener('change', function() {
            const province = this.value;
            const citySelect = document.getElementById('city');
            
            citySelect.innerHTML = '<option value="">انتخاب شهر...</option>';
            
            if (province && citiesData[province]) {
                citySelect.disabled = false;
                citiesData[province].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
                
                // آپدیت هزینه ارسال
                updateShippingCost(province);
            } else {
                citySelect.disabled = true;
            }
        });

        // آپدیت هزینه ارسال
        function updateShippingCost(province) {
            const cost = shippingCosts[province] || 50000;
            const subtotal = <?php echo $subtotal; ?>;
            const tax = subtotal * 0.09;
            const total = subtotal + cost + tax;
            
            document.getElementById('shipping-cost').textContent = cost.toLocaleString('fa-IR') + ' تومان';
            document.getElementById('final-total').textContent = total.toLocaleString('fa-IR') + ' تومان';
        }

        // اعتبارسنجی فرم
        const form = document.getElementById('checkoutForm');
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(input => {
            // اعتبارسنجی آنی
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.parentElement.classList.contains('error')) {
                    validateField(this);
                }
            });
        });

        function validateField(field) {
            const group = field.closest('.form-group');
            group.classList.remove('error', 'success');

            if (field.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !emailRegex.test(field.value)) {
                    group.classList.add('error');
                    return false;
                }
            }

            if (field.id === 'national_code' && field.value) {
                if (field.value.length !== 10 || !/^\d{10}$/.test(field.value)) {
                    group.classList.add('error');
                    return false;
                }
            }

            if (field.id === 'postal_code') {
                if (field.value.length !== 10 || !/^\d{10}$/.test(field.value)) {
                    group.classList.add('error');
                    return false;
                }
            }

            if (field.hasAttribute('required') && !field.value) {
                group.classList.add('error');
                return false;
            }

            if (field.value) {
                group.classList.add('success');
            }
            return true;
        }

        // فیلتر فقط عدد برای کد ملی و کد پستی
        ['national_code', 'postal_code', 'phone'].forEach(id => {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                });
            }
        });

        // ارسال فرم
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                alert('لطفاً تمام فیلدهای الزامی را به درستی پر کنید');
                return;
            }

            // نمایش لودینگ
            document.getElementById('loadingOverlay').classList.add('active');

            // ارسال به سرور (شبیه‌سازی)
            setTimeout(() => {
                // this.submit(); // واقعی
                alert('سفارش شما با موفقیت ثبت شد! در حال انتقال به درگاه پرداخت...');
                // window.location.href = 'payment.php';
            }, 2000);
        });
    </script>
</body>
</html>
