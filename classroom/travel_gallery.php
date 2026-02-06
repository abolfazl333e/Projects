<?php
// مسیر پوشه عکس‌ها
$imageDir = "images/travel";
$images = glob($imageDir . "/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> دبستان سردار سلیمانی 🌈</title>
<style>
body {
    font-family: "B Nazanin", "Comic Sans MS", sans-serif;
    background: linear-gradient(135deg, #fceabb, #f8b500);
    margin: 0;
    padding: 0;
    direction: rtl;
    text-align: center;
}

/* بخش هدر */
.header {
    background-color: #fff;
    padding: 15px;
    border-bottom: 4px solid #ffcc00;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header h1 {
    color: #ff6f61;
    font-size: 28px;
    margin: 5px 0;
}

.header p {
    color: #444;
    font-size: 16px;
    margin: 0;
}

/* اسلایدر */
.slider {
    width: 90%;
    max-width: 500px;
    margin: 25px auto;
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    border: 4px solid #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    background-color: #fff;
}

.slider img {
    width: 100%;
    height: auto;
    display: none;
    border-radius: 20px;
    transition: opacity 0.5s ease;
}

.slider img.active {
    display: block;
    opacity: 1;
}

/* دکمه‌ها */
.buttons {
    margin-top: 15px;
}

button {
    background-color: #ff6f61;
    color: white;
    border: none;
    padding: 10px 20px;
    margin: 8px;
    border-radius: 25px;
    cursor: pointer;
    font-family: "B Nazanin", "Comic Sans MS", sans-serif;
    font-size: 16px;
    font-weight: bold;
    transition: background 0.3s, transform 0.2s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

button:hover {
    transform: scale(1.05);
}

#prev { background-color: #4caf50; }
#next { background-color: #2196f3; }

#prev:hover { background-color: #3e8e41; }
#next:hover { background-color: #1976d2; }

/* نقطه‌ها */
.dots {
    margin-top: 10px;
}

.dot {
    display: inline-block;
    width: 14px;
    height: 14px;
    background-color: #ddd;
    border-radius: 50%;
    margin: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.dot.active {
    background-color: #ff6f61;
}

footer {
    margin-top: 25px;
    color: #555;
    font-size: 14px;
}

@media (max-width: 600px) {
    .header h1 { font-size: 22px; }
    .header p { font-size: 14px; }
    button { padding: 8px 14px; font-size: 14px; }
}
</style>
</head>
<body>

<!-- بخش عنوان و توضیح -->
<div class="header">
    <h1>🌻 دبستان سردار سلیمانی 🌻</h1>
</div>

<!-- گالری -->
<div class="slider" id="slider">
    <?php foreach ($images as $i => $img): ?>
        <img src="<?php echo $img; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" alt="عکس سفر">
    <?php endforeach; ?>
</div>

<div class="buttons">
    <button id="prev">⬅️ قبلی</button>
    <button id="next">بعدی ➡️</button>
</div>

<div class="dots" id="dots"></div>

<footer>
    📸 طراحی شده با عشق برای دانش‌آموزان دبستان سردار سلیمانی 💛
</footer>

<script>
const images = document.querySelectorAll("#slider img");
const dotsContainer = document.getElementById("dots");
let index = 0;
let slideInterval; // متغیر برای ذخیره تایمر

// ایجاد نقطه‌ها
images.forEach((_, i) => {
    const dot = document.createElement("span");
    dot.classList.add("dot");
    if (i === 0) dot.classList.add("active");
    dot.addEventListener("click", () => {
        showImage(i);
        resetTimer(); // وقتی روی نقطه کلیک میشه تایمر ریست بشه
    });
    dotsContainer.appendChild(dot);
});

const dots = document.querySelectorAll(".dot");

function showImage(i) {
    images.forEach(img => img.classList.remove("active"));
    dots.forEach(dot => dot.classList.remove("active"));
    images[i].classList.add("active");
    dots[i].classList.add("active");
    index = i;
}

// تابعی برای شروع تایمر
function startTimer() {
    slideInterval = setInterval(() => {
        index = (index + 1) % images.length;
        showImage(index);
    }, 10000);
}

// تابعی برای ریست تایمر
function resetTimer() {
    clearInterval(slideInterval);
    startTimer();
}

// دکمه بعدی
document.getElementById("next").onclick = () => {
    index = (index + 1) % images.length;
    showImage(index);
    resetTimer(); // ریست تایمر هنگام کلیک
};

// دکمه قبلی
document.getElementById("prev").onclick = () => {
    index = (index - 1 + images.length) % images.length;
    showImage(index);
    resetTimer(); // ریست تایمر هنگام کلیک
};

// شروع تایمر اولیه
startTimer();
</script>


</body>
</html>
