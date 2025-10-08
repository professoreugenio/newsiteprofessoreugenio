<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    // Verifica se os dados básicos estão presentes
    if (!isset($_POST['chave']) || empty($_POST['chave'])) {
        throw new Exception('Turma não identificada.');
    }

    $chave = $_POST['chave'];
    $nome = trim($_POST['nometurma'] ?? '');
    $produtoAfiliado = trim($_POST['produtoafiliado'] ?? '');
    $pasta = trim($_POST['pasta'] ?? '');
    $nomeProfessor = trim($_POST['nomeprofessor'] ?? '');
    $bgcolor = trim($_POST['bgcolor_cs'] ?? '');
    $linkWhatsapp = trim($_POST['linkwhatsapp'] ?? '');
    $linkYoutube = trim($_POST['youtubesct'] ?? '');
    $lead = trim($_POST['lead'] ?? '');
    $detalhes = trim($_POST['detalhes'] ?? '');
    $sobre = trim($_POST['sobreocurso'] ?? '');
    $previa = trim($_POST['previa'] ?? '');

    // horários
    $horadem = trim($_POST['manha_de']) ?? '';
    $horaparam = trim($_POST['manha_as']) ?? '';

    $horadet = trim($_POST['tarde_de']) ?? '';
    $horaparat = trim($_POST['tarde_as']) ?? '';

    $horaden = trim($_POST['noite_de']) ?? '';
    $horaparan = trim($_POST['noite_as']) ?? '';

    // Checkboxes (se não marcados, não vêm no POST)
    $visivelst = isset($_POST['visivelst']) ? 1 : 0;
    $andamento = isset($_POST['andamento']) ? 1 : 0;
    $comercialt = isset($_POST['comercialt']) ? 1 : 0;
    $visiveltube = isset($_POST['visiveltube']) ? 1 : 0;
    $institucional = isset($_POST['institucional']) ? 1 : 0;


    // Atualiza os dados da turma
    $stmt = $con->prepare("
        UPDATE new_sistema_cursos_turmas SET
            nometurma = :nome,
            idprodutoafiliadoct = :produtoafiliado,
            horadem = :manha1,
            horaparam = :manha2,
            horadet = :tarde1,
            horaparat = :tarde2,
            horaden = :noite1,
            horaparan = :noite2,
            pasta = :pasta,
            previa = :previa,
            nomeprofessor = :nomeprofessor,
            bgcolor_cs = :bgcolor,
            linkwhatsapp = :linkwhatsapp,
            youtubesct = :youtubesct,
            lead = :lead,
            detalhes = :detalhes,
            sobreocurso = :sobre,
            visivelst = :visivelst,
            andamento = :andamento,
            comercialt = :comercialt,
            visiveltube = :visiveltube,
            institucional = :institucional
        WHERE chave = :chave
    ");

    $stmt->execute([
        ':nome' => $nome,
        ':produtoafiliado' => $produtoAfiliado,
        ':manha1' => $horadem,
        ':manha2' => $horaparam,
        ':tarde1' => $horadet,
        ':tarde2' => $horaparat,
        ':noite1' => $horaden,
        ':noite2' => $horaparan,
        ':pasta' => $pasta,
        ':previa' => $previa,
        ':nomeprofessor' => $nomeProfessor,
        ':bgcolor' => $bgcolor,
        ':linkwhatsapp' => $linkWhatsapp,
        ':youtubesct' => $linkYoutube,
        ':lead' => $lead,
        ':detalhes' => $detalhes,
        ':sobre' => $sobre,
        ':visivelst' => $visivelst,
        ':andamento' => $andamento,
        ':comercialt' => $comercialt,
        ':visiveltube' => $visiveltube,
        ':institucional' => $institucional,
        ':chave' => $chave
    ]);

    echo json_encode(['status' => 'ok', 'mensagem' => 'Turma atualizada com sucesso'.$produtoAfiliado.!'.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}
