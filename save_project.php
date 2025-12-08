<?php
session_start();
require_once "db.php";

// 1. الحماية: التأكد من تسجيل الدخول
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود معرف المشروع
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $project_id = intval($_GET["id"]);
    $user_id = $_SESSION["user_id"];

    // تحديد نوع العملية ( حفظ أم حذف؟ )
    // إذا لم يتم تحديد action في الرابط، الافتراضي هو 'save'
    $action = isset($_GET["action"]) ? $_GET["action"] : "save";

    if ($action === "save") {
        // --- عملية الحفظ ---

        // أولاً: نتأكد أن المشروع لم يتم حفظه مسبقاً لتجنب التكرار
        $check_stmt = $conn->prepare("SELECT id FROM saved_projects WHERE user_id = ? AND project_id = ?");
        $check_stmt->bind_param("ii", $user_id, $project_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows == 0) {
            // إذا لم يكن محفوظاً، نقوم بإضافته
            $insert_stmt = $conn->prepare(
                "INSERT INTO saved_projects (user_id, project_id, saved_at) VALUES (?, ?, NOW())"
            );
            $insert_stmt->bind_param("ii", $user_id, $project_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    } elseif ($action === "remove") {
        // --- عملية الحذف ---

        $delete_stmt = $conn->prepare("DELETE FROM saved_projects WHERE user_id = ? AND project_id = ?");
        $delete_stmt->bind_param("ii", $user_id, $project_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
}

// 2. التوجيه الذكي ( العودة للصفحة السابقة )
// إذا كان المستخدم قادماً من المكتبة، يعود للمكتبة. وإذا كان من الرئيسية، يعود للرئيسية.
if (isset($_SERVER["HTTP_REFERER"])) {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
} else {
    // وجهة احتياطية في حال عدم معرفة المصدر
    header("Location: my-library.php");
}
exit();
?>
