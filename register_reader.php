<?php
session_start();
require_once "db.php";
include "header.php";

// توجيه المستخدم إذا كان مسجلاً بالفعل
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: index.php");
    exit();
}

$msg = "";

// متغيرات لحفظ القيم المدخلة ( Sticky Form )
$full_name_val = "";
$email_val = "";
$phone_val = "";
$bio_val = "";
$interests_val = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. استقبال وتنظيف البيانات
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $bio = trim($_POST["bio"]);
    $interests = trim($_POST["interests"]);

    // حفظ القيم لإعادة عرضها في حال وجود خطأ
    $full_name_val = $full_name;
    $email_val = $email;
    $phone_val = $phone;
    $bio_val = $bio;
    $interests_val = $interests;

    // 2. التحقق من صحة البيانات
    // حساب عدد الكلمات في الشعار
    $word_count = count(explode(" ", $bio));

    if (empty($full_name) || empty($email) || empty($password)) {
        $msg = '<div class="error-msg">⚠️ يرجى ملء الحقول الأساسية (الاسم، البريد، كلمة المرور).</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = '<div class="error-msg">⚠️ صيغة البريد الإلكتروني غير صحيحة.</div>';
    } elseif ($word_count > 20) {
        $msg =
            '<div class="error-msg">⚠️ الشعار طويل جداً! يرجى اختصاره إلى 20 كلمة (حالياً: ' . $word_count . ").</div>";
    } else {
        // 3. التحقق من تكرار البريد ( في جدول القراء وجدول الإدارة )
        $mail_check = true;

        // فحص جدول القراء
        $stmt1 = $conn->prepare("SELECT id FROM readers WHERE email = ?");
        $stmt1->bind_param("s", $email);
        $stmt1->execute();
        $stmt1->store_result();
        if ($stmt1->num_rows > 0) {
            $mail_check = false;
        }
        $stmt1->close();

        // فحص جدول المستخدمين ( الإدارة )
        if ($mail_check) {
            $stmt2 = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $stmt2->store_result();
            if ($stmt2->num_rows > 0) {
                $mail_check = false;
            }
            $stmt2->close();
        }

        if (!$mail_check) {
            $msg = '<div class="error-msg">❌ هذا البريد مسجل مسبقاً! حاول تسجيل الدخول.</div>';
        } else {
            // 4. التشفير والحفظ
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO readers (full_name, email, phone, password, bio, interests) VALUES (?, ?, ?, ?, ?, ?)"
            );
            if ($stmt) {
                $stmt->bind_param("ssssss", $full_name, $email, $phone, $hashed_password, $bio, $interests);

                if ($stmt->execute()) {
                    $msg =
                        '<div class="success-msg">✅ تم إنشاء حسابك بنجاح! <a href="login.php" style="font-weight:bold; text-decoration:underline;">سجل دخولك الآن</a></div>';
                    // تصفير الخانات بعد النجاح
                    $full_name_val = $email_val = $phone_val = $bio_val = $interests_val = "";
                } else {
                    $msg = '<div class="error-msg">❌ حدث خطأ تقني: ' . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            }
        }
    }
}
?>

<style>
.reg-container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba( 0, 0, 0, 0.1 );
    font-family: 'Tajawal';
}
.reg-container h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Tajawal';
    box-sizing: border-box;
}
.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #c3e6cb;
}
.error-msg {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #f5c6cb;
}
.btn-submit {
    width: 100%;
    padding: 15px;
    background: #e3ce8a;
    color: #333;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.btn-submit:hover {
    background: #d4be75;
}
</style>

<main>
<div class = "reg-container">
<h2>👤 إنشاء حساب قارئ جديد</h2>
<?php echo $msg; ?>

<form method = "POST" action = "">
<div class = "form-group">
<label>الاسم الكامل:</label>
<input type = "text" name = "full_name" required placeholder = "مثال: يوسف حسين" value = "<?php echo htmlspecialchars(
    $full_name_val
); ?>">
</div>

<div class = "form-group">
<label>البريد الإلكتروني:</label>
<input type = "email" name = "email" required placeholder = "email@gmail.com" value = "<?php echo htmlspecialchars(
    $email_val
); ?>">
</div>

<div class = "form-group">
<label>رقم الهاتف:</label>
<input type = "text" name = "phone" required placeholder = "01xxxxxxxxx" value = "<?php echo htmlspecialchars(
    $phone_val
); ?>">
</div>

<div class = "form-group">
<label>كلمة المرور:</label>
<input type = "password" name = "password" required placeholder = "********">
</div>

<div class = "form-group">
<label>شعارك ( جملة قصيرة تعبر عنك - 20 كلمة كحد أقصى ):</label>
<input type = "text" name = "bio" placeholder = "مثال: قارئ نهم للتاريخ..." value = "<?php echo htmlspecialchars(
    $bio_val
); ?>">
</div>

<div class = "form-group">
<label>الاهتمامات:</label>
<textarea name = "interests" rows = "3" placeholder = "تاريخ، أدب، فلسفة..."><?php echo htmlspecialchars(
    $interests_val
); ?></textarea>
</div>

<button type = "submit" class = "btn-submit">تسجيل الحساب</button>
</form>
</div>
</main>

<?php include "footer.php";
?>
