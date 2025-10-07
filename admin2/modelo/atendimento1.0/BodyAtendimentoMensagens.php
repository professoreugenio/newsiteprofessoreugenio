<?php
// alunoAtendimentoMensagens.php (PÁGINA MODULADA)
// Requisitos: $con (PDO), Bootstrap 5, jQuery e Summernote já carregados.
// NÃO incluir <html>, <head>, <body> aqui.

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
        if ($f === '' || $f === 'usuario.jpg') return 'https://professoreugenio.com/fotos/usuarios/usuario.jpg';
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
if (!function_exists('nomeDiaSemana')) {
    function nomeDiaSemana(?string $ymd): string
    {
        if (!$ymd) return '';
        $ts = strtotime($ymd);
        if (!$ts) return '';
        $n = (int)date('N', $ts); // 1=Mon...7=Sun
        $map = [1 => 'segunda-feira', 2 => 'terça-feira', 3 => 'quarta-feira', 4 => 'quinta-feira', 5 => 'sexta-feira', 6 => 'sábado', 7 => 'domingo'];
        return $map[$n] ?? '';
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
   Parâmetros: aluno e etapa
------------------------ */
$idAluno = 0;
$idParamUser  = $_GET['idUsuario'] ?? $_GET['id'] ?? '';
$idParamEtapa = $_GET['idEtapa']   ?? '';

if ($idParamUser !== '') {
    if (function_exists('encrypt') && preg_match('/^[A-Za-z0-9+\/=]+$/', $idParamUser)) {
        $dec = @encrypt($idParamUser, 'd');
        if (ctype_digit((string)$dec)) $idAluno = (int)$dec;
    }
    if ($idAluno === 0 && ctype_digit((string)$idParamUser)) $idAluno = (int)$idParamUser;
}
if ($idAluno <= 0) {
    echo '<div class="alert alert-warning">Aluno não identificado.</div>';
    return;
}

// Etapa (obrigatória)
$idEtapa = 0;
if ($idParamEtapa !== '') {
    if (function_exists('encrypt') && preg_match('/^[A-Za-z0-9+\/=]+$/', $idParamEtapa)) {
        $decE = @encrypt($idParamEtapa, 'd');
        if (ctype_digit((string)$decE)) $idEtapa = (int)$decE;
    }
    if ($idEtapa === 0 && ctype_digit((string)$idParamEtapa)) $idEtapa = (int)$idParamEtapa;
}
if ($idEtapa <= 0) {
    echo '<div class="alert alert-warning">Etapa do atendimento não informada.</div>';
    return;
}

// Ids encryptados p/ links
$idAlunoEnc = function_exists('encrypt') ? encrypt((string)$idAluno, 'e') : (string)$idAluno;
$idEtapaEnc = function_exists('encrypt') ? encrypt((string)$idEtapa, 'e') : (string)$idEtapa;

/* -----------------------
   Consulta: aluno
------------------------ */
$sqlAluno = "
  SELECT codigocadastro, nome, pastasc, imagem200, imagem50,
         datanascimento_sc, possuipc, celular, email
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
$possuipcTxt = ((int)($aluno['possuipc'] ?? 0) === 1) ? 'Sim' : 'Não';
$emailAluno  = trim((string)($aluno['email'] ?? ''));

// Celular normalizado para wa.me
$celular = preg_replace('/\D/', '', (string)($aluno['celular'] ?? ''));
if ($celular !== '' && substr($celular, 0, 2) !== '55') $celular = '55' . $celular;
$celularDisplay = $celular ? ('+' . substr($celular, 0, 2) . ' ' . substr($celular, 2)) : '';

/* -----------------------
   Turmas do aluno
------------------------ */
$sqlTurmas = "
  SELECT i.chaveturma, i.dataprazosi, t.nometurma
  FROM new_sistema_inscricao_PJA i
  INNER JOIN new_sistema_cursos_turmas t ON t.chave = i.chaveturma
  WHERE i.codigousuario = :id
  ORDER BY t.nometurma
";
$stmtT = $con->prepare($sqlTurmas);
$stmtT->bindValue(':id', $idAluno, PDO::PARAM_INT);
$stmtT->execute();
$turmas = $stmtT->fetchAll(PDO::FETCH_ASSOC);

// Ajuste destino do link da turma:
$linkTurmaBase = 'curso.php?chave=';

// Curso principal para a saudação (pega a 1ª turma; ajuste se quiser outra lógica)
$cursoPrincipal = 'seu curso';
if (!empty($turmas)) {
    $curso0 = trim($turmas[0]['nometurma'] ?? '');
    if ($curso0 !== '') $cursoPrincipal = $curso0;
}

/* -----------------------
   Mensagens da etapa
------------------------ */
$sqlMsgs = "
  SELECT codigoatendimentomsg, iddetapaatm, idtipomsgatm, tituloatm, textoatm, dataatm, horaatm
  FROM a_aluno_atendimento_mensagem
  WHERE iddetapaatm = :idetapa
  ORDER BY dataatm DESC, horaatm DESC, codigoatendimentomsg DESC
";
$stmtM = $con->prepare($sqlMsgs);
$stmtM->bindValue(':idetapa', $idEtapa, PDO::PARAM_INT);
$stmtM->execute();
$mensagens = $stmtM->fetchAll(PDO::FETCH_ASSOC);

// Grupos por tipo (1=WhatsApp, 2=E-mail)
$msgsWhats = array_filter($mensagens, fn($r) => (int)($r['idtipomsgatm'] ?? 0) === 1);
$msgsEmail = array_filter($mensagens, fn($r) => (int)($r['idtipomsgatm'] ?? 0) === 2);
?>

<style>
    /* --- chips layout, no estilo do exemplo --- */
    .list-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }

    .chip-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-chip {
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 700;
        letter-spacing: .3px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
    }

    .btn-chip i {
        font-size: 1.05rem;
    }

    .btn-edit {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
    }

    .btn-edit i {
        font-size: 1rem;
    }

    .card-chips .card-header {
        background: transparent;
        border-bottom: 0;
        padding-bottom: 0;
    }
</style>

<!-- Cabeçalho com aluno -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center">
            <img src="<?= h($fotoAluno) ?>" alt="Foto do aluno" width="64" height="64"
                class="rounded-circle border shadow-sm me-3" style="object-fit:cover;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h5 class="mb-0"><?= h($nomeAluno) ?></h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="bi bi-collection me-1"></i> Etapa atual
                    </span>
                </div>

                <div class="text-muted small mt-1 d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-cake me-1"></i>
                        Nascimento: <?= h($nascBr) ?><?= $idade !== null ? ' • ' . (int)$idade . ' anos' : '' ?>
                    </span>
                    <span class="badge <?= $possuipcTxt === 'Sim' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                        <i class="bi bi-pc-display me-1"></i> Possui PC: <strong class="ms-1"><?= h($possuipcTxt) ?></strong>
                    </span>

                    <!-- Contatos -->
                    <span class="badge <?= $celular ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp:
                        <strong class="ms-1"><?= $celular ? h($celularDisplay) : 'não informado' ?></strong>
                    </span>
                    <span class="badge <?= $emailAluno ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?>">
                        <i class="bi bi-envelope me-1"></i> E-mail:
                        <strong class="ms-1"><?= $emailAluno ? h($emailAluno) : 'não informado' ?></strong>
                    </span>
                </div>
            </div>

            <?php

            // Pega variáveis da sessão (com fallback caso não existam)
            $idUsuario = $_SESSION['idUsuario'] ?? '';
            $idUrl = $_SESSION['id'] ?? '';
            $tm = $_SESSION['tm'] ?? '';
            $ts  = $_SESSION['ts'] ?? '';

            $linkFinal = "cursos_TurmasAlunos.php?id={$idUrl}&tm={$tm}";
            ?>

            <div class="ms-3 d-flex gap-2">
                <a href="<?= htmlspecialchars($linkFinal) ?>" class="btn btn-success">
                    Acessar Turma
                </a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaMsg">
                    <i class="bi bi-plus-circle me-1"></i> Nova mensagem
                </button>


            </div>
        </div>
    </div>
</div>

<!-- Turmas do aluno -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="fw-semibold"><i class="bi bi-mortarboard me-2"></i>Turmas do aluno</div>
            <div class="small text-muted">Clique no nome da turma para abrir</div>
        </div>
        <div class="mt-2 d-flex flex-wrap gap-2">
            <?php if (empty($turmas)): ?>
                <span class="text-muted">Sem inscrições encontradas.</span>
                <?php else: foreach ($turmas as $t): ?>
                    <?php
                    $nomeTurma = trim($t['nometurma'] ?? '');
                    $chave     = (string)($t['chaveturma'] ?? '');
                    $prazoBr   = dtBr($t['dataprazosi'] ?? null);
                    $urlTurma  = $linkTurmaBase . urlencode($chave);
                    ?>
                    <a href="<?= h($urlTurma) ?>" class="badge bg-light text-dark text-decoration-none px-3 py-2">
                        <i class="bi bi-journal-text me-1"></i><?= h($nomeTurma) ?>
                        <span class="ms-2 text-muted">venc.: <?= h($prazoBr) ?></span>
                    </a>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</div>

<!-- Botão de SAUDAÇÃO WhatsApp (acima das listas) -->
<div class="d-flex align-items-center gap-2 mb-3">

    <?php require 'usuariosv1.0/require_MsgsWhatsApp.php'; ?>



</div>

<!-- Filtros por tipo -->
<div class="d-flex align-items-center gap-2 mb-2">
    <input type="radio" class="btn-check" name="tipoMsg" id="tipoWhats" autocomplete="off" checked>
    <label class="btn btn-outline-success" for="tipoWhats"><i class="bi bi-whatsapp me-1"></i> WhatsApp</label>

    <input type="radio" class="btn-check" name="tipoMsg" id="tipoEmail" autocomplete="off">
    <label class="btn btn-outline-primary" for="tipoEmail"><i class="bi bi-envelope me-1"></i> E-mail</label>
</div>

<!-- CHIPS: WhatsApp -->
<div id="chipsWhats" class="card card-chips border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <?php if (empty($msgsWhats)): ?>
            <div class="text-muted">Sem mensagens de WhatsApp nesta etapa.</div>
        <?php else: ?>
            <div class="list-chips">
                <?php foreach ($msgsWhats as $m): ?>
                    <?php
                    $cod  = (int)$m['codigoatendimentomsg'];
                    $tit  = trim($m['tituloatm'] ?? '');
                    $txt  = $m['textoatm'] ?? '';
                    $label = $tit !== '' ? $tit : 'Mensagem';
                    ?>
                    <div class="chip-group" data-codigo="<?= (int)$cod ?>">
                        <!-- Enviar WhatsApp -->
                        <button
                            type="button"
                            class="btn btn-success btn-chip"
                            onclick="sendWhatsFromChip(this)"
                            data-title="<?= h($label) ?>"
                            data-html="<?= h($txt) ?>"
                            title="<?= $celular ? 'Enviar no WhatsApp' : 'Sem número do aluno' ?>">
                            <i class="bi bi-whatsapp me-2"></i><?= h(mb_strtoupper($label, 'UTF-8')) ?>
                        </button>

                        <!-- Editar -->
                        <button
                            type="button"
                            class="btn btn-warning btn-edit"
                            onclick="editFromChip(this)"
                            data-codigo="<?= (int)$cod ?>"
                            data-tipo="1"
                            data-title="<?= h($tit) ?>"
                            data-html="<?= h($txt) ?>"
                            title="Editar mensagem">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CHIPS: E-mail -->
<div id="chipsEmail" class="card card-chips border-0 shadow-sm rounded-4 mb-3" style="display:none;">
    <div class="card-body">
        <?php if (empty($msgsEmail)): ?>
            <div class="text-muted">Sem mensagens de E-mail nesta etapa.</div>
        <?php else: ?>
            <div class="list-chips">
                <?php foreach ($msgsEmail as $m): ?>
                    <?php
                    $cod  = (int)$m['codigoatendimentomsg'];
                    $tit  = trim($m['tituloatm'] ?? '');
                    $txt  = $m['textoatm'] ?? '';
                    $label = $tit !== '' ? $tit : 'Mensagem';
                    ?>
                    <div class="chip-group" data-codigo="<?= (int)$cod ?>">
                        <!-- Enviar E-mail -->
                        <button
                            type="button"
                            class="btn btn-primary btn-chip"
                            onclick="sendEmailFromChip(this)"
                            data-title="<?= h($label) ?>"
                            data-html="<?= h($txt) ?>"
                            title="<?= $emailAluno ? 'Enviar por E-mail' : 'Sem e-mail do aluno' ?>">
                            <i class="bi bi-envelope me-2"></i><?= h(mb_strtoupper($label, 'UTF-8')) ?>
                        </button>

                        <!-- Editar -->
                        <button
                            type="button"
                            class="btn btn-warning btn-edit"
                            onclick="editFromChip(this)"
                            data-codigo="<?= (int)$cod ?>"
                            data-tipo="2"
                            data-title="<?= h($tit) ?>"
                            data-html="<?= h($txt) ?>"
                            title="Editar mensagem">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL NOVA/EDITAR MENSAGEM -->
<div class="modal fade" id="modalNovaMsg" tabindex="-1" aria-labelledby="modalNovaMsgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovaMsgLabel"><i class="bi bi-chat-square-text me-2"></i>Nova mensagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovaMsg">
                    <input type="hidden" name="iddetapaatm" value="<?= (int)$idEtapa ?>">
                    <input type="hidden" name="codigoatendimentomsg" id="codigoatendimentomsg" value="">
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="idtipomsgatm" id="tipo1" value="1" checked>
                                <label class="form-check-label" for="tipo1"><i class="bi bi-whatsapp me-1 text-success"></i> WhatsApp</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="idtipomsgatm" id="tipo2" value="2">
                                <label class="form-check-label" for="tipo2"><i class="bi bi-envelope me-1 text-primary"></i> E-mail</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título / Assunto</label>
                        <input type="text" class="form-control" name="tituloatm" id="tituloatm" maxlength="50" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea class="form-control summernote" name="textoatm" id="textoatm"></textarea>
                    </div>
                </form>
                <div id="msgRet" class="alert d-none mt-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" id="btnSalvarMsg"><i class="bi bi-save me-1"></i> Salvar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicialização Summernote (se disponível)
    $(function() {
        if ($.fn.summernote) $('.summernote').summernote({
            height: 220
        });
    });

    // Alterna chips por tipo
    $('#tipoWhats').on('change', function() {
        if (this.checked) {
            $('#chipsWhats').show();
            $('#chipsEmail').hide();
        }
    });
    $('#tipoEmail').on('change', function() {
        if (this.checked) {
            $('#chipsEmail').show();
            $('#chipsWhats').hide();
        }
    });

    // Util: decodifica HTML e retorna texto plano
    function htmlToText(html) {
        const ta = document.createElement('textarea');
        ta.innerHTML = html; // decodifica entidades
        const decoded = ta.value;
        const tmp = document.createElement('div');
        tmp.innerHTML = decoded; // cria DOM
        return (tmp.textContent || tmp.innerText || '').trim();
    }

    // Envio SAUDAÇÃO (topo)
    function sendSaudacaoTop() {
        const numero = '<?= h($celular) ?>';
        if (!numero) {
            alert('Sem número do aluno.');
            return;
        }
        const nome = '<?= h($nomeAluno) ?>';
        const curso = '<?= h($cursoPrincipal) ?>';
        const msg = 'Boa tarde ' + nome + ' abrindo contato do seu curso de ' + curso + '.';
        const url = 'https://wa.me/' + numero + '?text=' + encodeURIComponent(msg);
        window.open(url, '_blank');
    }

    // Envio por WhatsApp (chips)
    function sendWhatsFromChip(btn) {
        const numero = '<?= h($celular) ?>';
        if (!numero) {
            alert('Sem número do aluno.');
            return;
        }
        const tit = btn.getAttribute('data-title') || '';
        const html = btn.getAttribute('data-html') || '';
        const texto = htmlToText(html);
        const full = (tit ? ('*' + tit + '*\n\n') : '') + texto;
        const url = 'https://wa.me/' + numero + '?text=' + encodeURIComponent(full);
        window.open(url, '_blank');
    }


    // Marca o rádio do tipo no modal
    function setTipoRadio(tipo) { // '1' ou '2'
        if (tipo === '2') {
            $('#tipo2').prop('checked', true);
            $('#tipo1').prop('checked', false);
        } else {
            $('#tipo1').prop('checked', true);
            $('#tipo2').prop('checked', false);
        }
    }

    // Editar: abre modal com dados e tipo selecionado
    function editFromChip(btn) {
        const cod = btn.getAttribute('data-codigo');
        const tipo = btn.getAttribute('data-tipo'); // '1' Whats | '2' Email
        const title = btn.getAttribute('data-title') || '';
        const htmlAttr = btn.getAttribute('data-html') || '';

        // Decodifica entidades para HTML original
        const ta = document.createElement('textarea');
        ta.innerHTML = htmlAttr;
        const htmlDecoded = ta.value;

        $('#modalNovaMsgLabel').html('<i class="bi bi-pencil-square me-2"></i>Editar mensagem');
        $('#codigoatendimentomsg').val(cod);
        $('#tituloatm').val(title);
        setTipoRadio(tipo);

        if ($.fn.summernote) $('#textoatm').summernote('code', htmlDecoded);
        else $('#textoatm').val(htmlDecoded);

        const modal = new bootstrap.Modal(document.getElementById('modalNovaMsg'));
        modal.show();
    }

    // "Nova mensagem": marca o tipo conforme filtro ativo
    $('#modalNovaMsg').off('show.bs.modal').on('show.bs.modal', function(e) {
        const isEdit = !!$('#codigoatendimentomsg').val();
        if (isEdit) return; // edição já tratada em editFromChip

        $('#modalNovaMsgLabel').html('<i class="bi bi-chat-square-text me-2"></i>Nova mensagem');
        $('#codigoatendimentomsg').val('');
        $('#tituloatm').val('');

        if ($('#tipoEmail').is(':checked')) setTipoRadio('2');
        else setTipoRadio('1');

        if ($.fn.summernote) $('#textoatm').summernote('code', '');
        else $('#textoatm').val('');
    });

    // Salvar (insert/update) via AJAX; atualiza chip e move entre listas se tipo mudar
    $('#btnSalvarMsg').off('click').on('click', function() {
        const isEdit = $('#codigoatendimentomsg').val() !== '';
        const idMsg = $('#codigoatendimentomsg').val();
        const tipoSel = $('input[name="idtipomsgatm"]:checked').val(); // '1' ou '2'
        const titulo = $('#tituloatm').val();

        const form = $('#formNovaMsg');
        const data = form.serializeArray();

        // Summernote -> HTML
        let html = $('#textoatm').val();
        if ($.fn.summernote) html = $('#textoatm').summernote('code');
        const idx = data.findIndex(it => it.name === 'textoatm');
        if (idx >= 0) data[idx].value = html;
        else data.push({
            name: 'textoatm',
            value: html
        });

        $.ajax({
                url: 'atendimento1.0/aja_atendimentoInsertMensagem.php',
                method: 'POST',
                data: $.param(data),
                dataType: 'json'
            })
            .done(function(resp) {
                const box = $('#msgRet').removeClass('d-none alert-danger').addClass('alert alert-success');
                if (!resp || !(resp.ok === 1 || resp.status === 'ok')) {
                    box.removeClass('alert-success').addClass('alert-danger')
                        .html('<i class="bi bi-exclamation-triangle me-1"></i> Não foi possível salvar.');
                    return;
                }

                if (!isEdit) {
                    // Novo: recarrega para montar chip
                    box.html('<i class="bi bi-check2-circle me-1"></i> Mensagem salva.');
                    setTimeout(function() {
                        window.location.reload();
                    }, 600);
                    return;
                }

                // Edição: atualiza chip em tempo real
                const $chip = $('.chip-group[data-codigo="' + idMsg + '"]');
                if ($chip.length) {
                    const $mainBtn = $chip.find('.btn-chip');
                    const $editBtn = $chip.find('.btn-edit');
                    const rotulo = (titulo || 'Mensagem').toLocaleUpperCase('pt-BR');

                    // Atualiza atributos
                    $mainBtn.attr('data-title', titulo);
                    $mainBtn.attr('data-html', html);
                    $editBtn.attr('data-title', titulo);
                    $editBtn.attr('data-html', html);

                    // Move entre grupos se mudou o tipo
                    const eraEmail = $chip.closest('#chipsEmail').length > 0;
                    const eraWhats = $chip.closest('#chipsWhats').length > 0;

                    if ((tipoSel === '2' && eraWhats) || (tipoSel === '1' && eraEmail)) {
                        if (tipoSel === '2') {
                            $mainBtn.removeClass('btn-success').addClass('btn-primary')
                                .off('click').on('click', function() {
                                    sendEmailFromChip(this);
                                })
                                .html('<i class="bi bi-envelope me-2"></i>' + rotulo);
                            $('#chipsEmail .list-chips').append($chip);
                            $editBtn.attr('data-tipo', '2');
                        } else {
                            $mainBtn.removeClass('btn-primary').addClass('btn-success')
                                .off('click').on('click', function() {
                                    sendWhatsFromChip(this);
                                })
                                .html('<i class="bi bi-whatsapp me-2"></i>' + rotulo);
                            $('#chipsWhats .list-chips').append($chip);
                            $editBtn.attr('data-tipo', '1');
                        }
                    } else {
                        // Mesmo grupo; só troca rótulo/ícone
                        if ($mainBtn.hasClass('btn-primary')) {
                            $mainBtn.html('<i class="bi bi-envelope me-2"></i>' + rotulo);
                        } else {
                            $mainBtn.html('<i class="bi bi-whatsapp me-2"></i>' + rotulo);
                        }
                    }
                }

                box.html('<i class="bi bi-check2-circle me-1"></i> Alterações salvas.');
                setTimeout(function() {
                    $('#modalNovaMsg').modal('hide');
                }, 300);
            })
            .fail(function() {
                $('#msgRet').removeClass('d-none alert-success').addClass('alert alert-danger')
                    .html('<i class="bi bi-x-octagon me-1"></i> Erro de comunicação.');
            });
    });
</script>


<script>
    // Envio por E-mail abrindo o Gmail (compose)
    function sendEmailFromChip(btn) {
        const email = '<?= h($emailAluno) ?>';
        if (!email) {
            alert('Sem e-mail do aluno.');
            return;
        }

        const tit = btn.getAttribute('data-title') || 'Mensagem';
        const html = btn.getAttribute('data-html') || '';
        const texto = htmlToText(html); // já converte o HTML em texto plano

        // Gmail compose
        const gmailUrl =
            'https://mail.google.com/mail/?view=cm&fs=1' +
            '&to=' + encodeURIComponent(email) +
            '&su=' + encodeURIComponent(tit) +
            '&body=' + encodeURIComponent(texto);

        // Abre em nova aba/janela
        window.open(gmailUrl, '_blank', 'noopener');
    }
</script>