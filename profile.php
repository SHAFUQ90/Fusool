<?php
session_start();
require_once "db.php";
include "header.php";

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// 2. تحديد هوية الملف الشخصي المراد عرضه
// إذا تم تمرير ID في الرابط نستخدمه، وإلا نستخدم ID المستخدم المسجل حالياً
$profile_id = isset($_GET["id"]) ? intval($_GET["id"]) : $_SESSION["user_id"];

// تحديد نوع الدور للبحث في الجدول الصحيح
// ( حالياً نفترض أننا نبحث بناءً على دور الشخص المسجل، لتطوير مستقبلي يمكن تمرير النوع في الرابط )
$search_role = $_SESSION["role"];

// متغيرات افتراضية
$name = "مستخدم";
$role_text = "";
$avatar = "media/default.png";
$slogan = "";
$bio_content = "لا توجد نبذة شخصية.";
$stats_html = "";

// 3. جلب البيانات بناءً على الجدول المناسب
if ($search_role === "reader") {
    // --- حالة القارئ ( جدول readers ) ---
    $stmt = $conn->prepare("SELECT full_name, bio, interests FROM readers WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row["full_name"]);
        $role_text = "قارئ نهم";
        $avatar = "media/reader-avatar.png";
        // صورة افتراضية للقراء
        $slogan = htmlspecialchars($row["bio"]);
        // الشعار مخزن في bio
        $bio_content = "<strong>الاهتمامات:</strong><br>" . htmlspecialchars($row["interests"]);

        // إحصائيات القارئ ( عدد الكتب المحفوظة )
        // نستخدم استعلام منفصل لحساب الكتب
        $stmt_lib = $conn->prepare("SELECT count(*) as total FROM saved_projects WHERE user_id = ?");
        $stmt_lib->bind_param("i", $profile_id);
        $stmt_lib->execute();
        $lib_count = $stmt_lib->get_result()->fetch_assoc()["total"];
        $stmt_lib->close();

        $stats_html =
            '
            <div class="stat-item">
                <span class="stat-num">' .
            $lib_count .
            '</span>
                <span class="stat-label">مشروع محفوظ</span>
            </div>';
    }
    $stmt->close();
} else {
    // --- حالة المدير أو الكاتب ( جدول users ) ---
    $stmt = $conn->prepare("SELECT username, full_name, role, avatar, slogan, bio FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $name = !empty($row["full_name"]) ? htmlspecialchars($row["full_name"]) : htmlspecialchars($row["username"]);
        $role_text = $row["role"] === "admin" ? "مدير الموقع" : "كاتب محتوى";

        // التحقق من الصورة
        $db_avatar = $row["avatar"];
        if (!empty($db_avatar) && file_exists($db_avatar)) {
            $avatar = htmlspecialchars($db_avatar);
        } else {
            $avatar = "media/default.png";
        }

        $slogan = htmlspecialchars($row["slogan"]);
        $bio_content = !empty($row["bio"]) ? htmlspecialchars($row["bio"]) : "لم تضف نبذة بعد.";

        // إحصائيات ( المقالات )
        $stmt_art = $conn->prepare("SELECT count(*) as total FROM articles WHERE author_id = ?");
        $stmt_art->bind_param("i", $profile_id);
        $stmt_art->execute();
        $art_count = $stmt_art->get_result()->fetch_assoc()["total"];
        $stmt_art->close();

        // إحصائيات ( المشاريع - بحث بالاسم لأن الجدول يخزن اسم المؤلف نصاً )
        // ملاحظة: يفضل مستقبلاً ربط المشاريع بـ ID المؤلف بدلاً من الاسم
        $author_name_search = "%" . $name . "%";
        $stmt_proj = $conn->prepare("SELECT count(*) as total FROM projects WHERE author LIKE ?");
        $stmt_proj->bind_param("s", $author_name_search);
        $stmt_proj->execute();
        $proj_count = $stmt_proj->get_result()->fetch_assoc()["total"];
        $stmt_proj->close();

        $stats_html =
            '
            <div class="stat-item">
                <span class="stat-num">' .
            $proj_count .
            '</span>
                <span class="stat-label">مشروع</span>
            </div>
            <div class="stat-item">
                <span class="stat-num">' .
            $art_count .
            '</span>
                <span class="stat-label">مقال</span>
            </div>';
    }
    $stmt->close();
}
?>

<style>
body {
    background-image: linear-gradient( rgba( 0, 0, 0, 0.4 ), rgba( 0, 0, 0, 0.4 ) ), url( "media/yy.png" ) !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}
.profile-wrapper {
    min-height: 85vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
}
.profile-card {
    background: rgba( 255, 255, 255, 0.15 );
    backdrop-filter: blur( 20px );
    -webkit-backdrop-filter: blur( 20px );
    width: 100%;
    max-width: 800px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba( 0, 0, 0, 0.3 );
    border: 1px solid rgba( 255, 255, 255, 0.3 );
}
.profile-header {
    background: linear-gradient( to right, rgba( 15, 32, 39, 0.9 ), rgba( 32, 58, 67, 0.9 ), rgba( 44, 83, 100, 0.9 ) );
    padding: 40px;
    border-bottom: 1px solid rgba( 255, 255, 255, 0.1 );
    display: flex;
    align-items: center;
    gap: 30px;
    text-align: right;
}
.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 4px solid rgba( 255, 255, 255, 0.3 );
    object-fit: cover;
    box-shadow: 0 10px 25px rgba( 0, 0, 0, 0.3 );
    flex-shrink: 0;
}
.header-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.profile-name {
    font-size: 36px;
    font-weight: 800;
    margin: 0 0 10px 0;
    color: #fff;
    text-shadow: 0 2px 10px rgba( 0, 0, 0, 0.5 );
    line-height: 1.2;
}
.profile-role {
    align-self: flex-start;
    background: linear-gradient( 45deg, #f39c12, #d35400 );
    color: #fff;
    padding: 5px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
    box-shadow: 0 4px 10px rgba( 0, 0, 0, 0.2 );
}
.profile-slogan {
    font-family: 'Tajawal', sans-serif;
    font-size: 20px;
    color: #f1c40f;
    font-weight: 600;
    line-height: 1.6;
    margin-top: 15px;
    text-shadow: 0 2px 4px rgba( 0, 0, 0, 0.6 );
}
.quote-mark {
    font-size: 30px;
    color: rgba( 255, 255, 255, 0.4 );
    margin: 0 5px;
    vertical-align: bottom;
}
@media ( max-width: 768px ) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    .profile-role {
        align-self: center;
    }
}
.profile-body {
    padding: 35px 30px;
    text-align: center;
}
.bio-text {
    line-height: 1.8;
    color: #fff;
    margin-bottom: 30px;
    font-size: 18px;
    text-shadow: 0 1px 2px rgba( 0, 0, 0, 0.6 );
}
.stats-grid {
    display: flex;
    justify-content: center;
    gap: 60px;
    border-top: 1px solid rgba( 255, 255, 255, 0.15 );
    border-bottom: 1px solid rgba( 255, 255, 255, 0.15 );
    padding: 25px 0;
    margin-bottom: 30px;
}
.stat-num {
    font-size: 28px;
    font-weight: bold;
    color: #fff;
    text-shadow: 0 2px 5px rgba( 0, 0, 0, 0.3 );
    display: block;
}
.stat-label {
    font-size: 15px;
    color: #ccc;
    display: block;
}
.back-link {
    color: #bbb;
    text-decoration: none;
    font-size: 14px;
    transition: 0.3s;
}
.back-link:hover {
    color: #fff;
    text-shadow: 0 0 5px #fff;
}
</style>

<main class = "profile-wrapper">
<div class = "profile-card">
<div class = "profile-header">
<img src = "<?php echo $avatar; ?>" class = "profile-avatar" alt = "الصورة الشخصية" />

<div class = "header-info">
<h1 class = "profile-name"><?php echo $name; ?></h1>
<span class = "profile-role"><?php echo $role_text; ?></span>

<?php if (!empty($slogan)): ?>
<div class = "profile-slogan">
<span class = "quote-mark">"</span>
                        <?php echo $slogan; ?>
                        <span class="quote-mark">"</span>
</div>
<?php endif; ?>
</div>
</div>

<div class = "profile-body">
<div class = "bio-text">
<?php echo nl2br($bio_content); ?>
</div>

<div class = "stats-grid">
<?php echo $stats_html; ?>
</div>

<br>
<a href = "index.php" class = "back-link">عودة للرئيسية</a>
</div>
</div>
</main>

<?php include "footer.php";
?>
