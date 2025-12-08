<?php
session_start();

// 1. الحماية: للمدير فقط
if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}

require_once "db.php";
include "header.php";

// التحقق من وجود مفتاح الصفحة في الرابط
$key = isset($_GET["key"]) ? $_GET["key"] : "";
if (empty($key)) {
    echo "<div class='error-msg' style='margin: 50px auto; max-width:600px;'>⚠️ لم يتم تحديد الصفحة المطلوبة للتعديل.</div>";
    include "footer.php";
    exit();
}

$message = "";
$page = [];

// 2. معالجة حفظ التعديلات (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = $_POST["content"]; // المحتوى HTML من المحرر

    if (!empty($title) && !empty($content)) {
        // تحديث البيانات باستخدام Prepared Statement
        $update_stmt = $conn->prepare("UPDATE site_pages SET title = ?, content = ? WHERE page_key = ?");

        if ($update_stmt) {
            $update_stmt->bind_param("sss", $title, $content, $key);

            if ($update_stmt->execute()) {
                // رسالة نجاح
                // ملاحظة: نفترض أن اسم الملف يطابق المفتاح (مثل about.php) للمعاينة
                $preview_link = htmlspecialchars($key) . ".php";
                $message =
                    '<div class="success-msg">✅ تم تحديث الصفحة بنجاح! <a href="' .
                    $preview_link .
                    '" target="_blank">معاينة الصفحة</a></div>';
            } else {
                $message =
                    '<div class="error-msg">❌ حدث خطأ أثناء الحفظ: ' .
                    htmlspecialchars($update_stmt->error) .
                    "</div>";
            }
            $update_stmt->close();
        }
    } else {
        $message = '<div class="error-msg">⚠️ العنوان والمحتوى لا يمكن أن يكونا فارغين.</div>';
    }
}

// 3. جلب المحتوى الحالي للصفحة لعرضه في النموذج
$stmt = $conn->prepare("SELECT * FROM site_pages WHERE page_key = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $page = $result->fetch_assoc();
    } else {
        echo "<div style='text-align:center; padding:50px; font-family:Tajawal;'>❌ عفواً، الصفحة غير موجودة في قاعدة البيانات.</div>";
        include "footer.php";
        exit(); // إيقاف التنفيذ
    }
    $stmt->close();
}
?>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<style>
    /* تم الحفاظ على التصميم كما هو تماماً */
    .form-container { 
        max-width: 900px; 
        margin: 50px auto; 
        background: #fff; 
        padding: 40px; 
        border-radius: 15px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
    }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 10px; font-weight: bold; font-family: 'Tajawal'; }
    input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: 'Tajawal'; }
    
    .submit-btn { 
        width: 100%; padding: 15px; background: #2980b9; color: white; 
        border: none; border-radius: 10px; cursor: pointer; 
        font-weight: bold; font-size: 18px; transition: 0.3s; margin-top: 20px;
    }
    .submit-btn:hover { background: #3498db; }
    
    .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb; }
    .error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid #f5c6cb; }

    /* إخفاء التنبيه الأحمر المزعج في المحرر */
    .cke_notification_warning { display: none !important; }
</style>

<main style="padding: 20px;">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px; font-family: 'Tajawal';">
            ✏️ تعديل صفحة: <?php echo htmlspecialchars($page["title"]); ?>
        </h2>
        
        <?php echo $message; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>عنوان الصفحة:</label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($page["title"]); ?>">
            </div>

            <div class="form-group">
                <label>محتوى الصفحة:</label>
                <textarea name="content" id="editor1" rows="15" required><?php echo $page["content"]; ?></textarea>
            </div>

            <button type="submit" class="submit-btn">حفظ التعديلات</button>
        </form>
    </div>
</main>

<script>
    // إعدادات المحرر
    CKEDITOR.replace('editor1', {
        height: 400,
        contentsLangDirection: 'rtl', // الاتجاه من اليمين لليسار
        language: 'ar', // اللغة عربية
        removePlugins: 'exportpdf', // تسريع التحميل
        // إضافة خط تجوال داخل المحرر ليكون مطابقاً للموقع
        contentsCss: 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap',
        font_defaultLabel: 'Tajawal',
        fontSize_defaultLabel: '16px'
    });
    
    // إضافة ستايل بسيط لجعل الخط الافتراضي داخل المحرر هو تجوال
    CKEDITOR.addCss('body { font-family: "Tajawal", sans-serif; }');
</script>

<?php include "footer.php"; ?>
