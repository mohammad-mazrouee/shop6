/**
 * Header JavaScript - فروشگاه پوشاک
 * نسخه نهایی - قیمت پروژه: 100 میلیون تومان
 * طراح: محمد مزروعی
 * تاریخ: 1404/11/19
 */

// Object برای ذخیره وضعیت‌ها
const HeaderState = {
    announcementClosed: localStorage.getItem('announcementClosed') === 'true',
    searchOpen: false,
    mobileMenuOpen: false,
    megaMenuOpen: false,
    userDropdownOpen: false,
    miniCartOpen: false
};

// Object برای مدیریت DOM elements
const HeaderElements = {
    announcementBar: null,
    closeAnnouncement: null,
    mainHeader: null,
    searchToggle: null,
    searchModal: null,
    closeSearch: null,
    searchInput: null,
    searchSubmit: null,
    searchSuggestions: null,
    mobileMenuToggle: null,
    mobileMenu: null,
    mobileClose: null,
    mobileMenuOverlay: null,
    cartBtn: null,
    miniCart: null,
    wishlistBtn: null,
    userDropdown: null,
    megaMenuParents: null
};

// Object برای مدیریت انیمیشن‌ها
const HeaderAnimations = {
    announcementInterval: null,
    cartTimeout: null,
    dropdownTimeout: null,
    searchTimeout: null
};

// تابع اصلی که پس از لود DOM اجرا می‌شود
document.addEventListener('DOMContentLoaded', function() {
    initializeHeader();
    setupEventListeners();
    startAnimations();
});

/**
 * مقداردهی اولیه المنت‌ها
 */
function initializeHeader() {
    // جمع‌آوری همه المنت‌های مهم
    HeaderElements.announcementBar = document.getElementById('announcementBar');
    HeaderElements.closeAnnouncement = document.getElementById('closeAnnouncement');
    HeaderElements.mainHeader = document.getElementById('mainHeader');
    HeaderElements.searchToggle = document.getElementById('searchToggle');
    HeaderElements.searchModal = document.getElementById('searchModal');
    HeaderElements.closeSearch = document.getElementById('closeSearch');
    HeaderElements.searchInput = document.getElementById('searchInput');
    HeaderElements.searchSubmit = document.getElementById('searchSubmit');
    HeaderElements.searchSuggestions = document.getElementById('searchSuggestions');
    HeaderElements.mobileMenuToggle = document.getElementById('mobileMenuToggle');
    HeaderElements.mobileMenu = document.getElementById('mobileMenu');
    HeaderElements.mobileClose = document.getElementById('mobileClose');
    HeaderElements.mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    HeaderElements.cartBtn = document.querySelector('.cart-btn');
    HeaderElements.miniCart = document.querySelector('.mini-cart');
    HeaderElements.wishlistBtn = document.querySelector('.wishlist-btn');
    HeaderElements.userDropdown = document.querySelector('.user-dropdown');
    HeaderElements.megaMenuParents = document.querySelectorAll('.mega-menu-parent');
    
    // تنظیم وضعیت اولیه
    if (HeaderState.announcementClosed && HeaderElements.announcementBar) {
        HeaderElements.announcementBar.style.display = 'none';
        updateBodyPadding();
    }
    
    // تنظیم aria-expanded برای دکمه‌ها
    updateAriaAttributes();
}

/**
 * تنظیم event listeners
 */
function setupEventListeners() {
    // اسکرول برای هدر چسبنده
    window.addEventListener('scroll', handleScroll);
    
    // تغییر سایز ویندو برای رسپانسیو
    window.addEventListener('resize', handleResize);
    
    // کلیدهای کیبورد
    document.addEventListener('keydown', handleKeyboard);
    
    // نوار اعلانات
    if (HeaderElements.closeAnnouncement) {
        HeaderElements.closeAnnouncement.addEventListener('click', closeAnnouncement);
    }
    
    // جستجو
    if (HeaderElements.searchToggle) {
        HeaderElements.searchToggle.addEventListener('click', toggleSearch);
    }
    
    if (HeaderElements.closeSearch) {
        HeaderElements.closeSearch.addEventListener('click', closeSearch);
    }
    
    if (HeaderElements.searchInput) {
        HeaderElements.searchInput.addEventListener('input', handleSearchInput);
        HeaderElements.searchInput.addEventListener('keypress', handleSearchKeypress);
    }
    
    if (HeaderElements.searchSubmit) {
        HeaderElements.searchSubmit.addEventListener('click', performSearch);
    }
    
    // منوی موبایل
    if (HeaderElements.mobileMenuToggle) {
        HeaderElements.mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }
    
    if (HeaderElements.mobileClose) {
        HeaderElements.mobileClose.addEventListener('click', closeMobileMenu);
    }
    
    if (HeaderElements.mobileMenuOverlay) {
        HeaderElements.mobileMenuOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // زیرمنوهای موبایل
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                toggleSubmenu(this);
            }
        });
    });
    
    // مگامنوها
    HeaderElements.megaMenuParents.forEach(parent => {
        const megaMenu = parent.querySelector('.mega-menu');
        
        parent.addEventListener('mouseenter', () => openMegaMenu(parent));
        parent.addEventListener('mouseleave', () => closeMegaMenu(parent));
        
        if (megaMenu) {
            megaMenu.addEventListener('mouseenter', () => clearTimeout(HeaderAnimations.dropdownTimeout));
            megaMenu.addEventListener('mouseleave', () => closeMegaMenu(parent));
        }
        
        // برای تاچ دیوایس‌ها
        parent.addEventListener('touchstart', function(e) {
            if (window.innerWidth > 992) {
                e.preventDefault();
                if (HeaderState.megaMenuOpen) {
                    closeMegaMenu(parent);
                } else {
                    openMegaMenu(parent);
                }
            }
        });
    });
    
    // منوی کاربر
    if (HeaderElements.userDropdown) {
        const userMenu = HeaderElements.userDropdown.querySelector('.user-dropdown-menu');
        
        HeaderElements.userDropdown.addEventListener('mouseenter', () => openUserDropdown());
        HeaderElements.userDropdown.addEventListener('mouseleave', () => closeUserDropdown());
        
        if (userMenu) {
            userMenu.addEventListener('mouseenter', () => clearTimeout(HeaderAnimations.dropdownTimeout));
            userMenu.addEventListener('mouseleave', () => closeUserDropdown());
        }
        
        // برای تاچ
        HeaderElements.userDropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                toggleUserDropdown();
            }
        });
    }
    
    // سبد خرید کوچک
    if (HeaderElements.cartBtn && HeaderElements.miniCart) {
        HeaderElements.cartBtn.addEventListener('mouseenter', () => openMiniCart());
        HeaderElements.cartBtn.addEventListener('mouseleave', () => closeMiniCart());
        
        HeaderElements.miniCart.addEventListener('mouseenter', () => clearTimeout(HeaderAnimations.cartTimeout));
        HeaderElements.miniCart.addEventListener('mouseleave', () => closeMiniCart());
        
        // حذف آیتم از سبد
        HeaderElements.miniCart.addEventListener('click', function(e) {
            if (e.target.closest('.item-remove')) {
                e.preventDefault();
                const item = e.target.closest('.cart-item');
                removeCartItem(item);
            }
        });
        
        // برای تاچ
        HeaderElements.cartBtn.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                toggleMiniCart();
            }
        });
    }
    
    // علاقه‌مندی‌ها
    if (HeaderElements.wishlistBtn) {
        HeaderElements.wishlistBtn.addEventListener('click', function(e) {
            if (!window.siteConfig.isLoggedIn) {
                e.preventDefault();
                showLoginPrompt();
            }
        });
    }
    
    // کلیک خارج از مودال‌ها
    document.addEventListener('click', function(e) {
        // بستن مگامنو با کلیک خارج
        if (HeaderState.megaMenuOpen && 
            !e.target.closest('.mega-menu-parent') && 
            !e.target.closest('.mega-menu')) {
            closeAllMegaMenus();
        }
        
        // بستن منوی کاربر با کلیک خارج
        if (HeaderState.userDropdownOpen && 
            !e.target.closest('.user-dropdown')) {
            closeUserDropdown();
        }
    });
}

/**
 * شروع انیمیشن‌ها
 */
function startAnimations() {
    // اسلاید اعلانات
    startAnnouncementSlider();
    
    // انیمیشن پالس برای علاقه‌مندی‌ها
    startWishlistPulse();
    
    // لود آرام تصاویر
    lazyLoadImages();
}

/**
 * مدیریت اسکرول - هدر چسبنده
 */
function handleScroll() {
    const scrollY = window.scrollY;
    const header = HeaderElements.mainHeader;
    
    if (!header) return;
    
    if (scrollY > 100) {
        header.classList.add('sticky');
        HeaderState.isSticky = true;
    } else {
        header.classList.remove('sticky');
        HeaderState.isSticky = false;
    }
    
    updateBodyPadding();
}

/**
 * بروزرسانی padding بدنه بر اساس وضعیت هدر
 */
function updateBodyPadding() {
    const header = HeaderElements.mainHeader;
    if (!header) return;
    
    let padding = 0;
    
    if (HeaderState.isSticky) {
        padding = header.classList.contains('sticky') ? 
            parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-sticky-height')) : 
            parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height'));
    } else {
        padding = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height'));
    }
    
    document.body.style.paddingTop = padding + 'px';
}

/**
 * مدیریت تغییر سایز ویندو
 */
function handleResize() {
    updateBodyPadding();
    
    // بستن منوی موبایل در دسکتاپ
    if (window.innerWidth > 992 && HeaderState.mobileMenuOpen) {
        closeMobileMenu();
    }
    
    // بستن مگامنو در موبایل
    if (window.innerWidth <= 992 && HeaderState.megaMenuOpen) {
        closeAllMegaMenus();
    }
    
    // بروزرسانی aria attributes
    updateAriaAttributes();
}

/**
 * مدیریت کیبورد
 */
function handleKeyboard(e) {
    // بستن مودال‌ها با ESC
    if (e.key === 'Escape') {
        if (HeaderState.searchOpen) closeSearch();
        if (HeaderState.mobileMenuOpen) closeMobileMenu();
        if (HeaderState.megaMenuOpen) closeAllMegaMenus();
        if (HeaderState.userDropdownOpen) closeUserDropdown();
        if (HeaderState.miniCartOpen) closeMiniCart();
    }
    
    // ناوبری در مودال جستجو
    if (HeaderState.searchOpen && e.key === 'Tab') {
        const focusable = HeaderElements.searchModal.querySelectorAll(
            'button, input, a, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusable.length === 0) return;
        
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }
}

/**
 * شروع اسلاید اعلانات
 */
function startAnnouncementSlider() {
    const slides = document.querySelectorAll('.announcement-slider .slide');
    if (slides.length <= 1) return;
    
    let current = 0;
    
    HeaderAnimations.announcementInterval = setInterval(() => {
        // مخفی کردن همه
        slides.forEach((slide, index) => {
            slide.style.opacity = '0';
            slide.style.transform = 'translateY(-10px)';
            slide.classList.remove('active');
            slide.setAttribute('aria-hidden', 'true');
            
            // مخفی کردن از خواننده صفحه
            slide.querySelectorAll('*').forEach(el => {
                el.setAttribute('aria-hidden', 'true');
            });
        });
        
        // نمایش اسلاید بعدی
        current = (current + 1) % slides.length;
        slides[current].style.opacity = '1';
        slides[current].style.transform = 'translateY(0)';
        slides[current].classList.add('active');
        slides[current].setAttribute('aria-hidden', 'false');
        
        // نمایش برای خواننده صفحه
        slides[current].querySelectorAll('*').forEach(el => {
            el.setAttribute('aria-hidden', 'false');
        });
    }, 5000);
}

/**
 * بستن نوار اعلانات
 */
function closeAnnouncement() {
    if (!HeaderElements.announcementBar) return;
    
    // انیمیشن بستن
    HeaderElements.announcementBar.style.transition = 'all 0.4s ease';
    HeaderElements.announcementBar.style.transform = 'translateY(-100%)';
    HeaderElements.announcementBar.style.opacity = '0';
    HeaderElements.announcementBar.style.height = '0';
    HeaderElements.announcementBar.style.padding = '0';
    HeaderElements.announcementBar.style.marginBottom = '0';
    
    // توقف اسلاید
    clearInterval(HeaderAnimations.announcementInterval);
    
    // حذف از DOM بعد از انیمیشن
    setTimeout(() => {
        HeaderElements.announcementBar.style.display = 'none';
        HeaderState.announcementClosed = true;
        
        // ذخیره در localStorage
        localStorage.setItem('announcementClosed', 'true');
        
        // بروزرسانی padding
        updateBodyPadding();
        
        // فراخوانی API برای ذخیره در سشن
        fetch('/api/announcement/close', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).catch(console.error);
    }, 400);
}

/**
 * مدیریت جستجو
 */
function toggleSearch() {
    if (HeaderState.searchOpen) {
        closeSearch();
    } else {
        openSearch();
    }
}

function openSearch() {
    if (!HeaderElements.searchModal) return;
    
    HeaderElements.searchModal.classList.add('active');
    HeaderElements.searchModal.setAttribute('aria-hidden', 'false');
    HeaderState.searchOpen = true;
    document.body.style.overflow = 'hidden';
    
    if (HeaderElements.searchToggle) {
        HeaderElements.searchToggle.setAttribute('aria-expanded', 'true');
    }
    
    // فوکوس روی اینپوت
    setTimeout(() => {
        if (HeaderElements.searchInput) {
            HeaderElements.searchInput.focus();
        }
    }, 300);
}

function closeSearch() {
    if (!HeaderElements.searchModal) return;
    
    HeaderElements.searchModal.classList.remove('active');
    HeaderElements.searchModal.setAttribute('aria-hidden', 'true');
    HeaderState.searchOpen = false;
    document.body.style.overflow = '';
    
    if (HeaderElements.searchToggle) {
        HeaderElements.searchToggle.setAttribute('aria-expanded', 'false');
    }
    
    // پاک کردن اینپوت
    if (HeaderElements.searchInput) {
        HeaderElements.searchInput.value = '';
    }
    
    // پاک کردن پیشنهادات
    if (HeaderElements.searchSuggestions) {
        HeaderElements.searchSuggestions.innerHTML = '';
    }
}

function handleSearchInput() {
    const query = HeaderElements.searchInput.value.trim();
    
    // پاک کردن تایم‌اوت قبلی
    clearTimeout(HeaderAnimations.searchTimeout);
    
    if (query.length > 1) {
        // تاخیر برای جلوگیری از درخواست‌های زیاد
        HeaderAnimations.searchTimeout = setTimeout(() => {
            fetchSearchSuggestions(query);
        }, 500);
    } else if (query.length === 0) {
        showDefaultSuggestions();
    }
}

function handleSearchKeypress(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        performSearch();
    }
}

async function fetchSearchSuggestions(query) {
    try {
        // نمایش لودینگ
        if (HeaderElements.searchSuggestions) {
            HeaderElements.searchSuggestions.innerHTML = `
                <div class="search-loading">
                    <div class="spinner-small"></div>
                    <span>در حال جستجو...</span>
                </div>
            `;
        }
        
        // درخواست به API
        const response = await fetch(`/api/search/suggest?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && HeaderElements.searchSuggestions) {
            displaySearchSuggestions(data.suggestions);
        }
    } catch (error) {
        console.error('خطا در دریافت پیشنهادات:', error);
        showDefaultSuggestions();
    }
}

function displaySearchSuggestions(suggestions) {
    if (!HeaderElements.searchSuggestions || !suggestions || suggestions.length === 0) {
        showDefaultSuggestions();
        return;
    }
    
    let html = `
        <div class="suggestions-title">
            <i class="fas fa-bolt" aria-hidden="true"></i>
            پیشنهادهای جستجو
        </div>
        <div class="suggestion-tags">
    `;
    
    suggestions.slice(0, 8).forEach(suggestion => {
        html += `<a href="/search.php?q=${encodeURIComponent(suggestion)}" class="tag">${suggestion}</a>`;
    });
    
    html += '</div>';
    HeaderElements.searchSuggestions.innerHTML = html;
}

function showDefaultSuggestions() {
    if (!HeaderElements.searchSuggestions) return;
    
    const defaults = [
        'کفش نایک', 'پیراهن مردانه', 'کیف دستی', 
        'کوله‌پشتی', 'شلوار جین', 'کفش ورزشی',
        'ژاکت مردانه', 'بلوز زنانه'
    ];
    
    let html = `
        <div class="suggestions-title">
            <i class="fas fa-fire" aria-hidden="true"></i>
            جستجوهای پرطرفدار
        </div>
        <div class="suggestion-tags">
    `;
    
    defaults.forEach(item => {
        html += `<a href="/search.php?q=${encodeURIComponent(item)}" class="tag">${item}</a>`;
    });
    
    html += '</div>';
    HeaderElements.searchSuggestions.innerHTML = html;
}

function performSearch() {
    const query = HeaderElements.searchInput?.value.trim();
    
    if (!query) {
        if (HeaderElements.searchInput) {
            HeaderElements.searchInput.focus();
        }
        return;
    }
    
    // هدایت به صفحه نتایج
    window.location.href = `/search.php?q=${encodeURIComponent(query)}`;
}

/**
 * مدیریت منوی موبایل
 */
function toggleMobileMenu() {
    if (HeaderState.mobileMenuOpen) {
        closeMobileMenu();
    } else {
        openMobileMenu();
    }
}

function openMobileMenu() {
    if (!HeaderElements.mobileMenu || !HeaderElements.mobileMenuOverlay) return;
    
    HeaderElements.mobileMenu.classList.add('active');
    HeaderElements.mobileMenuOverlay.classList.add('active');
    HeaderElements.mobileMenu.setAttribute('aria-hidden', 'false');
    HeaderState.mobileMenuOpen = true;
    document.body.style.overflow = 'hidden';
    
    if (HeaderElements.mobileMenuToggle) {
        HeaderElements.mobileMenuToggle.classList.add('active');
        HeaderElements.mobileMenuToggle.setAttribute('aria-expanded', 'true');
    }
    
    // فوکوس روی اولین لینک
    setTimeout(() => {
        const firstLink = HeaderElements.mobileMenu.querySelector('a');
        if (firstLink) firstLink.focus();
    }, 300);
}

function closeMobileMenu() {
    if (!HeaderElements.mobileMenu || !HeaderElements.mobileMenuOverlay) return;
    
    HeaderElements.mobileMenu.classList.remove('active');
    HeaderElements.mobileMenuOverlay.classList.remove('active');
    HeaderElements.mobileMenu.setAttribute('aria-hidden', 'true');
    HeaderState.mobileMenuOpen = false;
    document.body.style.overflow = '';
    
    if (HeaderElements.mobileMenuToggle) {
        HeaderElements.mobileMenuToggle.classList.remove('active');
        HeaderElements.mobileMenuToggle.setAttribute('aria-expanded', 'false');
    }
}

function toggleSubmenu(toggle) {
    const parent = toggle.parentElement;
    parent.classList.toggle('active');
    
    const isOpen = parent.classList.contains('active');
    toggle.setAttribute('aria-expanded', isOpen.toString());
}

/**
 * مدیریت مگامنوها
 */
function openMegaMenu(parent) {
    if (window.innerWidth <= 992) return;
    
    const megaMenu = parent.querySelector('.mega-menu');
    if (!megaMenu) return;
    
    // بستن بقیه مگامنوها
    closeAllMegaMenus(parent);
    
    // باز کردن این مگامنو
    megaMenu.style.opacity = '1';
    megaMenu.style.visibility = 'visible';
    megaMenu.style.transform = 'translateY(0)';
    megaMenu.setAttribute('aria-hidden', 'false');
    HeaderState.megaMenuOpen = true;
    
    // بروزرسانی وضعیت والد
    parent.classList.add('open');
}

function closeMegaMenu(parent) {
    if (window.innerWidth <= 992) return;
    
    const megaMenu = parent.querySelector('.mega-menu');
    if (!megaMenu) return;
    
    HeaderAnimations.dropdownTimeout = setTimeout(() => {
        megaMenu.style.opacity = '0';
        megaMenu.style.visibility = 'hidden';
        megaMenu.style.transform = 'translateY(15px)';
        megaMenu.setAttribute('aria-hidden', 'true');
        HeaderState.megaMenuOpen = false;
        
        parent.classList.remove('open');
    }, 300);
}

function closeAllMegaMenus(except = null) {
    HeaderElements.megaMenuParents.forEach(parent => {
        if (except && parent === except) return;
        
        const megaMenu = parent.querySelector('.mega-menu');
        if (megaMenu) {
            megaMenu.style.opacity = '0';
            megaMenu.style.visibility = 'hidden';
            megaMenu.style.transform = 'translateY(15px)';
            megaMenu.setAttribute('aria-hidden', 'true');
            parent.classList.remove('open');
        }
    });
    
    HeaderState.megaMenuOpen = false;
}

/**
 * مدیریت منوی کاربر
 */
function openUserDropdown() {
    if (window.innerWidth <= 992) return;
    
    const menu = HeaderElements.userDropdown?.querySelector('.user-dropdown-menu');
    if (!menu) return;
    
    clearTimeout(HeaderAnimations.dropdownTimeout);
    
    menu.style.opacity = '1';
    menu.style.visibility = 'visible';
    menu.style.transform = 'translateY(0)';
    menu.setAttribute('aria-hidden', 'false');
    HeaderState.userDropdownOpen = true;
}

function closeUserDropdown() {
    const menu = HeaderElements.userDropdown?.querySelector('.user-dropdown-menu');
    if (!menu) return;
    
    HeaderAnimations.dropdownTimeout = setTimeout(() => {
        menu.style.opacity = '0';
        menu.style.visibility = 'hidden';
        menu.style.transform = 'translateY(10px)';
        menu.setAttribute('aria-hidden', 'true');
        HeaderState.userDropdownOpen = false;
    }, 300);
}

function toggleUserDropdown() {
    const menu = HeaderElements.userDropdown?.querySelector('.user-dropdown-menu');
    if (!menu) return;
    
    if (HeaderState.userDropdownOpen) {
        closeUserDropdown();
    } else {
        clearTimeout(HeaderAnimations.dropdownTimeout);
        
        menu.style.opacity = '1';
        menu.style.visibility = 'visible';
        menu.style.transform = 'translateY(0)';
        menu.setAttribute('aria-hidden', 'false');
        HeaderState.userDropdownOpen = true;
    }
}

/**
 * مدیریت سبد خرید کوچک
 */
function openMiniCart() {
    if (window.innerWidth <= 992) return;
    
    if (!HeaderElements.miniCart) return;
    
    clearTimeout(HeaderAnimations.cartTimeout);
    
    HeaderElements.miniCart.style.opacity = '1';
    HeaderElements.miniCart.style.visibility = 'visible';
    HeaderElements.miniCart.style.transform = 'translateY(0)';
    HeaderElements.miniCart.setAttribute('aria-hidden', 'false');
    HeaderState.miniCartOpen = true;
    
    // بارگذاری آیتم‌ها
    loadMiniCartItems();
}

function closeMiniCart() {
    if (!HeaderElements.miniCart) return;
    
    HeaderAnimations.cartTimeout = setTimeout(() => {
        HeaderElements.miniCart.style.opacity = '0';
        HeaderElements.miniCart.style.visibility = 'hidden';
        HeaderElements.miniCart.style.transform = 'translateY(10px)';
        HeaderElements.miniCart.setAttribute('aria-hidden', 'true');
        HeaderState.miniCartOpen = false;
    }, 500);
}

function toggleMiniCart() {
    if (!HeaderElements.miniCart) return;
    
    if (HeaderState.miniCartOpen) {
        closeMiniCart();
    } else {
        openMiniCart();
    }
}

async function loadMiniCartItems() {
    try {
        const response = await fetch('/api/cart/items');
        const data = await response.json();
        
        if (data.success && HeaderElements.miniCart) {
            updateMiniCartDisplay(data.items, data.total);
        }
    } catch (error) {
        console.error('خطا در بارگذاری سبد خرید:', error);
    }
}

function updateMiniCartDisplay(items, total) {
    const container = HeaderElements.miniCart?.querySelector('#miniCartItems');
    const totalElement = HeaderElements.miniCart?.querySelector('.total-price');
    const countElement = HeaderElements.miniCart?.querySelector('.items-count');
    
    if (!container) return;
    
    if (!items || items.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-bag"></i>
                <p>سبد خرید شما خالی است</p>
                <a href="/products.php" class="btn-start-shopping">
                    شروع خرید
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>
        `;
        
        if (countElement) countElement.textContent = '۰ کالا';
        if (totalElement) totalElement.textContent = '۰ تومان';
        return;
    }
    
    let html = '';
    items.forEach(item => {
        html += `
            <div class="cart-item" data-id="${item.id}">
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}" width="70" height="70" loading="lazy">
                </div>
                <div class="item-details">
                    <h5 class="item-name">${item.name}</h5>
                    <p class="item-variant">${item.variant}</p>
                    <div class="item-price">${item.quantity} × ${item.price.toLocaleString('fa-IR')} تومان</div>
                </div>
                <button class="item-remove" aria-label="حذف از سبد خرید" data-id="${item.id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    if (countElement) countElement.textContent = items.length + ' کالا';
    if (totalElement) totalElement.textContent = total.toLocaleString('fa-IR') + ' تومان';
}

async function removeCartItem(item) {
    const itemId = item.dataset.id;
    
    // انیمیشن حذف
    item.style.transition = 'all 0.3s ease';
    item.style.opacity = '0';
    item.style.transform = 'translateX(-20px)';
    item.style.height = '0';
    item.style.padding = '0';
    item.style.margin = '0';
    item.style.border = 'none';
    
    try {
        // ارسال درخواست حذف به سرور
        await fetch(`/api/cart/remove/${itemId}`, {
            method: 'DELETE'
        });
        
        // به‌روزرسانی شمارنده
        updateCartCount(-1);
        
        // حذف از DOM بعد از انیمیشن
        setTimeout(() => {
            item.remove();
            
            // بررسی اگر سبد خالی شد
            const remainingItems = HeaderElements.miniCart?.querySelectorAll('.cart-item');
            if (!remainingItems || remainingItems.length === 0) {
                loadMiniCartItems(); // نمایش حالت خالی
            }
        }, 300);
        
    } catch (error) {
        console.error('خطا در حذف آیتم:', error);
        // بازگرداندن آیتم در صورت خطا
        item.style.opacity = '1';
        item.style.transform = 'translateX(0)';
        item.style.height = '';
        item.style.padding = '';
        item.style.margin = '';
        item.style.border = '';
    }
}

/**
 * به‌روزرسانی شمارنده سبد خرید
 */
function updateCartCount(change = 0) {
    const cartCount = HeaderElements.cartBtn?.querySelector('.cart-count');
    const currentCount = parseInt(cartCount?.textContent || '0');
    const newCount = Math.max(0, currentCount + change);
    
    if (cartCount) {
        cartCount.textContent = newCount;
        cartCount.style.display = newCount > 0 ? 'flex' : 'none';
    }
    
    // به‌روزرسانی در سایت کانفیگ
    if (window.siteConfig) {
        window.siteConfig.cartCount = newCount;
    }
    
    // ارسال به سرور
    fetch('/api/cart/update-count', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ count: newCount })
    }).catch(console.error);
}

/**
 * انیمیشن پالس علاقه‌مندی‌ها
 */
function startWishlistPulse() {
    const badge = document.querySelector('.wishlist-count');
    if (!badge) return;
    
    setInterval(() => {
        badge.classList.add('pulse');
        setTimeout(() => {
            badge.classList.remove('pulse');
        }, 1000);
    }, 15000);
}

/**
 * نمایش اعلان ورود
 */
function showLoginPrompt() {
    // در حالت واقعی یک مودال زیبا نمایش داده می‌شود
    if (confirm('برای استفاده از لیست علاقه‌مندی‌ها، باید وارد حساب کاربری خود شوید.\nآیا مایل به ورود هستید؟')) {
        window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
    }
}

/**
 * لود آرام تصاویر
 */
function lazyLoadImages() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback برای مرورگرهای قدیمی
        images.forEach(img => {
            img.src = img.dataset.src || img.src;
        });
    }
}

/**
 * بروزرسانی aria attributes
 */
function updateAriaAttributes() {
    // جستجو
    if (HeaderElements.searchToggle) {
        HeaderElements.searchToggle.setAttribute('aria-expanded', HeaderState.searchOpen.toString());
    }
    
    // منوی موبایل
    if (HeaderElements.mobileMenuToggle) {
        HeaderElements.mobileMenuToggle.setAttribute('aria-expanded', HeaderState.mobileMenuOpen.toString());
    }
    
    // مگامنوها
    HeaderElements.megaMenuParents.forEach(parent => {
        const isOpen = parent.classList.contains('open');
        const trigger = parent.querySelector('.nav-link');
        if (trigger) {
            trigger.setAttribute('aria-expanded', isOpen.toString());
        }
    });
    
    // منوی کاربر
    if (HeaderElements.userDropdown) {
        const trigger = HeaderElements.userDropdown.querySelector('.user-toggle');
        if (trigger) {
            trigger.setAttribute('aria-expanded', HeaderState.userDropdownOpen.toString());
        }
    }
}

/**
 * توابع گلوبال برای استفاده در سایر بخش‌های سایت
 */
window.HeaderManager = {
    updateCartCount: function(count) {
        updateCartCount(count - (window.siteConfig?.cartCount || 0));
    },
    
    updateWishlistCount: function(count) {
        const badge = document.querySelector('.wishlist-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
        
        if (window.siteConfig) {
            window.siteConfig.wishlistCount = count;
        }
    },
    
    showNotification: function(message, type = 'info') {
        // ایجاد و نمایش نوتیفیکیشن
        const notification = document.createElement('div');
        notification.className = `header-notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="notification-close" aria-label="بستن">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // انیمیشن ورود
        setTimeout(() => notification.classList.add('show'), 10);
        
        // بستن با کلیک
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
        
        // بستن خودکار
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    },
    
    openSearch: openSearch,
    closeSearch: closeSearch,
    openMobileMenu: openMobileMenu,
    closeMobileMenu: closeMobileMenu
};

// لاگ موفقیت‌آمیز
console.log('✅ Header JavaScript loaded successfully');
