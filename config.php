<?php 
    $host = "localhost";
    $user = "root";
    $password = "";
    $db_name = "49_coffee_time";

    $conn = new mysqli($host, $user, $password, $db_name);

    if($conn -> connect_error) {
        die("Couldn't connected to database" . $conn -> error);
    }

    $conn -> set_charset("utf8"); // รองรับภาษาไทย

    function DeleteGuestSession($conn) {
        $days = 1;
        $sql_guest_delete = 
        " DELETE FROM orders
            WHERE user_id IS NULL
                AND status = 'pending'
            AND order_date < NOW() - INTERVAL $days DAY
        ";
        try {
            if($conn -> query($sql_guest_delete)) {
                // นับจำนวน guest session ที่ลบออกไป
                    $delete_count = $conn -> affected_rows;
                return $delete_count;
            }
        } catch(Exception $e) {
            $_SESSION["error_message"] = "เกิดข้อผิดพลาดขึ้น" . $e -> getMessage();
        }
        return 0;
    }

    // Function เอาไว้แสดงผลจำนวนสินค้าที่อยู่ในตะกร้าของ customer
    // เหตุผลที่ต้องเอาไว้ใน config.php เพราะไฟล์ navbar.php จะต้องเรียกใช้ทุกๆหน้า
    function getCartQuantity($conn ,$id = null) {

        if($id !== null) {
            $cart_quantity_sql = "SELECT SUM(quantity) AS total_quantity FROM carts WHERE user_id = ?";
            $cart_quantity_query = $conn ->prepare($cart_quantity_sql);
            $cart_quantity_query -> bind_param("i", $id);

        } else {
            $guest_id = session_id();
            $cart_quantity_sql = "SELECT SUM(quantity) AS total_quantity FROM carts WHERE guest_id = ? AND user_id IS NULL";
            $cart_quantity_query = $conn ->prepare($cart_quantity_sql);
            $cart_quantity_query -> bind_param("s", $guest_id);
        }

        $cart_quantity_query -> execute();
        $cart_quantity_query_result = $cart_quantity_query -> get_result();

        if($cart_quantity_query_result -> num_rows > 0) {
            $cart = $cart_quantity_query_result -> fetch_assoc();
            $_SESSION["total_quantity"] = $cart["total_quantity"];
        }

        $total_quantity = $cart["total_quantity"];
        return $total_quantity;

    }

    if(isset($_SESSION["user_id"])) {
        $current_cart_quantity = getCartQuantity($conn, $_SESSION["user_id"]);
    } else { 
        $current_cart_quantity = getCartQuantity($conn, null);
    }

?>