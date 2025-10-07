<?php

/**
 * bancoimagens1.0/ajax_bancoGaleriaDelete.php
 * Remove uma galeria do Banco de Imagens (e opcionalmente suas mídias por pasta).
 * Requisitos:
 *  - Método: POST
 *  - Campo: idgaleria (CRIPTO)
 *  - encrypt($var, 'd') para decrypt do id
 *  - Cabeçalho padrão AJAX
 *
 * Obs.: A tabela a_site_banco_imagensMidias não possui
 * explicitamente (nesta especificação) um FK para a galeria.
 * Caso sua relação seja por PASTA (pastaBI == pastaIM), o script
 * pode remover também as mídias dessa pasta (opcional, ver flag $DELETE_MIDIAS_POR_PASTA).
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

// === Verificação do método ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
    exit;
}

// === Captura do ID criptografado ===
$idEnc = trim($_POST['idgaleria'] ?? '');
if ($idEnc === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da galeria não informado.']);
    exit;
}

// === Decrypt do ID ===
try {
    $id = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao decodificar o ID.']);
    exit;
}
if (!is_numeric($id) || (int)$id <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
    exit;
}
$id = (int)$id;

// === Configuração da remoção de mídias por pasta (opcional) ===
$DELETE_MIDIAS_POR_PASTA = true; // defina para false se NÃO quiser remover mídias da mesma pasta

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    // Inicia transação
    $con->beginTransaction();

    // 1) Busca a galeria para obter a pasta (se for usar remoção por pasta)
    $sqlSel = "SELECT codigobancoimagens, pastaBI FROM a_site_banco_imagens WHERE codigobancoimagens = :id LIMIT 1";
    $stSel  = $con->prepare($sqlSel);
    $stSel->bindParam(':id', $id, PDO::PARAM_INT);
    $stSel->execute();

    if ($stSel->rowCount() === 0) {
        $con->rollBack();
        echo json_encode(['status' => 'erro', 'mensagem' => 'Galeria não encontrada.']);
        exit;
    }

    $gal = $stSel->fetch(PDO::FETCH_ASSOC);
    $pastaBI = trim((string)($gal['pastaBI'] ?? ''));

    // 2) (Opcional) Apaga mídias pela pasta (se houver critério por pasta)
    if ($DELETE_MIDIAS_POR_PASTA && $pastaBI !== '') {
        $sqlDelMidias = "DELETE FROM a_site_banco_imagensMidias WHERE pastaIM = :pasta";
        $stDelM = $con->prepare($sqlDelMidias);
        $stDelM->bindParam(':pasta', $pastaBI, PDO::PARAM_STR);
        $stDelM->execute();
        // Obs.: Se quiser apagar também os ARQUIVOS físicos,
        // faça aqui com unlink() após localizar os caminhos reais no seu servidor.
        // Este script remove apenas do banco para segurança.
    }

    // 3) Apaga a galeria
    $sqlDelGal = "DELETE FROM a_site_banco_imagens WHERE codigobancoimagens = :id LIMIT 1";
    $stDelG = $con->prepare($sqlDelGal);
    $stDelG->bindParam(':id', $id, PDO::PARAM_INT);
    $stDelG->execute();

    $con->commit();

    echo json_encode([
        'status'   => 'ok',
        'mensagem' => 'Galeria excluída com sucesso.'
    ]);
    exit;
} catch (\PDOException $e) {
    if ($con && $con->inTransaction()) {
        $con->rollBack();
    }
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
} catch (\Throwable $t) {
    if ($con && $con->inTransaction()) {
        $con->rollBack();
    }
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro inesperado ao excluir.']);
    exit;
}
