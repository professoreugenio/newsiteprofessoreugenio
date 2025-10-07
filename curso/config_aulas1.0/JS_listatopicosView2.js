/*document.addEventListener("DOMContentLoaded", function () {
    const h2Elements = document.querySelectorAll('#curso-corpotexto h2');
    const exibeListaMenu = document.getElementById('exibelistaTopicos');
    if (!exibeListaMenu || h2Elements.length === 0) return;

    const ul = document.createElement('ul');
    ul.classList.add('list-unstyled');

    h2Elements.forEach(h2 => {
        const h2Text = h2.textContent.trim();
        if (h2Text === '') return;

        const generatedId = h2Text
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');

        h2.id = generatedId;

        const li = document.createElement('li');
        const link = document.createElement('a');
        link.href = `#${generatedId}`;
        link.classList.add('link-anchor');

        const icon = document.createElement('i');
        icon.classList.add('bi', 'bi-dot'); // ícone de marcador

        link.appendChild(icon);
        link.appendChild(document.createTextNode(h2Text));

        li.appendChild(link);
        ul.appendChild(li);
    });

    exibeListaMenu.innerHTML = '';
    exibeListaMenu.appendChild(ul);
});
*/

document.addEventListener("DOMContentLoaded", function () {
    const h2Elements = document.querySelectorAll('#curso-corpotexto h2');
    const exibeListaMenu = document.getElementById('exibelistaTopicos');
    if (!exibeListaMenu || h2Elements.length === 0) return;

    const ul = document.createElement('ul');
    ul.classList.add('list-unstyled');

    let contador = 1;

    h2Elements.forEach(h2 => {
        const h2Text = h2.textContent.trim();
        if (h2Text === '') return;

        const generatedId = h2Text
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');

        h2.id = generatedId;

        const li = document.createElement('li');
        const link = document.createElement('a');
        link.href = `#${generatedId}`;
        link.classList.add('link-anchor');

        // const icon = document.createElement('i');
        // icon.classList.add('bi', 'bi-dot'); // ícone de marcador
        // const checkIcon = document.createElement('i');
        // checkIcon.classList.add('bi', 'bi-check'); // ícone de check
        // link.appendChild(checkIcon);

        // Adiciona o número da ordem antes do texto
        const itemText = `${contador}. ${h2Text}`;

        // link.appendChild(icon);
        link.appendChild(document.createTextNode(itemText));

        li.appendChild(link);
        ul.appendChild(li);

        contador++;
    });

    exibeListaMenu.innerHTML = '';
    exibeListaMenu.appendChild(ul);
});
