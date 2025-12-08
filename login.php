<?php
session_start();
require_once "db.php";
// استخدام require_once لضمان الاتصال

// 1. إعادة التوجيه التلقائي إذا كان المستخدم مسجلاً
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if (isset($_SESSION["role"]) && $_SESSION["role"] === "reader") {
        header("Location: index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

include "header.php";

$error = "";

// 2. معالجة طلب تسجيل الدخول
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تنظيف البريد الإلكتروني
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";
    } else {
        // --- الخطوة أ: البحث في جدول الإدارة ( users ) ---
        // نستخدم Prepared Statement للأمان
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // الحساب موجود كـ ( مدير أو كاتب )
            $row = $result->fetch_assoc();

            if (password_verify($password, $row["password"])) {
                // كلمة المرور صحيحة
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["username"] = $row["username"];
                $_SESSION["role"] = $row["role"];
                // admin أو writer

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "كلمة المرور غير صحيحة.";
            }
        } else {
            // --- الخطوة ب: البحث في جدول القراء ( readers ) ---
            // إذا لم نجده في الإدارة، نبحث في القراء
            $stmt->close();
            // إغلاق الاستعلام السابق

            $stmt_reader = $conn->prepare("SELECT id, full_name, password FROM readers WHERE email = ? LIMIT 1");
            $stmt_reader->bind_param("s", $email);
            $stmt_reader->execute();
            $result_reader = $stmt_reader->get_result();

            if ($result_reader->num_rows > 0) {
                // الحساب موجود كـ ( قارئ )
                $row = $result_reader->fetch_assoc();

                if (password_verify($password, $row["password"])) {
                    // كلمة المرور صحيحة
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["username"] = $row["full_name"];
                    // نستخدم الاسم الكامل للقارئ
                    $_SESSION["role"] = "reader";
                    // تعيين الدور يدوياً

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "كلمة المرور غير صحيحة.";
                }
            } else {
                $error = "لا يوجد حساب مسجل بهذا البريد الإلكتروني.";
            }
            $stmt_reader->close();
        }
    }
}
?>

<style>
/* الحفاظ على التصميم كما هو */
.login-container {
    max-width: 400px;
    margin: 80px auto;
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba( 0, 0, 0, 0.1 );
    font-family: 'Tajawal';
    text-align: center;
}
.login-container h2 {
    margin-bottom: 20px;
    color: #333;
}
.form-group {
    margin-bottom: 15px;
    text-align: right;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}
.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: 'Tajawal';
    box-sizing: border-box;
}
.btn-login {
    width: 100%;
    padding: 12px;
    background: #2c3e50;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
}
.btn-login:hover {
    background: #1a252f;
}
.error-msg {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.register-link {
    margin-top: 20px;
    font-size: 14px;
    color: #666;
}
.register-link a {
    color: #e3ce8a;
    font-weight: bold;
    text-decoration: none;
}
</style>

<main>
<div class = "login-container">
<h2>🔐 تسجيل الدخول</h2>

<?php if (!empty($error)): ?>
<div class = "error-msg"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method = "POST" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<div class = "form-group">
<label>البريد الإلكتروني:</label>
<input type = "email" name = "email" required placeholder = "email@example.com">
</div>

<div class = "form-group">
<label>كلمة المرور:</label>
<input type = "password" name = "password" required placeholder = "********">
</div>

<button type = "submit" class = "btn-login">دخول</button>
</form>

<div class = "register-link">
ليس لديك حساب؟ <a href = "register.php">أنشئ حساباً جديداً</a>
</div>
</div>
</main>

<?php include "footer.php";
?>
