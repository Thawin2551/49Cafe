<?php 
    session_start();
    require_once "config.php";

    if(isset($_SESSION["user_id"])) {
        header("Location: index.php");
        exit();
    }

    $message = "";
    if(isset($_POST["submit"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];

        $check_username = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check_username->bind_param("s", $username);
        $check_username->execute();
        $check_username_result = $check_username->get_result();
        
        if($check_username_result->num_rows > 0) {
            $rows = $check_username_result->fetch_assoc();
            
            if(password_verify($password, $rows["password"])) {
                $_SESSION["user_id"] = $rows["id"];
                $_SESSION["username"] = $rows["username"];
                $_SESSION["role"] = $rows["role"];
                
                if($rows["role"] === "admin") {
                    header("Location: admin_view_menu.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }      
            } else {
                $message = "รหัสหรือชื่อผู้ใช้งานไม่ถูกต้อง";
            }
        } else {
            $message = "รหัสหรือชื่อผู้ใช้งานไม่ถูกต้อง";
        }
        $check_username -> close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">
    
    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    
</head>
<body>
    <?php require_once "navbar.php" ?>
    
    <div class="hero min-h-screen" style="background-image: url(image/49cafe_new_view_menu.jpg);">
    
    <!-- ลด opacity ของรูปภาพโดยใช้ hero-overlay ของ daisyUI -->
    <div class="hero-overlay"></div>
    <div class="hero-content text-center">
    <form action="login.php" method="post" class="fieldset bg-base-200 border-base-300 rounded-box w-xs p-5 border p-4" >
        
        <!-- แสดงข้อความตอนเกิดข้อผิดพลาด -->
        <?php if(!empty($message)) : ?>
            <div id="alert-message" class="bg-red-500 text-white text-[16px] text-center py-3 mb-5 rounded-md transition-opacity duration-500">
                <?php echo $message; 
                    unset($message);
                ?>
            </div>
         <?php endif; ?>

        <legend class="fieldset-legend text-xl">Login</legend>
    
        <label class="label text-gray-600 text-[1rem]">Username</label>
        <input name="username" type="text" class="input" placeholder="Username"  required/>
    
        <label class="label text-gray-600 text-[1rem]">Password</label>
        <input name="password" type="password" class="input" placeholder="Password" required/>
    
        <input name="submit" type="submit" value="เข้าสู่ระบบ" class="btn btn-primary mt-4"></input>
        <div class="text-center text-[17px] mt-2"><h1>ยังไม่มีบัญชีใช่มั้ย ? <br> ไปที่หน้า  <a class="link link-success" href="register.php">สมัครสมาชิก</a></h1></div>
    </form>
    </div>
    
    </div>

    

    <script>
        const alert_message = document.getElementById("alert-message");
        if(alert_message) {
            setTimeout(() => {
                    alert_message.style.opacity = 0;
                setTimeout(() => {
                    alert_message.style.display = "none";
                }, 500)
            }, 3000)
        }
    </script>

</body>
</html>