    document.addEventListener("DOMContentLoaded", function () {
        const pres = document.querySelectorAll("#curso-corpotexto pre");

        pres.forEach((pre, index) => {
            const wrapper = document.createElement("div");
    wrapper.classList.add("pre-wrapper", "mb-3");
    pre.parentNode.insertBefore(wrapper, pre);
    wrapper.appendChild(pre);

    const button = document.createElement("button");
    button.innerText = "Copiar";
    button.className = "copy-btn";
    button.setAttribute("data-target", index);

    button.addEventListener("click", function () {
                const range = document.createRange();
    range.selectNodeContents(pre);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);

    try {
                    const successful = document.execCommand("copy");
    if (successful) {
        showCopyTooltip();
    button.innerText = "Copiado!";
                        setTimeout(() => button.innerText = "Copiar", 2000);
                    } else {
        alert("Falha ao copiar");
                    }
                } catch (err) {
        alert("Erro ao copiar: " + err);
                }

    selection.removeAllRanges();
            });

    wrapper.appendChild(button);
        });

    function showCopyTooltip() {
            const tooltip = document.getElementById("copyTooltip");
    tooltip.style.display = "block";
    tooltip.style.opacity = "1";
            setTimeout(() => {
        tooltip.style.opacity = "0";
                setTimeout(() => tooltip.style.display = "none", 400);
            }, 1500);
        }
    });

