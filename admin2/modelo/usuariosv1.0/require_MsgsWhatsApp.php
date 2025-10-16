<?php
// ========================
// BLOCO PHP (dados do aluno + mensagens)
// ========================

// Parâmetros esperados: $_GET['idUsuario'] ou $_GET['id'] (criptografados)
// Variáveis opcionais que podem vir de fora:
// - $saudacao (ex.: "Bom dia")
// - $cursoPrincipal (ex.: "Power BI Iniciante 2025")

$saudacao       = $saudacao       ?? 'Olá';
$cursoPrincipal = $cursoPrincipal ?? 'seu curso';

$idUsuario = $_GET['idUsuario'] ?? ($_GET['id'] ?? '');
$idUsuario = encrypt($idUsuario, $action = 'd'); // decodifica

$stmt = $con->prepare("
    SELECT codigocadastro AS idAluno, nome, celular, pastasc, imagem50, email, senha
    FROM new_sistema_cadastro
    WHERE codigocadastro = :idusuario
    LIMIT 1
");
$stmt->bindValue(':idusuario', (int)$idUsuario, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo '<div class="alert alert-warning">Aluno não encontrado.</div>';
    return;
}

// ------- Tratamentos -------
$decSenha = encrypt($row['senha'], $action = 'd');
$expSenha = explode("&", $decSenha);
$senha    = $expSenha[1] ?? 'não registrado';
$email    = (string)$row['email'];

$idAluno  = (int)($row['idAluno'] ?? 0);

// celular só dígitos
$celularBruto = (string)($row['celular'] ?? '');
$celular      = preg_replace('/\D/', '', $celularBruto);

// nome curto (nome + sobrenome)
$nomeArr    = explode(' ', trim((string)$row['nome']));
$nomeAluno  = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
$nome1      = htmlspecialchars($nomeArr[0] ?? '');
$temWhats   = strlen($celular) >= 10;

// fallback turma
$ultimaTurma = $ultimaTurma ?? $cursoPrincipal;

// ------- Mensagens (WhatsApp) -------
$msgSenha =
    "*{$nome1}*, caso não se recorde de sua senha de acesso 
segue seus dados de acesso ao portal *professoreugenio.com* tanto por celular quanto por computador:
E-*mail*: {$email}
*Senha*: {$senha}
Página de login:
https://professoreugenio.com/login_aluno.php?ts=" . time();

$msgSaudacao =
    "*-------------*
*{$saudacao} {$nomeAluno}*, aqui é o professor Eugênio! Tudo bem?";

$msgNovidades =
    "*-------------*
Venho trazer algumas novidades para você sobre o curso online do Professor!*";

$msgAcolhimento =
    "*{$nomeAluno}*,
Seja bem-vindo(a)
Ao curso de {$ultimaTurma}!";

$msgAcessoGratuito =
    "📢 Você recebeu acesso *GRATUITO por 5 dias* ao *Curso de Informática MASTER CLASS* do Professor Eugênio!
💻 Aulas novas toda semana, para você assistir de qualquer lugar e a qualquer hora.
✅ Este é o momento de se manter atualizado, evoluir na sua formação e não deixar o aprendizado esfriar (como acontece no curso presencial, que já acabou).
👉 Aproveite esta experiência *TOTALMENTE GRATUITA* e sinta como é ter suporte direto via WhatsApp e tira-dúvidas com o professor.
✨ Novidade especial que você vai gostar:
Se quiser continuar após este período gratuito, você pode escolher:
🔹 *Assinatura Anual:* R$ 39,90 (paga só uma vez)
🔹 *Assinatura Vitalícia:* R$ 85,00 (acesso para sempre!)
🚀 Não perca essa oportunidade de se manter sempre preparado e atualizado no mercado!

📲 Conte comigo no WhatsApp para suporte direto.";

$msgOfertaPowerBI =
    "*{$nomeAluno}*,

Se você tem interesse em continuar seu aprendizado em *Power BI* (dashboards + IA), essa é sua oportunidade!

💡 *Acesso Vitalício* com todo conteúdo liberado, suporte, materiais para download e atualizações.
👉 https://professoreugenio.com/pagina_vendas.php?nav=blV1Z1R1QXpuQjgxblBwMmZjYVRxWlFFc09oMGh0SWM1SFRPaGx3RVlmMD0=&ts=" . time() . "

Fico à disposição para tirar qualquer dúvida!

*Professor Eugênio*";

$msgRedes =
    "{$saudacao} *{$nomeAluno}*, tudo bem?
Venho aqui pedir para me acompanhar nas redes sociais e ficar por dentro das novidades, dicas e conteúdos gratuitos!
📺 YouTube:
https://www.youtube.com/@professoreugenio
📸 Instagram:
https://instagram.com/professoreugenio
🎵 TikTok:
https://www.tiktok.com/@professoreugeniomci
Conte comigo no seu aprendizado!
Abraço,
Professor Eugênio";

// ------- Links auxiliares -------
$emailPromo = 'mailto:' . rawurlencode($email)
    . '?subject=' . rawurlencode('Promoção de Cursos')
    . '&body=' . rawurlencode("Olá {$nomeAluno}, Novidades!\n\n" . $msgAcessoGratuito);

$emailMotiv = 'mailto:' . rawurlencode($email)
    . '?subject=' . rawurlencode('Mensagem Motivacional')
    . '&body=' . rawurlencode("Continue firme, {$nomeAluno}! Você está indo muito bem.");

// ------- Utilitário: link do WhatsApp -------
function linkWhats(string $cel, string $msg): string
{
    if (!mb_check_encoding($msg, 'UTF-8')) {
        $msg = mb_convert_encoding($msg, 'UTF-8', 'auto');
    }
    $cel = preg_replace('/\D/', '', $cel);
    return "https://wa.me/{$cel}?text=" . rawurlencode($msg);
}
?>

<!-- ========================
     AÇÕES (botões)
========================= -->
<div class="d-flex align-items-center gap-2">
    <!-- BOTÃO QUE ABRE O MODAL -->
    <button
        class="btn btn-outline-primary btn-sm"
        type="button"
        data-bs-toggle="modal"
        data-bs-target="#modalMensagens<?= (int)$idAluno; ?>">
        <i class="bi bi-send"></i> Enviar Mensagem
    </button>

    <!-- BOTÃO PAGAMENTO (permanece) -->
    <button class="btn btn-outline-success btn-sm abrirPagamentoBtn ms-2"
        data-idusuario="<?= (int)$idAluno ?>"
        data-idturma="<?= htmlspecialchars($_GET['idturma'] ?? '') ?>"
        data-nomealuno="<?= $nomeAluno ?>">
        <i class="bi bi-currency-dollar"></i> Pagamento
    </button>
</div>

<!-- ========================
     MODAL DE MENSAGENS
========================= -->
<div class="modal fade" id="modalMensagens<?= (int)$idAluno; ?>" tabindex="-1" aria-labelledby="modalMensagensLabel<?= (int)$idAluno; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title" id="modalMensagensLabel<?= (int)$idAluno; ?>">
                    <i class="bi bi-chat-dots me-2"></i> Mensagens para <?= $nomeAluno ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs small" role="tablist">
                    <?php if ($temWhats): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-wpp-<?= (int)$idAluno; ?>" data-bs-toggle="tab" data-bs-target="#pane-wpp-<?= (int)$idAluno; ?>" type="button" role="tab">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </button>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= !$temWhats ? 'active' : '' ?>" id="tab-email-<?= (int)$idAluno; ?>" data-bs-toggle="tab" data-bs-target="#pane-email-<?= (int)$idAluno; ?>" type="button" role="tab">
                            <i class="bi bi-envelope-paper"></i> E-mail
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <!-- PANE: WHATSAPP -->
                    <?php if ($temWhats): ?>
                        <div class="tab-pane fade show active" id="pane-wpp-<?= (int)$idAluno; ?>" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="list-group small" id="listaMsgWpp-<?= (int)$idAluno; ?>">
                                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active"
                                            data-msg="<?= htmlspecialchars($msgSaudacao); ?>">
                                            <span><i class="bi bi-hand-thumbs-up me-2 text-success"></i>Saudação</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgNovidades); ?>">
                                            <span><i class="bi bi-megaphone me-2 text-primary"></i>Novidades</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgAcessoGratuito); ?>">
                                            <span><i class="bi bi-gift me-2 text-success"></i>Acesso Gratuito 5 dias</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgAcolhimento); ?>">
                                            <span><i class="bi bi-emoji-smile me-2 text-success"></i>Acolhimento</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgSenha); ?>">
                                            <span><i class="bi bi-key me-2 text-warning"></i>Recuperar Senha</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgRedes); ?>">
                                            <span><i class="bi bi-instagram me-2 text-danger"></i>Siga nas Redes</span>
                                        </button>
                                        <button type="button" class="list-group-item list-group-item-action"
                                            data-msg="<?= htmlspecialchars($msgOfertaPowerBI); ?>">
                                            <span><i class="bi bi-lightning-charge me-2 text-info"></i>Oferta Power BI</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label fw-semibold small mb-1">Pré-visualização</label>
                                    <textarea class="form-control form-control-sm" rows="10" id="previewWpp-<?= (int)$idAluno; ?>"></textarea>
                                    <div class="d-flex gap-2 mt-2">
                                        <a id="btnAbrirWpp-<?= (int)$idAluno; ?>" class="btn btn-success btn-sm" target="_blank" rel="noopener">
                                            <i class="bi bi-whatsapp"></i> Abrir no WhatsApp
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCopyWpp-<?= (int)$idAluno; ?>">
                                            <i class="bi bi-clipboard"></i> Copiar texto
                                        </button>
                                    </div>
                                    <div class="form-text mt-1">Destino: +55 <?= htmlspecialchars($celular); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- PANE: EMAIL -->
                    <div class="tab-pane fade <?= !$temWhats ? 'show active' : '' ?>" id="pane-email-<?= (int)$idAluno; ?>" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <div class="list-group small" id="listaMsgEmail-<?= (int)$idAluno; ?>">
                                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        href="<?= $emailPromo; ?>">
                                        <span><i class="bi bi-envelope-paper me-2"></i>E-mail Promoção</span>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                        href="<?= $emailMotiv; ?>">
                                        <span><i class="bi bi-emoji-smile me-2"></i>E-mail Motivacional</span>
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="alert alert-info py-2 px-3 small mb-2">
                                    <i class="bi bi-info-circle me-1"></i> Os links acima já abrem seu cliente de e-mail com assunto e corpo preenchidos.
                                </div>
                                <label class="form-label fw-semibold small mb-1">Pré-visualização (somente leitura)</label>
                                <textarea class="form-control form-control-sm" rows="10" readonly><?= "Promoção de Cursos\n\nOlá {$nomeAluno}, Novidades!\n\n{$msgAcessoGratuito}\n\n—\nProfessor Eugênio"; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div><!-- /tab-content -->
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================
     JS do modal (comportamento)
========================= -->
<script>
    (function() {
        const idAluno = <?= json_encode((int)$idAluno); ?>;
        const temWhats = <?= $temWhats ? 'true' : 'false'; ?>;
        const cel = <?= json_encode($celular); ?>; // apenas dígitos

        function setActive(el, group) {
            group.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
        }

        function buildWa(numero, msg) {
            const n = (numero || '').replace(/\D/g, '');
            return n ? ('https://wa.me/55' + n + '?text=' + encodeURIComponent(msg)) : '#';
        }

        if (temWhats) {
            const list = document.querySelector('#listaMsgWpp-' + idAluno);
            const preview = document.querySelector('#previewWpp-' + idAluno);
            const btnAbr = document.querySelector('#btnAbrirWpp-' + idAluno);
            const btnCop = document.querySelector('#btnCopyWpp-' + idAluno);

            // inicializa com o primeiro item
            const first = list.querySelector('.list-group-item');
            if (first) {
                preview.value = first.dataset.msg || '';
                btnAbr.href = buildWa(cel, preview.value);
            }

            list.addEventListener('click', function(e) {
                const item = e.target.closest('.list-group-item');
                if (!item) return;
                setActive(item, list);
                const msg = item.dataset.msg || '';
                preview.value = msg;
                btnAbr.href = buildWa(cel, msg);
            });

            preview.addEventListener('input', function() {
                btnAbr.href = buildWa(cel, preview.value);
            });

            btnCop.addEventListener('click', async function() {
                try {
                    await navigator.clipboard.writeText(preview.value || '');
                    btnCop.classList.remove('btn-outline-secondary');
                    btnCop.classList.add('btn-success');
                    btnCop.innerHTML = '<i class="bi bi-clipboard-check"></i> Copiado!';
                    setTimeout(() => {
                        btnCop.classList.add('btn-outline-secondary');
                        btnCop.classList.remove('btn-success');
                        btnCop.innerHTML = '<i class="bi bi-clipboard"></i> Copiar texto';
                    }, 1200);
                } catch (err) {
                    console.warn('Falha ao copiar', err);
                }
            });
        }
    })();
</script>