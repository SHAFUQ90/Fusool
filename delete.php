<?php
session_start();
require_once "db.php";

// 1. الحماية: التأكد من تسجيل الدخول
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// 2. التحقق من وجود المعرف وصحته
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = intval($_GET["id"]);

    // إذا كان المعرف 0 أو أقل، نعود للوحة التحكم فوراً
    if ($id <= 0) {
        header("Location: dashboard.php");
        exit();
    }

    // 3. الحذف الآمن باستخدام Prepared Statement
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");

    if ($stmt) {
        // ربط المعرف كعدد صحيح ( i = integer )
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // تم الحذف بنجاح، العودة للوحة التحكم
            $stmt->close();
            header("Location: dashboard.php?msg=deleted");
            exit();
        } else {
            // في حال وجود خطأ تقني، نعرض رسالة بسيطة
            die("عذراً، حدث خطأ أثناء محاولة الحذف.");
        }
    } else {
        die("خطأ في الاتصال بقاعدة البيانات.");
    }
} else {
    // إذا لم يكن هناك ID في الرابط، نعود للوحة التحكم
    header("Location: dashboard.php");
    exit();
}
?>
