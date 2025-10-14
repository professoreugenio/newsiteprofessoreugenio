
function abrirPainel() {
    document.getElementById('painelLateral').style.transform = 'translateX(0)';
}

function fecharPainel() {
    document.getElementById('painelLateral').style.transform = 'translateX(-100%)';
}


function abrirMenu(menuId) {
    const todosMenus = document.querySelectorAll('.submenu');
    const todosToggles = document.querySelectorAll('.toggle-seta');
    const todosLinks = document.querySelectorAll('#painelLateral a.d-block');

    todosMenus.forEach(menu => {
        if (menu.id === 'menu-' + menuId) {
            menu.classList.toggle('ativo');
        } else {
            menu.classList.remove('ativo');
        }
    });

    todosToggles.forEach(toggle => {
        if (toggle.id === 'toggle-' + menuId) {
            toggle.classList.toggle('rotacionado');
        } else {
            toggle.classList.remove('rotacionado');
        }
    });

    todosLinks.forEach(link => link.classList.remove('ativo'));
    const linkAtivo = document.querySelector('[onclick="abrirMenu(\'' + menuId + '\')"]');
    if (linkAtivo) linkAtivo.classList.add('ativo');
}

