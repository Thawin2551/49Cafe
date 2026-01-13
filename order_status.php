<?php
session_start();
require_once "config.php";

$user_id = $_SESSION["user_id"] ?? null;
$guest_id = isset($user_id) ? null : session_id();

// order_id ล่าสุดที่ user หรือ guest สั่งมา
$last_order_id = $_SESSION["last_order_id"] ?? null;

if (!$last_order_id) {
    $sql_find_last = "SELECT id FROM orders WHERE " . ($user_id ? "user_id = ?" : "guest_id = ?") . " ORDER BY id DESC LIMIT 1";
    $find_order_id_stmt = $conn->prepare($sql_find_last);
    if ($user_id) {
        $find_order_id_stmt->bind_param("i", $user_id);
    } else {
        $find_order_id_stmt->bind_param("s", $guest_id);
    }
    
    $find_order_id_stmt->execute();
    $res_last = $find_order_id_stmt->get_result();
    $row_last = $res_last->fetch_assoc();
    $last_order_id = $row_last['id'] ?? null;
}

try {
    if ($last_order_id) {
        
    // เอาเมนูนั้นๆ จาก order มาแสดงโดย join product order_items
    // เพื่อเอารายละเอียดมาแสดงผล
        $sql_order_items_stmt = "SELECT *, orders.id AS main_order_id FROM orders
                                 INNER JOIN order_items ON orders.id = order_items.order_id
                                 INNER JOIN products ON order_items.product_id = products.id
                                 WHERE orders.id = ?";
        
        $order_items_stmt = $conn->prepare($sql_order_items_stmt);
        $order_items_stmt->bind_param("i", $last_order_id);
        $order_items_stmt->execute();
        $result_order_items = $order_items_stmt->get_result();
        $items_array = $result_order_items->fetch_all(MYSQLI_ASSOC);
        $first_item = $items_array[0] ?? null;

    // คำนวณยอดรวมของทั้งออเดอร็
        $sql_total_stmt = "SELECT SUM(unit_price * quantity) AS total_price, 
                                 SUM(quantity) AS total_quantity 
                          FROM order_items 
                          WHERE order_id = ?";
        $total_stmt = $conn->prepare($sql_total_stmt);
        $total_stmt->bind_param("i", $last_order_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result();
        $total_data = $total_result->fetch_assoc();
    }
} catch (Exception $e) {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาดขึ้น " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการสั่งซื้อ</title>

    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
</head>
<body class="bg-gray-50">
    <?php require_once "navbar.php" ?>

    <div class="p-5 container shadow-xl max-w-lg mx-auto min-h-screen bg-white">
        <?php if ($first_item): ?>
            <div class="px-5 text-center flex flex-col items-center">
                <h1 class="text-2xl font-bold mb-6">
                    หมายเลขออเดอร์ #<?php echo $first_item["main_order_id"]; ?>
                </h1>

                <?php if($first_item["status"] === "preparing"): ?>
                    <div class="badge badge-warning text-white gap-2 py-4 px-6 font-semibold">
                        <span class="loading loading-spinner loading-xs"></span> กำลังปรุงอาหาร
                    </div>
                    <div class="py-10"><img class="w-full h-auto" src="image/preparing-status.png" alt="preparing"></div>
                    <ul class="steps steps-horizontal w-full my-6">
                        <li data-content="✓" class="step step-primary font-medium">กำลังปรุง</li>
                        <li data-content="2" class="step">รอเสิร์ฟ</li>
                        <li data-content="3" class="step">เสร็จสิ้น</li>
                    </ul>

                <?php elseif($first_item["status"] === "ready"): ?>
                    <div class="badge badge-primary gap-2 py-4 px-6 font-semibold shadow-lg">อาหารพร้อมเสิร์ฟแล้ว!</div>
                    <div class="py-10"><img class="w-full h-auto" src="image/ready_status.png" alt="ready"></div>
                    <ul class="steps steps-horizontal w-full my-6">
                        <li data-content="✓" class="step step-primary">กำลังปรุง</li>
                        <li data-content="✓" class="step step-primary font-medium">รอเสิร์ฟ</li>
                        <li data-content="3" class="step">เสร็จสิ้น</li>
                    </ul>

                <?php elseif($first_item["status"] === "completed"): ?>
                    <div class="badge badge-success text-white gap-2 py-4 px-6 font-semibold shadow-lg">ทำรายการเสร็จสิ้น</div>
                    <div class="py-10"><img class="w-full h-auto" src="image/completed_status.png" alt="completed"></div>
                    <ul class="steps steps-horizontal w-full my-6">
                        <li data-content="✓" class="step step-primary">กำลังปรุง</li>
                        <li data-content="✓" class="step step-primary">รอเสิร์ฟ</li>
                        <li data-content="✓" class="step step-success">เสร็จสิ้น</li>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="max-w-md mx-auto mt-4">
                <h2 class="font-semibold text-lg mb-3 border-b pb-2">สรุปรายการอาหาร</h2>
                <div class="bg-base-200 shadow-sm rounded-xl overflow-hidden">
                    <?php foreach($items_array as $row): ?>
                        <div class="p-4 border-b border-base-300 last:border-none">
                            <div class="flex items-center justify-between">
                                <div class="font-medium"><?php echo $row["name"] ?></div>
                                <div class="flex items-center gap-4">
                                    <div class="text-error font-semibold"><?php echo number_format($row["unit_price"] * $row["quantity"], 2) ?> ฿</div>
                                    <div class="text-success text-sm">x <?php echo $row["quantity"] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-6 border-t mt-4">
                    <div class="flex justify-between items-center py-1">
                        <span class="font-semibold text-lg">ยอดสุทธิ</span>
                        <span class="text-error font-bold text-xl"><?php echo number_format($total_data["total_price"], 2) ?> ฿</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="font-semibold">จำนวนทั้งหมด</span>
                        <span class="text-primary font-semibold"><?php echo $total_data["total_quantity"] ?> รายการ</span>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="index.php" class="btn btn-primary w-full text-white">กลับหน้าหลัก</a>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <p class="text-gray-500 text-lg">ไม่พบข้อมูลออเดอร์ที่กำลังรอดำเนินการ</p>
                <a href="index.php" class="btn btn-ghost mt-4 underline">ไปหน้าสั่งอาหาร</a>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once "footer.php" ?>
</body>
</html>