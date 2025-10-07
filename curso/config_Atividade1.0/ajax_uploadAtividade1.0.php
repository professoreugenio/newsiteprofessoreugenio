<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Requisição inválida.');
    }

    if (
        !isset($_POST['idpublicacacaoAA'], $_POST['idalulnoAA'], $_POST['idmoduloAA']) ||
        empty($_FILES['arquivos'])
    ) {
        throw new Exception('Dados incompletos.');
    }
    $ano = date('Y');
    $dateabrv = new DateTime($data);
    $mesAbreviado = strftime('%b_', $dateabrv->getTimestamp()); // Retorna "dez_"
    echo ucfirst($mesAbreviado); // Saída: Dez_
    $idPublicacao = intval($_POST['idpublicacacaoAA']);
    $idAluno = intval($_POST['idalulnoAA']);
    $idModulo = intval($_POST['idmoduloAA']);
    $pasta = intval($_POST['pastaAA']);
    $diretorio = "../../fotos/atividades/" . $mesAbreviado . $ano . "_" . $pasta;

    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'zip', 'rar', 'doc', 'docx', 'xlsx', 'xls', 'pptx', 'txt', 'pdf'];
    $sucesso = 0;
    $falha = 0;

    foreach ($_FILES['arquivos']['name'] as $index => $nomeOriginal) {
        $tmpName = $_FILES['arquivos']['tmp_name'][$index];
        $tamanho = $_FILES['arquivos']['size'][$index];

        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        if (!in_array($extensao, $permitidos)) {
            $falha++;
            continue;
        }

        $nomeUnico = uniqid('arquivo_' . $idAluno) . '.' . $extensao;
        $caminhoFinal = $diretorio . '/' . $nomeUnico;

        if (move_uploaded_file($tmpName, $caminhoFinal)) {
            $stmt = $con->prepare("INSERT INTO a_curso_AtividadeAnexos (
                idpublicacacaoAA, idalulnoAA, idmoduloAA, fotoAA, pastaAA, dataenvioAA, horaenvioAA, sizeAA, extensaoAA
            ) VALUES (
                :idpublicacacaoAA, :idalulnoAA, :idmoduloAA, :fotoAA, :pastaAA, :dataenvioAA, :horaenvioAA, :sizeAA, :extensaoAA
            )");

            $stmt->execute([
                ':idpublicacacaoAA' => $idPublicacao,
                ':idalulnoAA' => $idAluno,
                ':idmoduloAA' => $idModulo,
                ':fotoAA' => $nomeUnico,
                ':pastaAA' => $pasta,
                ':dataenvioAA' => $data,
                ':horaenvioAA' => $hora,
                ':sizeAA' => $tamanho,
                ':extensaoAA' => $extensao
            ]);
            $sucesso++;
        } else {
            $falha++;
        }
    }

    if ($sucesso > 0) {
        echo json_encode([
            'sucesso' => true,
            'mensagem' => "$sucesso arquivo(s) enviado(s) com sucesso. " . ($falha > 0 ? "$falha falha(s)." : "")
        ]);
    } else {
        throw new Exception("Não foi possível enviar os arquivos.");
    }
} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => $e->getMessage()
    ]);
}
