<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$codpublicacao = intval($_POST['codpublicacao'] ?? 0);
$idmodulo = intval($_POST['idmodulo'] ?? 0);
$urlpa = trim($_POST['urlpa'] ?? '');
$titulo = trim($_POST['titulopa'] ?? '');
$pasta = trim($_POST['pasta'] ?? '');
$visivel = 1;
$data = date('Y-m-d');
$hora = date('H:i:s');

if ($codpublicacao <= 0 || $idmodulo <= 0 || !$urlpa || !$titulo) {
    echo json_encode(['sucesso' => false, 'msg' => 'Dados insuficientes']);
    exit;
}

$stmt = $con->prepare("INSERT INTO new_sistema_publicacoes_anexos_PJA
  (codpublicacao, idmodulo_pa, titulopa, pastapa, urlpa, visivel, tipo, datapa, horapa)
  VALUES (:pub, :md, :tit, :pasta, :url, :vis, 'url', :dt, :hr)");
$ok = $stmt->execute([
    ':pub' => $codpublicacao,
    ':md' => $idmodulo,
    ':tit' => $titulo,
    ':pasta' => $pasta,
    ':url' => $urlpa,
    ':vis' => $visivel,
    ':dt' => $data,
    ':hr' => $hora
]);

echo json_encode(['sucesso' => $ok]);
