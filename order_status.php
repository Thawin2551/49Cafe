<?php
session_start();
require_once "config.php";

$user_id = $_SESSION["user_id"] ?? null;
$guest_id = isset($user_id) ? null : session_id();

try {
    // ปรับ SQL เพื่อดึงเลข Order ID มาโชว์ และป้องกันชื่อคอลัมน์ ID ตีกัน

    $sql_order_status_stmt = "SELECT status FROM orders WHERE " . ($user_id ? "orders.user_id = ?" : "orders.guest_id = ?");
    $order_status_stmt = $conn -> prepare($sql_order_status_stmt);
    
    if($user_id !== null) {
        $order_status_stmt -> bind_param("i", $user_id); 
    } else {
        $order_status_stmt -> bind_param("s", $guest_id); 
    }

    $order_status_stmt -> execute();
    $result_status = $order_status_stmt -> get_result();
    
    $sql_order_items_stmt =
        "SELECT *, orders.id AS main_order_id FROM orders
            INNER JOIN order_items ON orders.id = order_items.order_id
            INNER JOIN products ON order_items.product_id = products.id
                WHERE " . ($user_id ? "orders.user_id = ?" : "orders.guest_id = ?") . 
        " ORDER BY orders.order_date DESC";
        
    $order_items_stmt = $conn->prepare($sql_order_items_stmt);

    if($user_id !== null) {
        $order_items_stmt -> bind_param("i", $user_id); 
    } else {
        $order_items_stmt -> bind_param("s", $guest_id); 
    }
    
    $order_items_stmt -> execute();
    $result_order_items = $order_items_stmt -> get_result();

    $sql_total_stmt = 
    " SELECT *, SUM(order_items.unit_price * order_items.quantity) AS total_price, 
                SUM(order_items.quantity) AS total_quantity 
        FROM order_items 
        JOIN orders ON order_items.order_id = orders.id 
        WHERE  " . ($user_id ? "orders.user_id = ?" : "orders.guest_id = ?");

    $total_stmt = $conn -> prepare($sql_total_stmt);

    if($user_id !== null) {
        $total_stmt -> bind_param("i", $user_id);
    } else {
        $total_stmt -> bind_param("s", $guest_id);
    }

    
    $total_stmt -> execute();
    $total_result = $total_stmt -> get_result();

    // ดึงข้อมูลแถวแรกออกมาเพื่อเอาเลข Order ID มาแสดงที่หัวข้อ
    // fetch_all คือการดึงข้อมูลออกมาหมดเลยจาก table
    // แล้วจะไม่สามารถใช้ fetch_assoc() ดึงอีกได้เพราะทำการ fetch_all ออกมาก่อนแล้วทำให้ข้อมูลว่างเปล่า
    // เลยเช็ค status ไม่เจอ
    $item = $result_order_items->fetch_all(MYSQLI_ASSOC);
    $first_item = $item[0] ?? null;

} catch (Exception $e) {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาดขึ้น " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>

    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
</head>

<body>
    <?php require_once "navbar.php" ?>

    <!-- ส่วนแสดง Alert Message -->
    <?php if (!empty($_SESSION["success_message"]) || !empty($_SESSION["error_message"]) || !empty($_SESSION["message"])): ?>
        <div id="alert-message" class="fixed top-0 w-full text-white text-lg text-center py-5 z-50 transition-opacity duration-500 
            <?php echo !empty($_SESSION['success_message']) ? 'bg-green-500' : (!empty($_SESSION['error_message']) ? 'bg-red-500' : 'bg-yellow-500'); ?>">
            <?php 
                echo $_SESSION["success_message"] ?? $_SESSION["error_message"] ?? $_SESSION["message"];
                unset($_SESSION["success_message"], $_SESSION["error_message"], $_SESSION["message"]);
            ?>
        </div>
    <?php endif; ?>

    <div class="p-5 container shadow-xl max-w-lg mx-auto min-h-screen bg-white">
        <div class=" px-5 text-center flex flex-col items-center">
            <h1 class="text-2xl font-bold mb-6">
                หมายเลขออเดอร์ #<?php echo $first_item ? $first_item["main_order_id"] : '-'; ?>
            </h1>
            <!-- ทำหน้า order_status ให้เสร็จภายในวันที 12 มกรา -->

            <?php if($first_item["status"] === "preparing"): ?>
                <div class="badge badge-warning text-white gap-2 py-4 px-6 font-semibold">
                    <span class="loading loading-spinner loading-xs"></span>
                    กำลังปรุงอาหาร
                </div>
                
                <div class="status-img-container py-10">
                    <img class="w-full h-auto" src="image/preparing-status.png" alt="กำลังปรุงอาหาร">
                </div>

                <ul class="steps steps-horizontal w-full my-6">
                    <li data-content="✓" class="step step-primary font-medium">กำลังปรุง</li>
                    <li data-content="2" class="step">รอเสิร์ฟ</li>
                    <li data-content="3" class="step">เสร็จสิ้น</li>
                </ul>

            <?php elseif($first_item["status"] === "ready"): ?>
                <div class="badge badge-primary gap-2 py-4 px-6 font-semibold shadow-lg">
                    <i class="fas fa-bell"></i>
                    อาหารพร้อมเสิร์ฟแล้ว!
                </div>
                
                <div class="status-img-container py-10">
                    <img class="w-full h-auto" src="image/ready_status.png" alt="พร้อมเสิร์ฟ">
                </div>

                <ul class="steps steps-horizontal w-full my-6">
                    <li data-content="✓" class="step step-primary">กำลังปรุง</li>
                    <li data-content="✓" class="step step-primary font-medium text-primary">รอเสิร์ฟ</li>
                    <li data-content="3" class="step">เสร็จสิ้น</li>
                </ul>

            <?php elseif($first_item["status"] === "completed"): ?>
                <div class="badge badge-success text-white gap-2 py-4 px-6 font-semibold shadow-lg">
                    <i class="fas fa-check-circle"></i>
                    ทำรายการเสร็จสิ้น
                </div>
                
                <div class="status-img-container opacity-90 scale-95 transition-all">
                    <img class="w-full h-auto" src="image/completed_status.png" alt="เสร็จสิ้น">
                </div>

                <ul class="steps steps-horizontal w-full my-6">
                    <li data-content="✓" class="step step-primary">กำลังปรุง</li>
                    <li data-content="✓" class="step step-primary">รอเสิร์ฟ</li>
                    <li data-content="✓" class="step step-primary font-medium text-success">เสร็จสิ้น</li>
                </ul>
            <?php endif; ?>
            
        </div>
    
        <?php if(!empty($item)): ?>
            <div class="max-w-md mx-auto">
                <h2 class="font-semibold text-lg mb-3 border-b pb-2">สรุปรายการอาหาร</h2>
                
                <!-- กล่องครอบรายการอาหาร (อยู่นอกลูป) -->
                <div class="bg-base-200 shadow-sm rounded-xl overflow-hidden">
                    <?php foreach($item as $item): ?>
                        <div class="p-4 border-b border-base-300 last:border-none hover:bg-base-300 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="font-medium"><?php echo $item["name"] ?></div>
                                <div class="flex items-center gap-4">
                                    <div class="text-error font-semibold">
                                        <?php echo number_format($item["unit_price"] * $item["quantity"], 2) ?> ฿
                                    </div>
                                    <div class="text-success">
                                        x <?php echo $item["quantity"] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                

                <?php if($total_result -> num_rows > 0) :?>
                    <?php while($total = $total_result -> fetch_assoc()): ?>    
                        <div class="max-w-md mx-auto pt-6">
                            <div class="flex justify-between items-center py-1 ">
                                <div class="text-lg">
                                    <h1 class="font-semibold">ยอดสุทธิ </h1>
                                </div>
                                <div class="text-error font-semibold ">
                                    <?php echo number_format($total["total_price"],2) ?> ฿
                                </div>
                            </div>
                            <div class="flex justify-between items-center py-1  border-b pb-2">
                                <div class="text-lg">
                                    <h1 class="font-semibold">จำนวน </h1>
                                </div>
                                <div class="text-primary font-semibold ">
                                    <?php echo number_format($total["total_quantity"]) ?> รายการ
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <div class="mt-6">
                    <a href="index.php" class="btn btn-primary w-full text-white">กลับหน้าหลัก</a>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-10">
                <p class="text-gray-500">ไม่พบข้อมูลออเดอร์</p>
            </div>
        <?php endif; ?>
    </div>

<script src="js/cart_customer.js"></script>
<?php require_once "footer.php" ?>

</body>
</html>