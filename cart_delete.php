<?php 
    session_start();
    require_once "config.php";

    if(!isset($_GET["id"])) {
        $_SESSION["error_message"] = "ไม่พบรายการที่ต้องการลบ";
        header("Location: cart_customer.php");
        exit();
    }

    $id = intval($_GET["id"]);
    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = (isset($_SESSION["user_id"])) ? null : session_id();

    try {        
        $conn -> begin_transaction();

        if($user_id !== null) {
            $query_cart_id = $conn -> prepare("SELECT * FROM carts JOIN products ON carts.product_id = products.id WHERE carts.id = ? AND user_id = ?");
            $query_cart_id -> bind_param("ii", $id, $user_id);
        } else {
            $query_cart_id = $conn -> prepare("SELECT * FROM carts JOIN products ON carts.product_id = products.id WHERE carts.id = ? AND guest_id = ? AND user_id IS NULL");
            $query_cart_id -> bind_param("is", $id, $guest_id);
        }
    
        $query_cart_id -> execute();
        $result_query_cart = $query_cart_id -> get_result();

        if($result_query_cart -> num_rows > 0) {
            $cart = $result_query_cart -> fetch_assoc();
            $product_name = $cart["name"];
        
            $delete_carts_items_stmt = $conn -> prepare("DELETE FROM carts WHERE id = ?");
            $delete_carts_items_stmt -> bind_param("i", $id);
            $delete_carts_items_stmt -> execute();

            $conn -> commit();
            $_SESSION["success_message"] = "ลบ " . $cart["name"] . " ( หมายเลขเมนู " . $product_name . " )" . " สำเร็จ";
        } else {
            $_SESSION["error_message"] = "ไม่พบรายการที่ต้องการลบ กรุณาลองใหม่อีกครั้ง";
        }
    } catch(Exception $e) {
        $conn -> rollback();
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด" . $e -> getMessage();
    }

    header("Location: cart_customer.php");
    exit();
?>  