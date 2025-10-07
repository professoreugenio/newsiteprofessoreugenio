<?php

/**
 * acessosv1.0/ajax_RegistraUsuario.php
 * Registra 1 acesso/dia do usuário logado em a_site_registraacessoUsuario.
 * Se já existir um registro hoje para (idusuariorau + chaveacessorau),
 * atualiza o idturmarau (e a hora) ao invés de inserir.
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

date_default_timezone_set('America/Fortaleza'); // alinhado ao seu projeto
header('Content-Type: application/json; charset=utf-8');

// ---------- Helpers ----------
function ensureCookieChavera(): string
{
    $cookieName = 'chavera';
    $expires = time() + (60 * 60 * 24 * 90); // 90 dias
    $path = '/';
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $httponly = false;
    $samesite = 'Lax';

    if (empty($_COOKIE[$cookieName])) {
        $novaChave = 'RA' . uniqid('', true);
        setcookie($cookieName, $novaChave, [
            'expires'  => $expires,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
        $_COOKIE[$cookieName] = $novaChave;
        return $novaChave;
    }
    return (string)$_COOKIE[$cookieName];
}

try {
    // Verifica cookie de sessão do usuário
    if (empty($_COOKIE['startusuario']) && empty($_COOKIE['adminstart'])) {
        echo json_encode(['ok' => false, 'error' => 'UsuarioNaoLogado']);
        exit;
    }

    // Decodifica token do usuário (admin tem prioridade)
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], 'd');
    } else {
        $decUser = encrypt($_COOKIE['startusuario'], 'd');
    }

    $expUser = explode("&", (string)$decUser);

    // Garante índices mínimos
    if (count($expUser) < 5) {
        echo json_encode(['ok' => false, 'error' => 'DadosUsuarioIncompletos']);
        exit;
    }

    // IDs segundo seu padrão
    $idUser  = (int)($expUser[0] ?? 0);
    $idTurma = (int)($expUser[4] ?? 0);

    if ($idUser <= 0) {
        echo json_encode(['ok' => false, 'error' => 'IdUsuarioInvalido']);
        exit;
    }

    // Garante chavera (mesmo visitante em diferentes sessões)
    $chavera  = ensureCookieChavera();
    $dataHoje = date('Y-m-d');
    $horaAgora = date('H:i:s');

    $con = config::connect();

    // 1) Verifica se já existe registro HOJE para (idusuariorau + chaveacessorau)
    $sqlHoje = "SELECT 1
                  FROM a_site_registraacessoUsuario
                 WHERE idusuariorau = :idu
                   AND chaveacessorau = :ch
                   AND datarau = :data
                 LIMIT 1";
    $chk = $con->prepare($sqlHoje);
    $chk->bindValue(':idu',  $idUser,  PDO::PARAM_INT);
    $chk->bindValue(':ch',   $chavera, PDO::PARAM_STR);
    $chk->bindValue(':data', $dataHoje, PDO::PARAM_STR);
    $chk->execute();

    if ($chk->fetchColumn()) {
        // 2) Se já existe registro hoje, ATUALIZA o idturmarau (e a hora)
        $sqlUpd = "UPDATE a_site_registraacessoUsuario
                      SET idturmarau = :idt,
                          horarau = :hora
                    WHERE idusuariorau = :idu
                      AND chaveacessorau = :ch
                      AND datarau = :data";
        $upd = $con->prepare($sqlUpd);
        $upd->bindValue(':idt',  $idTurma,   PDO::PARAM_INT);
        $upd->bindValue(':hora', $horaAgora, PDO::PARAM_STR);
        $upd->bindValue(':idu',  $idUser,    PDO::PARAM_INT);
        $upd->bindValue(':ch',   $chavera,   PDO::PARAM_STR);
        $upd->bindValue(':data', $dataHoje,  PDO::PARAM_STR);
        $upd->execute();

        echo json_encode([
            'ok'       => true,
            'status'   => 'updated_today',
            'chavera'  => $chavera,
            'idUser'   => $idUser,
            'idTurma'  => $idTurma,
            'today'    => $dataHoje
        ]);
        exit;
    }

    // 3) Não há registro hoje -> Inserir um novo
    $sqlIns = "INSERT INTO a_site_registraacessoUsuario
               (chaveacessorau, idusuariorau, idturmarau, datarau, horarau)
               VALUES (:ch, :idu, :idt, :data, :hora)";
    $ins = $con->prepare($sqlIns);
    $ins->bindValue(':ch',   $chavera,  PDO::PARAM_STR);
    $ins->bindValue(':idu',  $idUser,   PDO::PARAM_INT);
    $ins->bindValue(':idt',  $idTurma,  PDO::PARAM_INT);
    $ins->bindValue(':data', $dataHoje, PDO::PARAM_STR);
    $ins->bindValue(':hora', $horaAgora, PDO::PARAM_STR);
    $ins->execute();

    echo json_encode([
        'ok'       => true,
        'status'   => 'inserted',
        'chavera'  => $chavera,
        'idUser'   => $idUser,
        'idTurma'  => $idTurma,
        'today'    => $dataHoje
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'FalhaAoRegistrarAcessoUsuario',
        'msg' => $e->getMessage()
    ]);
}
