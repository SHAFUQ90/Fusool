<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fusool_db";

// إنشاء الاتصال
// علامة @ تمنع ظهور الأخطاء البرمجية المزعجة للزائر في حال الفشل
$conn = @new mysqli($servername, $username, $password, $dbname);

// التحقق من وجود خطأ في الاتصال
if ($conn->connect_error) {
    // لأسباب أمنية: لا تقم بطباعة تفاصيل الخطأ ( connect_error ) للزوار
    // بدلاً من ذلك نعرض رسالة عامة
    die("عذراً، حدث خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.");
}

// تعيين الترميز إلى utf8mb4 لدعم اللغة العربية بشكل كامل والإيموجي
$conn->set_charset("utf8mb4");

?>
