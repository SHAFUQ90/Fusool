<?php
session_start();
require_once "db.php";
include "header.php";

// 1. استقبال كلمة البحث وتأمينها
$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";
?>

<style>
/* تنسيقات خاصة بصفحة البحث */
.search-result-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba( 0, 0, 0, 0.05 );
    padding: 25px;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
    border: 1px solid #eee;
}
.search-result-card:hover {
    transform: translateY( -5px );
    box-shadow: 0 10px 25px rgba( 0, 0, 0, 0.1 );
}
.result-tag {
    background: #2c3e50;
    color: #fff;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    align-self: flex-start;
    margin-bottom: 10px;
}
.result-title {
    font-size: 1.4rem;
    margin: 0 0 10px 0;
    color: #333;
}
.result-snippet {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}
.result-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f9f9f9;
    padding-top: 15px;
    margin-top: auto;
    /* يدفع الفوتر للأسفل */
}
.project-badge {
    font-size: 12px;
    color: #7f8c8d;
    background: #f1f1f1;
    padding: 5px 10px;
    border-radius: 5px;
}
.read-link {
    color: #e3ce8a;
    font-weight: bold;
    text-decoration: none;
    font-size: 14px;
}
.read-link:hover {
    color: #c4b06e;
}
</style>

<main class = "projects-container">
<div style = "grid-column: 1/-1; text-align: center; margin-bottom: 30px;">
<h2>🔍 نتائج البحث عن: "<?php echo htmlspecialchars($query); ?>"</h2>
</div>

<?php if (!empty($query)) {
    // 2. البحث باستخدام JOIN لجلب اسم المشروع في نفس الاستعلام ( أداء أفضل )
    // نستخدم Prepared Statements مع LIKE
    $search_term = "%" . $query . "%";

    $sql = "SELECT articles.*, projects.title AS project_title 
                FROM articles 
                LEFT JOIN projects ON articles.project_id = projects.id 
                WHERE articles.title LIKE ? 
                   OR articles.summary LIKE ? 
                   OR articles.tag LIKE ?";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($article = $result->fetch_assoc()) {

                // تأمين المخرجات
                $title = htmlspecialchars($article["title"]);
                $tag = htmlspecialchars($article["tag"]);
                $project_name = htmlspecialchars($article["project_title"]);

                // معالجة الملخص
                $summary_raw = strip_tags($article["summary"]);
                $summary = mb_substr($summary_raw, 0, 150) . "...";
                ?>

                <article class = "search-result-card">
                <span class = "result-tag"><?php echo $tag; ?></span>

                <h3 class = "result-title"><?php echo $title; ?></h3>

                <p class = "result-snippet"><?php echo $summary; ?></p>

                <div class = "result-footer">
                <span class = "project-badge">📂 <?php echo $project_name; ?></span>
                <a href = "article.php?id=<?php echo $article["id"]; ?>" class = "read-link">
                قراءة المقال &larr;
                </a>
                </div>
                </article>

                <?php
            }
        } else {
            echo "<div style='grid-column: 1/-1; text-align:center; padding:50px; background:#f9f9f9; border-radius:10px;'>
                        <h3 style='color:#777;'>لم يتم العثور على نتائج تطابق بحثك. 🤷‍♂️</h3>
                        <p>جرب البحث بكلمات أخرى أو تصفح <a href='index.php'>المشاريع</a>.</p>
                      </div>";
        }
        $stmt->close();
    }
} else {
    echo "<p style='grid-column: 1/-1; text-align:center; color:#e74c3c;'>⚠️ الرجاء كتابة كلمة للبحث.</p>";
} ?>
</main>

<?php include "footer.php";
?>
