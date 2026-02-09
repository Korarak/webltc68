
<?php
@include 'condb/condb.php'; 
// ดึงข้อมูล footer info (เอา record แรกพอ)
$info_sql = "SELECT * FROM footer_info LIMIT 1";
$info_result = $mysqli4->query($info_sql);
$info = $info_result->fetch_assoc();

// ดึงลิงก์ menu
$menu_sql = "SELECT * FROM footer_links WHERE category='menu' AND visible=1 ORDER BY position ASC";
$menu_result = $mysqli4->query($menu_sql);

// ดึงลิงก์ resource
$res_sql = "SELECT * FROM footer_links WHERE category='resource' AND visible=1 ORDER BY position ASC";
$res_result = $mysqli4->query($res_sql);

// ดึงลิงก์ social
$social_sql = "SELECT * FROM footer_links WHERE category='social' AND visible=1 ORDER BY position ASC";
$social_result = $mysqli4->query($social_sql);
?>

<!-- <footer class="bg-gray-900 text-gray-300 py-10 mt-auto"> -->
  <footer class="bg-emerald-800 text-white py-10 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

      <!-- ข้อมูลวิทยาลัย -->
      <div>
        <h3 class="text-lg font-semibold text-white"><?= htmlspecialchars($info['college_name']) ?></h3>
        <p class="mt-2 text-sm leading-6">
          <?= nl2br(htmlspecialchars($info['address'])) ?><br>
          โทร. <?= htmlspecialchars($info['phone']) ?><br>
          อีเมล: <a href="mailto:<?= htmlspecialchars($info['email']) ?>" class="hover:text-emerald-400"><?= htmlspecialchars($info['email']) ?></a>
        </p>
      </div>

      <!-- เมนู -->
      <div>
        <h3 class="text-lg font-semibold text-white">เมนู</h3>
        <ul class="mt-2 space-y-2 text-sm">
          <?php while ($row = $menu_result->fetch_assoc()): ?>
            <li>
              <a href="<?= htmlspecialchars($row['url']) ?>" class="hover:text-emerald-400 transition">
                <?= htmlspecialchars($row['title']) ?>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>

      <!-- แหล่งข้อมูล -->
      <div>
        <h3 class="text-lg font-semibold text-white">แหล่งข้อมูล</h3>
        <ul class="mt-2 space-y-2 text-sm">
          <?php while ($row = $res_result->fetch_assoc()): ?>
            <li>
              <a href="<?= htmlspecialchars($row['url']) ?>" class="hover:text-emerald-400 transition" target="_blank">
                <?= htmlspecialchars($row['title']) ?>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>

      <!-- โซเชียล -->
      <div>
        <h3 class="text-lg font-semibold text-white">ติดต่อเรา</h3>
        <div class="flex space-x-4 mt-3">
          <?php while ($row = $social_result->fetch_assoc()): ?>
            <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="hover:text-emerald-400">
              <i class="<?= htmlspecialchars($row['icon']) ?> fa-lg"></i>
            </a>
          <?php endwhile; ?>
        </div>
      </div>

    </div>

    <!-- เส้นคั่น -->
    <div class="border-t border-gray-700 mt-8 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-400">
      <p>© <?= date("Y") ?> Loei Technical College. All rights reserved.</p>
      <p>จำนวนผู้เข้าชม : <span id="visitorCount">0</span></p>
    </div>
  </div>
</footer>
