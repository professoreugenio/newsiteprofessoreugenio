<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (empty($_POST['idcurso'])) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Curso não informado.']);
        exit;
    }
    $idcurso = (int) $_POST['idcurso'];

    $pdo = config::connect();

    // Ajuste os nomes de colunas se necessário no seu schema:
    // Ex.: WHERE codcursos = :idcurso  OU  codigocurso_sm = :idcurso
    $sql = "SELECT codigomodulos AS id, modulo AS nome
            FROM new_sistema_modulos_PJA
            WHERE codcursos = :idcurso 
            ORDER BY modulo ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':idcurso', $idcurso, PDO::PARAM_INT);
    $stmt->execute();

    $opcoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['sucesso' => true, 'opcoes' => $opcoes]);
} catch (Throwable $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao carregar módulos. Curso ID: ' . $idcurso]);
}
