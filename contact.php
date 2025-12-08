<?php
session_start();
require_once "db.php";
include "header.php";

$msg_status = "";

// متغيرات لحفظ القيم في حال حدوث خطأ ( حتى لا يضطر المستخدم لإعادة الكتابة )
$name_val = "";
$email_val = "";
$message_val = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تنظيف المدخلات
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    // حفظ القيم لإعادتها للحقول
    $name_val = $name;
    $email_val = $email;
    $message_val = $message;

    // التحقق من صحة البيانات
    if (empty($name) || empty($email) || empty($message)) {
        $msg_status = "<div class='error-box'>⚠️ يرجى ملء جميع الحقول.</div>";
    }

    // التحقق من صحة صيغة البريد الإلكتروني
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg_status = "<div class='error-box'>⚠️ البريد الإلكتروني غير صحيح.</div>";
    } else {
        // الإدخال الآمن باستخدام Prepared Statements
        $stmt = $conn->prepare("INSERT INTO messages (sender_name, email, message) VALUES (?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("sss", $name, $email, $message);

            if ($stmt->execute()) {
                $msg_status = "<div class='success-box'>✅ شكراً لك، تم إرسال رسالتك بنجاح. سنرد عليك قريباً.</div>";
                // تصفير الحقول بعد الإرسال الناجح
                $name_val = $email_val = $message_val = "";
            } else {
                $msg_status =
                    "<div class='error-box'>❌ حدث خطأ أثناء الإرسال: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $msg_status = "<div class='error-box'>❌ خطأ في الاتصال بقاعدة البيانات.</div>";
        }
    }
}
?>

<style>
/* تم الحفاظ على التصميم كما هو تماماً */
.contact-container {
    max-width: 600px;
    margin: 60px auto;
    background: rgba( 255, 255, 255, 0.9 );
    backdrop-filter: blur( 20px );
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba( 0, 0, 0, 0.2 );
    border: 1px solid rgba( 255, 255, 255, 0.5 );
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #333;
}
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-family: 'Tajawal';
    font-size: 16px;
    box-sizing: border-box;
}
.send-btn {
    width: 100%;
    padding: 15px;
    background: #2c3e50;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.send-btn:hover {
    background: #1a252f;
}
.success-box {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #c3e6cb;
}
.error-box {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #f5c6cb;
}
</style>

<main class = "profile-wrapper">
<div class = "contact-container">
<h2 style = "text-align: center; margin-bottom: 10px; color: #2c3e50;">تواصل معنا 📩</h2>
<p style = "text-align: center; color: #666; margin-bottom: 30px;">يسعدنا استقبال استفساراتكم واقتراحاتكم</p>

<?php echo $msg_status; ?>

<form method = "POST" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<div class = "form-group">
<label>الاسم:</label>
<input type = "text" name = "name" required placeholder = "اسمك الكريم" value = "<?php echo htmlspecialchars(
    $name_val
); ?>">
</div>

<div class = "form-group">
<label>البريد الإلكتروني:</label>
<input type = "email" name = "email" required placeholder = "name@example.com" value = "<?php echo htmlspecialchars(
    $email_val
); ?>">
</div>

<div class = "form-group">
<label>نص الرسالة:</label>
<textarea name = "message" rows = "5" required placeholder = "اكتب رسالتك هنا..."><?php echo htmlspecialchars(
    $message_val
); ?></textarea>
</div>

<button type = "submit" class = "send-btn">إرسال الرسالة</button>
</form>
</div>
</main>

<?php include "footer.php";
?>
