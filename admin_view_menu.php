<?php 
    session_start();
    require_once "config.php";

    if(!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
        header("Location: index.php");
        exit();
    }

    try {
        $query_products = $conn -> prepare("SELECT * FROM products");
        $query_products -> execute();
        $result_query_product = $query_products -> get_result();
    } catch(Exception $e) {
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด" . $e ->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผู้ดูแล | ดูเมนู</title>   
    
    <link rel="icon" href="icon/49cafe_icon.png" type="image/png">

    <!-- CSS Files กับ tailwind, daisyui -->
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />

</head>

<body>

    <?php require_once "sidebar/admin_sidebar_top.php" ?>  

    <div class="my-10 text-center">
        <h1 class="font-bold text-xl">เมนูทั้งหมด</h1>
        <p class="text-gray-400 text-lg">ลบหรือเพิ่มเมนูได้ที่นี่หน้านี้</p>
    </div>
    <div class="overflow-x-auto md:max-w-6xl md:mx-auto mb-20 mx-2">
        <table class="table">
            <!-- head -->
            <?php if($result_query_product -> num_rows > 0): ?>
            <thead class="bg-gray-100">
                <tr class="items-center">
                    <th class="text-center">หมายเลขเมนู</th>
                    <th>รูปภาพและชื่อเมนู</th>
                    <th>รายละเอียด</th>
                    <th>แก้ไข / ลบเมนู</th>
                </tr>
            </thead>
            <tbody>
                <!-- While Loop -->
                 
                <?php while($product = $result_query_product -> fetch_assoc()): ?>
                <tr class="hover:bg-gray-200 duration-100">
                    <td class="text-center"><?php echo $product["id"] ?></td>
                    <td class="items-center">
                        <div class="flex items-center gap-5">
                            <div class="avatar">
                                <div class="mask rounded-lg">
                                    <?php if(isset($product["image_url"])): ?>
                                        <img
                                        class="mask mask-squircle w-20 h-20"    
                                        src=<?php echo "image/" . $product["image_url"]  ?>
                                        alt="Avatar Tailwind CSS Component" 
                                        />
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Product -->
                            <div>
                                <div class="font-bold"><?php ?></div>   
                                <div class=""><span class="font-bold text-[15px]"><?php echo $product["name"] ?></span></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-col gap-2">
                            <div>
                                หมวดหมู่ <span class="font-semibold"><?php echo strtoupper($product["category"]) ?></span> 
                            </div>
                            <div>
                                <?php echo $product["price"] ?> บาท
                            </div>
                            <div class="text-gray-500">
                                <?php echo $product["description"] ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="flex gap-2 flex-col items-center md:flex-row">
                            <div>
                                <a class="btn btn-success text-white"
                                    onclick='fillEditModal(<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <span class="hidden md:flex">
                                        แก้ไขเมนู
                                    </span>
                                    <span class="flex md:hidden">
                                        <svg class="w45 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L368 46.1 465.9 144 490.3 119.6c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L432 177.9 334.1 80 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                                    </span>
                                    </a>
                            </div>
                            <div>
                                <a class="btn btn-error text-white"
                                    onclick="return confirm('ต้องการลบเมนูนี้ใช่หรือไม่ ?')"
                                    href="delete_menu.php?id=<?= $product['id'] ?>">
                                    <span class="hidden md:flex">
                                        ลบเมนู
                                    </span>
                                    <span class="flex md:hidden">
                                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M166.2-16c-13.3 0-25.3 8.3-30 20.8L120 48 24 48C10.7 48 0 58.7 0 72S10.7 96 24 96l400 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-96 0-16.2-43.2C307.1-7.7 295.2-16 281.8-16L166.2-16zM32 144l0 304c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-304-48 0 0 304c0 8.8-7.2 16-16 16L96 464c-8.8 0-16-7.2-16-16l0-304-48 0zm160 72c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 176c0 13.3 10.7 24 24 24s24-10.7 24-24l0-176zm112 0c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 176c0 13.3 10.7 24 24 24s24-10.7 24-24l0-176z"/></svg>
                                    </span>
                                </a>
                            </div>                                        
                        </div>
                    </td>
                </tr>
            <!-- endwhile loop -->
            <?php endwhile; ?>
            <?php else: ?>
                <div role="alert" class="alert alert-error text-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-white">ไม่พบเมนูในระบบ กรุณาลองใหม่อีกครั้ง</span>
                </div>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
     <input type="checkbox" id="edit_modal_toggle" class="modal-toggle" />

    <!-- Edit Form -->
    <div class="modal backdrop-blur-sm bg-black/40" role="dialog">
        <div class="modal-box max-w-2xl">
            <h3 class="text-2xl font-bold mb-6">แก้ไขข้อมูลเมนู</h3>
            <form action="edit_menu.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-control w-full">
                    <input type="text" name="name" id="edit_name" class="input input-bordered w-full" required />
                </div>

                <div class="form-control w-full">
                    <textarea name="description" id="edit_description" class="textarea textarea-bordered h-24"></textarea>
                </div>

                <div class="form-control w-full">
                    <input type="number" name="price" id="edit_price" step="0.01" class="input input-bordered w-full" required />
                </div>

                <div class="form-control w-full">
                    <input type="file" name="image" class="file-input file-input-bordered file-input-primary w-full" accept="image/*" />
                </div>

                <div class="modal-action">
                    <label for="edit_modal_toggle" class="btn btn-ghost">ยกเลิก</label>
                    <button type="submit" name="update" class="btn btn-primary px-10">บันทึกการแก้ไข</button>
                </div>
            </form>

            <!-- คลิกพื้นที่ว่างรอบๆ Modal เพื่อปิด -->
            <label class="modal-backdrop" for="edit_modal_toggle">Close</label>
        </div>
    </div>
    <?php require_once "sidebar/admin_sidebar_bottom.php" ?>  
    
    <script>
        function fillEditModal(data) {
        document.getElementById("edit_id").value = data.id;
        document.getElementById("edit_name").value = data.name;
        document.getElementById("edit_description").value = data.description;
        document.getElementById("edit_price").value = data.price;
        document.getElementById("edit_modal_toggle").checked = true;
    }
    </script>
</body>
</html>