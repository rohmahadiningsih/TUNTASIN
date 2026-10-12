(function () {
  const cfg = window.TUNTASIN || {};
  const status = document.getElementById('notif-status');
  if (!('Notification' in window)) { if (status) status.textContent = 'Browser tidak mendukung notifikasi.'; return; }
  if (!cfg.notifications) { if (status) status.textContent = 'Notifikasi dimatikan.'; return; }

  if (Notification.permission === 'default') Notification.requestPermission().then(update);
  update();
  function update() {
    if (status) status.textContent = Notification.permission === 'granted'
      ? 'Notifikasi aktif: kamu akan diingatkan 60 menit sebelum tenggat.'
      : 'Izin notifikasi belum diberikan di browser.';
  }

  const shown = JSON.parse(sessionStorage.getItem('tuntasin-notified') || '[]');
  async function check() {
    if (Notification.permission !== 'granted') return;
    try {
      const res = await fetch('api.php', { credentials: 'same-origin' });
      const tasks = await res.json();
      tasks.forEach(t => {
        if (shown.includes(t.id)) return;
        new Notification('⏰ Pengingat TUNTASIN', { body: `${t.title} — tenggat ${t.due.slice(0, 16)}` });
        shown.push(t.id);
      });
      sessionStorage.setItem('tuntasin-notified', JSON.stringify(shown));
    } catch (e) { /* abaikan */ }
  }
  check();
  setInterval(check, 60000);
})();
