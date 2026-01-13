<?php 
    session_start();
    require_once "config.php";

    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = isset($user_id) ? null : session_id();

    try {

        $conn -> begin_transaction();

        $sql_cart_total = 
        " SELECT SUM(products.price * carts.quantity) AS total_price
                    FROM carts
                JOIN products ON carts.product_id = products.id
                WHERE " . ($user_id ? "carts.user_id = ?" : "carts.guest_id = ?");
        $cart_total_stmt = $conn -> prepare($sql_cart_total);

        if($user_id !== null) {
            $cart_total_stmt -> bind_param("i", $user_id);
        } else {
            $cart_total_stmt -> bind_param("s", $guest_id);
        }

        $cart_total_stmt -> execute();

        $result_cart_total = $cart_total_stmt -> get_result();
        $cart_total = $result_cart_total -> fetch_assoc();

        if(!$cart_total["total_price"]) {
            throw new Exception("เกิดข้อผิดพลาดเกี่ยวกับคำสั่งซื้อ");
        }

        $total_price = $cart_total["total_price"];

        $final_price = $user_id ? $total_price * 0.9 : $total_price;

        // insert orders ก่อน

        $sql_orders = "INSERT INTO orders (user_id, guest_id, order_date, total_price, status) VALUES (?, ?, NOW(), ?, 'preparing')";
        $orders_insert_stmt = $conn -> prepare($sql_orders);
        $orders_insert_stmt -> bind_param("isd", $user_id, $guest_id, $final_price);
        $orders_insert_stmt -> execute();

        // ดึง id ที่มีการ insert ล่าสุดก็คือที่ table orders เมื่อกี้
        $order_id = $conn -> insert_id;

        // แล้วต่อด้วยการ insert order_items โดยไป SELECT ข้อมูลมาจาก table carts
        $sql_carts_to_order_items = 
        " INSERT INTO 
            order_items (order_id, product_id, product_option, sweet_level, quantity, unit_price)
                SELECT ?, carts.product_id, carts.product_option, carts.sweet_level, carts.quantity, products.price
                FROM carts
                JOIN products ON carts.product_id = products.id
            WHERE " . ($user_id ? "carts.user_id = ?" : "carts.guest_id = ?");

        $move_table_stmt = $conn -> prepare($sql_carts_to_order_items);

        if($user_id !== null) {
            $move_table_stmt -> bind_param("ii", $order_id, $user_id);
        } else {
            $move_table_stmt -> bind_param("is", $order_id, $guest_id);
        }

        $move_table_stmt -> execute();

        // Payments
        $sql_insert_payments = " INSERT INTO payments (order_id, amount_paid, payment_date) VALUES (?, ?, NOW()) ";
        $payments_insert_stmt = $conn -> prepare($sql_insert_payments);
        $payments_insert_stmt -> bind_param("id", $order_id, $final_price);
        $payments_insert_stmt -> execute();

        // Clear ตะกร้าหลังจาก user or guest ทำการกดสั่งซื้อ
        $sql_clear_cart = "DELETE FROM carts WHERE " . ($user_id ? "user_id = ?" : "guest_id = ?");
        $clear_cart_stmt = $conn -> prepare($sql_clear_cart);

        if($user_id !== null) {
            $clear_cart_stmt -> bind_param("i", $user_id);
        } else {
            $clear_cart_stmt -> bind_param("s", $guest_id);
        }

        $clear_cart_stmt -> execute();
        $_SESSION["success_message"] = "ชำระเงินสำเร็จ";

        $conn -> commit();

        header("Location: order_status.php");
        exit();

    } catch (Exception $e) {
        $conn -> rollback();
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด" . $e -> getMessage();
        header("Location: cart_customer.php");
        exit();
    }
 
?>