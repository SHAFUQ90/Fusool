<?php
session_start();

// 1. الحماية: التحقق من الصلاحيات ( Admin Only )
if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}

require_once "db.php";
include "header.php";

$message = "";

// متغيرات لحفظ القيم المدخلة ( لإعادتها للحقول في حال حدوث خطأ )
$title_val = "";
$desc_val = "";
$author_val = "";
$image_val = "media/fusool-logo.png";
// القيمة الافتراضية

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تنظيف المدخلات من المسافات الزائدة
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $author = trim($_POST["author"]);
    $image = trim($_POST["image"]);

    // تحديث المتغيرات لتبقى في الحقول
    $title_val = $title;
    $desc_val = $description;
    $author_val = $author;
    $image_val = $image;

    // التحقق من أن الحقول ليست فارغة
    if (!empty($title) && !empty($description) && !empty($author)) {
        // استخدام Prepared Statements
        $stmt = $conn->prepare("INSERT INTO projects (title, description, author, image) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("ssss", $title, $description, $author, $image);

            if ($stmt->execute()) {
                $message = '<div class="success-msg">✅ تم إنشاء المشروع الجديد بنجاح!</div>';
                // تصفير الحقول بعد النجاح
                $title_val = $desc_val = $author_val = "";
                $image_val = "media/fusool-logo.png";
            } else {
                $message =
                    '<div class="error-msg">❌ حدث خطأ أثناء الحفظ: ' . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = '<div class="error-msg">❌ خطأ في الاتصال بقاعدة البيانات.</div>';
        }
    } else {
        $message = '<div class="error-msg">⚠️ يرجى ملء جميع الحقول المطلوبة.</div>';
    }
}
?>

<style>
/* نفس التصميم تماماً */
.form-container {
    max-width: 600px;
    margin: 50px auto;
    background: rgba( 255, 255, 255, 0.95 );
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba( 0, 0, 0, 0.2 );
}
.form-group {
    margin-bottom: 20px;
}
label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}
input, textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-family: 'Tajawal', sans-serif;
    font-size: 16px;
    box-sizing: border-box;
}
.submit-btn {
    width: 100%;
    padding: 15px;
    background-color: #8e44ad;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.submit-btn:hover {
    background-color: #732d91;
}
.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.error-msg {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

<main>
<div class = "form-container">
<h2 style = "text-align: center; margin-bottom: 30px; color: #333;">🏗️ إنشاء مشروع ( غرفة ) جديدة</h2>

<?php echo $message; ?>

<form method = "POST" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

<div class = "form-group">
<label>اسم المشروع ( الغرفة ):</label>
<input type = "text" name = "title" required placeholder = "مثال: الفلسفة اليونانية" value = "<?php echo htmlspecialchars(
    $title_val
); ?>">
</div>

<div class = "form-group">
<label>وصف المشروع:</label>
<textarea name = "description" rows = "4" required placeholder = "وصف قصير يظهر في الواجهة..."><?php echo htmlspecialchars(
    $desc_val
); ?></textarea>
</div>

<div class = "form-group">
<label>اسم المشرف ( المؤلف ):</label>
<input type = "text" name = "author" required placeholder = "مثال: يوسف البوتلي" value = "<?php echo htmlspecialchars(
    $author_val
); ?>">
</div>

<div class = "form-group">
<label>مسار الصورة:</label>
<input type = "text" name = "image" required value = "<?php echo htmlspecialchars(
    $image_val
); ?>" placeholder = "مثال: media/my-image.jpg">
<small style = "color:#666; display:block; margin-top:5px;">* يرجى التأكد من وضع الصورة في مجلد media وكتابة مسارها هنا.</small>
</div>

<button type = "submit" class = "submit-btn">إنشاء المشروع</button>
</form>
</div>
</main>

<?php include "footer.php";
?>
