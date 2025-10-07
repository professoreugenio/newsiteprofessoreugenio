<?php

/**
 * bancoimagens1.0/ajax_bancoGaleriaUpdate.php
 * Atualiza título e descrição de uma galeria do Banco de Imagens.
 * Requisitos:
 *  - Método: POST
 *  - Campos: idgaleria (CRIPTO), titulo (obrigatório), descricao (opcional)
 *  - encrypt($var, 'd') para decrypt do id
 *  - Cabeçalho padrão AJAX
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

// === Captura e limpeza dos dados ===
$idEnc     = trim($_POST['idgaleria'] ?? '');
$titulo    = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');

// Validações básicas
if ($idEnc === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID da galeria não informado.']);
    exit;
}
if ($titulo === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Informe o título da galeria.']);
    exit;
}

// Limites de comprimento (ajuste conforme necessidade do schema)
if (function_exists('mb_substr')) {
    $titulo    = mb_substr($titulo, 0, 150, 'UTF-8');     // ex.: limite 150 chars
    $descricao = mb_substr($descricao, 0, 1000, 'UTF-8'); // ex.: limite 1000 chars
} else {
    $titulo    = substr($titulo, 0, 150);
    $descricao = substr($descricao, 0, 1000);
}

// === Decrypt do ID ===
try {
    $id = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao decodificar o ID.']);
    exit;
}

// Verificação de ID numérico
if (!is_numeric($id) || (int)$id <= 0) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
    exit;
}
$id = (int)$id;

// === Atualização no banco ===
try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    $sql = "UPDATE a_site_banco_imagens 
            SET tituloBI = :titulo, descricaoBI = :descricao
            WHERE codigobancoimagens = :id
            LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Mesmo que não haja alteração (rowCount = 0), consideramos sucesso.
    echo json_encode([
        'status'    => 'ok',
        'mensagem'  => 'Galeria atualizada com sucesso.',
        'id'        => $idEnc,      // devolve o id ainda criptografado (se for útil no front)
        'titulo'    => $titulo,
        'descricao' => $descricao
    ]);
    exit;
} catch (\PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
} catch (\Throwable $t) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro inesperado ao atualizar.']);
    exit;
}
