<?php

/**
 * bancoimagens1.0/ajax_bancoNovaGaleria.php
 * Cria uma nova galeria no Banco de Imagens.
 * Requisitos:
 *  - Método: POST
 *  - Campos: titulo (obrigatório), descricao (opcional), idadmin (obrigatório)
 *  - Gera pastaBI única (slug do título + timestamp + rand)
 *  - Insere em a_site_banco_imagens: tituloBI, descricaoBI, idadminBI, pastaBI, dataBI, horaBI
 *  - Retorna JSON com status e id criptografado
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

// === Captura e limpeza ===
$titulo    = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$idadmin   = trim($_POST['idadmin'] ?? '');

// Validações
if ($titulo === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Informe o título da galeria.']);
    exit;
}
if ($idadmin === '' || !ctype_digit($idadmin)) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Administrador inválido.']);
    exit;
}

// Limites de comprimento (ajuste conforme schema)
if (function_exists('mb_substr')) {
    $titulo    = mb_substr($titulo, 0, 150, 'UTF-8');
    $descricao = mb_substr($descricao, 0, 1000, 'UTF-8');
} else {
    $titulo    = substr($titulo, 0, 150);
    $descricao = substr($descricao, 0, 1000);
}

$idadmin = (int)$idadmin;

// Utilitários
if (!function_exists('slugify_safe')) {
    function slugify_safe($text)
    {
        $text = (string)$text;
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: 'galeria';
    }
}

// Gera pastaBI única
$slug      = slugify_safe($titulo);
$stamp     = date('Ymd_His');
$rand      = substr((string)mt_rand(1000, 9999), 0, 4);
$pastaBI   = 'bi_' . $slug . '_' . $stamp . '_' . $rand;

$dataBI = date('Y-m-d');
$horaBI = date('H:i:s');

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    $sql = "INSERT INTO a_site_banco_imagens 
              (tituloBI, descricaoBI, idadminBI, pastaBI, dataBI, horaBI)
            VALUES
              (:titulo, :descricao, :idadmin, :pasta, :data, :hora)";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
    $stmt->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt->bindParam(':idadmin', $idadmin, PDO::PARAM_INT);
    $stmt->bindParam(':pasta', $pastaBI, PDO::PARAM_STR);
    $stmt->bindParam(':data', $dataBI, PDO::PARAM_STR);
    $stmt->bindParam(':hora', $horaBI, PDO::PARAM_STR);
    $stmt->execute();

    $novoId = (int)$con->lastInsertId();
    // Criptografa o id para o front (padrão do projeto)
    $idEnc = encrypt($novoId, 'e');

    echo json_encode([
        'status'    => 'ok',
        'mensagem'  => 'Galeria criada com sucesso.',
        'id'        => $idEnc,
        'titulo'    => $titulo,
        'descricao' => $descricao,
        'pasta'     => $pastaBI,
        'data'      => $dataBI,
        'hora'      => $horaBI
    ]);
    exit;
} catch (\PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
} catch (\Throwable $t) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro inesperado ao inserir.']);
    exit;
}
