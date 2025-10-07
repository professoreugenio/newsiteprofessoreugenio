<?php

/**
 * usuariosv1.0/ajax_update_senha_aluno.php
 * Atualiza senha do aluno (tabela new_sistema_cadastro)
 * Retorno: JSON
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
    // ===== Cabeçalho padrão =====
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    // ===== Entrada =====
    $idUsuario   = isset($_POST['idUsuario']) ? (int)$_POST['idUsuario'] : 0;
    $novaSenha   = trim($_POST['novaSenha'] ?? '');
    $confirma    = trim($_POST['confirmaSenha'] ?? '');

    if ($idUsuario <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID de usuário inválido.']);
        exit;
    }
    if ($novaSenha === '' || $confirma === '') {
        echo json_encode(['ok' => false, 'msg' => 'Preencha os dois campos de senha.']);
        exit;
    }
    if ($novaSenha !== $confirma) {
        echo json_encode(['ok' => false, 'msg' => 'As senhas não conferem.']);
        exit;
    }

    // ===== Busca e-mail do aluno =====
    $con = config::connect();
    $q = $con->prepare("SELECT email FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
    $q->execute([':id' => $idUsuario]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['email'])) {
        echo json_encode(['ok' => false, 'msg' => 'Usuário não encontrado ou sem e-mail cadastrado.']);
        exit;
    }

    $email = trim($row['email']);

    // ===== Gera senhaCripto e chave =====
    $action     = 'e'; // encrypt
    $senhaCripto = encrypt($email . "&" . $novaSenha, $action);
    $chave      = strtoupper(md5($email . "&" . $novaSenha));

    // ===== Atualiza no banco =====
    $u = $con->prepare("UPDATE new_sistema_cadastro 
                           SET senha = :s, chave = :c 
                         WHERE codigocadastro = :id 
                         LIMIT 1");
    $u->execute([
        ':s'  => $senhaCripto,
        ':c'  => $chave,
        ':id' => $idUsuario
    ]);

    $rows = $u->rowCount();

    echo json_encode([
        'ok'  => true,
        'msg' => ($rows > 0 ? 'Senha atualizada com sucesso.' : 'Nenhuma alteração realizada.'),
        'id'  => $idUsuario,
        'chave' => $chave
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => 'Erro interno ao atualizar senha.',
        'err' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
