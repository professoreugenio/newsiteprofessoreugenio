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
    $pdo->beginTransaction();

    // Verifica se já tem
    $st = $pdo->prepare("SELECT codigochaveafiliados FROM a_site_afiliados_chave WHERE idusuarioSA = :u LIMIT 1");
    $st->bindParam(':u', $idUser, PDO::PARAM_INT);
    $st->execute();
    if ($st->fetch(PDO::FETCH_ASSOC)) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'msg' => 'Você já possui chave cadastrada.']);
        exit;
    }

    // Gera chave única
    $chave = uniqid('afl_', true); // ex.: afl_64ff6f8c8c2d0.12345678

    // Insere
    $hoje = date('Y-m-d');
    $hora = date('H:i:s');
    $ins = $pdo->prepare("
        INSERT INTO a_site_afiliados_chave (idusuarioSA, chaveafiliadoSA, dataSA, horaSA)
        VALUES (:u, :c, :d, :h)
    ");
    $ins->bindParam(':u', $idUser, PDO::PARAM_INT);
    $ins->bindParam(':c', $chave, PDO::PARAM_STR);
    $ins->bindParam(':d', $hoje, PDO::PARAM_STR);
    $ins->bindParam(':h', $hora, PDO::PARAM_STR);
    $ins->execute();

    $pdo->commit();
    echo json_encode(['ok' => true, 'chave' => $chave]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
