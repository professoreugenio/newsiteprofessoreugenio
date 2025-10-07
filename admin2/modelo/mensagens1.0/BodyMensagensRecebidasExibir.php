<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">ID inválido.</div>';
    exit;
}

$idMsg = intval($_GET['id']);

// Marcar como lida se ainda não estiver
$stmtLida = config::connect()->prepare("
    UPDATE new_sistema_contato 
    SET statussc = 1 
    WHERE codigocontato = :id AND statussc = 0
");
$stmtLida->execute([':id' => $idMsg]);

$stmt = config::connect()->prepare("
    SELECT codigocontato, nomesc, emailsc, assuntosc, celularsc, dadospc_sc, statussc, msgsc, datasc, horasc
    FROM new_sistema_contato
    WHERE codigocontato = :id
");
$stmt->execute([':id' => $idMsg]);
$mensagem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mensagem) {
    echo '<div class="alert alert-warning">Mensagem não encontrada.</div>';
    exit;
}
?>

<div class="container py-4">
    <h3 class="mb-3 text-primary">
        <i class="bi bi-envelope-open-fill me-2"></i>Mensagem de <?= htmlspecialchars($mensagem['nomesc']) ?>
    </h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <p><strong>📅 Data:</strong> <?= date('d/m/Y', strtotime($mensagem['datasc'])) ?> às <?= date('H:i', strtotime($mensagem['horasc'])) ?></p>
            <p><strong>📨 Assunto:</strong> <?= htmlspecialchars($mensagem['assuntosc']) ?></p>
            <p><strong>📧 E-mail:</strong> <a href="mailto:<?= htmlspecialchars($mensagem['emailsc']) ?>"><?= htmlspecialchars($mensagem['emailsc']) ?></a></p>
            <p><strong>📱 Celular:</strong> <?= htmlspecialchars($mensagem['celularsc']) ?></p>
            <p><strong>💻 Informações do dispositivo:</strong> <small class="text-muted"><?= htmlspecialchars($mensagem['dadospc_sc']) ?></small></p>
        </div>
    </div>

    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white">
            <strong><i class="bi bi-chat-dots-fill me-2"></i>Mensagem</strong>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($mensagem['msgsc'])) ?></p>
        </div>
    </div>

    <div class="mt-4">
        <a href="MensagensRecebidas.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i> Voltar à lista
        </a>
        <a href="https://wa.me/55<?= preg_replace('/\D/', '', $mensagem['celularsc']) ?>" target="_blank" class="btn btn-success">
            <i class="bi bi-whatsapp me-1"></i> Responder via WhatsApp
        </a>
        <a href="mailto:<?= htmlspecialchars($mensagem['emailsc']) ?>" class="btn btn-primary">
            <i class="bi bi-envelope-at-fill me-1"></i> Responder por E-mail
        </a>
    </div>
</div>