<!-- Section Title -->
<div class="text-center py-2">
  <h1 class="text-4xl font-extrabold text-gray-800">Selamat Datang di <span class="text-red-600">SRB Garasi Sneaker</span></h1>
  <p class=" text-gray-500 text-lg">Temukan sepatu favoritmu dengan mudah dan cepat</p>
</div>

<!-- Static Top Banner -->
<div class="overflow-hidden shadow-xl mb-2">
  <img
    src="/shoe-shop/public/assets/images/bannertop.png"
    alt="Banner Top"
    class="w-full h-40 object-cover" />
</div>

<!-- Carousel Section -->
<div class="relative overflow-hidden w-full h-auto shadow-lg">
  <?php
  $banners = [
    "banner1.png",
    "banner2.png",
    "banner3.png",
    "banner4.png"
  ];
  ?>
  <div id="slider" class="flex transition-transform duration-700 ease-in-out w-max">
    <?php foreach ($banners as $index => $banner): ?>
      <a href="/shoe-shop/index.php?route=baru" class="w-screen h-auto flex-shrink-0">
        <img src="/shoe-shop/public/assets/images/<?= htmlspecialchars($banner) ?>"
          alt="Slide <?= $index + 1 ?>"
          class="w-full h-full object-cover" />
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Indicator Dots -->
  <div id="indicators" class="absolute bottom-52 left-1/2 -translate-x-1/2 flex gap-3 z-10">
    <!-- dots generated via JS -->
  </div>

  <div class="overflow-hidden shadow-xl mt-2">
    <img
      src="/shoe-shop/public/assets/images/bottombanner.jpg"
      alt="Banner Top"
      class="w-full h-auto object-cover" />
  </div>
</div>

<!-- Script -->
<script>
  const slider = document.getElementById("slider");
  const indicatorsContainer = document.getElementById("indicators");

  const originalSlides = Array.from(slider.querySelectorAll("a"));
  const totalSlides = originalSlides.length;
  let currentIndex = 0;

  // Duplicate slides for infinite effect
  slider.innerHTML += slider.innerHTML;
  const allSlides = slider.querySelectorAll("a");

  // Create indicator dots
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement("span");
    dot.className = "w-3 h-3 bg-gray-100/60 hover:bg-white rounded-full cursor-pointer transition";
    indicatorsContainer.appendChild(dot);
  }
  const dots = indicatorsContainer.querySelectorAll("span");

  function updateActiveDot() {
    dots.forEach(dot => dot.classList.remove("bg-white"));
    dots[currentIndex % totalSlides].classList.add("bg-white");
  }

  function goToSlide(index) {
    const slideWidth = window.innerWidth;
    slider.style.transition = "transform 0.7s ease-in-out";
    slider.style.transform = `translateX(-${slideWidth * index}px)`;
    updateActiveDot();
  }

  function slideNext() {
    currentIndex++;
    goToSlide(currentIndex);

    if (currentIndex >= totalSlides) {
      setTimeout(() => {
        slider.style.transition = "none";
        slider.style.transform = "translateX(0px)";
        currentIndex = 0;
        updateActiveDot();
      }, 700);
    }
  }

  let autoSlide = setInterval(slideNext, 4000);

  // Re-adjust slider on window resize
  window.addEventListener("resize", () => {
    slider.style.transition = "none";
    slider.style.transform = `translateX(-${window.innerWidth * currentIndex}px)`;
  });

  // Make indicators clickable
  dots.forEach((dot, i) => {
    dot.addEventListener("click", () => {
      currentIndex = i;
      goToSlide(currentIndex);
      clearInterval(autoSlide);
      autoSlide = setInterval(slideNext, 4000);
    });
  });

  updateActiveDot();
</script>