<?php
   $categories = [
    'food' => [],
    'drinking' => [],
    'dessert' => []
    ];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cat = strtolower($row['category']);
            if (array_key_exists($cat, $categories)) {
                $categories[$cat][] = $row;
            }
        }
    }
// ฟังก์ชันช่วยแสดง Card
function renderProductCard($rows) {
    ob_start(); 
?>
    <div class="card bg-base-100 w-full shadow-lg hover:shadow-2xl transition-all duration-300">
        <figure class="aspect-video overflow-hidden">
            <?php if (!empty($rows["image_url"])): ?>
                <img src="image/<?php echo $rows["image_url"]; ?>" alt="<?php echo $rows["name"] ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <img src="image/no_image.jpg" class="w-full h-full object-cover">
            <?php endif; ?>
        </figure>
        <div class="card-body p-5">
            <h2 class="card-title text-xl"><?php echo $rows["name"] ?></h2>
            <p class="text-sm opacity-70 line-clamp-2"><?php echo $rows["description"] ?? "ไม่มีคำอธิบาย" ?></p>
            
            <?php if(isset($_SESSION["user_id"]) && $_SESSION["role"] === "admin"): ?>
                <div class="badge badge-outline"><?php echo strtoupper($rows["category"]) ?></div>
            <?php endif; ?>

            <p class="text-lg font-bold text-success"><?php echo number_format($rows["price"]) ?> บาท</p>
            
            <div class="card-actions justify-end mt-4">
                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                    <button class="btn btn-success btn-sm text-white" onclick='fillEditModal(<?= json_encode($rows, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>แก้ไข</button>
                    <a class="btn btn-error btn-sm text-white" onclick="return confirm('ลบเมนูนี้?')" href="delete_menu.php?id=<?= $rows['id'] ?>">ลบ</a>
                <?php else: ?>
                    <button onclick='viewBuyMenuModal(<?php echo json_encode($rows); ?>)' class="btn btn-primary btn-block">สั่งเลย</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="max-w-7xl mx-auto p-4 hidden md:flex">
    <!-- DaisyUI Tabs -->
    <div role="tablist" class="tabs tabs-boxed mb-8 justify-center">
        <input type="radio" name="menu_tabs" role="tab" class="tab" aria-label="ทั้งหมด" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                foreach($categories as $cat_items) {
                    foreach($cat_items as $item) echo renderProductCard($item);
                }
                ?>
            </div>
        </div>

        <input type="radio" name="menu_tabs" role="tab" class="tab" aria-label="อาหาร (Food)" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                if(empty($categories['food'])) echo "<p class='text-center col-span-full'>ไม่มีรายการอาหาร</p>";
                foreach($categories['food'] as $item) echo renderProductCard($item); 
                ?>
            </div>
        </div>

        <input type="radio" name="menu_tabs" role="tab" class="tab" aria-label="เครื่องดื่ม (Drinks)" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                if(empty($categories['drinking'])) echo "<p class='text-center col-span-full'>ไม่มีรายการเครื่องดื่ม</p>";
                foreach($categories['drinking'] as $item) echo renderProductCard($item); 
                ?>
            </div>
        </div>

        <input type="radio" name="menu_tabs" role="tab" class="tab" aria-label="ของหวาน (Dessert)" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                if(empty($categories['dessert'])) echo "<p class='text-center col-span-full'>ไม่มีรายการของหวาน</p>";
                foreach($categories['dessert'] as $item) echo renderProductCard($item); 
                ?>
            </div>
        </div>
    </div>
</div>