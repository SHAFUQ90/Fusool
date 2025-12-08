<?php
session_start();
require_once "db.php";
include "header.php";

// 1. حماية الوصول: للمدير والكاتب فقط
// التحقق مما إذا كانت الجلسة مسجلة والدور مسموح به
if (!isset($_SESSION["loggedin"]) || !in_array($_SESSION["role"], ["admin", "writer"])) {
    header("Location: index.php");
    exit();
}

$message = "";

// 2. معالجة إرسال النموذج (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استقبال البيانات وتنظيف الفراغات الجانبية
    $title = trim($_POST["title"]);
    $project_id = intval($_POST["project_id"]);
    $tag = trim($_POST["tag"]);
    $summary = $_POST["content"]; // المحتوى يأتي بتنسيق HTML من المحرر
    $author_id = $_SESSION["user_id"];

    // التحقق من أن الحقول الأساسية ليست فارغة
    if (!empty($title) && !empty($summary) && $project_id > 0) {
        // استخدام Prepared Statement لإدراج البيانات بأمان
        $stmt = $conn->prepare(
            "INSERT INTO articles (title, project_id, tag, summary, author_id, publish_date) VALUES (?, ?, ?, ?, ?, NOW())"
        );

        if ($stmt) {
            // s = string, i = integer
            $stmt->bind_param("sisii", $title, $project_id, $tag, $summary, $author_id);

            if ($stmt->execute()) {
                $message = '<div class="success-msg">✅ تم نشر المقال بنجاح!</div>';
            } else {
                $message =
                    '<div class="error-msg">❌ حدث خطأ أثناء الحفظ: ' . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = '<div class="error-msg">❌ خطأ في الاتصال بقاعدة البيانات.</div>';
        }
    } else {
        $message = '<div class="error-msg">⚠️ يرجى ملء عنوان المقال، واختيار المشروع، وكتابة المحتوى.</div>';
    }
}

// 3. جلب قائمة المشاريع (للـ Dropdown)
// نفصل الاستعلام عن العرض لترتيب الكود
$projectsList = [];
$projQuery = "SELECT id, title FROM projects ORDER BY title ASC";
$projResult = $conn->query($projQuery);
if ($projResult) {
    while ($row = $projResult->fetch_assoc()) {
        $projectsList[] = $row;
    }
}
?>

<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<style>
    /* --- تنسيقات النموذج العامة --- */
    .form-container { 
        max-width: 1000px; margin: 50px auto; 
        background: rgba(255,255,255,0.95); padding: 40px; 
        border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); 
    }
    label { font-weight: bold; font-family: 'Tajawal'; display: block; margin-bottom: 8px; color: #333; }
    input, select { 
        width: 100%; padding: 12px; margin-bottom: 20px; 
        border: 1px solid #ccc; border-radius: 8px; 
        font-family: 'Tajawal'; font-size: 16px; box-sizing: border-box;
    }
    .submit-btn { 
        width: 100%; padding: 15px; background: #2c3e50; color: white; 
        border: none; border-radius: 10px; font-size: 18px; font-weight: bold; 
        cursor: pointer; transition: 0.3s; margin-top: 20px;
    }
    .submit-btn:hover { background: #1a252f; }
    .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
    .error-msg { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; }

    /* --- تحسينات CKEditor (كما طلبت تماماً) --- */
    .cke_notification_warning { display: none !important; }
    .cke_chrome {
        margin: 0 auto !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    /* إصلاح التكبير والقوائم */
    .cke_maximized { z-index: 999999 !important; }
    .cke_panel, .cke_float { z-index: 1000000 !important; }
</style>

<main>
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px; font-family: 'Tajawal';">✍️ كتابة مقال جديد</h2>
        
        <?php echo $message; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label>عنوان المقال:</label>
            <input type="text" name="title" required placeholder="مثال: جماليات الشعر الجاهلي">

            <label>اختر الغرفة (المشروع):</label>
            <select name="project_id" required>
                <option value="">-- اختر المشروع --</option>
                <?php foreach ($projectsList as $proj): ?>
                    <option value="<?php echo $proj["id"]; ?>">
                        <?php echo htmlspecialchars($proj["title"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>التصنيف (Tag):</label>
            <input type="text" name="tag" placeholder="مثال: نقد، شعر، تاريخ">

            <label>نص المقال:</label>
            <textarea name="content" id="editor1"></textarea>

            <button type="submit" class="submit-btn">نشر المقال</button>
        </form>
    </div>
</main>

<script>
    // 1. إعداد المحرر بنفس الإعدادات السابقة
    var editor = CKEDITOR.replace('editor1', {
        width: '21cm',
        height: '29.7cm',
        contentsLangDirection: 'rtl',
        language: 'ar',
        uiColor: '#ffffff',
        // تنسيق الورقة الداخلية
        contentsCss: 'body { font-family: "Tajawal", sans-serif; font-size: 18px; line-height: 1.8; background-color: #e3ce8a; padding: 2cm; }'
    });

    // 2. إصلاح مشكلة التكبير (Maximize)
    editor.on('maximize', function(evt) {
        if (evt.data == 1) {
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
        }
    });
</script>

<?php include "footer.php"; ?>
