<?php
// Verificação básica
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['emailUsuario'])) {
    header("Location: pagina_vendas.php");
    exit;
}
$plano = $_GET['plano'] ?? ''; // ou usar $_SESSION['planoEscolhido'] se quiser salvar
if ($plano == 'anual') {
    $plano = 'Plano Anual';
    $valorFinal = $valoranual;
} else {
    $plano = 'Plano Vitalício';
    $valorFinal = $valorvendavitalicia;
}
$_SESSION['plano'] = $plano;
$_SESSION['valorFinal'] = $valorFinal;
?>
<main class="container">
    <div class="text-center mb-5" data-aos="fade-down">
        <h2 style="color: #00BB9C;"><?= $nomeCurso ?></h2>
        <p class="lead"> <strong><?= ucfirst($plano) ?></strong> – Valor: <strong>R$ <?= $valorFinal ?></strong></p>
        <!-- <p class="small text-warning"><i class="bi bi-shield-lock"></i> Escolha a forma de pagamento segura</p> -->
    </div>
    <div class="row justify-content-center g-4">
        <!-- Pix -->
        <div class="col-md-4">
            <div class="card bg-secondary bg-opacity-10 border-0 text-center p-4 h-100 shadow-sm">
                <i class="bi bi-qr-code fs-1 text-success mb-3"></i>
                <h5>Pagamento via Pix</h5>
                <p class="small">Aprovação imediata após o pagamento.</p>
                <button type="button" class="btn btn-success w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modalPix">
                    Pagar com Pix
                </button>
            </div>
        </div>
        <!-- Cartão -->
        <div class="col-md-4">
            <div class="card bg-secondary bg-opacity-10 border-0 text-center p-4 h-100 shadow-sm">
                <i class="bi bi-credit-card fs-1 text-primary mb-3"></i>
                <h5>Cartão de Crédito</h5>
                <p class="small">Parcele em até 12x com segurança.</p>
                <button type="button" class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#modalCartao">
                    Pagar com Cartão
                </button>
            </div>
        </div>
    </div>
    <!-- ✅ Botão de Confirmação -->
    <div class="text-center mt-5">
        <!-- Substitua o botão atual por este (sem onclick) -->
        <button type="button" class="btn btn-gradient btn-lg px-5" id="btnConfirmarPagamento">
            ✅ Confirmar Pagamento Realizado
        </button>
        <p class="small text-muted mt-2">Clique somente se já realizou o pagamento.</p>

        <p class="small text-muted mt-2">Clique somente se já realizou o pagamento.</p>
    </div>
    <?php require 'vendasv1.0/modalPagamentoPix.php' ?>
    <script>
        function copiarChavePix() {
            const chaveInput = document.getElementById('chavePix');
            chaveInput.select();
            chaveInput.setSelectionRange(0, 99999);
            document.execCommand('copy');
            const alerta = document.getElementById('copiadoAlerta');
            alerta.classList.remove('d-none');
            setTimeout(() => alerta.classList.add('d-none'), 2500);
        }
    </script>
    <?php require 'vendasv1.0/modalPagamentoCartao.php' ?>
</main>


<script>
    // --------- Contexto do pagamento (vindo do PHP) ---------
    const planoSlug = '<?= isset($_GET["plano"]) ? htmlspecialchars($_GET["plano"]) : "" ?>'; // 'anual'|'vitalicio'
    const planoLabel = '<?= htmlspecialchars($plano) ?>'; // 'Plano Anual'|'Plano Vitalício'
    const valorRaw = '<?= (string)$valorFinal ?>'; // ex: 299.9
    const valorFmt = 'R$ <?= number_format((float)$valorFinal, 2, ",", ".") ?>'; // ex: R$ 299,90
    const nomecurso = '<?= addslashes($nomeCurso) ?>';

    // PIX (muda conforme o plano)
    const chavePix = '<?= ($plano === "Plano Anual") ? addslashes($chavepix) : addslashes($chavepixvitalicia) ?>';
    const payloadPix = ''; // Se tiver "copia e cola" do Pix, preencha aqui

    // CARTÃO – dois checkouts (conforme seu modal)
    const checkoutPagSeguro = `<?= ($plano === "Plano Anual") ? addslashes($linkpagseguro)         : addslashes($linkpagsegurovitalicia) ?>`;
    const checkoutMercadoPago = `<?= ($plano === "Plano Anual") ? addslashes($linkmercadopago)       : addslashes($linkmercadopagovitalicio) ?>`;

    // Estado da escolha do usuário
    let metodoSelecionado = ''; // 'pix' | 'cartao'
    let parcelas = ''; // opcional: "1","6","12"
    let bandeira = ''; // opcional: "visa","mastercard"

    // Marque a forma assim que o usuário clicar nos botões que abrem os modais
    const btnAbrirPix = document.querySelector('[data-bs-target="#modalPix"]');
    const btnAbrirCartao = document.querySelector('[data-bs-target="#modalCartao"]');
    if (btnAbrirPix) btnAbrirPix.addEventListener('click', () => {
        metodoSelecionado = 'pix';
    });
    if (btnAbrirCartao) btnAbrirCartao.addEventListener('click', () => {
        metodoSelecionado = 'cartao';
    });

    // (Opcional) Se tiver selects no modal Cartão, capture mudanças:
    // document.addEventListener('change', (ev) => {
    //   if (ev.target && ev.target.id === 'parcelasCartao') parcelas = ev.target.value;
    //   if (ev.target && ev.target.id === 'bandeiraCartao')  bandeira = ev.target.value;
    // });

    async function enviarPgtoAjax(metodo) {
        const fd = new FormData();
        fd.append('metodo', metodo); // 'pix' | 'cartao'
        fd.append('plano_slug', planoSlug);
        fd.append('plano_label', planoLabel);
        fd.append('valor', valorRaw);
        fd.append('valor_fmt', valorFmt);
        fd.append('nomecurso', nomecurso);

        if (metodo === 'pix') {
            fd.append('chavepix', chavePix || '');
            fd.append('payloadpix', payloadPix || '');
        } else if (metodo === 'cartao') {
            fd.append('parcelas', parcelas || '');
            fd.append('bandeira', bandeira || '');
            fd.append('checkout_pagseguro', checkoutPagSeguro || '');
            fd.append('checkout_mercadopago', checkoutMercadoPago || '');
        }

        const r = await fetch('vendasv1.0/ajaxInscricaoPagamentos.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        });
        // Leitura silenciosa (pode conter logs); se falhar lançará no catch
        await r.text();
    }

    // Handler do botão Confirmar
    document.getElementById('btnConfirmarPagamento').addEventListener('click', async () => {
        if (!metodoSelecionado) {
            // Exija que o usuário escolha Pix ou Cartão antes de confirmar
            alert('Escolha uma forma de pagamento (Pix ou Cartão) antes de confirmar.');
            return;
        }

        // Mostra modal de carregamento
        const modalEl = document.getElementById('modalCarregandoPgto');
        const bsModal = new bootstrap.Modal(modalEl, {
            backdrop: 'static',
            keyboard: false
        });
        bsModal.show();

        try {
            await enviarPgtoAjax(metodoSelecionado);
            // Sucesso: segue para a página de confirmação
            window.location.href = 'pagina_vendasPagamentoConfirmado.php';
        } catch (err) {
            console.error('Falha no envio do e-mail de pagamento:', err);
            bsModal.hide();
            alert('Não foi possível confirmar o pagamento agora. Tente novamente.');
        }
    });
</script>

<!-- Modal de Carregamento -->
<div class="modal fade" id="modalCarregandoPgto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
            <div class="modal-body d-flex align-items-center gap-3">
                <div class="spinner-border" role="status" aria-hidden="true"></div>
                <div>
                    <h6 class="mb-1">Confirmando pagamento…</h6>
                    <small class="text-secondary">Aguarde um instante, estamos registrando sua confirmação.</small>
                </div>
            </div>
        </div>
    </div>
</div>