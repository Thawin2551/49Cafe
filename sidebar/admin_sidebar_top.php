<div class="drawer lg:drawer-open">
  <input id="my-drawer-4" type="checkbox" class="drawer-toggle" />
  <div class="drawer-content">
    <!-- Navbar -->
    <nav class="navbar w-full bg-base-300">
      <label for="my-drawer-4" aria-label="open sidebar" class="btn btn-square btn-ghost">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor" class="my-1.5 inline-block size-4"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path><path d="M9 4v16"></path><path d="M14 10l2 2l-2 2"></path></svg>
      </label>
      <div class="px-4">
        <h1>
            ยินดีต้อนรับกลับมา คุณ 
            <span class="font-semibold">
              <?php echo $_SESSION["username"]  ?>
            </span>
        </h1> 
      </div>    
    </nav>
    
    <!-- แสดงผลหน้าต่างๆของ admin -->

    <!-- แสดงผล sidebar ที่เป็น navigation bar -->
    