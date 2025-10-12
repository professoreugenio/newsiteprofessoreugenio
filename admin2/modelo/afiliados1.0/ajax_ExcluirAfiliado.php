<?php

/**
 * afiliados1.0/ajax_ExcluirAfiliado.php
 * Exclui (DELETE) um registro de a_site_afiliados_chave pela PK codigochaveafiliados.
 * Retorna JSON { ok: true/false, msg: "..." }
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Bootstrap do projeto
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3)); // sobe 2 níveis a partir de afiliados1.0/
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'msg' => 'Método não permitido.']);
        exit;
    }

    $idaf = isset($_POST['idaf']) ? (int)$_POST['idaf'] : 0;
    if ($idaf <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'ID inválido.']);
        exit;
    }

    // Verifica existência
    $chk = $con->prepare("SELECT codigochaveafiliados FROM a_site_afiliados_chave WHERE codigochaveafiliados = :id LIMIT 1");
    $chk->bindValue(':id', $idaf, PDO::PARAM_INT);
    $chk->execute();
    if (!$chk->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'Registro não encontrado.']);
        exit;
    }

    // Exclusão física
    $del = $con->prepare("DELETE FROM a_site_afiliados_chave WHERE codigochaveafiliados = :id LIMIT 1");
    $del->bindValue(':id', $idaf, PDO::PARAM_INT);
    $del->execute();

    if ($del->rowCount() < 1) {
        throw new Exception('Nenhum registro excluído. Verifique o ID.');
    }

    echo json_encode(['ok' => true, 'msg' => 'Registro excluído com sucesso.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
