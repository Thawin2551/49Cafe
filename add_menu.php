<?php 
    session_start();
    require_once "config.php";

    $message = "";

    if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
        header("Location: index.php");
        exit();
    }

    if(isset($_POST["submit"])) {
        $image = $_FILES["image"]["name"];
        $temp_location = $_FILES["image"]["tmp_name"];

        $target_dir = "image/";
        $target_location = $target_dir . basename($image);

        $category = $_POST["category"];
        $name = $_POST["name"];
        $description = $_POST["description"];
        $price = $_POST["price"];
        $has_sweetness = isset($_POST["has_sweetness"]) ? 1 : 0;
        $has_product_option = isset($_POST["has_product_option"]) ? 1 : 0;

        $insert_stmt = $conn -> prepare("INSERT INTO products 
        (category, has_sweetness, has_product_option, name, description, price, image_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt -> bind_param("siissds", $category, $has_sweetness, $has_product_option, $name, $description, $price, $image);


            if($insert_stmt -> execute()) {
                if(move_uploaded_file($temp_location, $target_location)) {
                    $_SESSION["success_message"] = "ดำเนินการเพิ่ม เมนูเครื่องดื่ม/อาหาร แล้ว";
                } else {
                    $_SESSION["success_message"] = "เพิ่มเมนูแล้ว (ไม่มีรูปภาพ)";
                }
            } else {
                $_SESSION["error_message"] = "เกิดข้อผิดพลาดในการเพิ่มเมนู กรุณาลองใหม่อีกครั้ง";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผู้ดูแล | เพิ่มเมนู</title>
        
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    
</head>
<body>
    <?php require_once "sidebar/admin_sidebar_top.php" ?>  
  
    <div class="mt-10">
        <form action="add_menu.php" method="post" enctype="multipart/form-data" class="container mx-auto max-w-md bg-white p-10 rounded-lg md:shadow-xl">
            <?php if(!empty($_SESSION["success_message"])): ?>
                <div id="alert-message" class="alert alert-success text-white text-center py-3 mb-5 rounded-md transition-opacity duration-500">
                    <?php echo $_SESSION["success_message"] ?>
                    <?php unset($_SESSION["success_message"]) ?>
                </div>
            <?php endif; ?>
            <?php if(!empty($_SESSION["error_message"])): ?>
                <div id="alert-message" class="alert alert-error text-white text-center py-3 mb-5 rounded-md transition-opacity duration-500">
                    <?php echo $_SESSION["error_message"] ?>
                    <?php unset($_SESSION["error_message"]) ?>
                </div>
            <?php endif; ?>
            <h1 class="text-center text-2xl font-semibold mb-5">เพิ่มเมนู</h1> 

            <!-- Checkbox ว่าปั่น / หวานน้อย ได้มั้ย -->
            <div class="form-check my-1">
                <input type="checkbox" name="has_sweetness" id="sweet" value="1" class="form-check-input">
                <label for="sweet" class="form-check-label">อนุญาตให้ปรับความหวานได้</label>
            </div>

            <div class="form-check my-1">
                <input type="checkbox" name="has_product_option" id="ptype" value="1" class="form-check-input">
                <label for="ptype" class="form-check-label">อนุญาตให้เลือกประเภท (ร้อน/เย็น/ปั่น)</label>
            </div>
            <div>
            <select class="select select-success" name="category">
                <!-- condition แยกระหว่างเครื่องดื่มกับขนมหวาน -->
                <!-- แยกหมวดหมู่ระหว่าง ขนมกับเครื่องดื่มในโดยดึงมาจาก category -->
                <?php ?>
                <option disabled selected>ประเภทของเมนู</option>
                <option value="dessert">dessert</option>
                <option value="food">food</option>
                <option value="drinking">drinking</option>
            </select>
            </div>   
            <iv><input type="text" class="bg-gray-200 rounded-md  my-1 py-2 px-3 w-full"placeholder="Menu Name" name="name" realert alert-successiv>
            <div><input type="text" class="bg-gray-200 rounded-md  my-1 py-2 px-3 w-full"placeholder="Description" name="description"></div>
            <div><input type="number" class="bg-gray-200 rounded-md  my-1 py-2 px-3 w-full"placeholder="Price" name="price" required></div>
            <div><input type="file"name="image" class="bg-gray-200 rounded-md cursor-pointer my-1 py-2 px-3 w-1/2"><p class="font-semibold">เลือกรูปภาพ </p></div>
           <!-- Submit menu -->
            <div><input type="submit" name="submit" class="bg-green-500 text-white hover:bg-green-600 cursor-pointer my-5 rounded-md  my-1 py-2 px-3 w-full" value="เพิ่มเมนู"></div>
        </form>
    </div>
    <?php require_once "sidebar/admin_sidebar_bottom.php" ?>
    
    <script>
        const alert_message = document.getElementById("alert-message");
        if(alert_message) {
            setTimeout(() => {
                alert_message.style.opacity = "0";
                setTimeout(() => {
                    alert_message.style.display = "none";
                }, 500)
            }, 3000)
        }
    </script>
</body>
</html>