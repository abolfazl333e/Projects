<?php
session_start();

$is_admin = isset($_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🏆 دانش‌آموز برتر</title>
<style>
body {
    font-family: "B Nazanin", sans-serif;
    background: #f0f4f8;
    margin: 0;
    padding: 20px;
}
h1 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}
.accordion {
    max-width: 1000px;
    margin: 0 auto;
}
.accordion-item {
    background: #fff;
    border-radius: 12px;
    margin-bottom: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.accordion-header {
    background: linear-gradient(90deg,#ff9800,#f57c00);
    color: #fff;
    padding: 15px 20px;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
}
.accordion-content {
    max-height: 0;
    overflow: hidden;
    background: #fff;
    transition: max-height 0.3s ease, padding 0.3s ease;
    padding: 0 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
.accordion-content.active {
    padding: 20px;
}
.icon {
    transition: transform 0.3s ease;
}
.icon.rotate {
    transform: rotate(90deg);
}
.card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    width: 220px;
    padding: 20px;
    text-align: center;
    position: relative;
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-10px);
}
.card img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid gold;
    margin-bottom: 10px;
}
.stars {
    color: gold;
    font-size: 18px;
    margin-top: 5px;
    display: flex;
    justify-content: center;
    gap: 3px;
}
.star {
    opacity: 0;
    transform: scale(0);
    animation: pop 0.5s forwards;
}
@keyframes pop {
    0% {opacity:0;transform:scale(0);}
    50% {opacity:1;transform:scale(1.3);}
    100% {opacity:1;transform:scale(1);}
}
.medal {
    position: absolute;
    top: -10px;
    right: -10px;
    background: gold;
    color: white;
    padding: 8px;
    border-radius: 50%;
    font-size: 16px;
}

/* دکمه ثبت ماه شیک */
.saveMonthBtn {
    margin-top: 20px;
    padding: 12px 25px;
    background: linear-gradient(90deg,#ff9800,#f57c00);
    color: white;
    font-size: 1rem;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.saveMonthBtn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.25);
}
.saveMonthBtn:active {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.saveMonthBtn:disabled {
    background: #ccc;
    cursor: not-allowed;
    box-shadow: none;
    color: #666;
}
</style>
</head>
<body>

<h1 id="pageTitle">🏆 دانش‌آموز برتر سال ... 🏆</h1>
<div class="accordion" id="accordionContainer"></div>

<script>
async function loadTopStudents() {
    const res = await fetch('api/get_top_students.php');
    const json = await res.json();

    document.getElementById('pageTitle').innerText = `🏆 دانش‌آموز برتر سال ${json.year} 🏆`;

    const container = document.getElementById('accordionContainer');
    const openMonths = {};
    document.querySelectorAll('.accordion-item').forEach((item, idx) => {
        const content = item.querySelector('.accordion-content');
        if (content.classList.contains('active')) openMonths[idx] = true;
    });
    container.innerHTML = '';

    const monthNames = ['', 'فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];

    json.data.forEach((month, idx) => {
        const item = document.createElement('div');
        item.className = 'accordion-item';

        const isArchived = month.archived ?? false;
        item.innerHTML = `
            <div class="accordion-header">
                <span>${monthNames[month.month]} ${json.year}</span>
                <span class="icon">▶</span>
            </div>
            <div class="accordion-content">
                ${month.students.length ? month.students.map(s=>{
                    const stars = '⭐'.repeat(s.total_stars);
                    const img = `images/students/${s.id}.jpg`;
                    return `
                        <div class="card">
                            <div class="medal">🏆</div>
                            <img src="${img}" onerror="this.src='images/default_student.png'">
                            <h3>${s.name}</h3>
                            <p>امتیاز: ${s.balance}</p>
                            <div class="stars">${stars}</div>
                        </div>
                    `;
                }).join('') : '<p>دانش‌آموزی برای این ماه ثبت نشده است.</p>'}

                <?php if ($is_admin): ?>
                <div style="width:100%;text-align:center;">
                    <button class="saveMonthBtn" data-month="${month.month}" ${isArchived ? 'disabled' : ''}>
                        ${isArchived ? 'ثبت شده ✅' : 'ثبت نهایی ماه'}
                    </button>
                </div>
                <?php endif; ?>
            </div>
        `;
        container.appendChild(item);

        if (openMonths[idx]) {
            const content = item.querySelector('.accordion-content');
            const icon = item.querySelector('.icon');
            content.classList.add('active');
            content.style.maxHeight = content.scrollHeight + 'px';
            icon.classList.add('rotate');
        }
    });

    // فعال‌سازی آکاردئون
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.icon');
            const open = document.querySelector('.accordion-content.active');
            if (content === open) {
                content.style.maxHeight = null;
                content.classList.remove('active');
                icon.classList.remove('rotate');
                return;
            }
            if (open) {
                open.style.maxHeight = null;
                open.classList.remove('active');
                open.previousElementSibling.querySelector('.icon').classList.remove('rotate');
            }
            content.style.maxHeight = content.scrollHeight + 'px';
            content.classList.add('active');
            icon.classList.add('rotate');
        });
    });

    // دکمه ثبت ماه
    document.querySelectorAll('.saveMonthBtn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const month = btn.dataset.month;
            const monthData = json.data.find(m => m.month == month);
            if (!monthData || !monthData.students.length) {
                alert('دانش‌آموزی برای این ماه وجود ندارد.');
                return;
            }
            const res = await fetch('api/save_top_students.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({month: month, year: json.year, students: monthData.students})
            });
            const result = await res.json();
            if (result.success) {
                alert('ثبت ماه انجام شد ✅');
                btn.innerText = 'ثبت شده ✅';
                btn.disabled = true;
            } else {
                alert('خطا در ثبت ماه ❌');
            }
        });
    });
}

loadTopStudents();
setInterval(loadTopStudents, 20000);
</script>
</body>
</html>
