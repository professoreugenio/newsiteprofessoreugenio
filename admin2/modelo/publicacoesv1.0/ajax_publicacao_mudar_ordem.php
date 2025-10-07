<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$id = intval($_POST['id']);
$direcao = $_POST['direcao'] ?? '';

$stmt = $con->prepare("SELECT ordem, codigocurso_sp, codmodulo_sp, aula FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['sucesso' => false, 'msg' => 'Publicação não encontrada']);
    exit;
}

$ordemAtual = intval($row['ordem']);
$curso = $row['codigocurso_sp'];
$modulo = $row['codmodulo_sp'];
$aula = $row['aula'];

if ($direcao == 'up') {
    $novoOrdem = $ordemAtual - 1;
} else if ($direcao == 'down') {
    $novoOrdem = $ordemAtual + 1;
} else {
    echo json_encode(['sucesso' => false, 'msg' => 'Direção inválida']);
    exit;
}

// Busca a publicação que está na nova ordem
$stmt2 = $con->prepare("SELECT codigopublicacoes FROM new_sistema_publicacoes_PJA WHERE codigocurso_sp = :curso AND codmodulo_sp = :modulo AND aula = :aula AND ordem = :novoOrdem");
$stmt2->execute([
    ':curso' => $curso,
    ':modulo' => $modulo,
    ':aula' => $aula,
    ':novoOrdem' => $novoOrdem
]);
$swap = $stmt2->fetch(PDO::FETCH_ASSOC);

if ($swap) {
    // Troca as ordens entre as publicações
    $con->beginTransaction();
    $con->prepare("UPDATE new_sistema_publicacoes_PJA SET ordem = :ordem WHERE codigopublicacoes = :id")
        ->execute([':ordem' => $ordemAtual, ':id' => $swap['codigopublicacoes']]);
    $con->prepare("UPDATE new_sistema_publicacoes_PJA SET ordem = :ordem WHERE codigopublicacoes = :id")
        ->execute([':ordem' => $novoOrdem, ':id' => $id]);
    $con->commit();
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'msg' => 'Não pode mover mais!']);
}
