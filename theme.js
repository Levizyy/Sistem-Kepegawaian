// Terapkan tema tersimpan sesegera mungkin agar tidak "flash"
(function () {
    var tema = localStorage.getItem('sik_theme') || 'light';
    document.documentElement.setAttribute('data-theme', tema);
})();

function toggleTheme() {
    var html = document.documentElement;
    var sekarang = html.getAttribute('data-theme') || 'light';
    var berikutnya = sekarang === 'light' ? 'dark' : 'light';
    html.setAttribute('data-theme', berikutnya);
    localStorage.setItem('sik_theme', berikutnya);
    perbaruiLabelTema();
}

function perbaruiLabelTema() {
    var tema = document.documentElement.getAttribute('data-theme') || 'light';
    var label = document.querySelectorAll('.theme-toggle-label');
    var icon = document.querySelectorAll('.theme-toggle-icon');
    label.forEach(function (el) {
        el.textContent = tema === 'light' ? 'Gelap' : 'Terang';
    });
    icon.forEach(function (el) {
        el.innerHTML = tema === 'light' ? ICON_MOON : ICON_SUN;
    });
}

var ICON_SUN = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>';
var ICON_MOON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>';

document.addEventListener('DOMContentLoaded', perbaruiLabelTema);

function toggleSidebar() {
    var shell = document.querySelector('.app-shell');
    if (shell) shell.classList.toggle('sidebar-open');
}
