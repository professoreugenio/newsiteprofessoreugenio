<?php
define('BASEPATH', true);

include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('America/Fortaleza');

function jsonResponse($ok, $extra = [])
{
    echo json_encode(array_merge(['sucesso' => $ok], $extra));
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, ['mensagem' => 'Método inválido.']);
    }

    $iduser   = isset($_POST['iduser'])   ? (int) $_POST['iduser']   : 0;
    $idartigo = isset($_POST['idartigo']) ? (int) $_POST['idartigo'] : 0;

    if ($iduser <= 0 || $idartigo <= 0) {
        jsonResponse(false, ['mensagem' => 'Parâmetros obrigatórios ausentes.']);
    }

    $con = config::connect();

    $sql = "SELECT codigoanotacoes, idpublicsa, idusuariosa, textosa, datasa, horasa
            FROM new_sistema_anotacoes
            WHERE idpublicsa = :idartigo AND idusuariosa = :iduser
            ORDER BY codigoanotacoes DESC
            LIMIT 1";
    $stm = $con->prepare($sql);
    $stm->bindValue(':idartigo', $idartigo, PDO::PARAM_INT);
    $stm->bindValue(':iduser',   $iduser,   PDO::PARAM_INT);
    $stm->execute();

    $row = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        // Sem anotação ainda
        jsonResponse(true, [
            'existe' => false,
            'html'   => '',
            'datasa'  => null,
            'horasa' => null
        ]);
    }

    jsonResponse(true, [
        'existe' => true,
        'html'   => $row['textosa'], // conteúdo HTML do Summernote
        'datasa'  => $row['datasa'],
        'horasa' => $row['horasa'],
        'id'     => (int)$row['codigoanotacoes']
    ]);
} catch (Throwable $e) {
    jsonResponse(false, ['mensagem' => 'Erro ao carregar anotação.', 'erro' => $e->getMessage()]);
}
