<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

$codigoForum = isset($_POST['codigoForum']) ? (int)$_POST['codigoForum'] : 0;
$novoTexto   = isset($_POST['novoTexto']) ? (string)$_POST['novoTexto'] : '';

try {
    if ($codigoForum <= 0) throw new Exception('ID inválido.');
    // Permite vazio? Se não, descomente a linha abaixo:
    // if (trim($novoTexto) === '') throw new Exception('O texto não pode ficar vazio.');

    // Atualiza apenas o texto (mantendo data/hora original). Ajuste se quiser registrar data/hora da edição.
    $up = config::connect()->prepare("
        UPDATE a_curso_forum
        SET textoCF = :texto
        WHERE codigoForum = :id
        LIMIT 1
    ");
    $up->bindParam(':texto', $novoTexto, PDO::PARAM_STR);
    $up->bindParam(':id', $codigoForum, PDO::PARAM_INT);
    $up->execute();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
