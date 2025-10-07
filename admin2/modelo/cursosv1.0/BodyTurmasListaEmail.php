<?php

// Parâmetro do curso
$idCurso = isset($idCurso) ? $idCurso : (isset($_GET['idCurso']) ? (int)$_GET['idCurso'] : 0);
if (!$idCurso) {
    echo '<div class="alert alert-danger">Curso não informado.</div>';
    exit;
}

// email em massa
?>

<?php require 'cursosv1.0/BodyTurmasListaEmailemMassa.php'; ?>

<div class="mb-4">
    <a target="_blank" href="<?= mailtoAll($emailsCCO, $assuntoPromo, $corpoPromo) ?>" class="btn btn-warning mb-2 me-2">
        <i class="bi bi-stars me-2"></i> Promoção de Curso
    </a>
    <a target="_blank" href="<?= mailtoAll($emailsCCO, $assuntoProdutos, $corpoProdutos) ?>" class="btn btn-primary mb-2 me-2">
        <i class="bi bi-bag-check me-2"></i> Produtos para Compra
    </a>
    <a target="_blank" href="<?= mailtoAll($emailsCCO, $assuntoSaudacao, $corpoSaudacao) ?>" class="btn btn-success mb-2 me-2">
        <i class="bi bi-emoji-smile me-2"></i> Saudação & Motivação
    </a>
</div>
<?php
// Consulta: todos alunos do curso

// ...código anterior...

$stmt = config::connect()->prepare("
    SELECT i.codigousuario, c.nome, c.email, c.celular, c.emailbloqueio 
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    WHERE i.codcurso_ip = :idCurso
    GROUP BY i.codigousuario
    ORDER BY c.nome
");
$stmt->bindParam(":idCurso", $idCurso);
$stmt->execute();
?>

<h4 class="mb-3">
    <i class="bi bi-envelope-at me-2 text-primary"></i>
    Lista de E-mails dos Alunos
</h4>

<?php
// Função para montar o link WhatsApp
if (!function_exists('formatarLinkWhatsapp')) {
    function formatarLinkWhatsapp($celular, $msg = "")
    {
        // Remove tudo que não for número
        $numero = preg_replace('/\D/', '', $celular);
        // Adiciona DDI 55 se necessário
        if ($numero && substr($numero, 0, 2) !== '55') {
            $numero = '55' . $numero;
        }
        return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : '#';
    }
}
?>

<?php if ($stmt->rowCount() > 0): ?>
    <button id="btnEnviarSelecionados" class="btn btn-danger mb-3" type="button">
        <i class="bi bi-envelope-plus"></i> Enviar e-mail para selecionados
    </button>

    <?php
    // Consulta anos disponíveis
    $stmtAnos = config::connect()->prepare("
    SELECT DISTINCT YEAR(data_ins) as ano
    FROM new_sistema_inscricao_PJA
    WHERE codcurso_ip = :idCurso
    ORDER BY ano DESC
");
    $stmtAnos->bindParam(":idCurso", $idCurso);
    $stmtAnos->execute();
    $anos = [];
    while ($row = $stmtAnos->fetch(PDO::FETCH_ASSOC)) {
        $anos[] = $row['ano'];
    }
    $anoSelecionado = isset($_GET['ano']) ? (int)$_GET['ano'] : 0;
    ?>
    <div class="row mb-3">
        <div class="col-auto">
            <form method="get" id="filtroAnoForm">
                <input type="hidden" name="idCurso" value="<?= $idCurso ?>">
                <label for="ano" class="form-label mb-0">Ano de inscrição:</label>
                <select name="ano" id="ano" class="form-select" style="display:inline-block;width:auto" onchange="document.getElementById('filtroAnoForm').submit()">
                    <option value="0">Todos</option>
                    <?php foreach ($anos as $ano): ?>
                        <option value="<?= $ano ?>" <?= $ano == $anoSelecionado ? 'selected' : '' ?>><?= $ano ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col">
            <input type="text" id="buscaNome" class="form-control" placeholder="Buscar por nome...">
        </div>
    </div>


    <div class="table-responsive">
        <table class="table table-bordered align-middle table-hover">

            <thead class="table-light">
                <tr>
                    <th style="min-width:80px;"> <input type="checkbox" id="checkAll" title="Marcar/Desmarcar todos"> todos</th>
                    <th style="min-width:200px;">Nome</th>
                    <th style="min-width:260px;">E-mail</th>
                    <th style="min-width:160px;">WhatsApp</th>
                    <th style="min-width:120px;">Status E-mail</th>
                    <th style="min-width:180px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <?php
                    $nomes = explode(' ', trim($row['nome']));
                    $nomeCurto = htmlspecialchars(implode(' ', array_slice($nomes, 0, 2)));

                    $email = trim($row['email']);
                    $celular = trim($row['celular']);
                    $bloqueado = (int)$row['emailbloqueio'];
                    $statusEmail = $bloqueado ? 'Bloqueado' : 'Liberado';
                    $badge = $bloqueado ? 'bg-danger' : 'bg-success';

                    // E-mail
                    $titulo = "Olá, {$nomeCurto} – Seu Curso Online com o Professor Eugênio";
                    $cabecalho = "Olá, {$nomeCurto}!\n\nAqui é o professor Eugênio.";
                    $corpo = "Quero lembrar que você está matriculado(a) no nosso curso online! Acesse sua área de aluno para mais informações.\n\nSe tiver dúvidas, responda este e-mail.\n\nAbraços,\nProf. Eugênio";
                    $msgCompleta = $cabecalho . "\n\n" . $corpo;
                    $mailto = 'mailto:' . rawurlencode($email) .
                        '?subject=' . rawurlencode($titulo) .
                        '&body=' . rawurlencode($msgCompleta);

                    // WhatsApp
                    $msgWpp = "👋 Olá, {$nomeCurto}! Aqui é o professor Eugênio. Qualquer dúvida, pode me chamar por aqui. Abraços!";
                    $linkWpp = formatarLinkWhatsapp($celular, $msgWpp);
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="checkEmail" value="<?= htmlspecialchars($email) ?>">
                        </td>

                        <td>
                            <?= $nomeCurto ?>
                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['nome']) ?>">
                                <i class="bi bi-info-circle text-secondary ms-1"></i>
                            </span>
                        </td>
                        <td>
                            <a target="_blank" href="<?= $mailto ?>" class="text-decoration-none fw-semibold" data-bs-toggle="tooltip" title="Enviar e-mail">
                                <i class="bi bi-envelope-at me-1"></i><?= htmlspecialchars($email) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($celular): ?>
                                <a target="_blank" href="<?= $linkWpp ?>" target="_blank"
                                    class="btn btn-outline-success btn-sm d-flex align-items-center"
                                    data-bs-toggle="tooltip"
                                    title="Enviar mensagem no WhatsApp">
                                    <i class="bi bi-whatsapp me-2 fs-5"></i> WhatsApp
                                </a>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-dash-circle"></i> Não informado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $badge ?> fs-6">
                                <?= $statusEmail ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($bloqueado): ?>
                                <button class="btn btn-danger btn-sm" disabled>
                                    <i class="bi bi-x-circle-fill"></i> E-mail Bloqueado
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success btn-sm" disabled>
                                    <i class="bi bi-check-circle-fill"></i> E-mail Liberado
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-warning">Nenhum aluno com e-mail cadastrado neste curso.</div>
<?php endif; ?>

<!-- Tooltips Bootstrap -->

<script>
    // Marcar/desmarcar todos
    document.getElementById('checkAll').addEventListener('change', function() {
        let marcar = this.checked;
        document.querySelectorAll('.checkEmail').forEach(el => el.checked = marcar);
    });

    // Atualiza "Selecionar Todos" se algum for desmarcado/marcado manualmente
    document.querySelectorAll('.checkEmail').forEach(el => {
        el.addEventListener('change', function() {
            let todos = document.querySelectorAll('.checkEmail').length;
            let marcados = document.querySelectorAll('.checkEmail:checked').length;
            document.getElementById('checkAll').checked = (todos === marcados);
        });
    });

    // Botão para enviar e-mail aos selecionados
    document.getElementById('btnEnviarSelecionados').addEventListener('click', function() {
        let selecionados = Array.from(document.querySelectorAll('.checkEmail:checked')).map(e => e.value);
        if (selecionados.length === 0) {
            alert("Selecione ao menos um e-mail!");
            return;
        }
        // Título, corpo padrão, pode customizar para cada envio:
        let assunto = "Assunto do E-mail";
        let corpo = "Olá!\n\nMensagem personalizada aqui.";
        let mailto = 'mailto:?bcc=' + encodeURIComponent(selecionados.join(',')) +
            '&subject=' + encodeURIComponent(assunto) +
            '&body=' + encodeURIComponent(corpo);

        // window.location.href = mailto;
        window.open(mailto, '_blank');

    });
</script>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>