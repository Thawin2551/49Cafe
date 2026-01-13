<?php 

    session_start();
    require_once "config.php";

    header("Content-Type: application/json");

    $cart_id = $_POST["cart_id"];
    $quantity = $_POST["quantity"];
    
    if(!$cart_id || !$quantity || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        exit;
    }

    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = isset($user_id) ? null : session_id();

    try {

        // เช็คว่าตะกร้านี้เป็ฯของใคร user หรือ guest 
        $sql_check_id = 
        " SELECT *, id AS cart_id FROM carts
            WHERE id = ? 
                AND (
                    ( user_id = ? AND user_id IS NOT NULL )
                     OR
                    ( guest_id = ? AND user_id IS NULL)
        )";
        $check_id_stmt = $conn -> prepare($sql_check_id);
        $check_id_stmt -> bind_param("iis", $cart_id, $user_id, $guest_id);
        $check_id_stmt -> execute();

        $result_check_id_stmt = $check_id_stmt -> get_result();
        
        if($result_check_id_stmt -> num_rows === 0) {
            echo json_encode(
                [
                'success' => false, 
                'message ' =>   'ไม่พข้อมูลสินค้า'
                ]
            );
            exit;
        }

        $cart = $result_check_id_stmt -> fetch_assoc();

        $sql_update_cart = "UPDATE carts SET quantity = ? WHERE id = ?";
        $update_cart_stmt = $conn -> prepare($sql_update_cart);
        $update_cart_stmt -> bind_param("ii",$quantity, $cart_id);

        if($update_cart_stmt -> execute()) {
            // เช็คราคาแต่ละชิ้นของ
            $sql_product_price = "SELECT price FROM products WHERE id = ?";
            $query_product_price = $conn -> prepare($sql_product_price);
            $query_product_price -> bind_param("i", $cart["product_id"]);
            $query_product_price -> execute();

            $result_product_price = $query_product_price -> get_result();
            $product_price = $result_product_price -> fetch_assoc();

            $unit_price = $product_price["price"];
            $sub_total = $unit_price * $quantity;

            // คำนวณราคารวม กับ จำนวนรวมโดยใช้ SUM
            $sql_total =
            "   SELECT SUM(products.price * carts.quantity) AS total_price,
                        SUM(carts.quantity) AS total_quantity
                    FROM carts
                        JOIN products ON carts.product_id = products.id
                    WHERE (carts.user_id = ? AND carts.user_id IS NOT NULL)  
                        OR
                        (carts.guest_id = ? AND carts.user_id IS NULL)
            ";

            $total_stmt = $conn -> prepare($sql_total);
            $total_stmt -> bind_param("is", $user_id, $guest_id);
            $total_stmt -> execute();

            $total_result = $total_stmt -> get_result();
            $totals = $total_result -> fetch_assoc();

            $total_price = $totals["total_price"];
            $total_quantity = $totals["total_quantity"];

            // เช็คว่าเป็น member มั้ยถ้าเป็นจะลด 10%
            $is_member = isset($_SESSION["user_id"]);
            $discount = $is_member ? ($total_price * 0.1) : 0;
            $net_price = $total_price - $discount;

            // แปลง array เป็น json fetch api ไปให้ database แล้วดึงกลับมา
            echo json_encode([
                'success' => true,
                'subtotal' => floatval($sub_total),
                'total_price' => floatval($total_price),
                'total_quantity' => intval($total_quantity),
                'discount' => floatval($discount),
                'net_price' => floatval($net_price)
            ]);
        } else {
            echo json_encode(
                [
                'success' => false, 
                'message ' => 'ไม่สามารถเพิ่ม / ลดสินค้าได้ กรุณาลองใหม่อีกครั้ง'
                ]
            );
        }

    } catch(Exception $e) {
        echo json_encode(
            [
             'success' => false, 
             'message ' =>   'Error: ' . $e -> getMessage()
            ]
        );
    }

$conn -> close();
