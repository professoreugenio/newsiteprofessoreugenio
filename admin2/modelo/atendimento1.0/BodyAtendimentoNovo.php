<?php
// BodyAtendimentoNovo.php
// Requisitos no contexto: $con (PDO) disponível no include principal.
// NÃO incluir <html>, <head>, <body> aqui (página modulada).

/* -----------------------
   Helpers
------------------------ */
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('fotoAlunoUrl')) {
    function fotoAlunoUrl(?string $pasta, ?string $img): string
    {
        $p = trim((string)$pasta);
        $f = trim((string)$img);
        if ($f === '' || $f === 'usuario.jpg') {
            return 'https://professoreugenio.com/fotos/usuarios/usuario.jpg';
        }
        return "https://professoreugenio.com/fotos/usuarios/{$p}/{$f}";
    }
}
if (!function_exists('dtBr')) {
    function dtBr(?string $ymd): string
    {
        if (!$ymd || $ymd === '0000-00-00') return '--/--/----';
        $ts = strtotime($ymd);
        return $ts ? date('d/m/Y', $ts) : '--/--/----';
    }
}
if (!function_exists('calcIdade')) {
    function calcIdade(?string $ymd): ?int
    {
        if (!$ymd || $ymd === '0000-00-00') return null;
        try {
            $n = new DateTime($ymd);
            $h = new DateTime('today');
            return (int)$n->diff($h)->y;
        } catch (Exception $e) {
            return null;
        }
    }
}

/* -----------------------
   Conexão (fallback)
------------------------ */
if (!isset($con) || !$con instanceof PDO) {
    if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__);
    require_once APP_ROOT . '/conexao/class.conexao.php';
    $con = config::connect();
}

/* -----------------------
   Captura de parâmetro
------------------------ */
$idAluno = 0;
$idParam = $_GET['id'] ?? $_GET['idUsuario'] ?? '';

if ($idParam !== '') {
    // Tenta decrypt se existir e se parecer base64-like
    if (function_exists('encrypt') && preg_match('/^[A-Za-z0-9+\/=]+$/', $idParam)) {
        $dec = @encrypt($idParam, 'd');
        if (ctype_digit((string)$dec)) {
            $idAluno = (int)$dec;
        }
    }
    // Fallback: inteiro puro
    if ($idAluno === 0 && ctype_digit((string)$idParam)) {
        $idAluno = (int)$idParam;
    }
}

if ($idAluno <= 0) {
    echo '<div class="alert alert-warning">Aluno não identificado.</div>';
    return;
}

/* -----------------------
   Consulta aluno
------------------------ */
$sqlAluno = "
  SELECT codigocadastro, nome, pastasc, imagem200, imagem50,
         datanascimento_sc, possuipc
  FROM new_sistema_cadastro
  WHERE codigocadastro = :id
  LIMIT 1
";
$stmtA = $con->prepare($sqlAluno);
$stmtA->bindValue(':id', $idAluno, PDO::PARAM_INT);
$stmtA->execute();
$aluno = $stmtA->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    echo '<div class="alert alert-danger">Aluno não encontrado.</div>';
    return;
}

$nomeAluno   = trim($aluno['nome'] ?? '');
$fotoAluno   = fotoAlunoUrl($aluno['pastasc'] ?? '', $aluno['imagem200'] ?? '');
$nascYmd     = $aluno['datanascimento_sc'] ?? null;
$nascBr      = dtBr($nascYmd);
$idade       = calcIdade($nascYmd);
$possuipcRaw = isset($aluno['possuipc']) ? (int)$aluno['possuipc'] : null;
$possuipcTxt = ($possuipcRaw === 1) ? 'Sim' : 'Não';

$idEnc       = function_exists('encrypt') ? encrypt((string)$idAluno, 'e') : (string)$idAluno;

/* -----------------------
   Consulta etapas
------------------------ */
$sqlEtapas = "
  SELECT codigoetapas, nomeetapa, ordem
  FROM a_aluno_atendimento_etapas
  ORDER BY ordem ASC, nomeetapa ASC
";
$stmtE = $con->query($sqlEtapas);
$etapas = $stmtE->fetchAll(PDO::FETCH_ASSOC);

/* Pré-calcula tokens encryptados para redirecionar */
foreach ($etapas as &$et) {
    $et['idEnc'] = function_exists('encrypt') ? encrypt((string)$et['codigoetapas'], 'e') : (string)$et['codigoetapas'];
}
unset($et);

/* Paleta de cores (Bootstrap) para variar botões */
$btnStyles = ['primary', 'success', 'warning', 'info', 'secondary', 'danger', 'dark'];
?>

<!-- Cabeçalho do aluno -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center">
            <img src="<?= h($fotoAluno) ?>" alt="Foto do aluno" width="64" height="64"
                class="rounded-circle border shadow-sm me-3" style="object-fit:cover;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><?= h($nomeAluno) ?></h5>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-cake me-1"></i>
                        Nascimento: <?= h($nascBr) ?><?= $idade !== null ? ' • ' . (int)$idade . ' anos' : '' ?>
                    </span>
                    <span class="badge <?= $possuipcTxt === 'Sim' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                        <i class="bi bi-pc-display me-1"></i> Possui PC: <strong class="ms-1"><?= h($possuipcTxt) ?></strong>
                    </span>
                </div>
                <div class="text-muted small mt-1">
                    Selecione uma <strong>etapa</strong> para registrar o atendimento e continuar a conversa.
                </div>
            </div>
            <div class="ms-3">
                <a href="alunoAtendimentoLista.php?idUsuario=<?= urlencode($idEnc) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Histórico
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Etapas como botões -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <?php if (empty($etapas)): ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-1"></i> Nenhuma etapa de atendimento cadastrada.
            </div>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php $i = 0;
                foreach ($etapas as $et): ?>
                    <?php
                    $style = $btnStyles[$i % count($btnStyles)];
                    $btnId = 'btnEtapa_' . (int)$et['codigoetapas'];
                    ?>
                    <button
                        id="<?= h($btnId) ?>"
                        type="button"
                        class="btn btn-<?= h($style) ?> d-inline-flex align-items-center gap-2"
                        data-id-aluno="<?= (int)$idAluno ?>"
                        data-id-aluno-enc="<?= h($idEnc) ?>"
                        data-id-etapa="<?= (int)$et['codigoetapas'] ?>"
                        data-id-etapa-enc="<?= h($et['idEnc']) ?>"
                        data-nome-etapa="<?= h($et['nomeetapa']) ?>">
                        <i class="bi bi-flag"></i>
                        <?= h($et['nomeetapa']) ?>
                       
                    </button>
                <?php $i++;
                endforeach; ?>
            </div>

            <div id="areaMensagemAt" class="mt-3" style="display:none;"></div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Requer jQuery e Bootstrap 5 no layout principal
    (function() {
        function showMsg(html, klass) {
            const area = document.getElementById('areaMensagemAt');
            area.className = '';
            area.classList.add('alert', klass || 'alert-info');
            area.innerHTML = html;
            area.style.display = 'block';
        }

        function clearMsg() {
            const area = document.getElementById('areaMensagemAt');
            area.style.display = 'none';
            area.innerHTML = '';
            area.className = '';
        }

        document.querySelectorAll('button[id^="btnEtapa_"]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                clearMsg();

                const el = e.currentTarget;
                const idAluno = el.getAttribute('data-id-aluno');
                const idAlunoEnc = el.getAttribute('data-id-aluno-enc');
                const idEtapa = el.getAttribute('data-id-etapa');
                const idEtapaEnc = el.getAttribute('data-id-etapa-enc');
                const nomeEtapa = el.getAttribute('data-nome-etapa');

                // UI: desabilita botão e mostra spinner
                const originalHtml = el.innerHTML;
                el.disabled = true;
                el.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';

                // Envio AJAX para inserir atendimento (servidor preenche data/hora atuais)
                $.ajax({
                        url: 'atendimento1.0/ajax_AtendimentoInsere.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            idaluno: idAluno,
                            idetapa: idEtapa
                        }
                    })
                    .done(function(resp) {
                        if (resp && (resp.ok === 1 || resp.status === 'ok')) {
                            // Redireciona para a tela de mensagens com IDs encryptados
                            const url = 'alunoAtendimentoMensagens.php?idUsuario=' + encodeURIComponent(idAlunoEnc) +
                                '&idEtapa=' + encodeURIComponent(idEtapaEnc);
                            window.location.href = url;
                        } else {
                            showMsg('<i class="bi bi-exclamation-triangle me-1"></i> Não foi possível salvar o atendimento. Tente novamente.', 'alert-warning');
                            el.disabled = false;
                            el.innerHTML = originalHtml;
                        }
                    })
                    .fail(function() {
                        showMsg('<i class="bi bi-x-octagon me-1"></i> Erro de comunicação. Verifique sua conexão.', 'alert-danger');
                        el.disabled = false;
                        el.innerHTML = originalHtml;
                    });
            });
        });
    })();
</script>