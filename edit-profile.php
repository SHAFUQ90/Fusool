<?php
session_start();
require_once "db.php";
include "header.php";

// 1. الحماية: التحقق من تسجيل الدخول
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// 2. جلب بيانات المستخدم الحالية
$user = [];
$stmt = $conn->prepare("SELECT full_name, slogan, bio, avatar FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// 3. معالجة حفظ التغييرات ( POST Request )
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تنظيف المدخلات
    $full_name = trim($_POST["full_name"]);
    $slogan = trim($_POST["slogan"]);
    $bio = trim($_POST["bio"]);

    // المسار الافتراضي للصورة هو المسار الحالي ( في حال لم يرفع صورة جديدة )
    $avatar_path = $user["avatar"];
    $uploadOk = true;

    // 4. معالجة رفع الصورة الجديدة ( إذا وجدت )
    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] == 0) {
        $allowed_ext = ["jpg", "jpeg", "png", "gif", "webp"];
        $file_name = $_FILES["avatar"]["name"];
        $file_size = $_FILES["avatar"]["size"];
        $file_tmp = $_FILES["avatar"]["tmp_name"];

        // الحصول على الامتداد
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // التحقق من الامتداد
        if (!in_array($file_ext, $allowed_ext)) {
            $message .= '<div class="error-msg">❌ نوع الملف غير مسموح. يرجى رفع صورة فقط (JPG, PNG, GIF).</div>';
            $uploadOk = false;
        }
        // التحقق من الحجم ( مثلاً 2 ميجابايت )
        elseif ($file_size > 2 * 1024 * 1024) {
            $message .= '<div class="error-msg">❌ حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت.</div>';
            $uploadOk = false;
        }

        if ($uploadOk) {
            // توليد اسم فريد للصورة لتجنب التكرار
            $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $target_dir = "media/";
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $avatar_path = $target_file;
            } else {
                $message .= '<div class="error-msg">❌ فشل رفع الصورة لسبب تقني.</div>';
                $uploadOk = false;
            }
        }
    }

    // 5. تحديث قاعدة البيانات إذا كانت الأمور سليمة
    if ($uploadOk) {
        $update_stmt = $conn->prepare("UPDATE users SET full_name=?, slogan=?, bio=?, avatar=? WHERE id=?");

        if ($update_stmt) {
            $update_stmt->bind_param("ssssi", $full_name, $slogan, $bio, $avatar_path, $user_id);

            if ($update_stmt->execute()) {
                $message = '<div class="success-msg">✅ تم تحديث ملفك الشخصي بنجاح!</div>';

                // تحديث البيانات المعروضة في الصفحة فوراً
                $user["full_name"] = $full_name;
                $user["slogan"] = $slogan;
                $user["bio"] = $bio;
                $user["avatar"] = $avatar_path;

                // تحديث اسم المستخدم في الجلسة ليظهر التغيير في الـ Header فوراً
                $_SESSION["username"] = $full_name;
            } else {
                $message =
                    '<div class="error-msg">❌ حدث خطأ أثناء الحفظ: ' .
                    htmlspecialchars($update_stmt->error) .
                    "</div>";
            }
            $update_stmt->close();
        }
    }
}
?>

<style>
/* تم الحفاظ على التصميم كما هو تماماً */
.form-container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba( 0, 0, 0, 0.1 );
}
input, textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-sizing: border-box;
    font-family: 'Tajawal';
}
.current-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 20px;
    display: block;
    border: 3px solid #eee;
}
.save-btn {
    width: 100%;
    padding: 15px;
    background: #2c3e50;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
}
.save-btn:hover {
    background: #1a252f;
}
.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #c3e6cb;
}
.error-msg {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #f5c6cb;
}
</style>

<main>
<div class = "form-container">
<h2 style = "text-align:center; margin-bottom:30px;">تعديل الملف الشخصي</h2>

<?php echo $message; ?>

<?php if (!empty($user["avatar"]) && file_exists($user["avatar"])): ?>
<img src = "<?php echo htmlspecialchars($user["avatar"]); ?>" class = "current-avatar" alt = "الصورة الشخصية">
<?php else: ?>
<img src = "media/user-placeholder.png" class = "current-avatar" alt = "صورة افتراضية">
<?php endif; ?>

<form method = "POST" enctype = "multipart/form-data">

<label>الاسم الظاهر ( للقراء ):</label>
<input type = "text" name = "full_name" value = "<?php echo htmlspecialchars(
    $user["full_name"]
); ?>" placeholder = "مثال: يوسف البوتلي" required>

<label>الشعار الشخصي:</label>
<input type = "text" name = "slogan" value = "<?php echo htmlspecialchars(
    $user["slogan"]
); ?>" placeholder = 'مثال: "في البدء كانت الكلمة..."'>

<label>صورة شخصية:</label>
<input type = "file" name = "avatar" accept = "image/*">
<small style = "display:block; margin-bottom:15px; color:#777;">* الصيغ المسموحة: JPG, PNG, GIF. الحد الأقصى 2 ميجا.</small>

<label>نبذة عنك:</label>
<textarea name = "bio" rows = "5" placeholder = "اكتب نبذة مختصرة عن اهتماماتك..."><?php echo htmlspecialchars(
    $user["bio"]
); ?></textarea>

<button type = "submit" class = "save-btn">حفظ التغييرات</button>
</form>
</div>
</main>

<?php include "footer.php";
?>
