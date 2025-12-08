<?php
session_start();
require_once "db.php";

// التحقق من وجود ID وأنه رقم صحيح
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = intval($_GET["id"]);

    // استخدام JOIN لجلب المقال واسم المشروع في استعلام واحد ( أداء أفضل )
    $sql = "SELECT articles.*, projects.title AS project_title 
            FROM articles 
            LEFT JOIN projects ON articles.project_id = projects.id 
            WHERE articles.id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $article = $result->fetch_assoc();
        } else {
            // توجيه لصفحة 404 أو عرض رسالة منسقة بدلاً من die
            header("HTTP/1.0 404 Not Found");
            die(
                "<div style='text-align:center; padding:50px; font-family:sans-serif;'>❌ عذراً، هذا المقال غير موجود.</div>"
            );
        }
        $stmt->close();
    }
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang = "ar">
<head>
<meta charset = "UTF-8">
<title><?php echo htmlspecialchars($article["title"]); ?> - فصول</title>
<meta name = "viewport" content = "width=device-width, initial-scale=1.0" />
<link rel = "stylesheet" href = "style.css">
<link href = "https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel = "stylesheet">

<style>
/* تم الحفاظ على التصميم كما هو تماماً */
.article-container {
    max-width: 800px;
    margin: 40px auto;
    background: rgba( 255, 255, 255, 0.85 );
    backdrop-filter: blur( 20px );
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba( 0, 0, 0, 0.1 );
}

.article-header {
    text-align: center;
    border-bottom: 1px solid rgba( 0, 0, 0, 0.1 );
    padding-bottom: 30px;
    margin-bottom: 30px;
}

.article-tag {
    background-color: #333;
    color: #fff;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    display: inline-block;
    margin-bottom: 15px;
}

.article-title {
    font-size: 2.5rem;
    color: #111;
    margin: 10px 0;
    line-height: 1.4;
}

.article-meta {
    color: #666;
    font-size: 0.9rem;
    margin-top: 15px;
}

.article-body {
    font-size: 1.2rem;
    line-height: 1.8;
    color: #222;
}

.article-body img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
    margin: 20px 0;
}

.back-link {
    display: inline-block;
    margin-top: 40px;
    text-decoration: none;
    color: #333;
    font-weight: bold;
    border: 1px solid #333;
    padding: 10px 20px;
    border-radius: 30px;
    transition: all 0.3s ease;
}
.back-link:hover {
    background: #333;
    color: white;
}

/* تنسيق شريط الأدوات ( في حال لم يكن موجوداً في style.css ) */
.admin-toolbar {
    background: #333;
    color: #fff;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.admin-links a {
    color: #fff;
    text-decoration: none;
    margin-left: 15px;
}
</style>
</head>
<body dir = "rtl">

<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
<div class = "admin-toolbar">
<div class = "welcome-msg">
<span>👋 مرحباً، <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
</div>
<div class = "admin-links">
<a href = "dashboard.php">⚙️ لوحة التحكم</a>
<a href = "add-article.php">📝 إضافة مقال</a>
<a href = "logout.php" class = "logout">خروج</a>
</div>
</div>
<?php endif; ?>

<header>
<div class = "brand-group">
<a href = "index.php"><img src = "media/fusool-logo.png" alt = "شعار" class = "logo-img" /></a>
<h1><a href = "index.php">فصول</a></h1>
</div>
<nav>
<ul>
<li><a href = "index.php">الرئيسية</a></li>
<li><a href = "room.php?id=<?php echo $article["project_id"]; ?>">العودة للغرفة</a></li>
</ul>
</nav>
</header>

<main>
<article class = "article-container">
<div class = "article-header">
<span class = "article-tag"><?php echo htmlspecialchars($article["tag"]); ?></span>
<h1 class = "article-title"><?php echo htmlspecialchars($article["title"]); ?></h1>
<div class = "article-meta">
نُشر بتاريخ: <?php echo date("Y/m/d", strtotime($article["publish_date"])); ?> |
مشروع: <?php echo htmlspecialchars($article["project_title"]); ?>
</div>
</div>

<div class = "article-body">
<?php echo $article["summary"]; ?>
</div>

<a href = "room.php?id=<?php echo $article["project_id"]; ?>" class = "back-link">&rarr;
عودة إلى قائمة المقالات</a>
</article>
</main>

<footer>
<p>جميع الحقوق محفوظة &copy;
<?php echo date("Y"); ?></p>
</footer>

</body>
</html>