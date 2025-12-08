<?php
session_start();

// 1. الحماية: التحقق من تسجيل الدخول قبل أي شيء
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

require_once "db.php";
?>

<!DOCTYPE html>
<html lang = "ar">
<head>
<meta charset = "UTF-8">
<title>لوحة الإدارة - فصول</title>
<link rel = "stylesheet" href = "style.css">
<link href = "https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel = "stylesheet">

<style>
/* تم الحفاظ على التصميم كما هو تماماً */
.dashboard-container {
    max-width: 90%;
    margin: 50px auto;
    background: rgba( 255, 255, 255, 0.9 );
    padding: 30px;
    border-radius: 15px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 15px;
    text-align: right;
    border-bottom: 1px solid #ddd;
}
th {
    background-color: #333;
    color: white;
}
tr:hover {
    background-color: #f1f1f1;
}
.btn {
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    margin-left: 5px;
    color: white;
    display: inline-block;
    /* تحسين للعرض */
}
.btn-add {
    background-color: #28a745;
    float: left;
    margin-bottom: 15px;
    font-size: 16px;
}
.btn-delete {
    background-color: #dc3545;
}
.btn-view {
    background-color: #007bff;
}
.btn-delete:hover {
    background-color: #c82333;
}
</style>
</head>
<body dir = "rtl">

<header>
<div class = "brand-group"><h1>لوحة التحكم</h1></div>
<nav>
<ul>
<li style = "color: #555; margin-left: 15px;">مرحباً، <?php echo htmlspecialchars($_SESSION["username"]); ?></li>
<li><a href = "index.php" target = "_blank">عرض الموقع</a></li>
<li><a href = "logout.php" style = "color: red; font-weight: bold;">تسجيل خروج</a></li>
</ul>
</nav>
</header>

<main>
<div class = "dashboard-container">
<h2 style = "display:inline-block;">إدارة المقالات</h2>
<a href = "add-article.php" class = "btn btn-add">+ إضافة مقال جديد</a>

<table>
<thead>
<tr>
<th>#</th>
<th>عنوان المقال</th>
<th>التاريخ</th>
<th>الإجراءات</th>
</tr>
</thead>
<tbody>
<?php
// تحسين الأداء: تحديد الأعمدة المطلوبة فقط بدلاً من *
$sql = "SELECT id, title, publish_date FROM articles ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        // حماية المخرجات النصية
        $title = htmlspecialchars($row["title"]);
        // تنسيق التاريخ ليكون مقروءاً بشكل أفضل
        $date = date("Y/m/d", strtotime($row["publish_date"]));

        echo "<tr>";
        echo "<td>{$id}</td>";
        echo "<td>{$title}</td>";
        echo "<td>{$date}</td>";
        echo "<td>";

        echo "<a href='edit-article.php?id={$id}' class='btn btn-view'>تعديل ✏️</a>";

        // زر الحذف مع تأكيد بالجافاسكريبت
        echo "<a href='delete.php?id={$id}' class='btn btn-delete' onclick=\"return confirm( 'هل أنت متأكد من حذف هذا المقال؟ سيتم حذفه نهائياً!' );
        \">حذف 🗑️</a>";

        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center; padding: 20px; color: #777;'>لا توجد مقالات حالياً</td></tr>";
}
?>
</tbody>
</table>
</div>
</main>

</body>
</html>