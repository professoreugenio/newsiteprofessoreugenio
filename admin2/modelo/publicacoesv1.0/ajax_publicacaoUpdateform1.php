<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json');
$response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.'];
try {
    if (!isset($_POST['idpublicacao'])) {
        throw new Exception("ID da publicação não recebido.");
    }
    $idPublicacao = encrypt(htmlspecialchars($_POST['idpublicacao']), 'd');
    $idModulo = encrypt(htmlspecialchars($_POST['idmodulo']), 'd');
    $idcurso = encrypt(htmlspecialchars($_POST['idcurso']), 'd');
    $comercial = trim($_POST['comercial']);
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $tags = trim($_POST['tags']);
    $ordem = intval($_POST['ordem']);
    $visivel = isset($_POST['visivel']) ? 1 : 0;
    $visivelhome = isset($_POST['visivelhome']) ? 1 : 0;
    // Contagem de usuários ativos



    if ($comercial == 1) {

        $sql = "SELECT * FROM a_aluno_publicacoes_cursos WHERE idpublicacaopc  = :idpublicacao And idcursopc = :idcurso";
        $stmt = $con->prepare($sql);
        $stmt->bindParam(":idpublicacao", $idPublicacao, PDO::PARAM_INT);
        $stmt->bindParam(":idcurso", $idcurso, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $idPublicacaoPC = $row['codigopublicacoescursos'];

        $con = config::connect();
        $queryUpdate = $con->prepare("UPDATE a_aluno_publicacoes_cursos SET idmodulopc=:idmodulo, ordempc=:ordem, visivelpc=:visivel WHERE codigopublicacoescursos = :idpublicacaoPC");
        $queryUpdate->bindParam(":idmodulo", $idModulo);
        $queryUpdate->bindParam(":ordem", $ordem);
        $queryUpdate->bindParam(":visivel", $visivel);
        $queryUpdate->bindParam(":idpublicacaoPC", $idPublicacaoPC);
        $queryUpdate->execute();

        $queryUpdate = $con->prepare("UPDATE new_sistema_publicacoes_PJA SET
        titulo = :titulo,
        linkexterno = :linkexterno,
        olho = :descricao,
        tag = :tags,
        dataatualizacao = :dataatual,
        horaatualizacao = :horaatual
        WHERE codigopublicacoes =:idpublicacao ");
        $queryUpdate->bindParam(":titulo", $titulo);
        $queryUpdate->bindParam(":linkexterno", $linkexterno);
        $queryUpdate->bindParam(":descricao", $descricao);
        $queryUpdate->bindParam(":tags", $tags);
        $queryUpdate->bindParam(":dataatual", $data);
        $queryUpdate->bindParam(":horaatual", $hora);
        $queryUpdate->bindParam(":idpublicacao", $idPublicacao);

        if ($queryUpdate->execute()) {
            $response['sucesso'] = true;
            $response['mensagem'] = "Publicação Cópia atualizada com sucesso!";
        } else {
            throw new Exception("Não foi possível atualizar a publicação.");
        }
    } else {
        $queryUpdate = $con->prepare("UPDATE new_sistema_publicacoes_PJA SET
        titulo = :titulo,
        linkexterno = :linkexterno,
        olho = :descricao,
        ordem = :ordem,
        tag = :tags,
        dataatualizacao = :dataatual,
        horaatualizacao = :horaatual
        WHERE codigopublicacoes =:idpublicacao ");
        $queryUpdate->bindParam(":titulo", $titulo);
        $queryUpdate->bindParam(":linkexterno", $linkexterno);
        $queryUpdate->bindParam(":descricao", $descricao);
        $queryUpdate->bindParam(":ordem", $ordem);
        $queryUpdate->bindParam(":tags", $tags);
        $queryUpdate->bindParam(":dataatual", $data);
        $queryUpdate->bindParam(":horaatual", $hora);
        $queryUpdate->bindParam(":idpublicacao", $idPublicacao);


        if ($queryUpdate->execute()) {
            $response['sucesso'] = true;
            $response['mensagem'] = "Publicação atualizada com sucesso!";
        } else {
            throw new Exception("Não foi possível atualizar a publicação.");
        }
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}
echo json_encode($response);
