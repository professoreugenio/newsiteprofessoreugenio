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

    // Parâmetros
    $iduser     = isset($_POST['iduser'])      ? (int) $_POST['iduser'] : 0;
    $idartigo   = isset($_POST['idartigo'])    ? (int) $_POST['idartigo'] : 0;
    $texto      = isset($_POST['depoimento'])  ? trim((string) $_POST['depoimento']) : '';
    $idcodforum = isset($_POST['idcodforum'])  ? (int) $_POST['idcodforum'] : 0; // opcional (thread/pai)
    $permissao  = isset($_POST['permissao'])   ? (int) $_POST['permissao'] : -1; // 0 = não autoriza, 1 = autoriza

    // Validações
    if ($iduser <= 0 || $idartigo <= 0) {
        jsonResponse(false, ['mensagem' => 'Parâmetros obrigatórios ausentes.']);
    }
    if ($texto === '') {
        jsonResponse(false, ['mensagem' => 'O depoimento não pode estar vazio.']);
    }
    if (!in_array($permissao, [0, 1], true)) {
        jsonResponse(false, ['mensagem' => 'Confirme a permissão de exibição do depoimento.']);
    }

    // Limita a 300 caracteres (mesmo limite do modal)
    if (mb_strlen($texto) > 300) {
        $texto = mb_substr($texto, 0, 300);
    }

    // Sanitização simples (texto puro). Se quiser permitir HTML, ajustar aqui.
    $texto = strip_tags($texto);

    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $con = config::connect();

    // IMPORTANTE: a tabela precisa ter a coluna permissaoCF (TINYINT 0/1)
    $sql = "INSERT INTO a_curso_forum
            (idusuarioCF, idartigoCF, idcodforumCF, textoCF, visivelCF, permissaoCF, dataCF, horaCF)
            VALUES
            (:iduser, :idartigo, :idcodforum, :texto, :visivel, :permissao, :data, :hora)";
    $stm = $con->prepare($sql);
    $stm->bindValue(':iduser',     $iduser,     PDO::PARAM_INT);
    $stm->bindValue(':idartigo',   $idartigo,   PDO::PARAM_INT);
    $stm->bindValue(':idcodforum', $idcodforum, PDO::PARAM_INT);
    $stm->bindValue(':texto',      $texto,      PDO::PARAM_STR);
    $stm->bindValue(':visivel',    1,           PDO::PARAM_INT); // 1 = visível na plataforma
    $stm->bindValue(':permissao',  $permissao,  PDO::PARAM_INT); // 0/1 conforme escolha
    $stm->bindValue(':data',       $data,       PDO::PARAM_STR);
    $stm->bindValue(':hora',       $hora,       PDO::PARAM_STR);
    $stm->execute();

    $novoId = (int) $con->lastInsertId();

    jsonResponse(true, [
        'id'        => $novoId,
        'iduser'    => $iduser,
        'idartigo'  => $idartigo,
        'permissao' => $permissao,
        'datacf'    => $data,
        'horacf'    => $hora
    ]);
} catch (Throwable $e) {
    jsonResponse(false, ['mensagem' => 'Erro ao salvar depoimento.', 'erro' => $e->getMessage()]);
}
