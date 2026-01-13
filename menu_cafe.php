<?php
    $stmt_users = $conn->prepare("SELECT * FROM users");
    $stmt_users->execute();
    $stmt_users_result = $stmt_users->get_result();

    // table users
    if ($stmt_users_result->num_rows > 0) {
        $users = $stmt_users_result->fetch_assoc();
    }

    // table products ที่ทำการ SELECT * FROM products
    // อยู่ในไฟล์ index.php

    if(isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
        $guest_id = null;
        $_SESSION["guest_id"] = $guest_id;
    } else {
        $user_id = null;
        $guest_id = session_id();
        $_SESSION["guest_id"] = $guest_id;
    }

?>


<div class="bg-white md:py-10 max-w-7xl mx-auto" id="menu_cafe">
    <h1 class="text-4xl font-semibold text-center py-10 mb-0 md:mb-10   ">
        เมนูของเรา
    </h1>
    
    <!-- จอคอม -->
    <?php require_once "menu_desktop.php" ?>
    <!-- จอโทรสับ -->
    <?php require_once "menu_mobile.php" ?>
</div>
            
<!-- เอาไว้เช็ค boolean แสดง popup -->
<input type="checkbox" id="edit_modal_toggle" class="modal-toggle" />

<!-- Edit Form -->
<div class="modal backdrop-blur-sm bg-black/40" role="dialog">
    <div class="modal-box max-w-2xl">
        <h3 class="text-2xl font-bold mb-6">แก้ไขข้อมูลสินค้า</h3>
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

<!-- Form ไว้ให้ Customer กดเลือก พอเลือกแล้วข้อมูลจะ insert ไปใส่ใน orders กับ order_items -->

<!-- form สำหรับลูกค้าสั่งซื้อ -->
<input type="checkbox" id="view_buy_modal_toggle" class="modal-toggle" />

<div class="modal backdrop-blur-sm bg-black/40" role="dialog">
<div class="modal-box max-w-md">
    <h3 id="buy_name" class="text-2xl font-bold mb-4 text-center"></h3>

    <div class="flex flex-col items-center space-y-4">
        <img id="buy_img" alt="product image" class="w-48 h-48 object-cover rounded-lg shadow-md">

        <div class="text-center">
            <p id="buy_description" class="text-gray-500"></p>
            <p class="text-2xl font-bold text-green-600 mt-2">
                <!-- ราคาตั้งต้น = 0 บาท -->
                <span id="buy_price">0</span> บาท
            </p>
        </div>
    </div>

    <!-- Form ให้ลูกค้ากดสั่ง -->
    <form action="add_to_cart.php" method="POST" class="mt-6">
        <input type="hidden" name="product_id" id="buy_id">
        <input type="hidden" name="final_price" id="buy_final_price"> 

        <div id="option_section" class="space-y-4 mb-4 border-t pt-4">
            
            <div id="select_product_option" class="form-control w-full hidden">
                <label class="label"><span class="label-text">ประเภทเครื่องดื่ม</span></label>
                <select id="buy_product_option" class="select select-bordered select-success w-full" name="product_option">
                    <option value="" disabled>เลือกประเภท</option>
                    <option value="hot" selected data-adj="-5">ร้อน (Hot)</option>
                    <option value="cold" data-adj="0">เย็น (Cold)</option>
                    <option value="frappe" data-adj="+5">ปั่น (Frappe)</option>
                </select> 
            </div>
            
            <!-- เลือกระดับความหวาน -->
            <div id="select_sweet_level" class="form-control w-full hidden">
                <label class="label"><span class="label-text">ระดับความหวาน</span></label>
                <select id="buy_sweet_level" class="select select-bordered select-success w-full" name="sweet_level">
                    <option value="" disabled>ระดับความหวาน</option>
                    <option value="0%">0% (ไม่หวาน)</option>
                    <option value="25%">25% (หวานน้อย)</option>
                    <option value="50%" selected>50% (ปกติ)</option>
                    <option value="75%">75% (หวานมาก)</option>
                    <option value="100%">100% (หวานจัด)</option>
                    </select>
            </div>
        </div>

        <!-- จำนวนสินค้า -->
        <div class="form-control w-full mb-4">
            <label class="label"><span class="label-text">จำนวนที่ต้องการ</span></label>
            <input type="number" name="quantity" id="buy_quantity" value="1" min="1" class="input input-bordered w-full" required />
        </div>

        <div class="modal-action flex justify-between">
            <label for="view_buy_modal_toggle" class="btn btn-ghost">ยกเลิก</label>
            <button type="submit" name="confirm_order" class="btn btn-success px-10 text-white">เพิ่มลงในรถเข็น</button>
        </div>
    </form>
</div>
<label class="modal-backdrop" for="view_buy_modal_toggle">Close</label>
</div>

<script>

let base_price = 0;

function calculateTotalPrice() {
    const productOptionSelect = document.getElementById("buy_product_option");
    const quantityInput = document.getElementById("buy_quantity");
    const displayPrice = document.getElementById("buy_price");
    const finalPrice = document.getElementById("buy_final_price");

    let price_adjust = 0;
    const selectOption = productOptionSelect.options[productOptionSelect.selectedIndex];
    if(selectOption && selectOption.dataset.adj) {
        price_adjust = parseFloat(selectOption.dataset.adj);
    }

    // จำนวนแก้ว
    const quantity = parseInt(quantityInput.value) || 1;
    
    //
    const total_price = (base_price + price_adjust) * quantity;

    displayPrice.innerText = new Intl.NumberFormat().format(total_price);
    finalPrice.value = total_price;
}

// ฟังก์ชันเป็น popup form สำหรับ Admin แก้ไขข้อมูล
function fillEditModal(data) {
    document.getElementById("edit_id").value = data.id;
    document.getElementById("edit_name").value = data.name;
    document.getElementById("edit_description").value = data.description;
    document.getElementById("edit_price").value = data.price;
    document.getElementById("edit_modal_toggle").checked = true;
}

// ฟังก์ชันสำหรับลูกค้ากดสั่งซื้อ
function viewBuyMenuModal(data) {
    // ดึง Element ของแต่ละค่าที่เราต้องจะแสดงผล
    
    base_price = parseFloat(data.price);

    const buyId = document.getElementById("buy_id");
    const buyName = document.getElementById("buy_name");
    const buyDesc = document.getElementById("buy_description");
    const buyPrice = document.getElementById("buy_price");
    const buyImg = document.getElementById("buy_img");

    const optionSection = document.getElementById("option_section");
    const selectProductOption = document.getElementById("select_product_option");
    const selectSweetLevel = document.getElementById("select_sweet_level");
    const productOptionSelect = document.getElementById("buy_product_option");
    const sweetLevelSelect = document.getElementById("buy_sweet_level");

    buyId.value = data.id;
    buyName.innerText = data.name;
    buyDesc.innerText = data.description || "ไม่มีคำอธิบาย";
    buyPrice.innerText = new Intl.NumberFormat().format(data.price);
    buyImg.src = data.image_url ? "image/" + data.image_url : "image/no_image.jpg";

    // ล้างค่าของตัวแปรที่เราไปทำการ getElementById มา Reset ให้เป็นค่า Default
    optionSection.classList.add('hidden');
    selectProductOption.classList.add('hidden');
    selectSweetLevel.classList.add('hidden');
    productOptionSelect.required = false;
    sweetLevelSelect.required = false;
    productOptionSelect.value = ""; // รีเซ็ตค่าประเภทเครื่องดื่ม ปั่น ร้อน เย็น ที่เลือกค้างไว้
    sweetLevelSelect.value = ""; // รีเซ็ตค่าระดับความหวานที่เลือกค้างไว้

    // Condition ในการแสดงผล Select sweet_level และ product_option
    if (data.has_product_option == 1 || data.has_sweetness == 1) {
        // ถ้าเข้าเงื่อนไข
        optionSection.classList.remove('hidden');
        
        if (data.has_product_option == 1) {
            selectProductOption.classList.remove('hidden');
            productOptionSelect.required = true;
        }

        if (data.has_sweetness == 1) {
            selectSweetLevel.classList.remove('hidden');
            sweetLevelSelect.required = true;
        }
    }

    calculateTotalPrice();

    // 4. เปิด Modal
    document.getElementById("view_buy_modal_toggle").checked = true;
}

document.getElementById("buy_product_option").addEventListener("change", calculateTotalPrice);
document.getElementById("buy_quantity").addEventListener("input", calculateTotalPrice);
</script>