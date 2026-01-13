<div
  class="hero min-h-screen"
  style="background-image: url(image/49cake_blueberry.jpg);"
>
  <div class="hero-overlay"></div>
  <div class="hero-content text-neutral-content text-center">
    <div class="max-w-lg text-center">
      <div class="mb-4">
        <?php if(isset($_SESSION["user_id"])): ?>
          <h1 class="text-4xl md:text-5xl">
            WELCOME  
            <span class="font-semibold text-3xl">
              "<?php echo htmlspecialchars($_SESSION["username"]) ?>"
            </span>
          </h1>
        <?php else: ?>
          <h1 class="text-4xl md:text-4xl">สั่งเมนูได้เลย <span class="font-semibold text-green-400 border-b-5 border-primary">ตอนนี้</span> ! <br/></h1>
        <?php endif; ?>
      </div>
        <h2 class="mb-5 text-3xl md:text-4xl font-bold">49 Coffee Time</h2>
        <p class="mb-5">
          Presented By 49 Coffee Time
        </p>
      <a class="btn btn-primary btn-wide font-semibold text-gray-200" href="#menu_cafe">สั่งเลย</a>
    </div>
  </div>
</div>
<!-- <div class="indicator">
  <span class="indicator-item badge badge-secondary">12</span>
  <button class="btn">inbox</button>
</div> -->