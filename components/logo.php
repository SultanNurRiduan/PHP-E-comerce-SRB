<?php
function renderLogo(
  $brandTop = "SRB",
  $brandBottom = "Garasi Sneakers",
  $logoSrc = "./public/assets/images/Logo1.png"
) {
  echo '
    <a href="index.php" class="flex items-center gap-2 group" style="padding-right: 2rem;">
      <img 
        src="' . $logoSrc . '" 
        alt="' . $brandTop . ' Logo" 
        class="rounded-md shadow-sm"
        style=" width: 60px; height: 60px; object-fit: cover; border-radius: 100%; box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px -1px;"
      />
      <div class="flex flex-col leading-tight">
        <span class="text-lg font-bold text-red-600 ">
          ' . $brandTop . '
        </span>
        <span class="text-base font-bold ">
          ' . $brandBottom . '
        </span>
      </div>
    </a>
  ';
}
?>
