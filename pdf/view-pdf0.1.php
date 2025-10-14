<?php

define('BASEPATH', true);
require_once __DIR__ . '/vendor/autoload.php';
require_once '../conexao/class.conexao.php';
require_once '../autenticacao.php';


$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'dejavusans',
    'format' => 'A4'
]);


function removeEmojis($text)
{
    return preg_replace('/[\x{1F600}-\x{1F64F}' .
        '\x{1F300}-\x{1F5FF}' .
        '\x{1F680}-\x{1F6FF}' .
        '\x{1F700}-\x{1F77F}' .
        '\x{1F780}-\x{1F7FF}' .
        '\x{1F800}-\x{1F8FF}' .
        '\x{1F900}-\x{1F9FF}' .
        '\x{1FA00}-\x{1FA6F}' .
        '\x{2600}-\x{26FF}' .
        '\x{2700}-\x{27BF}]++/u', '', $text);
}
// Decodifica e extrai ID do módulo
$dec = encrypt($_GET['var'], 'd');
$exp = explode("&", $dec);
$idmodulo = isset($exp[2]) ? $exp[2] : null;

// Conexão e busca do módulo
$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_modulos_PJA, new_sistema_categorias_PJA WHERE codigomodulos = :idmodulo AND new_sistema_modulos_PJA.codcursos = new_sistema_categorias_PJA.codigocategorias");
$query->bindParam(":idmodulo", $idmodulo);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);

// CSS personalizado
$css = "
body { font-family: dejavusans; font-size: 10pt; }
p  { margin: 0; padding: 0; }
h1 { color: #050d14ff; font-size: 16pt; }
h2 { color: #050d14ff; font-size: 14pt; }
h3 { color: #050d14ff; font-size: 12pt; }
h2{ color: #050d14ff; font-size: 12pt; background-color: #dee7eeff; padding: 5px; margin-bottom: 10px; }
h5{ color: #050d14ff; font-size: 12pt; background-color: #c0dedaff; padding: 5px; margin-bottom: 10px; display:inline-block; }

.titulo-capa {
    text-align: center;
    margin-top: 200px;
}
.marca-agua {
    position: absolute;
    font-size: 50pt;
    color: #cccccc;
    transform: rotate(-45deg);
    top: 300px;
    left: 100px;
    z-index: -1;
}
.pagina {
    page-break-after: always;
}
.tituloPublicacao{
border: solid 1px #050d14ff;
padding: 5px;
margin-bottom: 10px;
}

.capa-header { height: 80px; background-color: {$rwNome['bgcolor']}; margin-bottom: 200px; }
.capa-footer { height: 80px; background-color: {$rwNome['bgcolor']}; margin-top: 300px; }
.capa-wrapper { position: relative; width: 100%; height: 100%; font-family: Arial, sans-serif; }
";








$mpdf->SetHTMLHeader('
    <div style="text-align: right; font-size: 10pt; color: #888;">
        ' . $rwNome['modulo'] . ' | Página {PAGENO}/{nbpg}
    </div>
');

// CAPA
$image = base64_encode(file_get_contents('https://professoreugenio.com/img/logo.png'));
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML("
<div class='capa-wrapper'>
 <div class='capa-header'></div>
    <div class='titulo-capa'>    
        <h2 style='font-size:24pt'>{$rwNome['modulo']}</h2>
            <h3 style='font-size:20pt'>{$rwNome['nome']}</h3>
            <h4>Professor Eugênio</h4>
            <h4 style='font-size: 12pt; color: #000;'>Site: professoreugenio.com</h4>
            <img src='data:image/png;base64,{$image}' width='130' style='margin-top: 30px;' />
    </div>
    <div class='capa-footer'></div>
   
</div>
 <div class='pagina'></div>
");

// ÍNDICE (Table of Contents)
$mpdf->TOCpagebreak([
    'links' => true,
    'toc-preHTML' => '<h1>Índice</h1>',
    'toc-bookmarkText' => 'Índice',
    'toc-pagenum-style' => '1',
    'toc-pagenum-offset' => -1 // Subtrai 2 páginas
]);



$mpdf->SetHTMLFooter('
    <div style="text-align: center; font-size: 10pt; color: #aaa;">
        www.professoreugenio.com | Página {PAGENO}
    </div>
');


// BUSCA AULAS
$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codmodulo_sp = :idmodulo AND visivel='1' ORDER BY ordem, aula");
$query->bindParam(":idmodulo", $idmodulo);
$query->execute();
$fetch = $query->fetchAll();

if (!$fetch) {
    $query = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos,new_sistema_publicacoes_PJA WHERE idmodulopc = :idmodulo AND visivelpc='1' AND codigopublicacoes = idpublicacaopc ORDER BY ordempc");
    $query->bindParam(":idmodulo", $idmodulo);
    $query->execute();
    $fetch = $query->fetchAll();
}

// Páginas de Conteúdo
echo $total = count($fetch);

foreach ($fetch as $i => $value) {
    $numeroAula = $i + 1;

    $mpdf->WriteHTML('<h1 class="tituloPublicacao">' . $numeroAula . ' - ' . htmlspecialchars_decode($value['titulo']) . '</h1>', \Mpdf\HTMLParserMode::HTML_BODY);

    $texto = htmlspecialchars_decode($value['texto']);
    $texto = removeEmojis($texto);
    $texto = str_replace('--break--', '<hr style="margin: 30px 0;">', $texto);

    $mpdf->WriteHTML($texto);
    $mpdf->TOC_Entry($numeroAula . ' - ' . $value['titulo']);

    // Só adiciona nova página se não for o último
    if ($i < $total - 1) {
        $mpdf->AddPage();
    }
}


// SAÍDA DO PDF
$mpdf->SetTitle('PDF com Capa e Índice');
$nomeArquivo = 'modulo_' . $rwNome['nome'] . '_' . $rwNome['modulo'] . '.pdf';
$mpdf->Output($nomeArquivo, 'I'); // I = inline no navegador
