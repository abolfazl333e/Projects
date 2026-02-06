const leafImages = [
    'images/orange.png',
    'images/red.png',
    'images/yellow.png',
    'images/brown.png'
];

const numLeaves = 15; // کاهش تعداد برگ‌ها برای سبک‌تر شدن

function createLeaf() {
    const leaf = document.createElement('div');
    leaf.classList.add('leaf');

    leaf.style.left = Math.random() * 100 + 'vw';
    const size = 20 + Math.random() * 30;
    leaf.style.width = size + 'px';
    leaf.style.height = size + 'px';

    const duration = 5 + Math.random() * 5;
    leaf.style.animationDuration = duration + 's';
    leaf.style.animationDelay = Math.random() * 5 + 's';

    const img = leafImages[Math.floor(Math.random() * leafImages.length)];
    leaf.style.backgroundImage = `url(${img})`;
    leaf.style.transform = `rotate(${Math.random() * 360}deg)`;

    document.body.appendChild(leaf);

    setTimeout(() => leaf.remove(), (duration + parseFloat(leaf.style.animationDelay)) * 1000);
}

// ایجاد اولیه برگ‌ها
for (let i = 0; i < numLeaves; i++) createLeaf();

// ایجاد برگ‌ها به صورت متناوب
setInterval(createLeaf, 1500);
