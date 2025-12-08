<?php
session_start();

// 1. تفريغ جميع متغيرات الجلسة
$_SESSION = [];

// 2. خطوة أمنية مهمة: حذف كعكة الجلسة (Session Cookie) من المتصفح
// هذا يضمن قطع الاتصال تماماً بين المتصفح والسيرفر
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. تدمير الجلسة نهائياً من السيرفر
session_destroy();

// 4. التوجيه لصفحة الدخول
header("Location: login.php");
exit();
?>
