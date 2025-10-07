// Abrir canvas
        const buttons = document.querySelectorAll('.btn-canvas');
        const canvases = document.querySelectorAll('.canvas');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-target');
                document.getElementById(target).classList.add('active');
            });
        });

        // Fechar canvas
        const closeButtons = document.querySelectorAll('.close-btn-canvas');
        closeButtons.forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                const target = closeBtn.getAttribute('data-close');
                document.getElementById(target).classList.remove('active');
            });
        });