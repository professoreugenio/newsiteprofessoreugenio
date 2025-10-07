document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("btliberaLicao");

    if (btn) {
        btn.addEventListener("click", function () {
            const btnIcon = btn.querySelector("i");
            const codAula = btn.getAttribute("data-id");

            // Desativa o botão temporariamente
            btn.disabled = true;

            fetch("config_curso1.0/liberaLicao.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "aula=" + encodeURIComponent(codAula),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        if (data.liberado === "1") {
                            btnIcon.className = "bi bi-lock-fill text-success";
                            btn.setAttribute("title", "Lição Liberada");
                        } else {
                            btnIcon.className = "bi bi-unlock-fill text-danger";
                            btn.setAttribute("title", "Lição Bloqueada");
                        }
                    } else {
                        alert("Erro ao atualizar status da lição.");
                    }
                })
                .catch(() => {
                    alert("Erro de conexão com o servidor.");
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    }
});
