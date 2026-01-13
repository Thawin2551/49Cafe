<?php 
session_start();
require_once "config.php";

if (isset($_SESSION["id"]) || $_SESSION["role"] !== "admin") {
    $_SESSION["error_message"] = "คุณไม่มีสิทธิ์ในการลบ";
    header("Location: index.php");
    exit();
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION["error_message"] = "ไม่พบ ID สินค้า";
    header("Location: index.php#menu_cafe");
    exit();
}

$id = intval($_GET["id"]);

// เช็คว่า id ของ products นี้มีจริงๆใช่มั้ย
if ($id <= 0) {
    $_SESSION["error_message"] = "ID สินค้าไม่ถูกต้อง";
    header("Location: index.php#menu_cafe");
    exit();
}

try {
    $image_query = $conn -> prepare("SELECT image_url FROM products WHERE id = ?");
    $image_query -> bind_param("i", $id);
    $image_query -> execute();
    $image_query_result = $image_query -> get_result();

    if($image_query_result -> num_rows > 0) {
        $product = $image_query_result -> fetch_assoc();

        $delete_stmt = $conn -> prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt -> bind_param("i", $id);
        
        if($delete_stmt -> execute()) {
            if(!empty($product["image_url"]) && file_exists("image/" . $product["image_url"])) {
                @unlink("image/" . $product["image_url"]);
            }
            $_SESSION["success_message"] = "ลบเมนูสำเร็จ";
        } else {
            throw new Exception("ไม่สามารถลบเมนูได้" . $delete_stmt -> error);
        }
        $delete_stmt -> close();
    } else {
        $_SESSION["error_message"] = "ไม่มีสินค้านี้";
    }
    $image_query -> close();
} catch(Exception $e) {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาด" . $e -> getMessage();
} finally {
    $conn -> close();
}

    header("Location: index.php#menu_cafe");
    exit();
?>