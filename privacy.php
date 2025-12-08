<?php
session_start();
require_once "db.php";
include "header.php";

// إعداد قيم افتراضية
$page = [
    "title" => "سياسات الموقع",
    "content" => "<p style='text-align:center; padding:20px;'>لم يتم إضافة المحتوى بعد.</p>",
];

// جلب المحتوى من قاعدة البيانات بأمان
$page_key = "privacy";
$stmt = $conn->prepare("SELECT title, content FROM site_pages WHERE page_key = ? LIMIT 1");

if ($stmt) {
    $stmt->bind_param("s", $page_key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $page = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<style>
/* خلفية الموقع */
body {
    background-image: linear-gradient( rgba( 0, 0, 0, 0.6 ), rgba( 0, 0, 0, 0.6 ) ), url( "media/yy.png" ) !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}

/* حاوية الصفحة */
.policy-container {
    max-width: 850px;
    margin: 60px auto;
    /* الخلفية الزجاجية */
    background: rgba( 255, 255, 255, 0.95 );
    backdrop-filter: blur( 20px );
    -webkit-backdrop-filter: blur( 20px );

    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba( 0, 0, 0, 0.3 );
    color: #333;
    line-height: 1.8;
    border: 1px solid rgba( 255, 255, 255, 0.5 );
}

.policy-header {
    text-align: center;
    border-bottom: 2px solid #f39c12;
    padding-bottom: 20px;
    margin-bottom: 30px;
}

.policy-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin: 0;
    font-family: 'Tajawal', sans-serif;
}

/* تنسيقات المحتوى القادم من قاعدة البيانات */
.policy-content {
    font-size: 1.1rem;
    color: #444;
}
.policy-content h3 {
    color: #d35400;
    font-size: 1.4rem;
    margin-top: 20px;
    margin-bottom: 10px;
}
.policy-content p {
    margin-bottom: 15px;
    text-align: justify;
}

/* تنسيق زر التعديل */
.edit-page-btn {
    display: inline-block;
    background: #2c3e50;
    color: white;
    text-decoration: none;
    font-weight: bold;
    padding: 12px 30px;
    border-radius: 30px;
    transition: 0.3s;
    border: 2px solid #2c3e50;
    margin-top: 30px;
}
.edit-page-btn:hover {
    background: transparent;
    color: #2c3e50;
}
</style>

<main class = "profile-wrapper">
<div class = "policy-container">
<div class = "policy-header">
<h1 class = "policy-title"><?php echo htmlspecialchars($page["title"]); ?></h1>
<p style = "color:#777; margin-top:10px;">آخر تحديث: <?php echo date("Y/m/d"); ?></p>
</div>

<div class = "policy-content">
<?php echo $page["content"]; ?>
</div>

<?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
<div style = "text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
<a href = "edit-page.php?key=privacy" class = "edit-page-btn">
✏️ تعديل محتوى السياسات
</a>
</div>
<?php endif; ?>

</div>
</main>

<?php include "footer.php";
?>
