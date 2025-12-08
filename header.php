<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فصول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* تنسيقات شريط البحث */
        .search-box {
            flex-grow: 1;
            max-width: 400px;
            margin: 0 20px;
        }
        .search-box form {
            display: flex;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            padding: 5px 15px;
            border: 1px solid rgba(255,255,255,0.5);
            align-items: center;
        }
        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            padding: 5px;
            font-family: 'Tajawal';
            font-size: 14px;
            color: #333;
        }
        .search-box button {
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        header { gap: 20px; align-items: center; }
        
        /* تنسيق زر التسجيل الجديد */
        .btn-register {
            background-color: #e3ce8a;
            color: #333 !important;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-register:hover {
            background-color: #d4be75;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .search-box { display: none; } 
        }
    </style>
</head>
<body dir="rtl">

    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    <div class="admin-toolbar">
        <div class="welcome-msg">
            <span>👋 مرحباً، <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
        </div>
        
        <div class="admin-links">
            <?php // التحقق من الرتبة (Admin أو Writer)
        // التحقق من الرتبة (Admin أو Writer)
        // التحقق من الرتبة (Admin أو Writer)
            if (isset($_SESSION["role"]) && ($_SESSION["role"] === "admin" || $_SESSION["role"] === "writer")): ?>
                <a href="dashboard.php">⚙️ لوحة التحكم</a>
                <a href="add-article.php">📝 إضافة مقال</a>
                
                <?php if ($_SESSION["role"] === "admin"): ?>
                    <a href="add-project.php" style="background-color:#8e44ad;">🏗️ إضافة مشروع</a>
                    <a href="inbox.php" style="background-color:#2980b9;">📥 الرسائل</a>
                <?php endif; ?>
            
            <?php else: ?>
                <a href="my-library.php">📚 مكتبتي</a>
            <?php endif; ?>
            
            <a href="edit-profile.php" title="ملفي الشخصي">👤 حسابي</a>
            <a href="logout.php" class="logout">خروج</a>
        </div>
    </div>
    <?php endif; ?>

    <header>
        <div class="brand-group">
            <a href="index.php">
                <img src="media/fusool-logo.png" alt="شعار فصول" class="logo-img" />
            </a>
            <h1><a href="index.php">فصول</a></h1>
        </div>

        <div class="search-box">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="ابحث عن مقال..." required value="<?php echo isset($_GET["q"])
                    ? htmlspecialchars($_GET["q"])
                    : ""; ?>" />
                <button type="submit">🔍</button>
            </form>
        </div>

        <nav>
            <ul>
                <li><a href="about.php">من نحن</a></li>
                <li><a href="contact.php">تواصل معنا</a></li>

                <?php if (!isset($_SESSION["loggedin"])): ?>
                    <li style="margin-right: 15px; border-right: 1px solid #ccc; padding-right: 15px;">
                        <a href="login.php">دخول</a>
                    </li>
                    <li>
                        <a href="register.php" class="btn-register">تسجيل قارئ جديد</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>