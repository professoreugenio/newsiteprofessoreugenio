<!-- FAB -->
<div class="fab-container" id="fabContainer">
    <button id="fabToggleBtn" class="fab-btn" data-bs-toggle="tooltip" title="Clique e acesse mais opções">+</button>
    <div id="fabOptions" class="fab-options">
        <button class="fab-option"><i class="bi bi-book"></i> Lições</button>
        <button class="fab-option"><i class="bi bi-paperclip"></i> Anexos</button>
        <button class="fab-option"><i class="bi bi-box"></i> Módulos</button>
        <button class="fab-option"><i class="bi bi-pencil"></i> Atividades</button>
    </div>
</div>

<!-- Bootstrap JS (para tooltip) -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fabBtn = document.getElementById('fabToggleBtn');
    const fabOptions = document.getElementById('fabOptions');
    const fabContainer = document.getElementById('fabContainer');

    // Verifica se os elementos existem
    if (!fabBtn || !fabOptions || !fabContainer) {
        console.error('FAB não encontrado no DOM');
        return;
    }

    // Toggle do menu
    fabBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log("evento 1");
        fabOptions.classList.toggle('active');
    });

    // Fecha ao clicar fora
    document.addEventListener('click', function(event) {
        const isOpen = fabOptions.classList.contains('active');
        if (isOpen && !fabContainer.contains(event.target)) {
            console.log("evento 2");
            fabOptions.classList.remove('active');
        }
    });

    // Tooltip Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>