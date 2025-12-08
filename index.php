<?php
session_start();
require_once "db.php";
include "header.php";
?>

<main class = "projects-container">
<?php
// جلب المشاريع مرتبة من الأحدث للأقدم
$sql = "SELECT * FROM projects ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()):

        // تجهيز البيانات للعرض بشكل آمن
        $p_id = $row["id"];
        $title = htmlspecialchars($row["title"]);
        $description = htmlspecialchars($row["description"]);
        $author = htmlspecialchars($row["author"]);
        $image = htmlspecialchars($row["image"]);
        ?>

<div class = "project-room">
<div class = "room-content">
<h2><?php echo $title; ?></h2>

<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
<a href = "save_project.php?id=<?php echo $p_id; ?>"
style = "display:inline-block; margin-bottom:10px; color:#e67e22; text-decoration:none; font-size:14px; font-weight:bold;">
🔖 حفظ في مكتبتي
</a>
<?php endif; ?>

<p class = "project-description"><?php echo $description; ?></p>

<a href = "room.php?id=<?php echo $p_id; ?>" class = "room-link">
دخول الغرفة &larr;
</a>
</div>

<div class = "vertical-divider"></div>

<div class = "author-section">
<a href = "#" class = "author-link-wrapper">
<img src = "<?php echo $image; ?>" alt = "<?php echo $author; ?>" class = "author-image" />
<span class = "author-name">بقلم: <?php echo $author; ?></span>
</a>
</div>
</div>

<?php
    endwhile;
    // رسالة في حال عدم وجود مشاريع بتنسيق بسيط
else:
    echo '<p style="text-align:center; padding:50px; color:#777; font-size:1.2rem;">لا توجد مشاريع مضافة حالياً.</p>';
endif;
?>
</main>

<?php include "footer.php";
?>
