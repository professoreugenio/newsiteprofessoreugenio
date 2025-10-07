<main class="container">
    <div class="text-center mb-2" data-aos="fade-down">
        <h4 style="color: #00BB9C;">Olá, <?= htmlspecialchars($_SESSION['nomeUsuario']) ?>!</h4>
        <p class="lead">Escolha o melhor plano para acessar o <strong><?= $nomeCurso ?></strong></p>
    </div>
    <form id="formPlano" class="mt-4">
        <div class="row justify-content-center g-4">
            <!-- Plano Anual -->
            <?php if ($valoranual > 0): ?>
                <div class="col-md-5">
                    <input type="radio" class="btn-check" name="plano" id="planoAnual"
                        value="anual" autocomplete="off"
                        data-valor="<?= htmlspecialchars($valoranual) ?>">
                    <label for="planoAnual" class="card plano-select border-2 border-light shadow-sm bg-secondary bg-opacity-10 text-white text-center p-4 h-100 transition">
                        <div class="d-flex flex-column justify-content-end h-100" style="cursor: pointer;">
                            <div>
                                <h5 class="text-warning fw-bold mb-2">Plano Anual</h5>
                                <p class="small mb-3">✅ Acesso por 12 meses<br>✅ Certificado e suporte</p>
                                <p class="fs-4 fw-bold text-success mb-0">R$ <?= str_replace('.', ',', $valoranual); ?></p>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endif; ?>
            <!-- Plano Vitalício -->
            <div class="col-md-5">
                <input type="radio" class="btn-check" name="plano" id="planoVitalicio"
                    value="vitalicio" autocomplete="off"
                    data-valor="<?= htmlspecialchars($valorvendavitalicia) ?>">
                <label for="planoVitalicio" class="card plano-select border-2 border-success shadow bg-secondary bg-opacity-10 text-white text-center p-4 h-100 position-relative transition">
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success px-3 py-2" style="font-size: 0.75rem;">
                        Mais Vendido
                    </div>
                    <div class="d-flex flex-column justify-content-end h-100" style="cursor: pointer;">
                        <div class="mt-4">
                            <h5 class="text-warning fw-bold mb-2">Plano Vitalício</h5>
                            <p class="small mb-3">✅ Acesso para sempre<br>✅ Atualizações grátis<br>
                                <!-- 🎁 Bônus: Mini-curso Excel -->
                            </p>
                            <p class="fs-4 fw-bold text-success mb-0">R$ <?= str_replace('.', ',', $valorvendavitalicia); ?></p>
                        </div>
                    </div>
                </label>
            </div>
        </div>
        <!-- Texto informativo -->
        <div class="text-center mt-4">
            <p id="mensagemPlano" class="text-white fst-italic">Nenhum plano escolhido.</p>
        </div>
        <!-- Botão visível apenas após seleção -->
        <div id="btnContinuarContainer" class="text-center mt-3" style="display: none;">
            <button type="submit" class="btn btn-gradient btn-lg px-5">
                Continuar para Pagamento <i class="bi bi-arrow-right-circle ms-2"></i>
            </button>
        </div>
    </form>
    <script>
        const radios = document.querySelectorAll('input[name="plano"]');
        const mensagem = document.getElementById('mensagemPlano');
        const btnContainer = document.getElementById('btnContinuarContainer');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    let textoPlano = this.value === 'anual' ? 'Plano Anual' : 'Plano Vitalício';
                    mensagem.textContent = `Você escolheu o ${textoPlano}.`;
                    btnContainer.style.display = 'block';
                }
            });
        });
    </script>
</main>
<!-- <script>
    document.getElementById('formPlano').addEventListener('submit', function(e) {
        e.preventDefault();
        const plano = document.querySelector('input[name="plano"]:checked');
        if (plano) {
            // Salva o plano na sessionStorage ou pode enviar via GET
            sessionStorage.setItem('planoEscolhido', plano.value);
            window.location.href = 'pagina_vendasPagamento.php?plano=' + plano.value;
        }
    });
</script> -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="plano"]');
        const mensagem = document.getElementById('mensagemPlano');
        const btnContainer = document.getElementById('btnContinuarContainer');
        const formPlano = document.getElementById('formPlano');
        radios.forEach(r => {
            r.addEventListener('change', function() {
                if (this.checked) {
                    mensagem.textContent = this.value === 'anual' ?
                        'Você escolheu o Plano Anual.' :
                        'Você escolheu o Plano Vitalício.';
                    btnContainer.style.display = 'block';
                }
            });
        });
        formPlano.addEventListener('submit', function(e) {
            e.preventDefault();
            const escolhido = document.querySelector('input[name="plano"]:checked');
            if (!escolhido) {
                mensagem.textContent = 'Selecione um plano para continuar.';
                return;
            }
            const modalEl = document.getElementById('modalCarregando');
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
            const formData = new FormData();
            formData.append('plano', escolhido.value);
            formData.append('nomecurso', '<?= addslashes($nomeCurso) ?>');
            // valor bruto (padrão com ponto) vindo do data-valor
            const valorRaw = escolhido.dataset.valor || (escolhido.value === 'anual' ?
                '<?= $valoranual ?>' :
                '<?= $valorvendavitalicia ?>');
            formData.append('valorplano', valorRaw);
            fetch('vendasv1.0/ajaxInscricaoPlano.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(async (r) => {
                    const txt = await r.text();
                    let data = null;
                    try {
                        data = JSON.parse(txt);
                    } catch (_) {}
                    const okByJson = data && data.status === 'ok';
                    const okByText = !okByJson && txt.includes('"status":"ok"');
                    if (r.ok && (okByJson || okByText)) {
                        const target = (data && data.redirect) ?
                            data.redirect :
                            'pagina_vendasPagamento.php?plano=' + encodeURIComponent(escolhido.value);
                        window.location.href = target;
                        return;
                    }
                    bsModal.hide();
                    mensagem.textContent = (data && data.mensagem) ? data.mensagem : 'Não foi possível atualizar seu plano. Tente novamente.';
                    console.error('Resposta AJAX (bruta):', txt);
                })
                .catch((err) => {
                    bsModal.hide();
                    mensagem.textContent = 'Falha de comunicação. Verifique sua conexão e tente novamente.';
                    console.error(err);
                });
        });
    });
</script>
<!-- Modal de Carregamento -->
<div class="modal fade" id="modalCarregando" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-body d-flex align-items-center gap-3">
                <div class="spinner-border" role="status" aria-hidden="true"></div>
                <div>
                    <h6 class="mb-1">Processando sua escolha…</h6>
                    <small class="text-secondary">Aguarde um instante, estamos atualizando sua inscrição.</small>
                </div>
            </div>
        </div>
    </div>
</div>