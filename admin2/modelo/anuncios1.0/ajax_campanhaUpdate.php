<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id         = (int)($_POST['id'] ?? 0);
    $clienteId  = (int)($_POST['idclienteACAM'] ?? 0);
    $titulo     = trim($_POST['tituloACAM'] ?? '');
    $valorBr    = trim($_POST['valorACAM'] ?? '');
    $dtIni      = trim($_POST['datainicioACAM'] ?? '');
    $dtFim      = trim($_POST['datafimACAM'] ?? '');
    $idVend     = trim($_POST['idvendedorACAM'] ?? '');
    $visivel    = isset($_POST['visivelACAM']) ? 1 : 0;

    if ($id <= 0 || $clienteId <= 0 || $titulo === '' || $valorBr === '' || $dtIni === '' || $dtFim === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha os campos obrigatórios.']);
        exit;
    }

    $valorNr = str_replace('.', '', $valorBr);
    $valorNr = str_replace(',', '.', $valorNr);

    $regexDate = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($regexDate, $dtIni) || !preg_match($regexDate, $dtFim) || strtotime($dtIni) > strtotime($dtFim)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Período inválido.']);
        exit;
    }

    $sql = "UPDATE a_site_anuncios_campanhas SET
                idclienteACAM   = :cli,
                tituloACAM      = :tit,
                valorACAM       = :val,
                datainicioACAM  = :di,
                datafimACAM     = :df,
                idvendedorACAM  = :vend,
                visivelACAM     = :vis
            WHERE codigocampanhaanuncio = :id
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':cli',  $clienteId, PDO::PARAM_INT);
    $stmt->bindValue(':tit',  $titulo);
    $stmt->bindValue(':val',  $valorNr);
    $stmt->bindValue(':di',   $dtIni);
    $stmt->bindValue(':df',   $dtFim);
    $stmt->bindValue(':vend', $idVend !== '' ? $idVend : null, $idVend !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':vis',  $visivel, PDO::PARAM_INT);
    $stmt->bindValue(':id',   $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['status' => 'ok', 'mensagem' => 'Campanha atualizada com sucesso!']);
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
}
