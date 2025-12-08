<?php
session_start();
require_once "db.php";
include "header.php";

// 1. الحماية: للمدير فقط
if (!isset($_SESSION["loggedin"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}

// التحقق من وجود رسالة ( مثل: تم الحذف بنجاح ) قادمة من delete-message.php
$alert_msg = "";
if (isset($_GET["msg"]) && $_GET["msg"] == "deleted") {
    $alert_msg = '<div class="alert-success">✅ تم حذف الرسالة بنجاح.</div>';
}
?>

<style>
/* خلفية الموقع خاصة بهذه الصفحة */
body {
    background-image: linear-gradient( rgba( 0, 0, 0, 0.3 ), rgba( 0, 0, 0, 0.3 ) ), url( "media/yy.png" ) !important;
    background-size: cover !important;
    background-attachment: fixed !important;
}

/* --- تصميم الكتلة البيضاء --- */
.inbox-container {
    background: rgba( 255, 255, 255, 0.95 );
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    border: 1px solid #fff;
    width: 95%;
    max-width: 1100px;
    margin: 50px auto;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba( 0, 0, 0, 0.3 );
    overflow-x: auto;
    color: #333;
}

h2 {
    text-align: center;
    margin-bottom: 30px !important;
    color: #2c3e50 !important;
    font-weight: 800;
}

/* رسالة التنبيه */
.alert-success {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #c3e6cb;
}

/* --- تصميم الجدول --- */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background: #2c3e50;
    color: #fff;
    padding: 18px;
    text-align: right;
    font-family: 'Tajawal';
    font-weight: bold;
    font-size: 15px;
}

th:first-child {
    border-top-right-radius: 10px;
}
th:last-child {
    border-top-left-radius: 10px;
}

tr {
    border-bottom: 1px solid #eee;
}
tr:hover {
    background-color: #f1f1f1;
}

td {
    padding: 15px;
    vertical-align: top;
    color: #444;
    font-size: 15px;
}

/* تنسيق الأعمدة */
td.date-col {
    color: #777;
    font-size: 13px;
    width: 120px;
}
td.name-col {
    font-weight: bold;
    color: #000;
    width: 150px;
}
td.email-col {
    color: #2980b9;
    width: 200px;
    direction: ltr;
    text-align: right;
}

.msg-body {
    line-height: 1.6;
    color: #333;
    white-space: pre-wrap;
    /* للحفاظ على تنسيق الأسطر */
}

/* --- الأزرار --- */
.delete-btn {
    text-decoration: none;
    color: #e74c3c;
    font-weight: bold;
    font-size: 24px;
    transition: 0.2s;
    display: block;
    text-align: center;
    line-height: 1;
}
.delete-btn:hover {
    color: #c0392b;
    transform: scale( 1.2 );
}

.back-btn {
    display: inline-block;
    margin-top: 40px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: bold;
    border: 2px solid #2c3e50;
    padding: 10px 30px;
    border-radius: 30px;
    transition: 0.3s;
    background: transparent;
}
.back-btn:hover {
    background: #2c3e50;
    color: #fff;
}
</style>

<main class = "projects-container">
<div class = "inbox-container">
<h2>📥 صندوق الوارد</h2>

<?php echo $alert_msg; ?>

<table>
<thead>
<tr>
<th>التاريخ</th>
<th>الاسم</th>
<th>البريد</th>
<th>الرسالة</th>
<th style = "text-align:center;">حذف</th>
</tr>
</thead>
<tbody>
<?php
$sql = "SELECT * FROM messages ORDER BY sent_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $id = $row["id"];
        // حماية المخرجات
        $name = htmlspecialchars($row["sender_name"]);
        $email = htmlspecialchars($row["email"]);
        // nl2br لتحويل الأسطر الجديدة إلى <br>
        $msg = nl2br(htmlspecialchars($row["message"]));
        $date = date("Y/m/d", strtotime($row["sent_date"]));
        ?>
        <tr>
        <td class = "date-col"><?php echo $date; ?></td>
        <td class = "name-col"><?php echo $name; ?></td>
        <td class = "email-col"><?php echo $email; ?></td>
        <td><div class = "msg-body"><?php echo $msg; ?></div></td>
        <td>
        <a href = "delete-message.php?id=<?php echo $id; ?>"

        class = "delete-btn"
        onclick = "return confirm('هل أنت متأكد من حذف هذه الرسالة نهائياً؟');"
        title = "حذف">
        &times;
        </a>
        </td>
        </tr>
        <?php
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center; padding:60px; color:#999; font-size:18px;'>📭 لا توجد رسائل جديدة</td></tr>";
}
?>
</tbody>
</table>

<div style = "text-align: center;">
<a href = "dashboard.php" class = "back-btn">عودة للوحة التحكم</a>
</div>
</div>
</main>

<?php include "footer.php";
?>
