// document.addEventListener('DOMContentLoaded', function () {
//     const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
//     tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

//     const sidebarLateral = document.getElementById('sidebarLateral');
//     const floatingBtn = document.getElementById('toggleFloatingBtn-menu');
//     const toggleLicoesBox = document.getElementById('toggleLicoesBox');
//     const licoesBox = document.getElementById('licoesBox');
//     const fecharBox = document.getElementById('fecharBox');
//     const hideSidebarBtn = document.getElementById('hidesidebarLateralBtn');

//     // Verifica existência antes de adicionar listeners
//     if (hideSidebarBtn) {
//         hideSidebarBtn.addEventListener('click', () => {
//             sidebarLateral.classList.add('hidden');
//             floatingBtn.style.display = 'flex';
//         });
//     }

//     if (floatingBtn) {
//         floatingBtn.addEventListener('click', () => {
//             sidebarLateral.classList.remove('hidden');
//             floatingBtn.style.display = 'none';
//         });
//     }

//     if (toggleLicoesBox) {
//         toggleLicoesBox.addEventListener('click', () => {
//             if (licoesBox.style.display === 'block') {
//                 licoesBox.style.display = 'none';
//             } else {
//                 licoesBox.style.display = 'block';
//             }
//         });
//     }

//     if (fecharBox) {
//         fecharBox.addEventListener('click', () => {
//             licoesBox.style.display = 'none';
//         });
//     }

//     // Lógica de leitura
//     const licoes = document.querySelectorAll('.licao');
//     licoes.forEach(licao => {
//         const button = licao.querySelector('button');
//         button?.addEventListener('click', () => {
//             licao.classList.toggle('lida');
//             const icon = licao.querySelector('i');
//             if (licao.classList.contains('lida')) {
//                 icon.classList.remove('bi-circle');
//                 icon.classList.add('bi-check-circle-fill');
//             } else {
//                 icon.classList.remove('bi-check-circle-fill');
//                 icon.classList.add('bi-circle');
//             }
//         });
//     });
// });


document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    const sidebarLateral = document.getElementById('sidebarLateral');
    const floatingBtn = document.getElementById('toggleFloatingBtn-menu');
    const toggleLicoesBox = document.getElementById('toggleLicoesBox');
    const licoesBox = document.getElementById('licoesBox');
    const fecharBox = document.getElementById('fecharBox');
    const hideSidebarBtn = document.getElementById('hidesidebarLateralBtn');


    setTimeout(() => {
        licoesBox.style.display = 'none';
    }, 25000);

    if (hideSidebarBtn) {
        hideSidebarBtn.addEventListener('click', () => {
            sidebarLateral.classList.add('hidden');
            floatingBtn.style.display = 'flex';
        });
    }

    if (floatingBtn) {
        floatingBtn.addEventListener('click', () => {
            sidebarLateral.classList.remove('hidden');
            floatingBtn.style.display = 'none';
        });
    }

    if (toggleLicoesBox) {
        toggleLicoesBox.addEventListener('click', () => {
            if (licoesBox.style.display === 'block') {
                licoesBox.style.display = 'none';
            } else {
                licoesBox.style.display = 'block';

               
                // setTimeout(() => {
                //     licoesBox.style.display = 'none';
                // }, 90000);
            }
        });
    }

    if (fecharBox) {
        fecharBox.addEventListener('click', () => {
            licoesBox.style.display = 'none';
        });
    }

    const licoes = document.querySelectorAll('.licao');
    licoes.forEach(licao => {
        const button = licao.querySelector('button');
        button?.addEventListener('click', () => {
            licao.classList.toggle('lida');
            const icon = licao.querySelector('i');
            if (licao.classList.contains('lida')) {
                icon.classList.remove('bi-circle');
                icon.classList.add('bi-check-circle-fill');
            } else {
                icon.classList.remove('bi-check-circle-fill');
                icon.classList.add('bi-circle');
            }
        });
    });
});
