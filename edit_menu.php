<?php
session_start();
require_once "config.php";

if (isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่ามีการกด update มามั้ย
if (!isset($_POST["update"])) {
    header("Location: index.php");
    exit();
}

// รับค่าจาก form
$id = intval($_POST["id"] ?? 0);
$name = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$price = floatval($_POST["price"] ?? 0);

try {
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    
        $old_query = $conn->prepare("SELECT image_url FROM products WHERE id=?");
        $old_query->bind_param("i", $id);
        $old_query->execute();
        $old_result = $old_query->get_result();
        
        if ($old_result->num_rows > 0) {
            $old_data = $old_result->fetch_assoc();
            if (!empty($old_data["image_url"]) && file_exists("image/" . $old_data["image_url"])) {
                @unlink("image/" . $old_data["image_url"]);
            }
        }
        $old_query->close();

        $image = uniqid() . "_" . time();
        
        // อัปโหลดไฟล์
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], "image/" . $image)) {
            $_SESSION["error_message"] = "ไม่สามารถอัปโหลดรูปภาพได้";
            header("Location: index.php");
            exit();
        }

        // Update พร้อมรูปภาพ
        $sql = "UPDATE products SET name= ?, description=? , price= ?, image_url= ? WHERE id= ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssdsi", $name, $description, $price, $image, $id);
        
    } else {
        // edit รูปแบบไม่ใส่รูปใหม่ก็ไม่ต้อง update field image_url 
        $sql = "UPDATE products SET name=?, description=?, price=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssdi", $name, $description, $price, $id);
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION["success_message"] = "แก้ไขเมนูสำเร็จ";
        } else {
            $_SESSION["message"] = "ไม่มีการเปลี่ยนแปลงข้อมูล";
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาด: " . $e->getMessage();
} finally {
    $conn->close();
}

header("Location: index.php");
exit();
?>