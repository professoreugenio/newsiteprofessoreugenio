<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json');
$response = ['sucesso' => false, 'mensagem' => 'Erro ao inserir a publicação.'];
try {
    // Validação básica
    if (
        empty($_POST['idcurso']) ||
        empty($_POST['idmodulo']) ||
        empty($_POST['titulo']) ||
        empty($_POST['aula'])
    ) {
        throw new Exception("Preencha todos os campos obrigatórios.");
    }
    // Descriptografa o ID do módulo
    $idModulo = encrypt($_POST['idmodulo'], 'd');

    if (!is_numeric($idModulo)) {
        throw new Exception("Módulo inválido.");
    }


    // Conta quantas publicações já existem no módulo
    $stmtContagem = config::connect()->prepare("SELECT COUNT(*) FROM new_sistema_publicacoes_PJA WHERE codmodulo_sp = :idmodulo");
    $stmtContagem->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $stmtContagem->execute();
    $quantidade = $stmtContagem->fetchColumn();
    $ordem = $quantidade + 1;
    
    // Prepara dados
    $idCurso     = encrypt($_POST['idcurso'], 'd');
    $titulo      = trim($_POST['titulo']);
    $linkexterno = trim($_POST['linkexterno'] ?? '');
    $descricao   = trim($_POST['descricao'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $aula        = intval($_POST['aula']);
    $visivel     = isset($_POST['visivel']) ? 1 : 0;
    $visivelhome = isset($_POST['visivelhome']) ? 1 : 0;
    // Query de INSERT
    $stmt = config::connect()->prepare("
        INSERT INTO new_sistema_publicacoes_PJA 
        (codigocurso_sp, codmodulo_sp, titulo, linkexterno, olho, tag, ordem, aula, visivel, texto, data, hora, datapub, horapub, dataatualizacao, horaatualizacao)
        VALUES 
        (:curso, :modulo, :titulo, :link, :descricao, :tags, :ordem, :aula, :visivel, 'Publicação em atualização.',:data,:hora,:datapub,:horapub, :dataatualizacao,:horaatualizacao)
    ");
    $stmt->bindParam(':curso', $idCurso);
    $stmt->bindParam(':modulo', $idModulo);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':link', $linkexterno);
    $stmt->bindParam(':descricao', $descricao);
    $stmt->bindParam(':tags', $tags);
    $stmt->bindParam(':ordem', $ordem, PDO::PARAM_INT);
    $stmt->bindParam(':aula', $aula, PDO::PARAM_INT);
    $stmt->bindParam(':visivel', $visivel, PDO::PARAM_INT);
    $stmt->bindParam(':data', $data);
    $stmt->bindParam(':hora', $hora);
    $stmt->bindParam(':datapub', $data);
    $stmt->bindParam(':horapub', $hora);
    $stmt->bindParam(':dataatualizacao', $data);
    $stmt->bindParam(':horaatualizacao', $hora);

    if ($stmt->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = 'Publicação inserida com sucesso.';
    } else {
        throw new Exception("Falha ao executar o cadastro.");
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}
echo json_encode($response);
