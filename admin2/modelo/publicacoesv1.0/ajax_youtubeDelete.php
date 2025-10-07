<?php

/** DELETE de vídeo */
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $id  = (int)($_POST['id'] ?? 0);
    $pub = (int)($_POST['codpublicacao_sy'] ?? 0);

    if ($id <= 0 || $pub <= 0) throw new Exception('Parâmetros inválidos.');

    $con = config::connect();
    $q = $con->prepare("DELETE FROM new_sistema_youtube_PJA WHERE codigoyoutube=:id AND codpublicacao_sy=:p");
    $ok = $q->execute([':id' => $id, ':p' => $pub]);

    echo json_encode(['ok' => $ok]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
