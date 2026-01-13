const alert_message = document.getElementById("alert-message");

if (alert_message) {
    setTimeout(() => {
        alert_message.style.opacity = "0";
        setTimeout(() => {
            alert_message.style.display = "none"
        }, 500)
    }, 3000)
}

async function updateQuantity(cartId, change) {
    const quantityElement = document.getElementById(`quantity-${cartId}`);
    const subtotalElement = document.getElementById(`subtotal-${cartId}`);
    const cart_item = quantityElement.closest(".cart-item");
    const unit_price = parseFloat(cart_item.dataset.price);

    let currentQuantity = parseInt(quantityElement.innerText);
    let newQuantity = currentQuantity + change;

    if (newQuantity < 1) {
        if (confirm("ต้องการลบรายการนี้ใช่หรือไม่")) {
            location.href = `cart_delete.php?id=${cartId}`
        }
        return
    }

    try {
        const formData = new FormData();
        formData.append("cart_id", cartId);
        formData.append("quantity", newQuantity);

        const response = await fetch('cart_update_quantity.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Update UI จำนวนสินค้าที่แสดงผล
            quantityElement.innerText = newQuantity;

            const formatNumber = (number) => number.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })

            // เพื่อให้ตอนราคาเกินหลักพันมีคอมม่ากั้นระหว่างหลักพันและหลักร้อย
            subtotalElement.innerText = data.subtotal.toLocaleString('th-TH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' ฿';
            
            // ให้ตัวเลขแสดงผลแบบมี comma และทศนิยม 2 ตำแหน่ง
            document.getElementById('total-price').innerText = formatNumber(data.total_price);
            document.getElementById('total-quantity').innerText = formatNumber(data.total_quantity);
            document.getElementById('discount-amount').innerText = formatNumber(data.discount);
            document.getElementById('net-price').innerText = formatNumber(data.net_price);
            
            // Update จำนวนในตะกร้ารถเข็นตามที่เรากดเพิ่ม กับ ลด
            const updateCartQuantity = (id, quantity) => {
                const element = document.getElementById(id);
                if(element) {
                    if (quantity > 0) {
                        element.innerText = quantity;
                        
                        element.classList.add('scale-120', 'bg-green-600', 'duration-200', 'ease-in-out');
                        setTimeout(() => {
                            element.classList.remove('scale-120', 'bg-green-600', 'ease-in-out');
                        }, 200)
                    } else {
                        element.classList.add('scale-0', 'duration-200');
                        setTimeout(() => {
                            element.classList.remove('scale-0', 'duration-200')
                        }, 200)
                    }
                }
            };

            // อัปเดตทั้ง navbar ในโทรสัพท์กับคอม
                updateCartQuantity('cart-count-desktop', data.total_quantity);
                updateCartQuantity('cart-count-mobile', data.total_quantity);   
        } else {
            alert('เกิดข้อผิดพลาด ' + data.message)
        }

    } catch (error) {
        console.error("Error: ", error)
        alert("เกิดข้อผิดพลาดในการอัปเดตจำนวนสินค้า");
    }
}

// DOMContenedLoaded คือการที่รอหน้าเว็บโหลดให้เสร็จก่อน ไม่งั้นเผื่อบางอันที่ script ทำงานนก่อนแล้วหา Element
// ที่เราต้องการไม่เจอ
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.cart-item').forEach(item => {
        const cartId = item.dataset.itemId
        const decreaseButton = item.querySelector('.btn-error');
        const increaseButton = item.querySelector('.btn-success');

        decreaseButton.addEventListener('click', () => updateQuantity(cartId, -1));
        increaseButton.addEventListener('click', () => updateQuantity(cartId, 1));
    })
})
