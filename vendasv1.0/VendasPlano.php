<main class="container ">
    <div class="text-center mb-5" data-aos="fade-down">
        <h2 style="color: #00BB9C;">Olá, <?= htmlspecialchars($_SESSION['nomeUsuario']) ?>!</h2>
        <p class="lead">Escolha o melhor plano para acessar o <strong><?= $nomeCurso ?></strong></p>
    </div>

    <form id="formPlano">
        <div class="row justify-content-center g-4">
            <!-- Plano Anual -->
            <div class="col-md-5">
                <input type="radio" class="btn-check" name="plano" id="planoAnual" value="anual" autocomplete="off" required>
                <label for="planoAnual" class="card plano-select border border-light shadow-sm bg-secondary bg-opacity-10 text-white text-center p-4">
                    <h5 class="text-warning fw-bold mb-2">Plano Anual</h5>
                    <p class="small mb-2">✅ Acesso por 12 meses<br>✅ Certificado e suporte</p>
                    <p class="fs-4 fw-bold text-success mb-0">R$ 59,90</p>
                </label>
            </div>

            <!-- Plano Vitalício -->
            <div class="col-md-5">
                <input type="radio" class="btn-check" name="plano" id="planoVitalicio" value="vitalicio" autocomplete="off" required>
                <label for="planoVitalicio" class="card plano-select border shadow-sm bg-secondary bg-opacity-10 text-white text-center p-4 position-relative">
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success px-3 py-2" style="font-size: 0.75rem;">
                        Mais Vendido
                    </div>
                    <h5 class="text-warning fw-bold mb-2 mt-4">Plano Vitalício</h5>
                    <p class="small mb-2">✅ Acesso para sempre<br>✅ Atualizações grátis<br>🎁 Bônus: Mini-curso Excel</p>
                    <p class="fs-4 fw-bold text-success mb-0">R$ 100,00</p>
                </label>
            </div>
        </div>

        <div class="text-center mt-5">
            <button type="submit" class="btn btn-gradient btn-lg px-5">
                Continuar para Pagamento <i class="bi bi-arrow-right-circle ms-2"></i>
            </button>
        </div>
    </form>



</main>

<script>
    document.getElementById('formPlano').addEventListener('submit', function(e) {
        e.preventDefault();
        const plano = document.querySelector('input[name="plano"]:checked');
        if (plano) {
            // Salva o plano na sessionStorage ou pode enviar via GET
            sessionStorage.setItem('planoEscolhido', plano.value);
            window.location.href = 'pagina_vendasPagamento.php?plano=' + plano.value;
        }
    });
</script>