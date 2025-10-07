<?php

/**
 * Registra visualizações de vídeos (clique para assistir no YouTube)
 * Tabela: a_site_view_conteudo
 * Campos: idusuariovc, chaveturmavc, idpublicacaovc, chaveyoutubevc, datavc, horavc
 */

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido.']);
        exit;
    }

    // Identifica usuário logado (via cookie + encrypt())
    if (!empty($_COOKIE['adminstart'])) {
        $dec = encrypt($_COOKIE['adminstart'], 'd');
    } elseif (!empty($_COOKIE['startusuario'])) {
        $dec = encrypt($_COOKIE['startusuario'], 'd');
    } else {
        // Não logado: ainda assim podemos não barrar o fluxo; apenas sinalizamos
        echo json_encode(['status' => 'sem_login', 'mensagem' => 'Usuário não autenticado.']);
        exit;
    }

    $exp = explode("&", $dec);
    $idUsuario = (int)($exp[0] ?? 0);
    $chaveturmaUser = trim($exp[5] ?? ''); // ajuste se sua chave da turma vier em outro índice

    // Entradas
    $idpublicacao = (int)($_POST['idpublicacao'] ?? 0);
    $chaveturma   = trim($_POST['chaveturma'] ?? $chaveturmaUser);
    $chaveyoutube = trim($_POST['chaveyoutube'] ?? '');

    // Validações básicas
    if ($idUsuario <= 0 || $idpublicacao <= 0 || $chaveyoutube === '') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Dados insuficientes.']);
        exit;
    }

    // Valida ID do YouTube (11 chars alfanum/“-”/“_”)
    if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $chaveyoutube)) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Chave do YouTube inválida.']);
        exit;
    }

    // Conexão
    $con = config::connect();

    // Insert simples (usa NOW() separado em data/hora)
    $sql = "
        INSERT INTO a_site_view_conteudo
            (idusuariovc, chaveturmavc, idpublicacaovc, chaveyoutubevc, datavc, horavc)
        VALUES
            (:idusuario, :chaveturma, :idpublicacao, :chaveyoutube, CURDATE(), CURTIME())
    ";
    $st = $con->prepare($sql);
    $st->bindValue(':idusuario',   $idUsuario, PDO::PARAM_INT);
    $st->bindValue(':chaveturma',  $chaveturma);
    $st->bindValue(':idpublicacao', $idpublicacao, PDO::PARAM_INT);
    $st->bindValue(':chaveyoutube', $chaveyoutube);

    $ok = $st->execute();

    if ($ok) {
        echo json_encode(['status' => 'ok', 'mensagem' => 'View registrada.']);
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao registrar.']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Exceção: ' . $e->getMessage()]);
}
