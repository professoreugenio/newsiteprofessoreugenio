        document.addEventListener("DOMContentLoaded", function() {
            var modal = new bootstrap.Modal(document.getElementById('modalLinks'));
            modal.show();
            // Gera IDs dinâmicos para os títulos e popula a lista de links no modal
            const topicsList = document.getElementById("topics-list");
            document.querySelectorAll("h2").forEach((h2, index) => {
                let generatedId = h2.innerText.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                if (/^[0-9]/.test(generatedId)) {
                    generatedId = 'id-' + generatedId; // Evita IDs começando com número
                }
                h2.id = generatedId;
                const listItem = document.createElement("li");
                listItem.classList.add("list-group-item");
                listItem.innerHTML = `<a href="#${generatedId}" class="link-anchor" data-bs-dismiss="modal">${h2.innerText}</a>`;
                topicsList.appendChild(listItem);
            });
        });
        document.addEventListener("click", function(event) {
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
