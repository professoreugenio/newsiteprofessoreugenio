
// Seleciona todos os elementos <h2> dentro do div com ID "curso-corpotexto"
const h2Elements = document.querySelectorAll('#curso-corpotexto h2');

// Seleciona o div onde a lista será exibida
const exibeListaMenu = document.getElementById('exibelista');

// Cria a <ul> da lista
const ul = document.createElement('ul');

h2Elements.forEach(h2 => {
    // Gera um ID baseado no texto do <h2>
    const generatedId = h2.innerText.toLowerCase().replace(/[^a-z0-9]/g, '-');

    // Define o ID no elemento <h2>
    h2.id = generatedId;

    // Cria o item da lista
    const li = document.createElement('li');
    const link = document.createElement('a');

    link.href = `#${generatedId}`;
    link.innerText = h2.innerText;
    link.classList.add('link-anchor');

    li.appendChild(link);
    ul.appendChild(li);
});

// Adiciona a lista ao contêiner
exibeListaMenu.appendChild(ul);
