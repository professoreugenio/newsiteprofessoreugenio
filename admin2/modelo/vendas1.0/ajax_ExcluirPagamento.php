<?php

/**
 * vendas1.0/ajax_ExcluirPagamento.php
 * Exclui um registro de venda/pagamento.
 * - Padrão: exclusão LÓGICA (statussv = 9)
 * - Opcional: exclusão FÍSICA (DELETE) quando POST[mode] = 'hard'
 *
 * Retorna JSON: { ok: true/false, msg: "...", statussv?: int }
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // Cabeçalho padrão (conforme seu projeto)
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3)); // vendas1.0/ -> sobe 2 níveis
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    // Somente POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'msg' => 'Método não permitido.']);
        exit;
    }

    // Validação do id
    $idvenda = isset($_POST['idvenda']) ? (int)$_POST['idvenda'] : 0;
    if ($idvenda <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'ID de venda inválido.']);
        exit;
    }

    // Modo de exclusão (soft/hard)
    $mode = isset($_POST['mode']) ? strtolower(trim((string)$_POST['mode'])) : 'soft';
    $isHardDelete = ($mode === 'hard');

    // Verificar existência
    $chk = $con->prepare("SELECT codigovendas, statussv FROM a_site_vendas WHERE codigovendas = :id LIMIT 1");
    $chk->bindValue(':id', $idvenda, PDO::PARAM_INT);
    $chk->execute();
    $row = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'msg' => 'Venda não encontrada.']);
        exit;
    }

    if ($isHardDelete) {
        // Exclusão física
        $del = $con->prepare("DELETE FROM a_site_vendas WHERE codigovendas = :id LIMIT 1");
        $del->bindValue(':id', $idvenda, PDO::PARAM_INT);
        $del->execute();

        if ($del->rowCount() < 1) {
            throw new Exception('Não foi possível excluir o registro. Verifique o ID.');
        }

        echo json_encode(['ok' => true, 'msg' => 'Registro excluído (DELETE) com sucesso.']);
        exit;
    }

    // Exclusão lógica (statussv = 9)
    if ((string)$row['statussv'] === '9') {
        echo json_encode(['ok' => true, 'msg' => 'Registro já estava marcado como excluído.', 'statussv' => 9]);
        exit;
    }

    $upd = $con->prepare("
        UPDATE a_site_vendas
           SET statussv = 9
         WHERE codigovendas = :id
         LIMIT 1
    ");
    $upd->bindValue(':id', $idvenda, PDO::PARAM_INT);
    $upd->execute();

    if ($upd->rowCount() < 1) {
        throw new Exception('Nenhuma linha atualizada. Verifique o ID.');
    }

    echo json_encode(['ok' => true, 'msg' => 'Registro marcado como excluído (status 9).', 'statussv' => 9]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
