@echo off
setlocal EnableDelayedExpansion

echo.
echo ===============================================
echo    ساخت خودکار ساختار پروژه لوکس پوشاک
echo    تاریخ: 1404/11/19
echo ===============================================
echo.

set "ROOT=lux-pooshak"

echo [+] ایجاد پوشه اصلی: %ROOT%
if not exist "%ROOT%" mkdir "%ROOT%"

cd /d "%ROOT%"

:: پوشه‌های اصلی
echo [+] ایجاد پوشه‌ها...
mkdir admin includes css js images

:: فایل‌های اصلی PHP
echo [+] ایجاد فایل‌های PHP...
(
echo ^<?php
echo // config.php - تنظیمات اتصال به دیتابیس
echo $host = 'localhost';
echo $dbname = 'lux_clothing_db';
echo $user = 'root';
echo $pass = '';
echo try {
echo     $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
echo     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo } catch (PDOException $e) {
echo     die("خطا در اتصال به دیتابیس: " . $e->getMessage());
echo }
echo ?^>
) > config.php

:: اینجا فقط نام فایل‌ها را ایجاد می‌کنیم - محتوای کامل را قبلاً در چت داریم
echo. > index.php
echo. > products.php
echo. > product.php
echo. > cart.php
echo. > wishlist.php
echo. > profile.php
echo. > checkout.php
echo. > order-confirmation.php
echo. > about.php
echo. > contact.php

:: includes
echo. > includes\header.php
echo. > includes\footer.php

:: admin
echo. > admin\login.php
echo. > admin\index.php
echo. > admin\logout.php

:: css و js
echo. > css\main.css
echo. > js\main.js

:: فایل sql
echo. > database.sql

echo.
echo ===============================================
echo    پروژه با موفقیت ساخته شد!
echo.
echo    مسیر: %CD%
echo.
echo    حالا می‌توانید محتوای فایل‌ها را از چت قبلی کپی کنید.
echo    برای تست: config.php را ویرایش کنید و database.sql را در phpMyAdmin اجرا کنید.
echo ===============================================
echo.

pause