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

// Remove <script> para evitar XSS, mas mantém HTML do editor.
// (Caso queira algo mais rígido, podemos whitelistar tags/atributos.)
function stripScripts($html)
{
    return preg_replace('#<\s*script[^>]*>.*?<\s*/\s*script\s*>#is', '', $html);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, ['mensagem' => 'Método inválido.']);
    }

    $iduser   = isset($_POST['iduser'])   ? (int) $_POST['iduser']   : 0;
    $idartigo = isset($_POST['idartigo']) ? (int) $_POST['idartigo'] : 0;
    $anotacao = isset($_POST['anotacao']) ? (string) $_POST['anotacao'] : '';

    if ($iduser <= 0 || $idartigo <= 0) {
        jsonResponse(false, ['mensagem' => 'Parâmetros obrigatórios ausentes.']);
    }

    // Sanitiza minimamente o HTML (remove scripts)
    $html = $anotacao;

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $con = config::connect();
    $con->beginTransaction();

    // Verifica se já existe anotação desse usuário para essa publicação
    $check = $con->prepare("SELECT * FROM new_sistema_anotacoes WHERE idpublicsa = :idartigo AND idusuariosa = :iduser LIMIT 1");
    $check->bindValue(':idartigo', $idartigo, PDO::PARAM_INT);
    $check->bindValue(':iduser',   $iduser,   PDO::PARAM_INT);
    $check->execute();
    $existente = $check->fetch(PDO::FETCH_ASSOC);

    if ($existente) {
        // UPDATE
        $upd = $con->prepare("UPDATE new_sistema_anotacoes 
                              SET textosa = :html, datasa = :datasa, horasa = :horasa
                              WHERE codigoanotacoes = :id");
        $upd->bindValue(':html',  $html, PDO::PARAM_STR);
        $upd->bindValue(':datasa', $data, PDO::PARAM_STR);
        $upd->bindValue(':horasa', $hora, PDO::PARAM_STR);
        $upd->bindValue(':id',    (int)$existente['codigoanotacoes'], PDO::PARAM_INT);
        $upd->execute();

        $con->commit();
        jsonResponse(true, [
            'acao' => 'update',
            'id'   => $idartigo,
            'iduser'   => $iduser,
            'datasa' => $data,
            'horasa' => $hora
        ]);
    } else {
        // INSERT
        $ins = $con->prepare("INSERT INTO new_sistema_anotacoes (idpublicsa, idusuariosa, textosa, datasa, horasa)
                              VALUES (:idartigo, :iduser, :html, :datasa, :horasa)");
        $ins->bindValue(':idartigo', $idartigo, PDO::PARAM_INT);
        $ins->bindValue(':iduser',   $iduser,   PDO::PARAM_INT);
        $ins->bindValue(':html',     $html,     PDO::PARAM_STR);
        $ins->bindValue(':datasa',    $data,     PDO::PARAM_STR);
        $ins->bindValue(':horasa',   $hora,     PDO::PARAM_STR);
        $ins->execute();

        $novoId = (int)$con->lastInsertId();
        $con->commit();

        jsonResponse(true, [
            'acao' => 'insert',
            'id'   => $novoId,
            'datasa' => $data,
            'horasa' => $hora
        ]);
    }
} catch (Throwable $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }
    jsonResponse(false, ['mensagem' => 'Erro ao salvar anotação.', 'erro' => $e->getMessage()]);
}
