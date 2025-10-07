<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    if (empty($_POST['idanexo'])) {
        throw new Exception('Código do anexo não informado.');
    }

    $idanexo = intval($_POST['idanexo']);

    // Buscar o registro
    $stmt = $con->prepare("SELECT fotoAA, pastaAA FROM a_curso_AtividadeAnexos WHERE codigoatividadeanexos = :id");
    $stmt->execute([':id' => $idanexo]);
    $arquivo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$arquivo) {
        throw new Exception('Arquivo não encontrado.');
    }

    $nomeArquivo = $arquivo['fotoAA'];
    $pasta = $arquivo['pastaAA'];

    $caminho = "../../fotos/atividades/$pasta/$nomeArquivo";

    // Excluir do banco
    $del = $con->prepare("DELETE FROM a_curso_AtividadeAnexos WHERE codigoatividadeanexos = :id");
    $del->execute([':id' => $idanexo]);

    // Excluir o arquivo físico
    if (file_exists($caminho)) {
        unlink($caminho);
    }

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Arquivo excluído com sucesso.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
