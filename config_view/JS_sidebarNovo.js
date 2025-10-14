const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

const sidebar = document.getElementById('sidebar');
const hideBtn = document.getElementById('hideSidebarBtn');
const floatingBtn = document.getElementById('toggleFloatingBtn-menu');

// Oculta a sidebar e mostra botão flutuante
hideBtn.addEventListener('click', () => {
    sidebar.classList.add('hidden');
    floatingBtn.style.display = 'flex';
});

// Mostra a sidebar novamente
floatingBtn.addEventListener('click', () => {
    sidebar.classList.remove('hidden');
    floatingBtn.style.display = 'none';
});