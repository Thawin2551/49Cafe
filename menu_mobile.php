<?php
function renderProductList($rows) {
    ob_start();
    ?>
    <li class="list-row items-center justify-between p-4 border-b border-gray-300">
        <div class="flex items-center gap-4">
            <div class="avatar">
                <div class="size-20 rounded-box">
                    <img src="<?= !empty($rows["image_url"]) ? 'image/' . $rows["image_url"] : 'image/no_image.jpg'; ?>" 
                         onerror="this.src='image/no_image.jpg'" />
                </div>
            </div>
            <div class="flex-1">
                <div class="font-bold text-lg"><?= htmlspecialchars($rows["name"]); ?></div>
                <div class="text-xs opacity-60 line-clamp-1"><?= htmlspecialchars($rows["description"]); ?></div>
                <div class="text-green-600 font-semibold"><?= number_format($rows["price"]); ?> บาท</div>
                
                <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                    <div class="text-xs badge badge-ghost mt-1">
                        <?= strtoupper($rows["category"]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ทำให้ ปุ่มกดสั่ง ไปอยู่ขวาสุด (justify-between) -->
        <div></div>

        <div class="flex gap-2">
            <?php if (isset($_SESSION["role"])): ?>
                <?php if ($_SESSION["role"] === "admin"): ?>
                         <button class="btn btn-sm btn-success text-white" 
                         onclick='fillEditModal(<?= json_encode($rows, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                         แก้ไข
                        </button>
                        <a class="btn btn-sm btn-error text-white"
                        onclick="return confirm('ต้องการลบเมนูนี้ใช่หรือไม่ ?')"
                        href="delete_menu.php?id=<?= $rows['id'] ?>">
                        ลบ
                    </a>
                <?php else: ?>
                    <button onclick='viewBuyMenuModal(<?= json_encode($rows, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                            class="btn btn-sm btn-primary">
                        สั่งเลย
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="flex flex-col items-end gap-1">
                    <button onclick='viewBuyMenuModal(<?= json_encode($rows, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                            class="btn btn-sm btn-primary">
                        สั่งเลย
                    </button>
                    <span class="text-[12px] text-red-500 leading-tight text-right">
                        กรุณาระบุ<br/>เลขโต๊ะ
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </li>
    <?php
    return ob_get_clean();
}
?>

<div class="md:hidden px-4 mb-20">
    <!-- DaisyUI Tabs using Radio buttons -->
    <div role="tablist" class="tabs tabs-boxed mb-4 bg-base-200">
        
        <!-- Tab: ทั้งหมด -->
        <input type="radio" name="mobile_list_tabs" role="tab" class="tab !text-xs" aria-label="ทั้งหมด" checked />
        <div role="tabpanel" class="tab-content mt-4">
            <ul class="list bg-base-100 rounded-box shadow-md overflow-hidden">
                <?php 
                $has_any = false;
                foreach($categories as $categories_item) {
                    foreach($categories_item as $rows) {
                        $has_any = true;
                        echo renderProductList($rows);
                    }
                }
                if(!$has_any) echo "<li class='p-10 text-center text-error'>ไม่พบข้อมูลสินค้า</li>";
                ?>
            </ul>
        </div>

        <?php
        // ประเภทเมนูทั้งหมด
        $tab_mapping = [
            'food' => 'อาหาร',
            'drinking' => 'เครื่องดื่ม',
            'dessert' => 'ของหวาน'
        ];

        foreach ($tab_mapping as $key => $label): ?>
            <input type="radio" name="mobile_list_tabs" role="tab" class="tab text-md" aria-label="<?= $label ?>" />
            <div role="tabpanel" class="tab-content mt-4">
                <ul class="list bg-base-100 rounded-box shadow-md overflow-hidden">
                    <?php 
                    if(empty($categories[$key])) {
                        echo "<li class='p-10 text-center text-error font-medium'>ไม่มีรายการ{$label}</li>";
                    } else {
                        foreach($categories[$key] as $rows) {
                            echo renderProductList($rows);
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>