<?php

/**
 * config_Portfolio1.0/ajax_portfolioCheckCreate.php
 * Checa/cria portfólio do aluno (autenticação por COOKIE).
 */
header('Content-Type: application/json; charset=utf-8');

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php'; // deve conter a função encrypt()

$out = ['ok' => false, 'hasPortfolio' => false];

try {
    // ========= Resgata ID do usuário via COOKIE =========
    $decUser = '';
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    } else if (!empty($_COOKIE['startusuario'])) {
        $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    } else {
        throw new Exception('Usuário não autenticado (cookies ausentes).');
    }

    if (!$decUser || strpos($decUser, '&') === false) {
        throw new Exception('Token de usuário inválido.');
    }

    $expUser = explode("&", $decUser);
    $idUser  = (int) ($expUser[0] ?? 0);
    if ($idUser <= 0) {
        throw new Exception('ID de usuário inválido.');
    }

    $action  = isset($_POST['action']) ? trim($_POST['action']) : 'check';
    $con     = config::connect();

    // ========= Verifica se já existe portfólio =========
    $q = $con->prepare("SELECT chaveap FROM a_aluno_portfolio WHERE idalunoap = :idaluno LIMIT 1");
    $q->bindParam(':idaluno', $idUser, PDO::PARAM_INT);
    $q->execute();
    $rw = $q->fetch(PDO::FETCH_ASSOC);

    if ($action === 'check') {
        $out['ok'] = true;
        $out['hasPortfolio'] = (bool)$rw;
        if ($rw) $out['chave'] = $rw['chaveap'];
        echo json_encode($out);
        exit;
    }

    if ($action === 'create') {
        if ($rw) {
            // Já existe — retorna existente
            $out['ok'] = true;
            $out['hasPortfolio'] = true;
            $out['chave'] = $rw['chaveap'];
            echo json_encode($out);
            exit;
        }

        // ========= Criar novo registro com chave uniqid =========
        $chave = uniqid('PF', true);
        $hoje  = date('Y-m-d');
        $hora  = date('H:i:s');

        $ins = $con->prepare("
            INSERT INTO a_aluno_portfolio (chaveap, idalunoap, dataap, horaap)
            VALUES (:chave, :idaluno, :data, :hora)
        ");
        $ins->bindParam(':chave', $chave);
        $ins->bindParam(':idaluno', $idUser, PDO::PARAM_INT);
        $ins->bindParam(':data', $hoje);
        $ins->bindParam(':hora', $hora);
        $ins->execute();

        $out['ok'] = true;
        $out['hasPortfolio'] = true;
        $out['chave'] = $chave;
        echo json_encode($out);
        exit;
    }

    throw new Exception('Ação inválida.');
} catch (Exception $e) {
    $out['msg'] = $e->getMessage();
    echo json_encode($out);
    exit;
}
