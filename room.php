<?php
session_start();
require_once "db.php";
include "header.php";

// 1. التحقق من وجود المعرف وصحته
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $project_id = intval($_GET["id"]);
    $project = null;

    // 2. جلب بيانات المشروع بأمان
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $project = $result->fetch_assoc();
        }
        $stmt->close();
    }
} else {
    // إذا لم يتم تحديد معرف، توجيه للرئيسية
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

// في حال لم يتم العثور على المشروع
if (!$project) {
    echo "<div style='text-align:center; padding:100px; color:#777;'>
            <h2>❌ عذراً، هذه الغرفة غير موجودة أو تم حذفها.</h2>
            <a href='index.php' style='color:#2980b9;'>عودة للرئيسية</a>
          </div>";
    include "footer.php";
    exit();
}
?>

<style>
/* تنسيقات خاصة بصفحة الغرفة */
.room-hero {
    background: linear-gradient( rgba( 0, 0, 0, 0.7 ), rgba( 0, 0, 0, 0.7 ) ), url( '<?php echo htmlspecialchars(
        $project["image"]
    ); ?>' );
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #fff;
    padding: 80px 20px;
    text-align: center;
    border-bottom: 5px solid #e3ce8a;
}
.room-hero h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-family: 'Tajawal', sans-serif;
}
.hero-desc {
    max-width: 700px;
    margin: 0 auto 30px;
    font-size: 1.2rem;
    line-height: 1.6;
    color: #ddd;
}
.author-pill {
    display: inline-flex;
    align-items: center;
    background: rgba( 255, 255, 255, 0.2 );
    padding: 5px 15px;
    border-radius: 30px;
    backdrop-filter: blur( 5px );
}
.author-pill img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-left: 10px;
}

/* تنسيق شبكة المقالات */
.articles-container {
    max-width: 1100px;
    margin: 50px auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat( auto-fit, minmax( 300px, 1fr ) );
    gap: 30px;
}
.article-card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba( 0, 0, 0, 0.05 );
    transition: transform 0.3s ease;
    border: 1px solid #eee;
}
.article-card:hover {
    transform: translateY( -5px );
    box-shadow: 0 10px 25px rgba( 0, 0, 0, 0.1 );
}
.article-content {
    padding: 25px;
}
.article-tag {
    background: #f1f1f1;
    color: #555;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: bold;
}
.article-content h3 {
    margin: 15px 0;
    font-size: 1.4rem;
    color: #2c3e50;
    height: 60px;
    /* لتوحيد ارتفاع العناوين */
    overflow: hidden;
}
.article-content p {
    color: #777;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
    height: 70px;
    /* لتوحيد ارتفاع النبذة */
    overflow: hidden;
}
.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f9f9f9;
    padding-top: 15px;
}
.date {
    font-size: 13px;
    color: #aaa;
}
.read-btn {
    text-decoration: none;
    color: #e3ce8a;
    font-weight: bold;
    transition: 0.2s;
}
.read-btn:hover {
    color: #c4b06e;
}
</style>

<main>
<section class = "room-hero">
<div class = "room-hero-content">
<h1><?php echo htmlspecialchars($project["title"]); ?></h1>

<p class = "hero-desc">
<?php echo htmlspecialchars($project["description"]); ?>
</p>

<div class = "hero-meta">
<div class = "author-pill">
<img src = "<?php echo htmlspecialchars($project["image"]); ?>" alt = "المؤلف" />
<span>إشراف: <?php echo htmlspecialchars($project["author"]); ?></span>
</div>
</div>
</div>
</section>

<div class = "articles-container">
<?php
// 3. جلب مقالات الغرفة مرتبة بالأحدث
$stmt_art = $conn->prepare(
    "SELECT id, title, summary, tag, publish_date FROM articles WHERE project_id = ? ORDER BY publish_date DESC"
);

if ($stmt_art) {
    $stmt_art->bind_param("i", $project_id);
    $stmt_art->execute();
    $result_articles = $stmt_art->get_result();

    if ($result_articles->num_rows > 0) {
        while ($article = $result_articles->fetch_assoc()) {

            // معالجة الملخص: إزالة التاغات وتقليل النص
            $raw_summary = strip_tags($article["summary"]);
            $summary = mb_substr($raw_summary, 0, 150) . "...";
            ?>

            <article class = "article-card">
            <div class = "article-content">
            <span class = "article-tag"><?php echo htmlspecialchars($article["tag"]); ?></span>

            <h3><?php echo htmlspecialchars($article["title"]); ?></h3>

            <p><?php echo $summary; ?></p>

            <div class = "card-footer">
            <span class = "date">
            <?php echo date("Y/m/d", strtotime($article["publish_date"])); ?>
            </span>
            <a href = "article.php?id=<?php echo $article["id"]; ?>" class = "read-btn">
            اقرأ المقال &larr;
            </a>
            </div>
            </div>
            </article>

            <?php
        }
    } else {
        echo '<div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f9f9f9; border-radius: 10px;">
                        <h3 style="color:#aaa;">📭 لا توجد مقالات مضافة في هذه الغرفة بعد.</h3>
                      </div>';
    }
    $stmt_art->close();
}
?>
    </div>
    </main>

    <?php include "footer.php";
?>
