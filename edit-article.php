<?php
session_start();
require_once "db.php";

// 1. الحماية: التحقق من تسجيل الدخول
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET["id"]);
$article = [];
$message = "";

// 2. جلب بيانات المقال الحالي (Prepared Statement)
$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
    $stmt->close();
}

if (!$article) {
    die("<div style='text-align:center; padding:50px;'>❌ المقال غير موجود أو تم حذفه.</div>");
}

// 3. معالجة تحديث المقال (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $summary = $_POST["summary"]; // المحتوى HTML
    $tag = trim($_POST["tag"]);
    $project_id = intval($_POST["project_id"]);

    if (!empty($title) && !empty($summary)) {
        // تحديث البيانات بأمان
        $update_stmt = $conn->prepare("UPDATE articles SET title=?, summary=?, tag=?, project_id=? WHERE id=?");

        if ($update_stmt) {
            $update_stmt->bind_param("sssii", $title, $summary, $tag, $project_id, $id);

            if ($update_stmt->execute()) {
                $message =
                    '<div class="success-msg">✅ تم تعديل المقال بنجاح! <a href="dashboard.php">عودة للوحة</a></div>';

                // تحديث المتغيرات لكي يرى المستخدم التعديلات فوراً في النموذج
                $article["title"] = $title;
                $article["summary"] = $summary;
                $article["tag"] = $tag;
                $article["project_id"] = $project_id;
            } else {
                $message =
                    '<div class="error-msg">❌ حدث خطأ أثناء التحديث: ' .
                    htmlspecialchars($update_stmt->error) .
                    "</div>";
            }
            $update_stmt->close();
        }
    } else {
        $message = '<div class="error-msg">⚠️ العنوان والمحتوى حقول مطلوبة.</div>';
    }
}

// 4. جلب قائمة المشاريع (للقائمة المنسدلة)
$projectsList = [];
$proj_res = $conn->query("SELECT id, title FROM projects");
if ($proj_res) {
    while ($row = $proj_res->fetch_assoc()) {
        $projectsList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل المقال: <?php echo htmlspecialchars($article["title"]); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.20.0/standard/ckeditor.js"></script>
    <style>
        /* الحفاظ على نفس الستايل */
        .form-container {
            max-width: 800px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            box-sizing: border-box;
        }
        button.submit-btn {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 18px;
            font-weight: bold;
            transition: background 0.3s;
        }
        button.submit-btn:hover {
            background: #0056b3;
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
        /* تحسينات إضافية للمحرر */
        .cke_chrome { box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important; }
    </style>
</head>
<body dir="rtl">

<header>
    <div class="brand-group"><h1>تعديل المقال</h1></div>
    <nav><ul><li><a href="dashboard.php">إلغاء وعودة</a></li></ul></nav>
</header>

<main>
    <div class="form-container">
        <?php echo $message; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>عنوان المقال:</label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($article["title"]); ?>">
            </div>

            <div class="form-group">
                <label>المشروع:</label>
                <select name="project_id" required>
                    <?php foreach ($projectsList as $proj): ?>
                        <option value="<?php echo $proj["id"]; ?>" <?php echo $proj["id"] == $article["project_id"]
    ? "selected"
    : ""; ?>>
                            <?php echo htmlspecialchars($proj["title"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>التصنيف (Tag):</label>
                <input type="text" name="tag" value="<?php echo htmlspecialchars($article["tag"]); ?>">
            </div>

            <div class="form-group">
                <label>نص المقال:</label>
                <textarea name="summary" id="editor1" rows="10" required><?php echo htmlspecialchars(
                    $article["summary"]
                ); ?></textarea>
            </div>

            <button type="submit" class="submit-btn">حفظ التعديلات</button>
        </form>
    </div>
</main>

<script>
    // الحفاظ على إعدادات المحرر الخاصة (الورقة الشمواه)
    CKEDITOR.addCss(
        'html { background-color: #e4e4e4; }' +
        'body {' +
        '    background-color: #fef9e7 !important;' +
        '    font-family: "Tajawal", sans-serif !important;' +
        '    font-size: 18px;' +
        '    color: #1a1a1a;' +
        '    line-height: 1.9;' +
        '    padding: 50px;' +
        '    max-width: 850px;' +
        '    margin: 30px auto;' +
        '    box-shadow: 0 4px 15px rgba(0,0,0,0.1);' +
        '    border: 1px solid #dcdcdc;' +
        '}'
    );

    CKEDITOR.replace('editor1', {
        uiColor: '#ffffff', 
        height: 500,
        contentsLangDirection: 'rtl',
        contentsCss: 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap',
        toolbar: [
            [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ],
            [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
            [ 'Link', 'Unlink' ],
            [ 'Format', 'Font', 'FontSize' ],
            [ 'Image', 'Table', 'HorizontalRule' ],
            [ 'Maximize', 'Source' ]
        ]
    });
</script>

</body>
</html>