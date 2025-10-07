<?php
define('APP_ROOT', dirname(__DIR__, 1));
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

header('Content-Type: application/json');

$pasta = isset($_POST['pasta']) ? trim($_POST['pasta']) : '';
$baseDir ="../../videos/publicacoes/";
$pasta720 = $baseDir . $pasta . "/720";



if (!$pasta) {
    echo json_encode(['sucesso' => false, 'msg' => 'Pasta não informada']);
    exit;
}
if (!preg_match('/^[\w\-]+$/', $pasta)) {
    echo json_encode(['sucesso' => false, 'msg' => 'Nome de pasta inválido']);
    exit;
}
if (!is_dir($baseDir . $pasta)) {
    echo json_encode(['sucesso' => false, 'msg' => 'Pasta base não existe']);
    exit;
}
if (is_dir($pasta720)) {
    echo json_encode(['sucesso' => true, 'msg' => 'Pasta 720 já existe']);
    exit;
}

if (mkdir($pasta720, 0777, true)) {
    echo json_encode(['sucesso' => true, 'msg' => 'Pasta 720 criada']);
} else {
    echo json_encode(['sucesso' => false, 'msg' => 'Falha ao criar a pasta']);
}
