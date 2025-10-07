// Seleciona todos os elementos <h2> dentro do div com ID "curso-corpotexto"
const h2Elements = document.querySelectorAll('#curso-corpotexto h2');

// Seleciona o div onde a lista será exibida
const exibeListaMenu = document.getElementById('exibelistaTopicos');

// Cria uma lista não ordenada para exibir os textos
const ol = document.createElement('ol');
ol.classList.add('menu-lista'); // Classe para estilizar via CSS

// Função para gerar um ID seguro baseado no texto
const generateId = (text) => {
    return text.trim().toLowerCase()
        .replace(/[áàâãä]/g, 'a')
        .replace(/[éèêë]/g, 'e')
        .replace(/[íìîï]/g, 'i')
        .replace(/[óòôõö]/g, 'o')
        .replace(/[úùûü]/g, 'u')
        .replace(/ç/g, 'c')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

h2Elements.forEach(h2 => {
    // Define o ID no elemento <h2> se ainda não tiver
    if (!h2.id) {
        h2.id = generateId(h2.innerText);
    }

    // Cria o item de lista e o link
    const li = document.createElement('li');
    li.classList.add('menu-item');

    const link = document.createElement('a');
    link.href = `#${h2.id}`;
    link.innerText = h2.innerText;
    link.classList.add('menu-link');

    li.appendChild(link);
    ol.appendChild(li);
});

// Adiciona a lista ao container
exibeListaMenu.appendChild(ol);
