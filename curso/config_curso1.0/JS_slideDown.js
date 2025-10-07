document.getElementById("toggleListaModulos").addEventListener("click", function() {
        let lista=document.getElementById("listaModulosLinks");

        if (lista.style.display==="none" || lista.style.display==="") {
            lista.style.display="block";
            lista.style.animation="slideDown 0.5s ease-out forwards";
        }

        else {
            lista.style.animation="slideUp 0.5s ease-out forwards";
            setTimeout(()=> lista.style.display="none", 500);
        }
    });