<?php
session_start();
require_once "db.php";
include "header.php";

// إذا كان المستخدم مسجلاً بالفعل، نوجهه للصفحة المناسبة
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: index.php");
    exit();
}

$msg = "";

// متغيرات لحفظ القيم المدخلة ( لإعادتها للحقول في حال حدوث خطأ )
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
    $interests = trim($_POST["interests"]);
    $bio = trim($_POST["bio"]);

    // حفظ القيم لإعادة عرضها
    $full_name_val = $full_name;
    $email_val = $email;
    $phone_val = $phone;
    $bio_val = $bio;
    $interests_val = $interests;

    // 2. التحقق من صحة البيانات الأساسية
    // التحقق من طول الشعار ( 20 كلمة )
    $word_count = count(explode(" ", $bio));

    if (empty($full_name) || empty($email) || empty($password)) {
        $msg = '<div class="error-msg">⚠️ يرجى ملء الحقول الأساسية (الاسم، البريد، كلمة المرور).</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = '<div class="error-msg">⚠️ صيغة البريد الإلكتروني غير صحيحة.</div>';
    } elseif ($word_count > 20) {
        $msg =
            '<div class="error-msg">⚠️ الشعار طويل جداً! يرجى اختصاره إلى 20 كلمة (حالياً: ' . $word_count . ").</div>";
    } else {
        // 3. التحقق من تكرار البريد الإلكتروني ( Prepared Statement )
        // نبحث في جدول القراء
        $check_stmt = $conn->prepare("SELECT id FROM readers WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        // نبحث أيضاً في جدول الإدارة ( لمنع تكرار البريد عبر النظامين )
        $check_admin = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_admin->bind_param("s", $email);
        $check_admin->execute();
        $check_admin->store_result();

        if ($check_stmt->num_rows > 0 || $check_admin->num_rows > 0) {
            $msg = '<div class="error-msg">❌ هذا البريد مسجل مسبقاً! حاول تسجيل الدخول.</div>';
        } else {
            // 4. تشفير كلمة المرور والحفظ
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // الإدخال الآمن
            $insert_stmt = $conn->prepare(
                "INSERT INTO readers (full_name, email, phone, password, bio, interests) VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($insert_stmt) {
                $insert_stmt->bind_param("ssssss", $full_name, $email, $phone, $hashed_password, $bio, $interests);

                if ($insert_stmt->execute()) {
                    $msg =
                        '<div class="success-msg">🎉 تم إنشاء حسابك بنجاح! <a href="login.php" style="font-weight:bold; text-decoration:underline;">سجل دخولك الآن</a></div>';
                    // تصفير الحقول بعد النجاح
                    $full_name_val = $email_val = $phone_val = $bio_val = $interests_val = "";
                } else {
                    $msg =
                        '<div class="error-msg">❌ حدث خطأ أثناء التسجيل: ' .
                        htmlspecialchars($insert_stmt->error) .
                        "</div>";
                }
                $insert_stmt->close();
            }
        }
        $check_stmt->close();
        $check_admin->close();
    }
}
?>

<style>
/* الحفاظ على التصميم كما هو */
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
    font-size: 16px;
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
    margin-top: 10px;
}
.btn-submit:hover {
    background: #d4be75;
}
.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #c3e6cb;
}
.error-msg {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
    border: 1px solid #f5c6cb;
}
.note {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}
</style>

<main>
<div class = "reg-container">
<h2>👤 انضم إلى مجتمع فصول</h2>
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
<label>شعارك الشخصي ( Bio ):</label>
<input type = "text" name = "bio" placeholder = "اكتب جملة قصيرة تعبر عنك..." value = "<?php echo htmlspecialchars(
    $bio_val
); ?>">
<div class = "note">* بحد أقصى 20 كلمة.</div>
</div>

<div class = "form-group">
<label>الاهتمامات:</label>
<textarea name = "interests" rows = "3" placeholder = "أدب، تاريخ، فلسفة..."><?php echo htmlspecialchars(
    $interests_val
); ?></textarea>
</div>

<button type = "submit" class = "btn-submit">إنشاء الحساب</button>
</form>
</div>
</main>

<?php include "footer.php";
?>
