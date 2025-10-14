document.addEventListener("DOMContentLoaded", function () {
    var modal = new bootstrap.Modal(document.getElementById('modalLinks'));
    modal.show();

    // Gera IDs dinâmicos para os títulos e popula a lista de links no modal
    const topicsList = document.getElementById("topics-list");

    document.querySelectorAll("h2, h5").forEach((heading, index) => {
        let generatedId = heading.innerText.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        if (/^[0-9]/.test(generatedId)) {
            generatedId = 'id-' + generatedId; // Evita IDs começando com número
        }
        heading.id = generatedId;

        const listItem = document.createElement("li");
        listItem.classList.add("list-group-item");

        // Adiciona uma classe extra para diferenciar visualmente h5 se desejar
        const linkClass = heading.tagName.toLowerCase() === "h5" ? "link-anchor small" : "link-anchor";

        let linkStyle = "";
        if (heading.tagName.toLowerCase() === "h5") {
            linkStyle = 'style="font-weight:bold; color:#000000;background-color:#fce7c9"';// Bootstrap azul padrão
        }

       
        if (heading.tagName.toLowerCase() === "h2") {
            linkStyle = 'style="padding:3px"'; // Bootstrap azul padrão
        }

        listItem.innerHTML = `<a href="#${generatedId}" class="link-anchor" data-bs-dismiss="modal" ${linkStyle}>${heading.innerText}</a>`;
        topicsList.appendChild(listItem);
    });
});

document.addEventListener("click", function (event) {
    if (event.target.classList.contains("link-anchor")) {
        event.preventDefault();
        const targetId = event.target.getAttribute("href");
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('modalLinks'));
            modal.hide();
            setTimeout(() => {
                window.location.hash = targetId;
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }, 300);
        }
    }
});
