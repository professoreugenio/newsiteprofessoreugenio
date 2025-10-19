<?php
if (!isset($_GET['nav']) && empty($_COOKIE['nav'])) {
    header('Location: index.php'); // ou outra página segura
    exit;
}


// 1) Captura com prioridade GET > COOKIE
$navGet    = isset($_GET['nav'])    ? trim((string)$_GET['nav'])    : null;
$navCookie = isset($_COOKIE['nav']) ? trim((string)$_COOKIE['nav']) : null;

$chaveNavegacao = $navGet !== null && $navGet !== '' ? $navGet : ($navCookie ?? '');
$navCriptografada = trim($chaveNavegacao);



$decNavCurso = encrypt($chaveNavegacao, 'd');
$exp = explode("&", $decNavCurso);

$idCursoVenda = $exp[1] ?? "0";
$idFoto = $exp[2] ?? null;

// Busca curso
$query = $con->prepare("SELECT * FROM new_sistema_cursos_turmas INNER JOIN new_sistema_chave ON chaveturmasc=chave WHERE codcursost = :id AND comercialt='1' ");
$query->bindParam(":id", $idCursoVenda);
$query->execute();
$rwTurma = $query->fetch(PDO::FETCH_ASSOC);



// Variáveis principais
$idCurso     = $rwTurma['codcursost'] ?? '';
$enIdCurso = encrypt($idCurso, 'e');
$nomeTurma     = $rwTurma['nometurma'] ?? '';
$descricao     = $rwTurma['previa'] ?? '';
$lead          = $rwTurma['lead'] ?? '';
$chaveTurma = $rwTurma['chave'] ?? '';

/*horários */

$horamanha = $rwTurma['horadem'] ?? '';
$horatarde = $rwTurma['horadet'] ?? '';
$horanoite = $rwTurma['horaden'] ?? '';


/*comercial*/
$vendaliberada = $rwTurma['visivelst'] ?? '';
$horasaulast = $rwTurma['horasaulast'] ?? '';
$valorvenda = $rwTurma['valorvenda'] ?? '';
$chavepix = $rwTurma['chavepix'] ?? '';
$chavepixvalorvenda = $rwTurma['chavepixvalorvenda'] ?? '';
$valoranual = $rwTurma['valoranual'] ?? '';
$valorvendavitalicia = $rwTurma['valorvendavitalicia'] ?? '';
$chavepixvitalicia = $rwTurma['chavepixvitalicia'] ?? '';
$linkpagseguro = $rwTurma['linkpagseguro'] ?? '';
$linkpagsegurovitalicia = $rwTurma['linkpagsegurovitalicia'] ?? '';
$linkmercadopago = $rwTurma['linkmercadopago'] ?? '';
$linkmercadopagovitalicio = $rwTurma['linkmercadopagovitalicio'] ?? '';
$valorhoraaula = $rwTurma['valorhoraaula'] ?? '';
$imgqrcodecurso = $rwTurma['imgqrcodecurso'] ?? '';
$imgqrcodeanual = $rwTurma['imgqrcodeanual'] ?? '';
$imgqrcodevitalicio = $rwTurma['imgqrcodevitalicio'] ?? '';



$tipo = "1";
$query = $con->prepare("
    SELECT 
        categorias.*, fotos.*
    FROM 
        new_sistema_categorias_PJA AS categorias
    INNER JOIN 
        new_sistema_midias_fotos_PJA AS fotos
    ON 
        categorias.pasta = fotos.pasta
    WHERE 
        fotos.codpublicacao = :id 
        AND fotos.tipo = :tipo
");

$query->bindParam(":id", $idCursoVenda);
$query->bindParam(":tipo", $tipo);
$query->execute();

$rwFotoCurso = $query->fetch(PDO::FETCH_ASSOC);

$imgMidiaCurso = 'https://professoreugenio.com/img/cat-2.jpg';
if ($rwFotoCurso) {
    $pastaMidia = $rwFotoCurso['pasta'];
    $fotoMidia = $rwFotoCurso['foto'];
    $imgMidiaCurso = "https://professoreugenio.com/fotos/midias/$pastaMidia/$fotoMidia";
}

$queryCurso = $con->prepare("SELECT * FROM new_sistema_categorias_PJA WHERE codigocategorias  = :id ");
$queryCurso->bindParam(":id", $idCursoVenda);
$queryCurso->execute();
$rwCurso = $queryCurso->fetch(PDO::FETCH_ASSOC);
$nomeCurso         = $rwCurso['nome'] ?? '';
$hero         = $rwCurso['heroSC'] ?? '';
$sobreocurso         = $rwCurso['sobreSC'] ?? '';
$beneficios         = $rwCurso['beneficiosSC'] ?? '';
$cta         = $rwCurso['ctaSC'] ?? '';

$Codigochave = '';
if (!empty($chaveTurma)) {
    $querychave = $con->prepare("SELECT * FROM new_sistema_chave WHERE chaveturmasc = :campo ");
    $querychave->bindParam(":campo", $chaveTurma);
    $querychave->execute();
    $rwChave = $querychave->fetch(PDO::FETCH_ASSOC);
    if ($rwChave) {
        $Codigochave = $enc = encrypt($rwChave['chavesc'], $action = 'e');
    }
}
