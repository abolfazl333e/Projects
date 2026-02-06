<?php
$folder = 'images/paints';
$images = glob($folder . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<title>🎨 گالری نقاشی‌های دانش آموزان 🌈</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    background: linear-gradient(135deg, #fff4e6, #ffe6f7);
    font-family: "Vazirmatn", sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
}

h1 {
    color: #ff6f61;
    text-shadow: 2px 2px 4px #fff;
    margin: 20px 0 60px;
    font-size: clamp(20px, 5vw, 32px);
    text-align: center;
}

.slider {
    width: 100%;
    max-width: 800px;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    overflow: hidden;
    position: relative;
    background: #fff;
}

.slides {
    display: flex;
    transition: transform 1s ease-in-out;
}

.slide {
    flex-shrink: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fff;
}

.slide img {
    max-width: 100%;
    max-height: 500px; /* می‌توانید این ارتفاع را تغییر دهید */
    object-fit: contain; /* نمایش کامل تصویر بدون برش */
}
.dots {
    text-align: center;
    margin-top: 15px;
}
.dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #ccc;
    margin: 0 5px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}
.dot.active {
    background-color: #ff6f61;
    transform: scale(1.2);
    box-shadow: 0 0 8px #ff6f61;
}

.nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 111, 97, 0.7);
    color: white;
    border: none;
    font-size: 24px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    transition: background 0.3s;
}
.nav-btn:hover {
    background: rgba(255, 111, 97, 1);
}
.prev { left: 10px; }
.next { right: 10px; }

/* ریسپانسیو */
@media (max-width: 480px) {
    .nav-btn { width: 32px; height: 32px; font-size: 18px; }
    .slide img { max-height: 300px; }
}
</style>
</head>
<body>

<h1>🎨 گالری نقاشی‌های دانش آموزان 🌈</h1>

<div class="slider" id="slider">
    <div class="slides" id="slides">
        <?php foreach ($images as $img): ?>
            <div class="slide">
                <img src="<?php echo $img; ?>" alt="نقاشی">
            </div>
        <?php endforeach; ?>
    </div>
    <button class="nav-btn prev" onclick="prevSlide()">❮</button>
    <button class="nav-btn next" onclick="nextSlide()">❯</button>
</div>

<div class="dots" id="dots">
    <?php foreach ($images as $index => $img): ?>
        <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)"></span>
    <?php endforeach; ?>
</div>

<script>
let currentSlide = 0;
const slides = document.getElementById("slides");
const dots = document.querySelectorAll(".dot");
const totalSlides = <?php echo count($images); ?>;

function showSlide(index) {
    if(index >= totalSlides) currentSlide = 0;
    else if(index < 0) currentSlide = totalSlides-1;
    else currentSlide = index;

    slides.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot,i)=>dot.classList.toggle("active", i===currentSlide));
}

function nextSlide(){ showSlide(currentSlide+1); resetAutoSlide(); }
function prevSlide(){ showSlide(currentSlide-1); resetAutoSlide(); }
function goToSlide(index){ showSlide(index); resetAutoSlide(); }

let autoSlide = setInterval(nextSlide, 7000);
function resetAutoSlide(){
    clearInterval(autoSlide);
    autoSlide = setInterval(nextSlide,7000);
}

const slider = document.getElementById("slider");
slider.addEventListener('mouseenter', resetAutoSlide);
slider.addEventListener('mouseleave', resetAutoSlide);

// پشتیبانی لمسی
let startX=0;
slides.addEventListener('touchstart', e => startX = e.touches[0].clientX);
slides.addEventListener('touchend', e => {
    let diff = e.changedTouches[0].clientX - startX;
    if(diff > 50) prevSlide();
    else if(diff < -50) nextSlide();
});
</script>

</body>
</html>
