<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';
header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro desconhecido.'];

if (!empty($_COOKIE['adminstart'])) {
    $desUser = $_COOKIE['adminstart'];
} elseif (!empty($_COOKIE['startusuario'])) {
    $desUser = $_COOKIE['startusuario'];
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Sessão inválida.']);
    exit;
}

$dectoken = encrypt($desUser, 'd');
$expta = explode('&', $dectoken);
$id = $expta[0] ?? null;

if (!$id) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID do usuário não identificado.']);
    exit;
}

// Campos recebidos
$nome       = $_POST['nome'] ?? '';
$datanasc   = $_POST['datanasc'] ?? '';
$email      = trim(strip_tags($_POST['email'] ?? ''));
$celular    = $_POST['celular'] ?? '';
$senhaatual = trim(strip_tags($_POST['senhaatual'] ?? ''));
$senhanova  = trim(strip_tags($_POST['senhanova'] ?? ''));

// Horário da edição
$data = date('Y-m-d');
$hora = date('H:i:s');

// Validação de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'E-mail inválido.']);
    exit;
}

try {
    // Se for alterar a senha
    if (!empty($senhaatual) && !empty($senhanova)) {
        $senhaatual_encrypted = encrypt($email . "&" . $senhaatual, 'e');

        $queryUser = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE senha = :senha AND codigocadastro = :id");
        $queryUser->bindParam(":senha", $senhaatual_encrypted);
        $queryUser->bindParam(":id", $id);
        $queryUser->execute();

        if (!$queryUser->rowCount()) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Senha atual incorreta.']);
            exit;
        }

        $senhaenc = encrypt($email . "&" . $senhanova, 'e');
        $chaveenc = strtoupper(md5($email . "&" . $senhanova));

        $queryUpdate = $con->prepare("UPDATE new_sistema_cadastro SET senha=:senha, chave=:chave, nome=:nome, datanascimento_sc=:datanasc, celular=:celular, dataedita=:dataedita, horaedita=:horaedita WHERE codigocadastro = :id ");
        $queryUpdate->bindParam(":senha", $senhaenc);
        $queryUpdate->bindParam(":chave", $chaveenc);
        $queryUpdate->bindParam(":nome", $nome);
        $queryUpdate->bindParam(":datanasc", $datanasc);
        $queryUpdate->bindParam(":celular", $celular);
        $queryUpdate->bindParam(":dataedita", $data);
        $queryUpdate->bindParam(":horaedita", $hora);
        $queryUpdate->bindParam(":id", $id);
        $queryUpdate->execute();

        $response = ['sucesso' => true, 'mensagem' => 'Dados e senha atualizados com sucesso.'];
    } else {
        // Atualiza apenas dados básicos
        $queryUpdate = $con->prepare("UPDATE new_sistema_cadastro SET nome=:nome, datanascimento_sc=:datanasc, celular=:celular WHERE codigocadastro = :id ");
        $queryUpdate->bindParam(":nome", $nome);
        $queryUpdate->bindParam(":datanasc", $datanasc);
        $queryUpdate->bindParam(":celular", $celular);
        $queryUpdate->bindParam(":id", $id);
        $queryUpdate->execute();

        $response = ['sucesso' => true, 'mensagem' => 'Dados atualizados com sucesso.'];
    }
} catch (Exception $e) {
    $response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()];
}

echo json_encode($response);
