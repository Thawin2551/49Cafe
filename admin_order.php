<?php 
    session_start();
    require_once "config.php";

    // ตรวจสอบสิทธิ์ Admin
    if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
        header("Location: index.php");
        exit();
    }

    // อัพเดต status
    if(isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $status, $order_id);
        if($update_stmt->execute()) {
            $_SESSION["success_message"] = "อัปเดตสถานะออเดอร์ #$order_id เรียบร้อยแล้ว";
        }
    }

    // ล้างออเดอร์ Guest 
    $cleanup_sql = "DELETE FROM orders WHERE user_id IS NULL AND status = 'pending' AND order_date < NOW() - INTERVAL 1 DAY";
    $conn->query($cleanup_sql);

    try {
        $query_orders = $conn->query("SELECT orders.*, users.username as member_name 
                                     FROM orders 
                                     LEFT JOIN users ON orders.user_id = users.id 
                                     ORDER BY orders.order_date DESC");
        $result_orders = $query_orders;
    } catch(Exception $e) {
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผู้ดูแล | จัดการออเดอร์</title> 
    
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">
    
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
</head>
<body class="bg-gray-50">

    <?php require_once "sidebar/admin_sidebar_top.php" ?>  

    <!-- แจ้งเตือนข้อความ -->
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <?php if(!empty($_SESSION["success_message"])): ?>
            <div id="alert-message" class="alert alert-success text-white shadow-lg mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span><?php echo $_SESSION["success_message"]; unset($_SESSION["success_message"]); ?></span>
            </div>
        <?php endif; ?>

        <?php if(!empty($_SESSION["error_message"])): ?>
            <div id="alert-message" class="alert alert-error text-white shadow-lg mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span><?php echo $_SESSION["error_message"]; unset($_SESSION["error_message"]); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="my-12 px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2">รายการออเดอร์ทั้งหมด</h1>
            <p class="text-gray-500">สถานะการสั่งซื้อจากลูกค้าสมาชิกและบุคคลทั่วไป (Guest)</p>
        </div>
        
        <div class="my-4 font-semibold badge badge-primary p-4">
            เลื่อนทางขวาเพื่อดูรายละเอียดออเดอร์
        </div>
        
        <div class="">
            <table class="table table-zebra w-full text-center">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th>หมายเลขออเดอร์</th>
                        <th>ประเภทลูกค้า</th>
                        <th>ชื่อลูกค้า / รหัส Guest</th>
                        <th>รายการอาหาร</th>
                        <th>ราคารวม</th>
                        <th>สถานะ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result_orders && $result_orders->num_rows > 0): ?>
                        <?php while($order = $result_orders->fetch_assoc()): ?>
                            <tr class="hover">
                                <td class="font-bold">#<?= $order["id"] ?></td>
                                <td>
                                    <?php if(!empty($order['user_id'])): ?>
                                        <span class="text-green-700">สมาชิก</span>
                                    <?php else: ?>
                                        <span class="text-gray-500">บุคคลทั่วไป</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-semibold text-sm">
                                        <?php 
                                            if(!empty($order['user_id'])) {
                                                echo htmlspecialchars($order['member_name']);
                                            } else {
                                                echo "<span class='text-gray-400'>Guest: " . substr($order['guest_id'], 0, 8) . "...</span>";
                                            }
                                        ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 italic">
                                        <?= date('d/m/Y H:i', strtotime($order["order_date"])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown dropdown-hover">
                                        <label tabindex="0" class="btn btn-ghost btn-xs underline text-blue-500">ดูรายการ</label>
                                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 text-left border">
                                            <?php 
                                                $items_query = $conn->prepare("SELECT order_items.*, products.name 
                                                                             FROM order_items 
                                                                             JOIN products ON order_items.product_id = products.id 
                                                                             WHERE order_id = ?");
                                                $items_query->bind_param("i", $order['id']);
                                                $items_query->execute();
                                                $items_result = $items_query->get_result();
                                                while($item = $items_result->fetch_assoc()): ?>
                                                    <li class="text-xs py-1 border-b last:border-0">
                                                        <span><?= htmlspecialchars($item['name']) ?> <b class="text-primary">x <?= $item['quantity'] ?></b></span>
                                                    </li>
                                                <?php endwhile; ?>
                                        </ul>
                                    </div>
                                </td>
                                <td class="font-bold text-lg text-slate-700"><?= number_format($order["total_price"], 2) ?> ฿</td>
                                <td>
                                    <?php 
                                        $status = $order["status"];
                                        $badge = "badge-ghost"; $text = "ไม่ระบุ";
                                        if($status == "preparing") { $badge = "badge-warning text-white"; $text = "กำลังปรุง"; }
                                        elseif($status == "ready") { $badge = "badge-primary text-white"; $text = "พร้อมเสิร์ฟ"; }
                                        elseif($status == "completed") { $badge = "badge-success text-white"; $text = "เสร็จสิ้น"; }
                                    ?>
                                    <span class="badge <?= $badge ?> p-3 w-24 text-xs font-medium"><?= $text ?></span>
                                </td>
                                <td>
                                    <form action="admin_order.php" method="POST" class="flex items-center gap-2 justify-center">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="select select-bordered select-xs w-28 bg-gray-50">
                                            <option value="preparing" <?= $status == 'preparing' ? 'selected' : '' ?>>กำลังปรุง</option>
                                            <option value="ready" <?= $status == 'ready' ? 'selected' : '' ?>>พร้อมเสิร์ฟ</option>
                                            <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-xs btn-primary shadow-sm">บันทึก</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-20 text-gray-400 italic">--- ยังไม่มีรายการสั่งซื้อในระบบ ---</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require_once "sidebar/admin_sidebar_bottom.php" ?>  

    <script>
        setTimeout(() => {
            const msg = document.getElementById("alert-message");
            if (msg) {
                msg.style.opacity = "0";
                setTimeout(() => msg.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>