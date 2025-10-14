document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const canvas = document.getElementById(targetId);
            if (canvas) {
                canvas.classList.toggle('active'); // alterna abrir/fechar
            }
        });
    });
});