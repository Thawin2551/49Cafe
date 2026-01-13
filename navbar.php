<?php 
    $user_id = $_SESSION["user_id"] ?? null;
    $guest_id = isset($user_id) ? null : session_id();

    // เอาไปแสดงผลสถานะออเดอร์
    $active_order = null;

    try {
        $sql_order_status = 
        " SELECT * FROM orders
            WHERE (" . ($user_id ? "user_id = ?" : "guest_id = ?") . ")
                AND status IN ('preparing', 'ready', 'completed')
                    ORDER BY id DESC LIMIT 1    
        ";
        
        $stmt_order_status = $conn -> prepare($sql_order_status);
        
        if($user_id !== null) {
            $stmt_order_status -> bind_param("i", $user_id);
        } else {
            $stmt_order_status -> bind_param("s", $guest_id);
        }

        $stmt_order_status -> execute();
        $result_status = $stmt_order_status -> get_result();
        
        if($result_status -> num_rows > 0) {
            $active_order = $result_status -> fetch_assoc();
        }
    } catch (Exception $e) {
    }
?>

<nav class="sticky top-0 z-50">
    <div class="hidden md:flex flex-col md:flex-row justify-between items-center shadow-lg py-4 px-10 items-center bg-white">
        <ul class="flex items-center">
            <div>
                <li class="text-lg mx-2 duration-300 ">
                    <a href="index.php" class="btn btn-ghost text-xl">
                        <span class="font-semibold dancing-script text-2xl">49 Coffee Time</span>
                    </a>
                </li>
            </div>
        </ul>

        <ul class="flex md:gap-3 items-center">
            <?php if (isset($_SESSION["user_id"])): ?>
                <?php if ($_SESSION["role"] === "admin"): ?>
                    <li class="text-md"><a class="btn btn-neutral" href="admin_view_menu.php">ระบบหลังบ้าน</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <div>
                        <li class="text-md btn btn-error text-white p-3 px-4"><a href="logout.php">ออกจากระบบ</a></li>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <li class="text-md btn btn-success text-white"><a href="register.php">ลงทะเบียน</a></li>
                <li class="text-md btn btn-primary"><a href="login.php">เข้าสู่ระบบ</a></li>
            <?php endif; ?>

            <!-- Order status ให้ user ดู -->
            <?php if ($active_order): ?>
                <div class="dropdown dropdown-end group">
                    <?php if($active_order["status"] === 'preparing'): ?>
                        <div tabindex="0" role="button" class="btn btn-warning rounded-full px-4 border border-base-300">
                    <?php elseif($active_order["status"] === 'ready') :?>
                        <div tabindex="0" role="button" class="btn btn-primray bg-base-200/50 hover:bg-base-200 rounded-full px-4 border border-base-300">
                    <?php elseif($active_order["status"] === 'completed'): ?>
                        <div tabindex="0" role="button" class="btn btn-success bg-base-200/50 hover:bg-base-200 rounded-full px-4 border border-base-300">
                    <?php endif; ?>
                        <!-- Icon แสดงตามสถานะ -->
                        <div class="flex items-center gap-2">
                            <?php if ($active_order['status'] === 'preparing'): ?>
                                <span class="loading loading-spinner loading-xs text-white"></span>
                                <span class="text-xs text-white font-bold hidden sm:inline">กำลังปรุงอาหาร</span>
                                <span class="text-xs text-white">#<?= $active_order['id'] ?></span>
                            <?php elseif ($active_order['status'] === 'ready'): ?>
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-primary opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                </span>
                                <span class="text-xs font-bold text-primary hidden sm:inline">อาหารพร้อมเสิร์ฟ</span>
                                <span class="text-xs text-primary">#<?= $active_order['id'] ?></span>
                            <?php elseif ($active_order["status"] === 'completed'): ?>
                                <span class="flex h-3 w-3">
                                    <span class=" absolute inline-flex h-3 w-3 rounded-full bg-success opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                                </span>
                                <span class="text-xs font-bold hidden text-success sm:inline">ทำรายการเสร็จสิ้น</span>
                                <span class="text-xs text-success">#<?= $active_order['id'] ?></span>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- Dropdown Details -->
                    <div tabindex="0" class="dropdown-content z-[1] card card-compact bg-base-100 w-64 shadow-2xl mt-4 border border-base-200">
                        <div class="card-body">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-bold text-md">ติดตามสถานะออเดอร์</h3>
                                <span class="badge badge-outline badge-sm">#<?= $active_order['id'] ?></span>
                            </div>

                            <div class="card-actions mt-4">
                                <a href="order_status.php?id=<?= $active_order['id'] ?>" class="btn btn-primary btn-sm btn-block rounded-lg shadow-md">
                                    ดูรายละเอียดออเดอร์
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex gap-3 <?php if ($_SESSION["total_quantity"] > 0) echo "gap-5" ?>">
                <div class="indicator">
                    <?php if (!empty($_SESSION["total_quantity"])): ?>
                        <span id="cart-count-desktop" class="indicator-item badge text-white badge-success rounded-full">
                            <?php echo $current_cart_quantity ?>
                        </span>
                    <?php endif; ?>
                    <li class="text-md btn btn-success hover:text-white btn-outline p-3 px-4"><a href="cart_customer.php"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg></a></li>
                </div>
            </div>
        </ul>
    </div>


    <!-- Mobile Navbar 49 Coffee Time -->
    <div class="md:hidden navbar bg-base-100 shadow-sm py-5 drawer lg:drawer-open flex" id="menuToggle">
        <input id="my-drawer-3" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col items-center justify-center ">
            <label for="my-drawer-3" class="btn btn-square btn-ghost drawer-button lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </label>
        </div>

        <div class="drawer-side">
            <label for="my-drawer-3" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-200 min-h-full w-70 p-4">
                <li class="py-2"><a class="py-2.5 font-bold dancing-script text-2xl" href="index.php">49 Coffee Time</a></li>
                <li class="py-2"><a class="py-2.5 font-sans" href="index.php">หน้าหลัก</a></li>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <?php if ($_SESSION["role"] === "admin"): ?>
                        <li class="text-md"><a class="btn btn-neutral" href="admin_view_menu.php">ระบบหลังบ้าน</a></li> 
                    <?php endif; ?>
                    <li class="py-2"><a class="py-2.5 btn btn-error text-white font-sans" href="logout.php">ออกจากระบบ</a></li>
                <?php else: ?>
                    <li class="py-2"><a class="py-2.5 btn btn-success text-white font-sans" href="register.php">ลงทะเบียน</a></li>
                    <li class="py-2"><a class="py-2.5 btn btn-primary text-white font-sans" href="login.php">เข้าสู่ระบบ</a></li>
                    <li class="py-2 text-center">
                        <a href="register.php" class="py-2.5 skeleton duration-300 bg-yellow-500 hover:bg-yellow-600 text-white">
                            สมัครสมาชิกเพื่อรับส่วนลดพิเศษ 10 %
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="flex-1">
            <a class="btn btn-ghost dancing-script text-xl md:text-2xl" href="index.php">49 Coffee Time</a>
        </div>


        <div class="flex items-center gap-2.5">

            <?php if ($active_order): ?>
                <div class="dropdown dropdown-end group">
                    <?php if($active_order["status"] === 'preparing'): ?>
                        <div tabindex="0" role="button" class="btn btn-warning rounded-full px-4 border border-base-300">
                    <?php elseif($active_order["status"] === 'ready') :?>
                        <div tabindex="0" role="button" class="btn btn-primary bg-base-200/50 hover:bg-base-200 rounded-full border border-base-300">
                     <?php elseif($active_order["status"] === 'completed'): ?>
                        <div tabindex="0" role="button" class="btn btn-success bg-base-200/50 hover:bg-base-200 rounded-full px-4 border border-base-300">
                    <?php endif; ?>
                        <!-- Icon แสดงตามสถานะ -->
                        <div class="flex items-center gap-2">
                            <?php if ($active_order['status'] === 'preparing'): ?>
                                <span class="loading loading-spinner loading-xs text-white"></span>
                                <span class="text-xs text-white">#<?= $active_order['id'] ?></span>
                            <?php elseif ($active_order['status'] === 'ready'): ?>
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-primary opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                                </span>
                                <span class="text-xs text-primary">#<?= $active_order['id'] ?></span>
                            <?php elseif($active_order["status"] === 'completed'): ?>
                                <span class="flex h-3 w-3">
                                    <span class="absolute inline-flex h-3 w-3 rounded-full bg-success opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                                </span>
                                <span class="text-xs text-success">#<?= $active_order['id'] ?></span>
                            <?php endif; ?>
                            
                        </div>
                    </div>

                    <!-- Dropdown Details -->
                    <div tabindex="0" class="dropdown-content z-[1] card card-compact bg-base-100 w-64 shadow-2xl mt-4 border border-base-200">
                        <div class="card-body">
                            <div class="flex flex-col justify-center items-center">
                                <span class="badge badge-outline badge-sm">#<?= $active_order['id'] ?></span>
                                <div class="mt-4 flex flex-col text-center">
                                    <h3 class="font-bold text-md">ติดตามสถานะออเดอร์</h3>
                                        <?php if($active_order["status"] === "preparing"): ?>
                                            <p class="font-semibold text-md text-warning">กำลังปรุงอาหาร</p>
                                        <?php elseif($active_order["status"] === "ready"): ?>    
                                            <p class="font-semibold text-md text-primary">อาหารพร้อมเสิร์ฟ</p>
                                        <?php elseif($active_order["status"] === "completed"): ?>    
                                            <p class="font-semibold text-md text-success">ทำรายการเสร็จสิ้น</p>
                                        <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-actions mt-2">
                                <a href="order_status.php?id=<?= $active_order['id'] ?>" class="btn btn-primary btn-sm btn-block rounded-lg shadow-md">
                                    ดูรายละเอียดออเดอร์
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex gap-3 items-center <?php if ($_SESSION["total_quantity"] > 0) echo "gap-5 mr-3" ?>">
                <div class="indicator">
                    <?php if (!empty($_SESSION["total_quantity"])): ?>
                        <span id="cart-count-mobile" class="indicator-item badge text-white badge-success rounded-full">
                            <?php echo $current_cart_quantity ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION["role"]) === "admin"): ?>
                        <li class="text-white btn btn-neutral" style="list-style: none;">
                            <a href="./admin_view_menu.php" data-tip="Settings">
                                <svg fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" stroke-linejoin="round" stroke-linecap="round" stroke-width="2" stroke="currentColor" class="h-5 w-5 my-1.5 inline-block size-4"><path d="M64 64c0-17.7-14.3-32-32-32S0 46.3 0 64L0 400c0 44.2 35.8 80 80 80l400 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 416c-8.8 0-16-7.2-16-16L64 64zm406.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L320 210.7 262.6 153.4c-12.5-12.5-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l73.4-73.4 57.4 57.4c12.5 12.5 32.8 12.5 45.3 0l128-128z"/></svg>
                                <span class="is-drawer-close:hidden"></span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="text-md btn btn-primary text-white p-3 px-4"><a href="cart_customer.php"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg></a>
                        </li>
                    <?php endif; ?>
                </div>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <div>
                        <li class="text-md btn btn-error text-white p-3 px-4"><a href="logout.php"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" file="none" viewBox="0 0 512 512">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M505 273c9.4-9.4 9.4-24.6 0-33.9L361 95c-6.9-6.9-17.2-8.9-26.2-5.2S320 102.3 320 112l0 80-112 0c-26.5 0-48 21.5-48 48l0 32c0 26.5 21.5 48 48 48l112 0 0 80c0 9.7 5.8 18.5 14.8 22.2s19.3 1.7 26.2-5.2L505 273zM160 96c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 32C43 32 0 75 0 128L0 384c0 53 43 96 96 96l64 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-64 0c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l64 0z" />
                        </svg></a></li>
                    </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</nav>

<script>
</script>