<?php

/**
 * vendas1.0/ajax_RegistrarPagamento.php
 * Atualiza a_site_vendas.statussv = 1 (pagamento efetuado)
 * Retorna JSON { ok: true/false, msg: ... }
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Cabeçalho padrão dos seus AJAX
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3)); // /vendas1.0/ está 2 níveis abaixo da raiz do app
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    // Somente POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'msg' => 'Método não permitido.']);
        exit;
    }

    // Validação do idvenda
    $idvenda = isset($_POST['idvenda']) ? (int)$_POST['idvenda'] : 0;
    if ($idvenda <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'ID de venda inválido.']);
        exit;
    }

    // (Opcional) Verificar se já está confirmado
    $chk = $con->prepare("SELECT statussv FROM a_site_vendas WHERE codigovendas = :id LIMIT 1");
    $chk->bindValue(':id', $idvenda, PDO::PARAM_INT);
    $chk->execute();
    $row = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'Venda não encontrada.']);
        exit;
    }
    if ((string)$row['statussv'] === '1') {
        echo json_encode(['ok' => true, 'msg' => 'Pagamento já estava confirmado.']);
        exit;
    }

    // Atualiza pagamento para confirmado
    $upd = $con->prepare("
        UPDATE a_site_vendas 
           SET statussv = 1
         WHERE codigovendas = :id
         LIMIT 1
    ");
    $upd->bindValue(':id', $idvenda, PDO::PARAM_INT);
    $upd->execute();

    if ($upd->rowCount() < 1) {
        throw new Exception('Nenhuma linha atualizada. Verifique o ID.');
    }

    echo json_encode(['ok' => true, 'msg' => 'Pagamento confirmado com sucesso.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
