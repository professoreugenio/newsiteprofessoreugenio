<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';





if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['aula'])) {
    $dec = encrypt($_POST['aula'], 'd');
    $exp = explode("&", $dec);
    $idAula = $exp[0] ?? null;
    $idModulo = $exp[1] ?? null;
    $liberadoAtual = $exp[2] ?? null;

    if ($idAula && $idModulo) {
        $novoStatus = ($liberadoAtual == "1") ? "0" : "1";

        $con = config::connect();
        $query = $con->prepare("UPDATE a_aluno_publicacoes_cursos SET aulaliberadapc = :novo WHERE idpublicacaopc = :idAula AND idmodulopc = :idModulo");
        $query->bindParam(":novo", $novoStatus);
        $query->bindParam(":idAula", $idAula);
        $query->bindParam(":idModulo", $idModulo);

        if ($query->execute()) {
            echo json_encode(["status" => "success", "liberado" => $novoStatus]);
            exit;
        }
    }
}

echo json_encode(["status" => "error"]);
// $dec = encrypt($_POST['id'], $action = 'd');
// $exp = explode('&', $dec);
// $idpublicacao = $exp[0];
// $idmodulo = $exp[1];
// $aulaLiberada = $exp[2];
// if ($aulaLiberada == '0') {
//     $aulaLiberada = '1';
// }


// $queryUpdate = $con->prepare("UPDATE  a_aluno_publicacoes_cursos SET aulaliberadapc=:liberada WHERE idpublicacaopc = :id AND idmodulopc = :idmodulo");
// $queryUpdate->bindParam(":liberada", $aulaLiberada);
// $queryUpdate->bindParam(":id", $idpublicacao);
// $queryUpdate->bindParam(":idmodulo", $idmodulo);
// $queryUpdate->execute();

// if ($queryUpdate->rowCount() >= 1) {
//     echo '1';
// } else {
//     echo '2';
// }
