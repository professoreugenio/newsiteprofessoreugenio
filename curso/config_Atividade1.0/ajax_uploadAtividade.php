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
        !isset($_POST['idpublicacacaoAA'], $_POST['idalulnoAA'], $_POST['idmoduloAA'], $_POST['pastaAA']) ||
        empty($_FILES['arquivos'])
    ) {
        throw new Exception('Dados incompletos.');
    }

    // === Variáveis principais ===
    $idPublicacao = intval($_POST['idpublicacacaoAA']);
    $idAluno = intval($_POST['idalulnoAA']);
    $idModulo = intval($_POST['idmoduloAA']);
    $pasta = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_POST['pastaAA']);

    // === Geração do caminho ===
    setlocale(LC_TIME, 'pt_BR.UTF-8'); // Para meses em português (Linux)
    $hoje = new DateTime();
    $mesAbreviado = strftime('%b_', $hoje->getTimestamp()); // ex: jul_
    $ano = $hoje->format('Y');
    $pasta = ucfirst($mesAbreviado) . $ano . "_" . $idAluno . "_".$pasta;
    $diretorio = "../../fotos/atividades/" . $pasta;

    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }

    // === Configurações de envio ===
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

        // === Nome do arquivo com aluno e data ===
        $nomeUnico = uniqid('arquivo_' . $idAluno . '_') . '.' . $extensao;
        $caminhoFinal = $diretorio . '/' . $nomeUnico;

        if (move_uploaded_file($tmpName, $caminhoFinal)) {
            $stmt = $con->prepare("INSERT INTO a_curso_AtividadeAnexos (
                idpublicacacaoAA, idalulnoAA, idmoduloAA, fotoAA, pastaAA, dataenvioAA, horaenvioAA, sizeAA, extensaoAA
            ) VALUES (
                :idpublicacacaoAA, :idalulnoAA, :idmoduloAA, :fotoAA, :pastaAA, CURDATE(), CURTIME(), :sizeAA, :extensaoAA
            )");

            $stmt->execute([
                ':idpublicacacaoAA' => $idPublicacao,
                ':idalulnoAA' => $idAluno,
                ':idmoduloAA' => $idModulo,
                ':fotoAA' => $nomeUnico,
                ':pastaAA' => $pasta,
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
