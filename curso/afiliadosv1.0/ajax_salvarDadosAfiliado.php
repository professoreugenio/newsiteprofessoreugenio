<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2)); // ajuste se sua raiz for diferente
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Requisição inválida.');
    }

    $con = config::connect();

    // auth por cookie, igual na página (sem SESSION)
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    } else if (!empty($_COOKIE['startusuario'])) {
        $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    } else {
        throw new Exception('Usuário não autenticado.');
    }
    if (!$decUser || strpos($decUser, '&') === false) throw new Exception('Token inválido.');
    $expUser = explode("&", $decUser);
    $idUser  = (int)($expUser[0] ?? 0);
    if ($idUser <= 0) throw new Exception('Usuário inválido.');

    // inputs
    $idusuarioad = (int)($_POST['idusuarioad'] ?? 0);
    if ($idusuarioad !== $idUser) throw new Exception('Usuário divergente.');

    $cpfad  = preg_replace('/\D+/', '', (string)($_POST['cpfad'] ?? ''));
    $pixad  = trim((string)($_POST['pixad'] ?? ''));
    $bancoad = trim((string)($_POST['bancoad'] ?? ''));

    if ($cpfad === '' || $pixad === '') {
        throw new Exception('Preencha CPF e Chave PIX.');
    }

    // upsert
    $stmt = $con->prepare("
        SELECT codigodados FROM a_site_afiliados_dados WHERE idusuarioad = :id LIMIT 1
    ");
    $stmt->bindValue(':id', $idUser, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $upd = $con->prepare("
            UPDATE a_site_afiliados_dados
               SET cpfad = :cpfad, pixad = :pixad, bancoad = :bancoad
             WHERE idusuarioad = :id
            LIMIT 1
        ");
        $upd->bindValue(':cpfad', $cpfad);
        $upd->bindValue(':pixad', $pixad);
        $upd->bindValue(':bancoad', $bancoad);
        $upd->bindValue(':id', $idUser, PDO::PARAM_INT);
        $ok = $upd->execute();
    } else {
        $ins = $con->prepare("
            INSERT INTO a_site_afiliados_dados (idusuarioad, cpfad, pixad, bancoad, dataad, horaad)
            VALUES (:id, :cpfad, :pixad, :bancoad, :dataad, :horaad)
        ");
        $ins->bindValue(':id', $idUser, PDO::PARAM_INT);
        $ins->bindValue(':cpfad', $cpfad);
        $ins->bindValue(':pixad', $pixad);
        $ins->bindValue(':bancoad', $bancoad);
        $ins->bindValue(':dataad', date('Ymd')); // se sua coluna é INT(11) e guarda AAAAMMDD
        $ins->bindValue(':horaad', date('His')); // se guarda HHMMSS
        $ok = $ins->execute();
    }

    if (!$ok) throw new Exception('Não foi possível salvar os dados.');
    echo json_encode(['ok' => true]);
    exit;
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
    exit;
}
