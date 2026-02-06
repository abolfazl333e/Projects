<?php
require_once 'inc/db.php';
$conn->set_charset('utf8mb4');

// دروس
$lessonsRes = $conn->query("SELECT id,name FROM lessons");
$lessons=[];
while($r=$lessonsRes->fetch_assoc()) $lessons[$r['id']]=$r['name'];

$selectedLesson = isset($_GET['lesson']) ? intval($_GET['lesson']) : 0;

// فیلتر آزمایش‌ها
if($selectedLesson > 0){
    $expRes = $conn->query("SELECT * FROM experiments WHERE lesson_id=$selectedLesson ORDER BY created_at DESC");
} else {
    $expRes = $conn->query("SELECT * FROM experiments ORDER BY created_at DESC");
}

$experiments=[];
while($exp=$expRes->fetch_assoc()){
    $exp['images']=[];
    $imgRes=$conn->query("SELECT filename FROM experiment_images WHERE experiment_id=".$exp['id']);
    while($img=$imgRes->fetch_assoc()){
        $exp['images'][]=$img['filename'];
    }
    $experiments[]=$exp;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<title>آزمایش‌ها</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600&display=swap');

body {
  font-family: 'Vazirmatn', sans-serif;
  margin: 0; padding: 0;
  background: linear-gradient(135deg, #f9f9f9, #e8f6ff);
  min-height: 100vh;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 30px 20px 60px;
}

h2 {
  text-align: center;
  color: #264653;
  font-size: 32px;
  margin-bottom: 30px;
  position: relative;
}
h2::after {
  content: "";
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: #2a9d8f;
  border-radius: 3px;
}

.filter-box {
  text-align: center;
  background: #fff;
  padding: 15px 20px;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
  margin-bottom: 30px;
  display: inline-block;
}

select {
  font-family: 'Vazirmatn', sans-serif;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 16px;
  background: #fff;
  transition: 0.3s;
}
select:focus {
  outline: none;
  border-color: #2a9d8f;
  box-shadow: 0 0 0 3px rgba(42,157,143,0.2);
}

.card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.1);
  padding: 20px;
  margin-bottom: 25px;
  transition: transform 0.25s, box-shadow 0.25s;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.12);
}

.card h3 {
  margin: 0 0 8px;
  color: #264653;
  font-size: 22px;
}
.card p {
  margin: 6px 0;
  color: #555;
  text-align: justify;
}

.images {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  margin: 10px 0;
}
.images img {
  height: 120px;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.3s;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
.images img:hover {
  transform: scale(1.05);
}

/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.85);
  align-items: center;
  justify-content: center;
  flex-direction: column;
  backdrop-filter: blur(5px);
}
.modal-content {
  max-width: 90%;
  max-height: 80vh;
  border-radius: 16px;
  box-shadow: 0 4px 30px rgba(0,0,0,0.5);
  transition: 0.3s;
}
.caption {
  color: #fff;
  font-size: 18px;
  margin-top: 12px;
  text-align: center;
}

.close {
  position: absolute;
  top: 20px;
  right: 30px;
  color: #fff;
  font-size: 36px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.close:hover { color: #f1f1f1; }

.nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  font-size: 36px;
  cursor: pointer;
  padding: 10px 20px;
  user-select: none;
}
.prev-btn { left: 20px; }
.next-btn { right: 20px; }

.no-exp {
  text-align: center;
  color: #777;
  font-size: 18px;
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
/* اصلاح جهت فلش‌ها در حالت راست‌به‌چپ */
html[dir="rtl"] .modal-nav.left {
  right: 20px;
  left: auto;
  transform: rotate(180deg);
}

html[dir="rtl"] .modal-nav.right {
  left: 20px;
  right: auto;
  transform: rotate(180deg);
}
html[dir="rtl"] .modal-nav.left::before {
  content: "▶"; /* راست */
}

html[dir="rtl"] .modal-nav.right::before {
  content: "◀"; /* چپ */
}
</style>
</head>
<body>

<div class="container">
  <h2>آزمایش‌ها</h2>

  <div class="filter-box">
    <form method="GET">
      <label for="lesson">انتخاب درس:</label>
      <select name="lesson" id="lesson" onchange="this.form.submit()">
        <option value="0">همه دروس</option>
        <?php foreach($lessons as $id=>$name): ?>
          <option value="<?= $id ?>" <?= ($id==$selectedLesson)?'selected':'' ?>><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <?php if(empty($experiments)): ?>
    <p class="no-exp">هیچ آزمایشی برای این درس ثبت نشده است 🧪</p>
  <?php endif; ?>

  <?php foreach($experiments as $index=>$exp): ?>
  <div class="card">
      <h3>🧫 <?= htmlspecialchars($exp['title']) ?></h3>
      <p><strong>درس:</strong> <?= htmlspecialchars($lessons[$exp['lesson_id']] ?? '-') ?></p>
      <div class="images" data-exp="<?= $index ?>" data-title="<?= htmlspecialchars($exp['title']) ?>">
          <?php foreach($exp['images'] as $img): ?>
              <img src="experiments_images/<?= htmlspecialchars($img) ?>" alt="" class="clickable-img">
          <?php endforeach; ?>
      </div>
      <p><strong>توضیح:</strong> <?= htmlspecialchars($exp['results']) ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Modal -->
<div id="imgModal" class="modal">
  <span class="close">&times;</span>
  <span class="nav-btn next-btn">&#10094;</span>
  <img class="modal-content" id="modalImage">
  <span class="nav-btn prev-btn">&#10095;</span>
  <div class="caption" id="captionText"></div>
</div>

<script>
const modal = document.getElementById("imgModal");
const modalImg = document.getElementById("modalImage");
const captionText = document.getElementById("captionText");
const closeBtn = document.querySelector(".close");
const nextBtn = document.querySelector(".next-btn");
const prevBtn = document.querySelector(".prev-btn");

let currentImages = [];
let currentIndex = 0;
let currentCaption = "";

// باز کردن مدال
document.querySelectorAll('.images').forEach(container => {
  const imgs = Array.from(container.querySelectorAll('img'));
  const title = container.getAttribute('data-title');
  imgs.forEach((img, index) => {
    img.addEventListener('click', () => {
      currentImages = imgs.map(i => i.src);
      currentCaption = title;
      currentIndex = index;
      openModal(currentImages[currentIndex], currentCaption);
    });
  });
});

function openModal(src, caption) {
  modal.style.display = "flex";
  modalImg.src = src;
  captionText.textContent = caption;
}

function closeModal() { modal.style.display = "none"; }
function showNext() { currentIndex = (currentIndex + 1) % currentImages.length; modalImg.src = currentImages[currentIndex]; }
function showPrev() { currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length; modalImg.src = currentImages[currentIndex]; }

closeBtn.onclick = closeModal;
nextBtn.onclick = showNext;
prevBtn.onclick = showPrev;
modal.onclick = e => { if (e.target === modal) closeModal(); };
document.addEventListener('keydown', e => {
  if (modal.style.display === "flex") {
    if (e.key === "ArrowRight") showNext();
    else if (e.key === "ArrowLeft") showPrev();
    else if (e.key === "Escape") closeModal();
  }
});
</script>
</body>
</html>
