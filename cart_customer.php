<?php
session_start();
require_once "config.php";

$user_id = $_SESSION["user_id"] ?? null;
$guest_id = (isset($user_id)) ? null : session_id();

$total_price = 0;
$total_quantity = 0;
$result_display_cart = null;

try {
    $sql_cart_and_products =
        " SELECT *,carts.id AS cart_id FROM carts 
            JOIN products ON carts.product_id = products.id
                WHERE (carts.user_id = ? AND carts.user_id IS NOT NULL)
                    OR (carts.guest_id = ? AND carts.guest_id IS NOT NULL)
        ";

    $display_item_stmt = $conn->prepare($sql_cart_and_products);
    $display_item_stmt->bind_param("is", $user_id, $guest_id);
    $display_item_stmt->execute();

    $result_display_cart = $display_item_stmt->get_result();
} catch (Exception $e) {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาด " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้ารถเข็น</title>
    
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <?php require_once "css/tailwind/style_tailwind.php" ?>
    <?php require_once "css/daisy/style_daisy_ui.php" ?>
    
</head>

<body>

    <?php require_once "navbar.php" ?>


    <?php if (!empty($_SESSION["message"])): ?>
        <div id="alert-message" class="bg-yellow-500 text-white text-lg text-center py-5 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["message"];
            unset($_SESSION["message"]) ?>
        </div>
    <?php elseif (!empty($_SESSION["success_message"])): ?>
        <div id="alert-message" class="bg-green-500 text-white text-lg text-center py-5 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["success_message"];
            unset($_SESSION["success_message"]) ?>
        </div>
    <?php elseif (!empty($_SESSION["error_message"])): ?>
        <div id="alert-message" class="bg-red-500 text-white text-lg text-center py-5 transition-opacity duration-500 sticky top-0">
            <?php echo $_SESSION["error_message"];
            unset($_SESSION["error_message"]) ?>
        </div>
    <?php else: ?>
        <div class="hidden"></div>
    <?php endif; ?>

    <?php if ($result_display_cart->num_rows > 0): ?>
        <div class="my-10">
            <h1 class="text-center font-bold text-xl">
                รายการทั้งหมด <span class="text-success"></span>
            </h1>
        </div>
        <div class="overflow-x-auto max-w-6xl md:mx-auto mb-20 mx-2.5">
            <table class="table">
                <!-- head -->
                <thead class="bg-gray-100">
                    <tr class="items-center">
                        <th class="text-center">เมนู</th>
                        <th class="text-center">รายละเอียด</th>
                        <th class="text-center">จำนวน / จัดการ</th>
                        <!-- <th class="text-center">หมายเลขรายการ</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cart = $result_display_cart->fetch_assoc()): ?>
                        <?php
                        $unit_price = $cart["unit_price"];
                        $quantity = $cart["quantity"];

                        $row_subtotal = $unit_price * $quantity;

                        $total_price = $total_price + $row_subtotal;
                        $total_quantity = $total_quantity + $quantity;

                        // กินข้าวเสร็จกลับมาทำ total checkout แล้วก็เพิ่มลดจำนวนใน cart ได้
                        ?>
                        <tr class="hover:bg-gray-200 duration-100 cart-item"
                            data-item-id="<?php echo $cart["cart_id"] ?>"
                            data-price="<?php echo $cart["unit_price"] ?>">
                            <!-- <td class="text-center">
                                # <?php echo $cart["cart_id"] ?>
                            </td> -->
                            <td class="text-start">
                                <div class="flex items-center gap-5">
                                    <div class="avatar">
                                        <div class="mask rounded-lg">
                                            <?php if (isset($cart["image_url"])): ?>
                                                <img
                                                    class="mask md:flex hidden md:w-20 md:h-20"
                                                    src=<?php echo "image/" . $cart["image_url"]  ?>
                                                    alt="Avatar Tailwind CSS Component" />
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Product -->
                                    <div>
                                        <div class="font-bold"><?php ?></div>
                                        <div class=""><span class="font-bold text-md md:text-[15px]"><?php echo $cart["name"] ?></span></div>
                                    </div>
                                </div>
                            </td>
                            <td class="items-center text-center">
                                <div class="flex flex-col gap-2 text-sm md:text-md">
                                    <div class="">
                                        <?php if (!empty($cart["product_option"]) || ($cart["sweet_level"])): ?>
                                            <div>
                                                <span class="font-semibold"><?php echo $cart["product_option"] ?></span>
                                            </div>
                                            <div>
                                                หวาน: <span class="font-semibold text-primary"><?php echo $cart["sweet_level"] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <span id="subtotal-<?php echo $cart["cart_id"] ?>">
                                            <?php echo number_format($row_subtotal, 2) ?> ฿
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="my-5 flex gap-3.5 md:flex-row md:items-center md:gap-4 justify-around">
                                <div class="flex items-center gap-3 justify-center">
                                    <button class="text-black hover:text-white btn btn-xs btn-error btn-circle btn-outline">-</button>
                                    <span id="quantity-<?php echo $cart["cart_id"] ?>"><?php echo $cart["quantity"] ?></span>
                                    <button class="text-black hover:text-white btn btn-xs btn-success btn-circle btn-outline">+</button>
                                </div>
                                <a class="btn btn-error text-white btn-sm md:btn-md"
                                    onclick="return confirm('ต้องการลบเมนูนี้ใช่หรือไม่ ?')"
                                    href="cart_delete.php?id=<?php echo $cart["cart_id"] ?>">
                                    <span class="hidden md:flex">
                                        ลบเมนู
                                    </span>
                                    <span class="flex md:hidden">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                            <path d="M166.2-16c-13.3 0-25.3 8.3-30 20.8L120 48 24 48C10.7 48 0 58.7 0 72S10.7 96 24 96l400 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-96 0-16.2-43.2C307.1-7.7 295.2-16 281.8-16L166.2-16zM32 144l0 304c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-304-48 0 0 304c0 8.8-7.2 16-16 16L96 464c-8.8 0-16-7.2-16-16l0-304-48 0zm160 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 176c0 13.3 10.7 24 24 24s24-10.7 24-24l0-176zm112 0c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 176c0 13.3 10.7 24 24 24s24-10.7 24-24l0-176z" />
                                        </svg>
                                    </span>
                                </a>
                            </td>
        </div>
        </tr>
        </tbody>
    <?php endwhile;  ?>
    </table>
<?php else: ?>
    <div class="hero bg-base-200 py-20  md:mb-0 md:py-15">
        <div class="hero-content text-center">
            <div class="max-w-md items-center justify-center ">
                <div role="alert" class="alert alert-error shadow-lg flex flex-col items-center text-white">
                    <span>คุณยังไม่มีรายการสินค้า</span>
                </div>
                <div>
                    <img src="image/no_product_cart.jpg" class="size-77 md:size-100 ">
                </div>
                <div>
                </div>
                <div class="pl-5">
                    <a href="index.php" class="btn btn-primary btn-wide ">ไปที่หน้าเมนู</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($total_price > 0): ?>
    <div class="mt-10 border rounded-lg overflow-hidden shadow-sm">
        <div class="p-3 px-4 bg-gray-100 border-b">
            <h1 class="font-semibold text-lg md:text-xl">สรุปออเดอร์</h1>
        </div>
        <div class="p-5 md:p-10 space-y-4 bg-white">
            <div class="flex justify-between font-bold">
                <div>ราคารวมสินค้า</div>
                <div><span id="total-price"><?php echo number_format($total_price, 2); ?></span> ฿</div>
            </div>
            <div class="flex justify-between font-bold">
                <div>จำนวนรวมทั้งหมด</div>
                <div class="text-primary"><span id="total-quantity"><?php echo $total_quantity; ?></span> ชิ้น</div>
            </div>

            <!-- ส่วนลด -->
            <?php
            $is_member = isset($_SESSION["user_id"]);
            $discount = $is_member ? ($total_price * 0.1) : 0;
            $net_price = $total_price - $discount;
            ?>
            <div class="flex justify-between font-bold items-center border-t pt-4">
                <div class="text-error">
                    <?php if ($is_member): ?>
                        ส่วนลดสมาชิก 10%
                    <?php else: ?>
                        <a href="register.php" class="skeleton bg-yellow-500 p-2 md:text-md text-sm text-white">สมัครสมาชิกรับส่วนลด 10%</a>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <div class="text-error text-lg">- <span id="discount-amount"><?php echo number_format($discount, 2); ?></span> ฿</div>
                </div>
            </div>

            <div class="flex justify-between font-bold text-xl border-t pt-4">
                <div>ยอดสุทธิ</div>
                <div class="text-success"><span id="net-price"><?php echo number_format($net_price, 2); ?></span> ฿</div>
            </div>
        </div>

        <form action="confirm_checkout.php" method="post" class="flex flex-col md:flex-row w-full">
            <a href="index.php#menucafe" class="btn btn-primary rounded-none flex-1 py-4">สั่งเมนูเพิ่ม</a>
            <button onclick="confirm('ต้องการชำระเงินใช่หรือไม่')" type="submit" name="confirmed_payment" class="btn btn-success rounded-none flex-1 py-4 text-white">ยืนยันออเดอร์</button>
        </form>
    </div>
    <!-- ถ้าไม่มีรายการสินค้าไม่ต้องแสดงราคารวม -->
<?php endif; ?>
</div>

<script src="js/cart_customer.js">
    // อยู๋ใน js/cart_customer.js
</script>
<?php require_once "footer.php" ?>
</body>

</html>