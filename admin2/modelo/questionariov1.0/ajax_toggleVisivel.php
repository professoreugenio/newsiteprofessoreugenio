<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método não permitido.');
    }

    $codigo = isset($_POST['codigo']) ? (int)$_POST['codigo'] : 0;
    $idpub  = isset($_POST['idpublicacao']) ? (int)$_POST['idpublicacao'] : 0;
    if ($codigo <= 0 || $idpub <= 0) {
        throw new InvalidArgumentException('Parâmetros inválidos.');
    }

    $pdo = config::connect();

    $sql = "UPDATE a_curso_questionario
            SET visivelcq = CASE WHEN visivelcq = 1 THEN 0 ELSE 1 END
            WHERE codigoquestionario = :cod AND idpublicacaocq = :idpub";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':cod', $codigo, PDO::PARAM_INT);
    $st->bindValue(':idpub', $idpub, PDO::PARAM_INT);
    $st->execute();

    if ($st->rowCount() < 1) {
        throw new RuntimeException('Registro não encontrado ou já atualizado.');
    }

    $st2 = $pdo->prepare("SELECT visivelcq FROM a_curso_questionario WHERE codigoquestionario = :cod");
    $st2->bindValue(':cod', $codigo, PDO::PARAM_INT);
    $st2->execute();
    $vis = (int)$st2->fetchColumn();

    echo json_encode(['success' => true, 'visivel' => $vis], JSON_UNESCAPED_UNICODE);
} catch (Throwable $th) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $th->getMessage()], JSON_UNESCAPED_UNICODE);
}
