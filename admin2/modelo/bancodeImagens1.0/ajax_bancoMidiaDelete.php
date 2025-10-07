<?php

/**
 * bancodeImagens1.0/ajax_bancoMidiaDelete.php
 * Exclui uma mídia (imagem) do Banco de Imagens.
 *
 * Requisitos:
 *  - Método: POST
 *  - Campo: idmidia (CRIPTO)
 *  - Decrypt com encrypt($var, 'd')
 *  - Remove arquivo físico com validação de caminho
 *  - Exclui registro em a_site_banco_imagensMidias
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
    exit;
}

// Captura
$idEnc = trim($_POST['idmidia'] ?? '');
if ($idEnc === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da mídia não informado.']);
    exit;
}

// Decrypt e validação
try {
    $idMidia = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao decodificar o ID da mídia.']);
    exit;
}
if (!is_numeric($idMidia) || (int)$idMidia <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da mídia inválido.']);
    exit;
}
$idMidia = (int)$idMidia;

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    // Inicia transação
    $con->beginTransaction();

    // 1) Buscar a mídia
    $sqlSel = "SELECT codigoimagemmidia, imagemIM, pastaIM FROM a_site_banco_imagensMidias WHERE codigoimagemmidia = :id LIMIT 1";
    $stSel  = $con->prepare($sqlSel);
    $stSel->bindParam(':id', $idMidia, PDO::PARAM_INT);
    $stSel->execute();

    if ($stSel->rowCount() === 0) {
        $con->rollBack();
        echo json_encode(['status' => 'erro', 'mensagem' => 'Mídia não encontrada.']);
        exit;
    }

    $mid = $stSel->fetch(PDO::FETCH_ASSOC);
    $arquivo = trim((string)($mid['imagemIM'] ?? ''));
    $pasta   = trim((string)($mid['pastaIM'] ?? ''));

    // 2) Apagar arquivo físico com segurança
    // Diretório base público dos arquivos (ajuste se seu caminho for diferente)
    $baseDir = APP_ROOT . '/fotos/bancoimagens';
    $galDir  = $baseDir . '/' . $pasta;

    // Garante que não há path traversal
    $baseReal = realpath($baseDir);
    $galReal  = $galDir && is_dir($galDir) ? realpath($galDir) : false;

    if ($arquivo !== '' && $pasta !== '' && $baseReal && $galReal && str_starts_with($galReal, $baseReal)) {
        $filePath = $galReal . '/' . $arquivo;

        // Confere novamente o realpath antes de apagar
        if (is_file($filePath)) {
            $fileReal = realpath($filePath);
            if ($fileReal && str_starts_with($fileReal, $galReal)) {
                @unlink($fileReal); // silencioso; se falhar, ainda assim remove do banco
            }
        }
    }

    // 3) Excluir registro no banco
    $sqlDel = "DELETE FROM a_site_banco_imagensMidias WHERE codigoimagemmidia = :id LIMIT 1";
    $stDel  = $con->prepare($sqlDel);
    $stDel->bindParam(':id', $idMidia, PDO::PARAM_INT);
    $stDel->execute();

    $con->commit();

    echo json_encode([
        'status'   => 'ok',
        'mensagem' => 'Imagem excluída com sucesso.'
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
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro inesperado ao excluir a mídia.']);
    exit;
}
