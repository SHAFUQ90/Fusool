<?php
// 1. إظهار الأخطاء ( لحل مشكلة الشاشة البيضاء )
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once "db.php";

echo "<h2>🚀 جاري إعادة بناء جدول المستخدمين...</h2>";

// 2. حذف الجدول القديم ( للتخلص من البيانات المتعارضة )
$sql_drop = "DROP TABLE IF EXISTS users";
if ($conn->query($sql_drop) === true) {
    echo "✅ تم حذف الجدول القديم لتنظيف البيانات.<br>";
} else {
    echo "⚠️ لم يتم حذف الجدول (ربما غير موجود): " . $conn->error . "<br>";
}

// 3. إنشاء الجدول من جديد بالتصميم الصحيح ( مع الإيميل )
$sql_create = "CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'reader',
    full_name VARCHAR(100) NULL,
    slogan VARCHAR(255) NULL,
    bio TEXT NULL,
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_create) === true) {
    echo "✅ تم إنشاء جدول users الجديد بنجاح.<br>";
} else {
    die("❌ خطأ فادح في إنشاء الجدول: " . $conn->error);
}

echo "<hr>";

// 4. إنشاء حساب المدير الجديد
$email = "admin@fusool.com";
$password = "123456";
$username = "Admin";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql_insert = "INSERT INTO users (username, email, password, role, full_name, bio) 
               VALUES ('$username', '$email', '$hashed_password', 'admin', 'المدير العام', 'حساب الإدارة الرئيسي')";

if ($conn->query($sql_insert) === true) {
    echo "✅ تم إنشاء حساب المدير بنجاح!<br><br>";
    echo "---------------------------------<br>";
    echo "<b>البريد الإلكتروني:</b> $email<br>";
    echo "<b>كلمة المرور:</b> $password<br>";
    echo "<br><a href='login.php' style='background:#2c3e50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>👉 اضغط هنا لتسجيل الدخول</a>";
} else {
    echo "❌ خطأ في إضافة المدير: " . $conn->error;
}
?>
