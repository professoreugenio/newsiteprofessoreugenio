<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Requisição inválida.');
    }

    $idfile = intval($_POST['idfile'] ?? 0);
    $iduserde = intval($_POST['iduserde'] ?? 0);
    $iduserpara = intval($_POST['iduserpara'] ?? 0);
    $texto = trim($_POST['texto'] ?? '');

    if ($idfile === 0 || $iduserde === 0 || $texto === '') {
        throw new Exception('Dados incompletos.');
    }

    $stmt = $con->prepare("INSERT INTO a_curso_AtividadeComentario (
        idfileAnexoAAC, iduserdeAAC, iduserparaAAC, textoAAC, dataAAC, horaAAC
    ) VALUES (
        :idfile, :iduserde, :iduserpara, :texto, CURDATE(), CURTIME()
    )");

    $stmt->execute([
        ':idfile' => $idfile,
        ':iduserde' => $iduserde,
        ':iduserpara' => $iduserpara,
        ':texto' => $texto
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Comentário adicionado com sucesso.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
