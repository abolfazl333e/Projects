async function fetchStatus() {
    const res = await fetch('api/get_today_status.php');
    const data = await res.json();
    const container = document.getElementById('studentsList');
    container.innerHTML = '';

    data.forEach(s => {
        const div = document.createElement('div');
        div.className = 'student ' +
            (s.status === 'حاضر' ? 'present' :
            (s.status === 'مرخصی' ? 'leave' : 'absent'));

        div.innerHTML = `
            <div class="name">${s.name}</div>
            <div class="status">${s.status}${s.time ? ' — ' + s.time : ''}</div>

            ${is_admin ? `
            <div class="actions">
                <button class="set-status" data-id="${s.id}" data-status="حاضر">حاضر</button>
                <button class="set-status" data-id="${s.id}" data-status="مرخصی">مرخصی</button>
                <button class="set-status" data-id="${s.id}" data-status="غایب">غایب</button>
            </div>` : ''}
        `;

        container.appendChild(div);
    });

    const lastUpdated = document.getElementById('lastUpdated');
    if (lastUpdated) {
        lastUpdated.textContent = 'آخرین بروزرسانی: ' + new Date().toLocaleTimeString();
    }
}

// دکمه‌ها
const refreshBtn = document.getElementById('refreshBtn');
const finalizeBtn = document.getElementById('finalizeBtn');

if (refreshBtn) {
    refreshBtn.addEventListener('click', fetchStatus);
}

if (finalizeBtn) {
    finalizeBtn.addEventListener('click', async () => {
        if (!confirm('آیا مطمئن هستید؟')) return;

        const res = await fetch('api/finalize_absent.php', { method: 'POST' });
        const json = await res.json();

        alert(json.msg || 'ثبت شد');
        fetchStatus();
    });
}

// ثبت دستی وضعیت
document.addEventListener('click', async e => {
    if (e.target.classList.contains('set-status')) {
        const id = e.target.dataset.id;
        const status = e.target.dataset.status;

        const res = await fetch('api/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&status=${status}`
        });

        const json = await res.json();
        alert(json.msg || 'انجام شد');
        fetchStatus();
    }
});

fetchStatus();
setInterval(fetchStatus, 5000);
