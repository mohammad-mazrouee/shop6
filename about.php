<?php
// about.php - صفحه درباره ما

require_once 'config.php'; // اگر نیاز به دیتابیس یا session باشد
session_start();

$store_name = "لوکس پوشاک";
$about_text = "لوکس پوشاک با هدف ارائه اصیل‌ترین و باکیفیت‌ترین پوشاک و اکسسوری‌های مردانه و زنانه راه‌اندازی شده است. ما باور داریم که لباس فقط یک پوشش نیست؛ بلکه بیان هویت، سلیقه و اعتماد به نفس شماست.

از سال ۱۴۰۳ فعالیت خود را آغاز کردیم و در این مدت کوتاه تلاش کردیم تا با انتخاب برندهای معتبر جهانی و ایرانی، تجربه‌ای متفاوت از خرید آنلاین را برای شما فراهم کنیم. کیفیت، اصالت، سرعت ارسال و رضایت مشتری، خط قرمز ماست.

تیم ما متشکل از طراحان مد، متخصصان دیجیتال و کارشناسان انبارداری است که همه با یک هدف مشترک کار می‌کنند: اینکه شما با هر خرید، احساس خاص بودن کنید.";

$mission = "ارائه پوشاک و اکسسوری با کیفیت برتر، قیمت منصفانه و تجربه خرید لذت‌بخش و بدون دغدغه.";
$vision  = "تبدیل شدن به مرجع اصلی خرید آنلاین پوشاک لوکس در ایران تا سال ۱۴۰۸.";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>درباره ما | <?= htmlspecialchars($store_name) ?></title>
    <!-- <link rel="stylesheet" href="css/about.css"> بعداً اضافه می‌شود -->
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="about-page">

    <section class="hero-about">
        <div class="hero-content">
            <h1>درباره لوکس پوشاک</h1>
            <p>جایی که کیفیت و اصالت، داستان هر خرید شماست</p>
        </div>
    </section>

    <section class="about-story">
        <div class="container">
            <h2>داستان ما</h2>
            <div class="story-text">
                <?= nl2br(htmlspecialchars($about_text)) ?>
            </div>
        </div>
    </section>

    <section class="mission-vision">
        <div class="container grid-2">
            <div class="card">
                <h3>مأموریت ما</h3>
                <p><?= htmlspecialchars($mission) ?></p>
            </div>
            <div class="card">
                <h3>چشم‌انداز ما</h3>
                <p><?= htmlspecialchars($vision) ?></p>
            </div>
        </div>
    </section>

    <section class="team-values">
        <div class="container">
            <h2>ارزش‌های ما</h2>
            <div class="values-grid">
                <div class="value-item">
                    <span class="icon">★</span>
                    <h4>کیفیت بی‌چون‌وچرا</h4>
                    <p>هر محصول قبل از عرضه، چندین مرحله کنترل کیفیت را طی می‌کند.</p>
                </div>
                <div class="value-item">
                    <span class="icon">🛡️</span>
                    <h4>اصالت تضمین‌شده</h4>
                    <p>همکاری مستقیم با برندهای معتبر داخلی و جهانی.</p>
                </div>
                <div class="value-item">
                    <span class="icon">🚚</span>
                    <h4>ارسال سریع و مطمئن</h4>
                    <p>ارسال رایگان بالای ۳ میلیون تومان + پیگیری آنلاین.</p>
                </div>
                <div class="value-item">
                    <span class="icon">🤝</span>
                    <h4>رضایت شما اولویت ماست</h4>
                    <p>۷ روز ضمانت بازگشت بی‌قیدوشرط.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-info">
        <div class="container">
            <h2>با ما در ارتباط باشید</h2>
            <div class="contact-grid">
                <div class="contact-card">
                    <h4>دفتر مرکزی</h4>
                    <p>تهران – خیابان ولیعصر، بالاتر از پارک ساعی، پلاک ۱۲۳۴</p>
                </div>
                <div class="contact-card">
                    <h4>تماس با ما</h4>
                    <p>تلفن: ۰۲۱-۱۲۳۴۵۶۷۸<br>واتس‌اپ: ۰۹۱۲-۳۴۵۶۷۸۹</p>
                </div>
                <div class="contact-card">
                    <h4>ساعات کاری</h4>
                    <p>شنبه تا پنج‌شنبه: ۹ صبح تا ۱۰ شب<br>جمعه: ۱۰ صبح تا ۸ شب</p>
                </div>
            </div>

            <div class="social-section">
                <h3>ما را دنبال کنید</h3>
                <div class="social-icons">
                    <a href="#" class="social-link instagram">اینستاگرام</a>
                    <a href="#" class="social-link telegram">تلگرام</a>
                    <a href="#" class="social-link eita">ایتا</a>
                    <a href="#" class="social-link youtube">یوتیوب</a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

</body>
</html> 
