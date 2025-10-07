<?php

// Filtro por mês
$mesAtual = date('Y-m');
$filtroMes = $_GET['mes'] ?? $mesAtual;

// Filtro por lixeira (padrão: mensagens não excluídas)
$modo = $_GET['modo'] ?? 'recebidas';
$lixeira = ($modo === 'lixeira') ? 1 : 0;

$stmt = config::connect()->prepare("
    SELECT codigocontato, nomesc, assuntosc, datasc, horasc, statussc, lixeiraSC
    FROM new_sistema_contato
    WHERE DATE_FORMAT(datasc, '%Y-%m') = :mes AND lixeiraSC = :lixeira
    ORDER BY datasc DESC, horasc DESC
");
$stmt->execute([
    ':mes' => $filtroMes,
    ':lixeira' => $lixeira
]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
    <h3 class="mb-3">
        📬 <?= $modo === 'lixeira' ? 'Mensagens na Lixeira' : 'Mensagens Recebidas' ?>
    </h3>

    <div class="d-flex justify-content-start gap-2 mb-3">
        <a href="MensagensRecebidas.php?modo=recebidas&mes=<?= $filtroMes ?>" class="btn btn-outline-primary <?= $modo === 'recebidas' ? 'active' : '' ?>">
            <i class="bi bi-inbox-fill me-1"></i> Recebidas
        </a>
        <a href="MensagensRecebidas.php?modo=lixeira&mes=<?= $filtroMes ?>" class="btn btn-outline-secondary <?= $modo === 'lixeira' ? 'active' : '' ?>">
            <i class="bi bi-trash-fill me-1"></i> Lixeira
        </a>
    </div>

    <form method="get" class="row mb-4">
        <input type="hidden" name="modo" value="<?= htmlspecialchars($modo) ?>">
        <div class="col-md-4">
            <label for="mes" class="form-label">Filtrar por mês:</label>
            <input type="month" id="mes" name="mes" class="form-control" value="<?= htmlspecialchars($filtroMes) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <?php if (count($mensagens)): ?>
        <div class="list-group">
            <?php foreach ($mensagens as $msg): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <a href="MensagensRecebidasExibir.php?id=<?= urlencode($msg['codigocontato']) ?>" class="text-decoration-none">
                            <strong>
                                <?= htmlspecialchars($msg['nomesc']) ?>
                                <?php if (intval($msg['statussc']) === 0): ?>
                                    <span class="badge bg-warning text-dark ms-2">Não lida</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">Lida</span>
                                <?php endif; ?>
                            </strong><br>
                            <small class="text-muted"><?= htmlspecialchars($msg['assuntosc']) ?></small>
                        </a>
                    </div>
                    <div class="text-end me-3 small text-secondary">
                        <?= date('d/m/Y', strtotime($msg['datasc'])) ?><br>
                        <?= date('H:i', strtotime($msg['horasc'])) ?>
                    </div>


                    <form method="post" onsubmit="return confirm('Mover esta mensagem para a lixeira?')">
                        <input type="hidden" name="id" value="<?= $msg['codigocontato'] ?>">
                        <?php if ($modo === 'recebidas'): ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="moverParaLixeira(<?= $msg['codigocontato'] ?>, this)"
                                title="Mover para Lixeira">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php elseif ($modo === 'lixeira'): ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-success"
                                onclick="restaurarDaLixeira(<?= $msg['codigocontato'] ?>, this)"
                                title="Restaurar mensagem">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>

                        <?php endif; ?>



                    </form>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-4">Nenhuma mensagem encontrada para este filtro.</div>
    <?php endif; ?>
</div>

<script>
    function moverParaLixeira(idMsg, elemento) {
        if (!confirm('Tem certeza que deseja mover esta mensagem para a lixeira?')) return;

        $.post('mensagens1.0/ajax_lixeiraMensagem.php', {
            id: idMsg
        }, function(response) {
            if (response.status === 'sucesso') {
                $(elemento).closest('.list-group-item').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.mensagem || 'Erro inesperado.');
            }
        }, 'json');
    }
</script>

<script>
    function restaurarDaLixeira(idMsg, elemento) {
        if (!confirm('Tem certeza que deseja mover esta mensagem para a lixeira?')) return;

        $.post('mensagensv1.0/ajax_lixeiraMensagem.php', {
            id: idMsg
        }, function(response) {
            if (response.status === 'sucesso') {
                $(elemento).closest('.list-group-item').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.mensagem || 'Erro inesperado.');
            }
        }, 'json');
    }

    function restaurarDaLixeira(idMsg, elemento) {
        if (!confirm('Deseja restaurar esta mensagem da lixeira?')) return;

        $.post('mensagens1.0/ajax_restaurarMensagem.php', {
            id: idMsg
        }, function(response) {
            if (response.status === 'sucesso') {
                $(elemento).closest('.list-group-item').fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.mensagem || 'Erro ao restaurar a mensagem.');
            }
        }, 'json');
    }
</script>