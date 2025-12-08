<?php
session_start();
require_once "db.php";

// 1. الحماية: للمدير فقط
// التحقق من أن المستخدم مسجل دخول وأن دوره "admin"
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}

// 2. التحقق من وجود ID وأنه رقم صحيح موجب
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);

    if ($id > 0) {
        // 3. الحذف الآمن باستخدام Prepared Statement
        $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// 4. العودة لصندوق الوارد
// يمكننا إضافة متغير في الرابط لإظهار رسالة نجاح في صفحة inbox
header("Location: inbox.php?msg=deleted");
exit();
?>
