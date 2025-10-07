<?php
// ============================================================================
// regixv1.0/ajax_finalizaAcesso.php
// Marca o encerramento de um acesso (hora final) para a chavera.
// ============================================================================

declare(strict_types=1);

// Cabeçalho padrão para AJAX
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('America/Fortaleza');

// Apenas POST
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método não permitido']);
    exit;
}

// Recupera CHAVERA do POST ou COOKIE
$chavera = trim($_POST['chavera'] ?? '') ?: trim($_COOKIE['registraacessosREGIX'] ?? '');
if ($chavera === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Chavera ausente']);
    exit;
}

$horaFinal = date('H:i:s');
$hoje      = date('Y-m-d');

try {
    // Atualiza apenas o registro da CHAVERA para o dia atual
    $sql = "UPDATE a_site_registraacessos
            SET horaFinalra = :horaFinal
            WHERE chavera = :chavera 
              AND datara = :dataAtual
            LIMIT 1";
    $st = $con->prepare($sql);
    $st->bindParam(':horaFinal', $horaFinal, PDO::PARAM_STR);
    $st->bindParam(':chavera', $chavera, PDO::PARAM_STR);
    $st->bindParam(':dataAtual', $hoje, PDO::PARAM_STR);
    $st->execute();

    if ($st->rowCount() >= 1) {
        echo json_encode(['ok' => true, 'msg' => 'Acesso finalizado', 'horaFinal' => $horaFinal]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Nenhum acesso encontrado para finalizar']);
    }
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Erro interno', 'err' => $e->getMessage()]);
    exit;
}
