<?php
// config_aulas1.0/ajax_updateTituloAnexos.php

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

if (!isset($_POST['titulo']) || !isset($_POST['oldTitulo'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos.']);
    exit;
}

$tituloNovo = trim($_POST['titulo']);
$tituloAntigo = trim($_POST['oldTitulo']);

if ($tituloNovo === '') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'O título não pode estar vazio.']);
    exit;
}

try {
    $query = $con->prepare("UPDATE new_sistema_publicacoes_anexos_PJA SET titulopa = :novo WHERE titulopa = :antigo");
    $query->bindParam(':novo', $tituloNovo);
    $query->bindParam(':antigo', $tituloAntigo);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo json_encode(['status' => 'ok', 'mensagem' => 'Título atualizado com sucesso.']);
    } else {
        echo json_encode(['status' => 'info', 'mensagem' => 'Nenhuma alteração feita.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
    exit;
}
