<?php

/**
 * acessosv1.0/ajax_RegistraUsuario.php
 * Registra 1 acesso/dia do usuário logado em a_site_registraacessoUsuario.
 * Correção: evita "Duplicate entry" em chaveacessorau usando UPSERT.
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

// ---------- Helpers ----------
function ensureCookieChavera(): string
{
    $cookieName = 'chavera';
    $expires  = time() + (60 * 60 * 24 * 90); // 90 dias
    $opts = [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => false,
        'samesite' => 'Lax'
    ];
    if (empty($_COOKIE[$cookieName])) {
        $novaChave = 'RA' . uniqid('', true);
        setcookie($cookieName, $novaChave, $opts);
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

    // Decodifica token (admin tem prioridade)
    $decUser = !empty($_COOKIE['adminstart'])
        ? encrypt($_COOKIE['adminstart'], 'd')
        : encrypt($_COOKIE['startusuario'], 'd');

    $expUser = explode("&", (string)$decUser);

    if (count($expUser) < 5) {
        echo json_encode(['ok' => false, 'error' => 'DadosUsuarioIncompletos']);
        exit;
    }

    $idUser  = (int)($expUser[0] ?? 0);
    $idTurma = (int)($expUser[4] ?? 0);

    if ($idUser <= 0) {
        echo json_encode(['ok' => false, 'error' => 'IdUsuarioInvalido']);
        exit;
    }

    $chavera   = ensureCookieChavera();
    $dataHoje  = date('Y-m-d');
    $horaAgora = date('H:i:s');

    /** @var PDO $con */
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se já existe QUALQUER registro para a chave (independente de data)
    $sqlChkAny = "SELECT datarau
                    FROM a_site_registraacessoUsuario
                   WHERE chaveacessorau = :ch
                   LIMIT 1";
    $stmAny = $con->prepare($sqlChkAny);
    $stmAny->bindValue(':ch', $chavera, PDO::PARAM_STR);
    $stmAny->execute();
    $rowAny = $stmAny->fetch(PDO::FETCH_ASSOC);

    $statusPreUpsert = 'nao_existia';
    if ($rowAny) {
        $statusPreUpsert = ($rowAny['datarau'] === $dataHoje) ? 'ja_existia_hoje' : 'existia_de_dia_anterior';
    }

    // UPSERT: insere ou atualiza caso a chave já exista (evita Duplicate entry)
    $sqlUpsert = "
        INSERT INTO a_site_registraacessoUsuario
            (chaveacessorau, idusuariorau, idturmarau, datarau, horarau)
        VALUES
            (:ch, :idu, :idt, :data, :hora)
        ON DUPLICATE KEY UPDATE
            idusuariorau = VALUES(idusuariorau),
            idturmarau   = VALUES(idturmarau),
            datarau      = VALUES(datarau),
            horarau      = VALUES(horarau)
    ";

    $ins = $con->prepare($sqlUpsert);
    $ins->bindValue(':ch',   $chavera,   PDO::PARAM_STR);
    $ins->bindValue(':idu',  $idUser,    PDO::PARAM_INT);
    $ins->bindValue(':idt',  $idTurma,   PDO::PARAM_INT);
    $ins->bindValue(':data', $dataHoje,  PDO::PARAM_STR);
    $ins->bindValue(':hora', $horaAgora, PDO::PARAM_STR);
    $ins->execute();

    // Determina status pós-upsert para resposta mais clara
    $status = match ($statusPreUpsert) {
        'nao_existia'             => 'inserted',
        'ja_existia_hoje'         => 'updated_today',
        'existia_de_dia_anterior' => 'updated_from_past',
        default                   => 'upserted'
    };

    echo json_encode([
        'ok'       => true,
        'status'   => $status,
        'chavera'  => $chavera,
        'idUser'   => $idUser,
        'idTurma'  => $idTurma,
        'today'    => $dataHoje
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'   => false,
        'error' => 'FalhaAoRegistrarAcessoUsuario',
        'msg'  => $e->getMessage()
    ]);
}
