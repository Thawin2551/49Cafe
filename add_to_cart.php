<?php

use LDAP\Result;

    session_start();
    require_once "config.php";

    if(!isset($_POST["confirm_order"])) {
        header("Location: index.php");
        exit();
    }

    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = (isset($_SESSION["user_id"])) ? null : session_id();
    
    $product_id = intval($_POST["product_id"] ?? 0);
    $quantity = intval($_POST["quantity"] ?? 0);
    $product_option = $_POST["product_option"] ?? "";
    $sweet_level = $_POST["sweet_level"] ?? "";

    try {

        $conn -> begin_transaction();

        $query_product = $conn -> prepare("SELECT * FROM products WHERE id = ?");
        $query_product -> bind_param("i", $product_id);
        $query_product -> execute();
        $result_query = $query_product -> get_result();

        if($result_query -> num_rows === 0) {
            throw new Exception("ไม่เจอสินค้าหมายเลข " . $product_id);
        }

        $product = $result_query -> fetch_assoc();
        $product_name = $product["name"];
        $unit_price = $product["price"];

        $final_price = $unit_price; // ที่ไป query มาจาก table product column product_option

        if($_POST["product_option"] === "hot") {
            $final_price = $unit_price - 5;
            
        } elseif($_POST["product_option"] === "frappe") {
            $final_price = $unit_price + 5; 
        } 
        
        // ราคารวมของเครื่องดื่ม * จำนวนแก้ว
        $total_price = $final_price * $quantity;

        // เช็คเมนูซ้ำว่าลูกค้าเพิ่มเข้าไปในตะกร้ายัง
        if($user_id !== null) {
            $check_item = $conn -> prepare("SELECT * FROM carts WHERE user_id = ? AND product_id = ? AND product_option = ? AND sweet_level = ?");
            $check_item -> bind_param("iiss", $user_id, $product_id, $product_option, $sweet_level);
        } else {
            $check_item = $conn -> prepare("SELECT * FROM carts WHERE guest_id = ? AND product_id = ? AND product_option = ? AND sweet_level = ?");
            $check_item -> bind_param("siss", $guest_id, $product_id, $product_option, $sweet_level);
        }
    
        $check_item-> execute();
        $result_check_item = $check_item -> get_result();
        
        if($result_check_item -> num_rows > 0) {
            $check_cart = $result_check_item -> fetch_assoc();
            $new_quantity = $check_cart["quantity"] + $quantity;
            $cart_id = $check_cart["id"];

            $update_sql = ("UPDATE carts SET quantity = ?, unit_price = ? WHERE id = ?");
            $update_cart_stmt = $conn -> prepare($update_sql);
            $update_cart_stmt -> bind_param('idi', $new_quantity, $unit_price, $cart_id);
            $update_cart_stmt -> execute();

        } else {
        // INSERT แต่ละเมนูเข้าไปเก็บไว้ใน carts เป็นตารางชั่วคราวไว้เก็บสินค้าที่ user เลือก
             $sql_insert_carts = 
            " INSERT INTO carts (user_id, guest_id, product_id, product_option, sweet_level, quantity, unit_price, add_to_cart_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW()) ";

            $insert_carts_stmt = $conn -> prepare($sql_insert_carts);
            $insert_carts_stmt -> bind_param("isissid", $user_id, $guest_id, $product_id, $product_option, $sweet_level,  $quantity, $unit_price);
            $insert_carts_stmt -> execute();
        }

        $conn -> commit();
        $_SESSION["success_message"] = "เพิ่ม " . $product_name . " เสร็จสิ้น";

        if($update_cart_stmt) {
            $_SESSION["message"] = "ทำการเพิ่ม " . $product_name . " อีก 1 รายการ";
        }

        header("Location: index.php");
        exit();

    } catch(Exception $e) {
        $conn -> rollback();
        $_SESSION["error_message"] = "เกิดข้อผิดพลาดขึ้น" . $e -> getMessage();

        header("Location: index.php");
        exit();
    }
?>