<?php 
    session_start();
    require_once "config.php";

    $message = "";

    if(isset($_SESSION["user_id"])) {
        header("Location: index.php");
        exit();
    }

    if(isset($_POST["submit"])) {
        $username  = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $role = "customer";

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $check_stmt = $conn -> prepare("SELECT * FROM users WHERE email = ? ");
        $check_stmt -> bind_param("s", $email);
        $check_stmt -> execute();
        $check_result = $check_stmt -> get_result();

        if($check_result -> num_rows > 0) {
            // Check email
            $message = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว";
        } else {
            $check_username_stmt = $conn -> prepare("SELECT * FROM users WHERE username = ?");
            $check_username_stmt -> bind_param("s", $username);
            $check_username_stmt -> execute();
            $check_username_result = $check_username_stmt -> get_result();
            if($check_username_result -> num_rows > 0) {
                // Check username
                $message = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว";
            } else {
                $insert_stmt = $conn -> prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insert_stmt -> bind_param("sss", $username, $email, $hashed_password);
                
                if($insert_stmt -> execute()) {
                    header("Location: login.php");
                    exit();
                } else {
                    header("Location: register.php");
                    $message = "มีข้อผิดพลาดเกิดขึ้น";
                    exit();
                }
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>

    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
     <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    
</head>
<body>
    <?php require_once "navbar.php" ?>
    
    <div
    class="hero min-h-screen"
    style="background-image: url(image/49front_shop.jpg);"
    >
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
        <form action="register.php" method="post" class="fieldset bg-base-200 border-base-300 rounded-box w-xs p-5 border p-4">
            <?php if(!empty($message)) : ?>
                <div id="alert-message" class="bg-red-500 text-white text-[16px] text-center py-3 mb-5 rounded-md transition-opacity duration-500">
                    <?php echo $message; 
                        unset($message);
                    ?>
                </div>
             <?php endif; ?>
            <legend class="fieldset-legend text-xl">Register</legend>

            <label class="label text-gray-600 text-[1rem]">Username</label>
            <input name="username" type="text" class="input" placeholder="Username" required>

            <label class="label text-gray-600 text-[1rem]">Email</label>
            <input name="email" type="email" class="input" placeholder="Email" required>

            <label class="label text-gray-600 text-[1rem]">Password</label>
            <input name="password" type="password" class="input" placeholder="Password" required>
            <select name="role" hidden>
                <option value="">Admin</option>
                <option value="">User</option>
            </select>
            <input name="submit" type="submit" value="สมัครสมาชิก" class="btn btn-neutral mt-4"></input>
            <div class="text-center text-[17px] mt-2"><h1>มีบัญชีแล้วใช่มั้ย ? ไปที่หน้า <a class="link link-primary hover:underline" href="login.php">เข้าสู่ระบบ</a></h1></div>
        </form>
    </div>
    </div>
    
    <!-- Alert Message Function Disappear -->
    <script>
        const alert_message = document.getElementById("alert-message");
        if(alert_message) {
            setTimeout(() => {
                alert_message.style.opacity = '0';

                setTimeout(() => {
                    alert_message.style.display = 'none';
                }, 500)
            }, 3000)
        }
    </script>

</body>
</html>

