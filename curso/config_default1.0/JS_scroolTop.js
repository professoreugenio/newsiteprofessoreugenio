    window.onscroll = function() {
        let botao = document.getElementById("btnTopo");
        if (document.documentElement.scrollTop > window.innerHeight) {
        botao.style.display = "block";
        } else {
        botao.style.display = "none";
        }
    };

    function voltarAoTopo() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
