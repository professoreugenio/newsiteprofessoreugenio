<?php

/** vendaPagamento.php
 * Etapa 3 do funil: seleção da forma de pagamento
 * Requisitos:
 *  - Tabela new_sistema_cursos_turmas com campos comerciais (pix/link/qr) — nomes podem variar.
 *  - Receber por GET/POST (ou localStorage) dados: plano, valorPlanoFmt, idCurso, idTurma, nome, email, telefone, objetivo, utm.
 */

define('BASEPATH', true);
require_once __DIR__ . '/conexao/class.conexao.php';
require_once __DIR__ . '/autenticacao.php';
@date_default_timezone_set('America/Fortaleza');

$con = config::connect();

/* ------- Sanitização de entrada ------- */
$get = function (string $k, $default = '') {
    $v = $_GET[$k] ?? $_POST[$k] ?? $default;
    if (is_string($v)) {
        $v = trim($v);
        $v = filter_var($v, FILTER_UNSAFE_RAW);
    }
    return $v;
};

$plano          = $get('plano', 'vitalicio'); // 'anual' | 'vitalicio'
$valorPlanoFmt  = $get('valorPlanoFmt', ($plano === 'anual' ? 'R$ 39,90/mês' : 'R$ 85,00'));
$idCurso        = $get('idCurso', 'excel-concursos');
$idTurma        = $get('idTurma', 'turma-2025-01');
$nome           = $get('nome', '');
$email          = $get('email', '');
$telefone       = $get('telefone', '');
$objetivo       = $get('objetivo', '');
$utm            = $get('utm', '');

/* ------- Busca dados comerciais da Turma ------- */
$dadosTurma = [];
try {
    // Tente idTurma como inteiro (se vier codigoturma), senão pode ser uma "chave"
    $stmt = $con->prepare("
        SELECT *
        FROM new_sistema_cursos_turmas
        WHERE (codigoturma = :idturma OR chave = :idturma) 
        LIMIT 1
    ");
    $stmt->bindValue(':idturma', $idTurma);
    $stmt->execute();
    $dadosTurma = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) {
}

/* ------- Helpers para achar campos mesmo com nomes diferentes ------- */
$firstNonEmpty = function (array $row, array $cands, $default = '') {
    foreach ($cands as $c) {
        if (isset($row[$c]) && (string)$row[$c] !== '') {
            return (string)$row[$c];
        }
    }
    return $default;
};

// QR codes por plano (imagens)
$qrcodeAnual     = $firstNonEmpty($dadosTurma, ['imgqrcodeanual', 'qrcode_anual', 'img_qr_anual', 'imgQrcodeAnual']);
$qrcodeVitalicio = $firstNonEmpty($dadosTurma, ['imgqrcodevitalicio', 'qrcode_vitalicio', 'img_qr_vitalicio', 'imgQrcodeVitalicio']);
$qrcodeGeral     = $firstNonEmpty($dadosTurma, ['imgqrcodecurso', 'qrcode_geral', 'img_qr_curso', 'imgQrcodeCurso']);

// Chave Pix (texto)
$pixKey          = $firstNonEmpty($dadosTurma, ['chavepix', 'pix_key', 'chave_pix', 'chavepixcurso', 'pix']);

// Links diretos
$linkCartao      = $firstNonEmpty($dadosTurma, ['linkcartao', 'pagcartao', 'link_cartao', 'url_cartao', 'link_pag_cartao']);
$linkBoleto      = $firstNonEmpty($dadosTurma, ['linkboleto', 'pagboleto', 'link_boleto', 'url_boleto', 'link_pag_boleto']);

// Nome da turma/curso para exibição
$nomeTurma       = $firstNonEmpty($dadosTurma, ['nometurma', 'titulo', 'titulo_turma'], 'Turma selecionada');

/* ------- Seleção do QR Code conforme plano ------- */
$qrcodePlano = ($plano === 'anual')
    ? ($qrcodeAnual ?: $qrcodeGeral)
    : ($qrcodeVitalicio ?: $qrcodeGeral);

// Fallbacks visuais e operacionais
$temPixImg  = !empty($qrcodePlano);
$temPixKey  = !empty($pixKey);
$temCartao  = !empty($linkCartao);
$temBoleto  = !empty($linkBoleto);

// Geração de um ID de reserva simples (apenas visual). Ideal: gerar/guardar no backend.
$reservaId  = substr(sha1(($email ?: uniqid('', true)) . microtime(true)), 0, 10);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Pagamento — Excel para Concursos | Professor Eugênio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- SEO básico -->
    <meta name="description" content="Finalize o pagamento do Curso de Excel para Concursos. Pix, Cartão ou Boleto. Acesso imediato na confirmação.">
    <link rel="canonical" href="https://professoreugenio.com/vendaPagamento.php">

    <!-- CSS: Bootstrap / Icons / AOS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

    <style>
        :root {
            --c-h1: #00BB9C;
            --c-h2: #FF9C00;
            --c-bg: #112240;
            --c-text: #ffffff;
            --c-card: #0d1a34;
            --c-muted: #9fb1d1;
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
            scroll-behavior: smooth;
        }

        .navbar {
            backdrop-filter: saturate(140%) blur(6px);
            background: rgba(17, 34, 64, .85);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .heading-1 {
            color: var(--c-h1);
            font-weight: 800;
            letter-spacing: .2px;
        }

        .heading-2 {
            color: var(--c-h2);
            font-weight: 800;
            letter-spacing: .2px;
            font-size: clamp(1.2rem, 2.5vw, 1.6rem);
            text-transform: uppercase;
        }

        .small-muted {
            color: var(--c-muted);
        }

        section {
            padding: 64px 0;
        }

        .card-dark {
            background: var(--c-card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #e9f3ff;
        }

        /* Tabs pagamento */
        .nav-pills .nav-link {
            color: #e6f2ff;
            border: 1px solid rgba(255, 255, 255, .15);
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #153060, #0f2244);
            border-color: rgba(255, 255, 255, .25);
        }

        .btn-cta {
            background: linear-gradient(135deg, #FF9C00, #ffb547);
            color: #112240;
            font-weight: 800;
            border: none;
            box-shadow: 0 10px 24px rgba(255, 156, 0, .25);
        }

        .btn-cta:hover {
            filter: brightness(1.05);
            color: #0b1730;
        }

        .btn-outline-soft {
            border: 1px solid rgba(255, 255, 255, .25);
            color: #e9f3ff;
        }

        .btn-outline-soft:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .pix-box {
            background: rgba(255, 255, 255, .04);
            border: 1px dashed rgba(255, 255, 255, .18);
            border-radius: 1rem;
            padding: 1rem;
        }

        .qr-wrap {
            background: #fff;
            border-radius: 1rem;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrap img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .copy-input {
            background: #0f1f3d;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .15);
        }

        .timer {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            color: #FF9C00;
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php"><i class="bi bi-microsoft me-1"></i> Professor Eugênio</a>
            <button class="navbar-toggler text-white" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navMain" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#pagamento">Pagamento</a></li>
                    <li class="nav-item"><a class="nav-link" href="#resumo">Resumo</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-cta" href="#pagamento"><i class="bi bi-credit-card me-1"></i> Finalizar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER -->
    <section class="pt-5">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-journal-check me-1"></i> Etapa 3 de 4 — Pagamento
                    </span>
                    <div class="heading-1 mb-2">Finalize seu pagamento</div>
                    <p class="small-muted mb-0">
                        Garanta seu acesso ao <strong>Curso de Excel para Concursos</strong>. Após a confirmação, o acesso é liberado automaticamente.
                    </p>
                </div>
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Reserva</div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">ID: <?php echo htmlspecialchars($reservaId); ?></span>
                        </div>
                        <div class="small-muted mt-2">Tempo para garantir a vaga:</div>
                        <div class="display-6 timer" id="timer">20:00</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PAGAMENTO -->
    <section id="pagamento">
        <div class="container">
            <div class="row gy-4">
                <!-- Coluna esquerda: Tabs de pagamento -->
                <div class="col-lg-7" data-aos="fade-up">
                    <ul class="nav nav-pills gap-2 mb-3" id="payTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pix-tab" data-bs-toggle="pill" data-bs-target="#pix-pane" type="button" role="tab">
                                <i class="bi bi-qr-code-scan me-1"></i> Pix
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $temCartao ? '' : 'disabled'; ?>" id="card-tab" data-bs-toggle="pill" data-bs-target="#card-pane" type="button" role="tab">
                                <i class="bi bi-credit-card me-1"></i> Cartão
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $temBoleto ? '' : 'disabled'; ?>" id="boleto-tab" data-bs-toggle="pill" data-bs-target="#boleto-pane" type="button" role="tab">
                                <i class="bi bi-receipt me-1"></i> Boleto
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- PIX -->
                        <div class="tab-pane fade show active" id="pix-pane" role="tabpanel">
                            <div class="card-dark p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="heading-2 mb-2">Pagar com Pix</div>
                                    <span class="badge rounded-pill text-dark" style="background:#cdeee7;">Confirmação mais rápida</span>
                                </div>
                                <p class="small-muted mb-3">
                                    Abra o app do seu banco, escolha Pix &rarr; “<em>Pagar com QR Code</em>” e aponte para o código abaixo. Valor: <strong><?php echo htmlspecialchars($valorPlanoFmt); ?></strong>.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="pix-box h-100">
                                            <?php if ($temPixImg): ?>
                                                <div class="qr-wrap mb-2">
                                                    <img src="<?php echo htmlspecialchars($qrcodePlano); ?>" alt="QR Code Pix" id="qrImg">
                                                </div>
                                                <div class="small text-center text-white-50">QR Code para o plano <strong><?php echo htmlspecialchars($plano); ?></strong></div>
                                            <?php else: ?>
                                                <div class="small text-white-50">
                                                    Nenhuma imagem de QR Code encontrada para esta turma/plano. Utilize a <strong>chave Pix</strong> ao lado.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="pix-box h-100 d-flex flex-column">
                                            <div class="small text-white-50 mb-2">Chave Pix</div>
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control copy-input" id="pixKey" value="<?php echo htmlspecialchars($temPixKey ? $pixKey : ''); ?>" placeholder="<?php echo $temPixKey ? '' : 'Chave Pix não cadastrada'; ?>" readonly>
                                                <button class="btn btn-outline-soft" type="button" id="btnCopyPix" <?php echo $temPixKey ? '' : 'disabled'; ?>>
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                            <div class="small text-white-50 mt-auto">
                                                Dica: copie a chave Pix e cole no seu app bancário em “Pagar por chave Pix”.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button class="btn btn-cta" id="btnJaPagueiPix"><i class="bi bi-check2-circle me-1"></i> Já paguei (Pix)</button>
                                    <a class="btn btn-outline-soft" target="_blank" rel="noopener" href="https://wa.me/5585XXXXXXXX?text=Acabei%20de%20pagar%20via%20Pix%20minha%20inscri%C3%A7%C3%A3o%20no%20curso%20Excel%20para%20Concursos.%20Reserva%20ID:%20<?php echo urlencode($reservaId); ?>">
                                        <i class="bi bi-whatsapp me-1"></i> Avisar no WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- CARTÃO -->
                        <div class="tab-pane fade" id="card-pane" role="tabpanel">
                            <div class="card-dark p-3 p-md-4">
                                <div class="heading-2 mb-2">Pagar com Cartão</div>
                                <?php if ($temCartao): ?>
                                    <p class="small-muted">Você será redirecionado para o ambiente seguro do nosso parceiro para finalizar a compra.</p>
                                    <a class="btn btn-cta btn-lg" href="<?php echo htmlspecialchars($linkCartao); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-credit-card me-2"></i> Ir para pagamento no Cartão
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">Link de cartão ainda não cadastrado para esta turma.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- BOLETO -->
                        <div class="tab-pane fade" id="boleto-pane" role="tabpanel">
                            <div class="card-dark p-3 p-md-4">
                                <div class="heading-2 mb-2">Gerar Boleto</div>
                                <?php if ($temBoleto): ?>
                                    <p class="small-muted">O boleto vence em 2 dias úteis. A liberação ocorre após a compensação bancária.</p>
                                    <a class="btn btn-cta btn-lg" href="<?php echo htmlspecialchars($linkBoleto); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-receipt me-2"></i> Gerar Boleto
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">Link de boleto ainda não cadastrado para esta turma.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna direita: Resumo e Suporte -->
                <div class="col-lg-5" id="resumo" data-aos="fade-left">
                    <div class="card-dark p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Resumo do Pedido</div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Plano: <?php echo htmlspecialchars(ucfirst($plano)); ?></span>
                        </div>

                        <div class="fs-2 fw-bold my-1" style="color:#00BB9C;"><?php echo htmlspecialchars($valorPlanoFmt); ?></div>
                        <div class="small text-white-50 mb-2"><?php echo htmlspecialchars($nomeTurma); ?></div>

                        <ul class="small mb-0">
                            <li class="mb-1">Curso: <strong>Excel para Concursos</strong></li>
                            <li class="mb-1">Aluno: <strong><?php echo htmlspecialchars($nome ?: '—'); ?></strong></li>
                            <li class="mb-1">E-mail: <strong><?php echo htmlspecialchars($email ?: '—'); ?></strong></li>
                            <li class="mb-1">Celular: <strong><?php echo htmlspecialchars($telefone ?: '—'); ?></strong></li>
                            <?php if (!empty($objetivo)): ?>
                                <li class="mb-1">Concurso-alvo: <strong><?php echo htmlspecialchars($objetivo); ?></strong></li>
                            <?php endif; ?>
                        </ul>

                        <hr class="border-secondary my-3">

                        <div class="d-grid gap-2">
                            <button class="btn btn-cta" id="btnJaPaguei"><i class="bi bi-check2-square me-1"></i> Já paguei</button>
                            <a class="btn btn-outline-soft" target="_blank" rel="noopener" href="https://wa.me/5585XXXXXXXX?text=Tenho%20d%C3%BAvidas%20sobre%20meu%20pagamento%20(Reserva%20ID:%20<?php echo urlencode($reservaId); ?>)">
                                <i class="bi bi-whatsapp me-1"></i> Suporte no WhatsApp
                            </a>
                        </div>
                        <div class="small text-white-50 mt-2">
                            Dica: mantenha esta página aberta até a confirmação para agilizar seu acesso.
                        </div>
                    </div>

                    <div class="card-dark p-4">
                        <div class="fw-bold mb-2"><i class="bi bi-shield-lock me-2"></i>Segurança</div>
                        <p class="small small-muted mb-0">Ambiente protegido e criptografado. Seus dados são usados apenas para liberação de acesso e notas fiscais, conforme nossa Política de Privacidade.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="py-4 border-top border-opacity-25" style="border-color: rgba(255,255,255,.06) !important;">
        <div class="container small text-white-50 d-flex flex-wrap justify-content-between gap-2">
            <div>© <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.</div>
            <div class="d-flex gap-3">
                <a class="link-light link-underline-opacity-0" href="#">Termos</a>
                <a class="link-light link-underline-opacity-0" href="#">Privacidade</a>
                <a class="link-light link-underline-opacity-0" href="#">Contato</a>
            </div>
        </div>
    </footer>

    <!-- TOAST (feedback de cópia / ações) -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
        <div id="toastMain" class="toast align-items-center text-bg-dark border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg">Ação concluída.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true
        });
        document.getElementById('ano').textContent = new Date().getFullYear();

        // ------ Toast helper ------
        const toastEl = document.getElementById('toastMain');
        const toastMsg = document.getElementById('toastMsg');
        let toast;

        function showToast(msg) {
            toastMsg.textContent = msg || 'OK';
            toast?.hide();
            toast = new bootstrap.Toast(toastEl, {
                delay: 2200
            });
            toast.show();
        }

        // ------ Copiar chave Pix ------
        document.getElementById('btnCopyPix')?.addEventListener('click', async () => {
            const keyEl = document.getElementById('pixKey');
            if (!keyEl || !keyEl.value) return;
            try {
                await navigator.clipboard.writeText(keyEl.value);
                showToast('Chave Pix copiada!');
            } catch (e) {
                keyEl.select();
                document.execCommand('copy');
                showToast('Chave Pix copiada!');
            }
        });

        // ------ Contador de reserva (20 min) ------
        let seconds = 20 * 60;
        const timerEl = document.getElementById('timer');
        const i = setInterval(() => {
            if (seconds <= 0) {
                clearInterval(i);
                timerEl.textContent = '00:00';
                return;
            }
            seconds--;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timerEl.textContent = m + ':' + s;
        }, 1000);

        // ------ Botões "Já paguei" ------
        function payloadLead() {
            // Coleta dados visíveis (ou vindo do localStorage/GET)
            return {
                plano: <?php echo json_encode($plano); ?>,
                valorPlanoFmt: <?php echo json_encode($valorPlanoFmt); ?>,
                idCurso: <?php echo json_encode($idCurso); ?>,
                idTurma: <?php echo json_encode($idTurma); ?>,
                nome: <?php echo json_encode($nome); ?>,
                email: <?php echo json_encode($email); ?>,
                telefone: <?php echo json_encode($telefone); ?>,
                objetivo: <?php echo json_encode($objetivo); ?>,
                utm: <?php echo json_encode($utm); ?>,
                reservaId: <?php echo json_encode($reservaId); ?>
            };
        }

        function irConfirmacao() {
            const data = payloadLead();
            const qs = new URLSearchParams(data).toString();
            // Ajuste para sua rota real de confirmação (backend confere pagamento / webhook)
            window.location.href = 'confirmarPagamento.php?' + qs;
        }

        document.getElementById('btnJaPaguei')?.addEventListener('click', () => {
            showToast('Vamos verificar sua confirmação...');
            setTimeout(irConfirmacao, 800);
        });
        document.getElementById('btnJaPagueiPix')?.addEventListener('click', () => {
            showToast('Obrigado! Validando pagamento Pix...');
            setTimeout(irConfirmacao, 800);
        });

        // ------ Carrega/localStorage (reforço de continuidade) ------
        try {
            const keys = ['nome', 'email', 'telefone', 'objetivo', 'idCurso', 'idTurma', 'utm', 'planoSelecionado', 'valorPlanoFmt'];
            keys.forEach(k => {
                const v = localStorage.getItem('insc_' + k);
                if (!v) return;
                // Poderia re-hidratar dados no DOM, se necessário
            });
        } catch (e) {}
    </script>
</body>

</html>