<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.'];

try {
    if (!isset($_POST['idCurso'])) {
        throw new Exception("ID do curso não recebido.");
    }

    $idCurso = encrypt(htmlspecialchars($_POST['idCurso']), 'd');

    $nome = trim($_POST['nome']);
    $pasta = trim($_POST['pasta']);
    $youtube = trim($_POST['youtube']);
    $linkexterno = trim($_POST['linkexterno']);
    $bgcolor = trim($_POST['bgcolor']);


    $manhade = trim($_POST['manha_de']) ?? '';
    $manhapara = trim($_POST['manha_as']) ?? '';

    $tardede = trim($_POST['tarde_de']) ?? '';
    $tardepara = trim($_POST['tarde_as']) ?? '';

    $noitede = trim($_POST['noite_de']) ?? '';
    $noitepara = trim($_POST['noite_as']) ?? '';



    // Checkboxes (se não marcados, não vêm no POST)
    $onlinesc = isset($_POST['onlinesc']) ? 1 : 0;
    $comercialsc = isset($_POST['comercialsc']) ? 1 : 0;
    $institucional = isset($_POST['institucionalsc']) ? 1 : 0;
    $visivelsc = isset($_POST['visivelsc']) ? 1 : 0;
    $visivelhomesc = isset($_POST['visivelhomesc']) ? 1 : 0;
    $matriz = isset($_POST['matriz']) ? 1 : 0;

    // Validação simples (adicione mais conforme necessário)
    if ($nome === '') {
        throw new Exception("O nome do curso é obrigatório.");
    }

    // Atualização
    $sql = "UPDATE new_sistema_categorias_PJA SET 
                nome = :nome,
                horadem = :manha1,
                horaparam = :manha2,
                horadet = :tarde1,
                horaparat = :tarde2,
                horaden = :noite1,
                horaparan = :noite2,
                pasta = :pasta,
                youtubeurl = :youtube,
                linkexterno = :linkexterno,
                bgcolor = :bgcolor,
                matriz = :matriz,
                onlinesc = :onlinesc,
                comercialsc = :comercialsc,
                institucionalsc = :institucional,
                visivelsc = :visivelsc,
                visivelhomesc = :visivelhomesc
            WHERE codigocategorias = :id";

    $stmt = $con->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':manha1', $manhade);
    $stmt->bindParam(':manha2', $manhapara);
    $stmt->bindParam(':tarde1', $tardede);
    $stmt->bindParam(':tarde2', $tardepara);
    $stmt->bindParam(':noite1', $noitede);
    $stmt->bindParam(':noite2', $noitepara);
    $stmt->bindParam(':pasta', $pasta);
    $stmt->bindParam(':youtube', $youtube);
    $stmt->bindParam(':linkexterno', $linkexterno);
    $stmt->bindParam(':bgcolor', $bgcolor);
    $stmt->bindParam(':matriz', $matriz, PDO::PARAM_INT);
    $stmt->bindParam(':onlinesc', $onlinesc, PDO::PARAM_INT);
    $stmt->bindParam(':comercialsc', $comercialsc, PDO::PARAM_INT);
    $stmt->bindParam(':institucional', $institucional, PDO::PARAM_INT);
    $stmt->bindParam(':visivelsc', $visivelsc, PDO::PARAM_INT);
    $stmt->bindParam(':visivelhomesc', $visivelhomesc, PDO::PARAM_INT);
    $stmt->bindParam(':id', $idCurso, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = "Curso atualizado com sucesso!";
    } else {
        throw new Exception("Não foi possível atualizar o curso.");
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
