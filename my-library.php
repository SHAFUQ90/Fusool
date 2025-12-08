<?php
session_start();
require_once "db.php";

// 1. الحماية: التحقق من تسجيل الدخول أولاً قبل تحميل أي محتوى
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

include "header.php";

$user_id = $_SESSION["user_id"];
?>

<main class = "projects-container">
<div style = "grid-column: 1/-1; text-align: center; margin-bottom: 40px;">
<h2 style = "color: #2c3e50;">📚 مكتبتي الخاصة</h2>
<p style = "color: #7f8c8d;">المشاريع التي قمت بحفظها للقراءة</p>
</div>

<?php
// 2. جلب المشاريع المحفوظة باستخدام JOIN و Prepared Statement
// قمت بإضافة ORDER BY لترتيبها حسب تاريخ الحفظ ( الأحدث أولاً )
$sql = "SELECT projects.*, saved_projects.saved_at 
            FROM projects 
            JOIN saved_projects ON projects.id = saved_projects.project_id 
            WHERE saved_projects.user_id = ? 
            ORDER BY saved_projects.saved_at DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            // تأمين المخرجات
            $p_id = $row["id"];
            $title = htmlspecialchars($row["title"]);
            $desc = htmlspecialchars($row["description"]);
            $author = htmlspecialchars($row["author"]);
            $image = htmlspecialchars($row["image"]);
            ?>

            <div class = "project-room">
            <div class = "room-content">
            <h2><?php echo $title; ?></h2>

            <a href = "save_project.php?action=remove&id=<?php echo $p_id; ?>"
            style = "color: #e74c3c; font-size: 0.9rem; text-decoration: none; display: inline-block; margin-bottom: 10px;"
            onclick = "return confirm('هل أنت متأكد من إزالة هذا المشروع من مكتبتك؟');">
            ❌ إزالة من المكتبة
            </a>

            <p class = "project-description"><?php echo $desc; ?></p>

            <a href = "room.php?id=<?php echo $p_id; ?>" class = "room-link">
            دخول الغرفة &larr;
            </a>
            </div>

            <div class = "vertical-divider"></div>

            <div class = "author-section">
            <img src = "<?php echo $image; ?>" class = "author-image" alt = "<?php echo $author; ?>" />
            <span class = "author-name">بقلم: <?php echo $author; ?></span>
            </div>
            </div>

            <?php
        }
    } else {
        // تصميم أجمل لحالة عدم وجود مشاريع
        echo '
            <div style="grid-column: 1/-1; text-align: center; padding: 50px; background: rgba(255,255,255,0.6); border-radius: 15px;">
                <h3 style="color: #7f8c8d; margin-bottom: 20px;">لم تقم بحفظ أي مشروع بعد.</h3>
                <a href="index.php" style="color: #2980b9; font-weight: bold; text-decoration: none; border: 1px solid #2980b9; padding: 10px 20px; border-radius: 20px; transition:0.3s;">
                    تصفح المشاريع الآن 🚀
                </a>
            </div>';
    }
    $stmt->close();
}
?>
</main>

<?php include "footer.php";
?>
