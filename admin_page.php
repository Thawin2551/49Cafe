<?php 
    session_start();
    require_once "config.php"; 

   if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
        header("Location: index.php");
        exit();
    }

    try {   

    } catch(Exception $e) {
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด " . $e -> getMessage();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผู้ดูแล | จัดการระบบ</title>
    
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    
    
</head>
<body>

    <?php require_once "sidebar/admin_sidebar_top.php" ?>  

    <header>
        <h1 class="text-2xl font-semibold text-center mt-10">
            Welcome Back <?php echo htmlspecialchars($_SESSION["username"]) ?>
        </h1>
        <h3 class="text-center">
            ตรงนี้เดี๋ยวทำ Admin Dashboard
        </h3>
    </header>
    <div class="stats shadow justify-center flex max-w-6xl mx-auto py-4">
        <div class="stat">
            <div class="stat-figure text-primary">
                <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                class="inline-block h-8 w-8 stroke-current"
                >
                <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                ></path>
            </svg>
        </div>
        <div class="stat-title">Total Likes</div>
        <div class="stat-value text-primary">25.6K</div>
        <div class="stat-desc">21% more than last month</div>
    </div>
    
    <div class="stat">
        <div class="stat-figure text-secondary">
            <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            class="inline-block h-8 w-8 stroke-current"
            >
            <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 10V3L4 14h7v7l9-11h-7z"
            ></path>
        </svg>
    </div>
    <div class="stat-title">Page Views</div>
    <div class="stat-value text-secondary">2.6M</div>
    <div class="stat-desc">21% more than last month</div>
</div>

<div class="stat">
    <div class="stat-figure text-secondary">
        <div class="avatar avatar-online">
            <div class="w-16 rounded-full">
                <img src="https://img.daisyui.com/images/profile/demo/anakeen@192.webp" />
            </div>
        </div>
    </div>
    <div class="stat-value">86%</div>
    <div class="stat-title">Tasks done</div>
    <div class="stat-desc text-secondary">31 tasks remaining</div>
</div>
</div>
<?php require_once "sidebar/admin_sidebar_bottom.php" ?>  
    <script src="js/cart_customer.js"></script>
</body>
</html>