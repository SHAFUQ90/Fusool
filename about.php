<?php
session_start();
require_once "db.php";
// استخدام require_once أفضل لتجنب تكرار التضمين
include "header.php";

// إعداد القيم الافتراضية لتجنب الأخطاء في حال عدم وجود بيانات
$pageData = [
    "title" => "من نحن",
    "content" => "عذراً، المحتوى غير متوفر حالياً.",
];

// جلب محتوى "من نحن" باستخدام Prepared Statement للأمان
$pageKey = "about";
$stmt = $conn->prepare("SELECT title, content FROM site_pages WHERE page_key = ? LIMIT 1");

if ($stmt) {
    $stmt->bind_param("s", $pageKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pageData = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<style>
/* تم الحفاظ على الستايل كما هو تماماً */
body {
    background-image: linear-gradient( rgba( 0, 0, 0, 0.5 ), rgba( 0, 0, 0, 0.5 ) ), url( "media/yy.png" ) !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}
.about-container {
    max-width: 800px;
    margin: 60px auto;
    background: rgba( 255, 255, 255, 0.15 );
    backdrop-filter: blur( 20px );
    -webkit-backdrop-filter: blur( 20px );
    border: 1px solid rgba( 255, 255, 255, 0.3 );
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba( 0, 0, 0, 0.3 );
    color: #fff;
    text-align: center;
    position: relative;
}
.about-logo {
    width: 100px;
    margin-bottom: 20px;
    filter: drop-shadow( 0 5px 15px rgba( 0, 0, 0, 0.3 ) );
}
.about-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 10px;
    color: #fff;
    text-shadow: 0 2px 10px rgba( 0, 0, 0, 0.5 );
}
.about-content {
    text-align: justify;
    line-height: 2;
    font-size: 1.1rem;
    color: #eee;
    margin-bottom: 40px;
}
/* تنسيقات القيم */
.values-grid {
    display: grid;
    grid-template-columns: repeat( auto-fit, minmax( 200px, 1fr ) );
    gap: 20px;
    margin-top: 30px;
}
.value-card {
    background: rgba( 255, 255, 255, 0.1 );
    padding: 20px;
    border-radius: 15px;
    border: 1px solid rgba( 255, 255, 255, 0.2 );
    transition: 0.3s;
}
.value-card:hover {
    background: rgba( 255, 255, 255, 0.2 );
    transform: translateY( -5px );
}
.value-icon {
    font-size: 30px;
    margin-bottom: 10px;
    display: block;
}
.value-title {
    font-weight: bold;
    font-size: 1.1rem;
    color: #fff;
}
/* تنسيق زر التعديل */
.admin-edit-btn {
    color: #f1c40f;

    text-decoration: none;

    font-weight: bold;

    border: 1px solid #f1c40f;

    padding: 8px 20px;

    border-radius: 20px;
    display: inline-block;
    transition: all 0.3s ease;
}
.admin-edit-btn:hover {
    background: #f1c40f;
    color: #000;
}
.admin-controls {
    margin-top: 40px;

    border-top: 1px solid rgba( 255, 255, 255, 0.2 );

    padding-top: 20px;
}
</style>

<main class = "profile-wrapper">
<div class = "about-container">
<img src = "media/fusool-logo.png" alt = "شعار فصول" class = "about-logo">

<h1 class = "about-title"><?php echo htmlspecialchars($pageData["title"]); ?></h1>
<p class = "about-subtitle">"حيث تلتقي المعرفة بالأصالة"</p>

<div class = "about-content">
<?php echo $pageData["content"]; ?>
</div>

<?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
<div class = "admin-controls">
<a href = "edit-page.php?key=about" class = "admin-edit-btn">
✏️ تعديل محتوى هذه الصفحة
</a>
</div>
<?php endif; ?>

</div>
</main>

<?php include "footer.php";
?>
