<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    // Autenticação via cookie
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    } else if (!empty($_COOKIE['startusuario'])) {
        $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    } else {
        throw new Exception('Usuário não autenticado.');
    }

    if (!$decUser || strpos($decUser, '&') === false) {
        throw new Exception('Token inválido.');
    }

    $expUser = explode("&", $decUser);
    $idUser  = (int)($expUser[0] ?? 0);
    if ($idUser <= 0) throw new Exception('ID inválido.');

    $pdo = config::connect();
    $sql = "SELECT chaveafiliadoSA FROM a_site_afiliados_chave WHERE idusuarioSA = :u LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindParam(':u', $idUser, PDO::PARAM_INT);
    $st->execute();
    $existe = $st->fetch(PDO::FETCH_ASSOC);

    if ($existe && !empty($existe['chaveafiliadoSA'])) {
        echo json_encode(['ok' => true, 'possui' => true]);
    } else {
        echo json_encode(['ok' => true, 'possui' => false]);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
