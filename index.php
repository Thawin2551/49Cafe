<?php 
    session_start();
    require_once "config.php";
    
    // menu_cafe.php ALL products query
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    $result = $stmt->get_result();

    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = isset($user_id) ? null : session_id();



?>

<!DOCTYPE html>
<html lang="en" data-theme="cupcake">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>

    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />

</head>
<body>
    <?php require_once "navbar.php" ?>

    <!-- ต้องไปไล่แก้ $_SESSION["message"] โดยเปลี่ยนตอนเก็บค่าให้เปป็น error success message ปกติ -->
     <!-- strpos คือการไล่หา string ตัวแรกของข้อความนั้นๆ -->
    <?php if(!empty($_SESSION["message"])): ?>
        <div id="alert-message" class="bg-yellow-500 text-white text-lg text-center py-5 z-100 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["message"]; unset($_SESSION["message"]) ?>
        </div>
    <?php elseif(!empty($_SESSION["success_message"])): ?>
         <div id="alert-message" class="bg-green-500 text-white text-lg text-center py-5 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["success_message"]; unset($_SESSION["success_message"]) ?>
        </div>
    <?php elseif(!empty($_SESSION["error_message"])): ?>
         <div id="alert-message" class="bg-red-500 text-white text-lg text-center py-5 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["error_message"]; unset($_SESSION["error_message"]) ?>
        </div>
    <?php else: ?>
        <div class="hidden"></div>
    <?php endif; ?>
        
    <!-- Components -->
    <?php require_once "home.php" ?>
    <?php require_once "menu_cafe.php" ?>
    <?php require_once "footer.php" ?>

    <script>
        const alert_message = document.getElementById("alert-message");
        if(alert_message) {
            setTimeout(() => {
                alert_message.style.opacity = "0";
                setTimeout(() => {
                    alert_message.style.display = "none";
                }, 500);
            }, 3000);
        }

        // ปิด modal หลัง submit สำเร็จ
        <?php if(isset($_GET['success'])): ?>
            document.getElementById("edit_modal_toggle").checked = false;
        <?php endif; ?>
    </script>
</body>
</html>