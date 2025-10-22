<?php
if (!isset($_GET['tm']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

require_once APP_ROOT . '/conexao/class.conexao.php'; // caso ainda não tenha sido incluído
require_once APP_ROOT . '/autenticacao.php'; // idem

$idTurma = encrypt($_GET['tm'], $action = 'd');

// Verifica se é um número válido após a descriptografia
if (!is_numeric($idTurma)) {
    header('Location: index.php');
    exit;
}

$queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma  = :idturma");
$queryTurma->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
$queryTurma->execute();
$rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);

// Se o curso não foi encontrado, redireciona
if (!$rwTurma) {
    header('Location: index.php');
    exit;
}

// Atribuição segura dos valores
$Bocolor = $rwTurma['bgcolor_cs'] ?? '';
$Pasta = $rwTurma['pasta'] ?? '';
if (empty($rwTurma['pasta'])) {
    $Pasta = date('Ymd') . time(); // Define a pasta como o ano, mês e dia atual
}
$Nometurma = $rwTurma['nometurma'] ?? '';
$linkWhatsapp = $rwTurma['linkwhatsapp'] ?? '';
$linkYoutube = $rwTurma['youtubesct'] ?? '';
$NomeProfessor = $rwTurma['nomeprofessor'] ?? '';
$CelularProfessor = $rwTurma['celularprofessorct'] ?? '';
$ChaveTurma = $rwTurma['chave'] ?? '';
$lead = $rwTurma['lead'] ?? '';
$produtoafiliado = $rwTurma['idprodutoafiliadoct'] ?? '';
$previa = $rwTurma['previa'] ?? '';
$detalhes = $rwTurma['detalhes'] ?? '';
$sobreocurso = $rwTurma['sobreocurso'] ?? '';
$datainiciost = $rwTurma['datainiciost'] ?? '';
$datafimst = $rwTurma['datafimst'] ?? '';
/*  horários  */
$horadem = $rwTurma['horadem'] ?? '';
$horaparam = $rwTurma['horaparam'] ?? '';
$horadet = $rwTurma['horadet'] ?? '';
$horaparat = $rwTurma['horaparat'] ?? '';
$horaden = $rwTurma['horaden'] ?? '';
$horaparan = $rwTurma['horaparan'] ?? '';

/** para checkbox */
$institucional =  ($rwTurma['institucional'] == '1') ? "checked" : "";
$chkon   = ($rwTurma['visivelst'] == '1') ? "checked" : "";
$chkanda   = ($rwTurma['andamento'] == '1') ? "0" : "";
$chcom   = ($rwTurma['comercialt'] == '1') ? "checked" : "";
$chvivo   = ($rwTurma['aovivoct'] == '1') ? "checked" : "";
$chkytube    = ($rwTurma['visiveltube'] == '1') ? "checked" : "";

/*comercial*/
$valorvenda = $rwTurma['valorbrutoct'] ?? '';
$valorcartao = $rwTurma['valorcartaoct'] ?? '';
$valorcartaoanual = $rwTurma['valorcartaoanualct'] ?? '';
$valoravista = $rwTurma['valoravistact'] ?? '';
$chavepixvaloravista = $rwTurma['pixvaloravistact'] ?? '';
$chavepixvaloranualavista = $rwTurma['pixvaloranualavistact'] ?? '';
$valoranual = $rwTurma['valoranualct'] ?? '';
$andamento = $rwTurma['andamento'] ?? '0';
$horasaulast = $rwTurma['horasaulast'] ?? '';
$chavepix = $rwTurma['chavepix'] ?? '';
$chavepixvalorvenda = $rwTurma['chavepixvalorvenda'] ?? '';
$chavepixvitalicia = $rwTurma['chavepixvitalicia'] ?? '';
$linkpagseguro = $rwTurma['linkpagseguro'] ?? '';
$linkpagsegurovitalicia = $rwTurma['linkpagsegurovitalicia'] ?? '';
$linkmercadopago = $rwTurma['linkmercadopago'] ?? '';
$linkmercadopagovitalicio = $rwTurma['linkmercadopagovitalicio'] ?? '';
$valorhoraaula = $rwTurma['valorhoraaula'] ?? '';
$imgqrcodecurso = $rwTurma['imgqrcodecurso'] ?? '';
$imgqrcodeanual = $rwTurma['imgqrcodeanual'] ?? '';
$imgqrcodevitalicio = $rwTurma['imgqrcodevitalicio'] ?? '';


$querychave = $con->prepare("SELECT * FROM new_sistema_chave WHERE chaveturmasc = :campo ");
$querychave->bindParam(":campo", $ChaveTurma);
$querychave->execute();
$rwChave = $querychave->fetch(PDO::FETCH_ASSOC);
if ($rwChave) {
    $rwChave['chavesc'];
}
