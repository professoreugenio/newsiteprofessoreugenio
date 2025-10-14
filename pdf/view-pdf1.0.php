<?php
define('BASEPATH', true);
require_once __DIR__ . '/vendor/autoload.php';
require_once '../conexao/class.conexao.php';
require_once '../autenticacao.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php

use Mpdf\Mpdf;

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

// Inicia o MPDF
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_header' => 5,
    'margin_footer' => 10,
]);

// Cabeçalho e rodapé
$mpdf->SetHTMLHeader('<div style="text-align: right; font-weight: bold; font-size: 10pt;">' . $rwNome['nome'] . ' - ' . $rwNome['modulo'] . '</div><div style="text-align: right; font-weight: bold; font-size: 9pt;">Professor Eugênio (https://professoreugenio.com)</div>');
$mpdf->SetHTMLFooter('<div style="text-align: center; font-size: 10pt;">Página {PAGENO} de {nbpg}</div>');

// Estilo CSS
$css = "
    li { margin:0; padding: 0; }
    ul { margin:0; padding: 0; }
    ol { margin:0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 9pt; }
    p { font-family: Arial, sans-serif; font-size: 9pt; margin: 0; padding: 0; }
    img { max-width:100%; height:auto; border-radius: 5px; margin: 10px 0; }
    h1 { font-size: 16pt; font-weight: bold; color: #003366; margin-bottom: 10px; }
    h2 { padding: 3px 10px; border: solid 1px rgb(0, 0, 0); border-radius: 10px ;background:rgb(226, 235, 241) ; font-size: 14pt; font-weight: bold; color: #0055a5; margin-top: 20px; margin-bottom: 8px; }
    img { padding: 2px ; border: solid 1px rgb(0, 0, 0); border-radius:20px; margin-top: 20px; margin-bottom: 8px; }
    blockquote { display:block; font-family: Arial, padding: 40px;  background:rgb(235, 242, 246) ; margin: 0 }   
     pre { background-size: 80px; font-family: Arial, padding: 20px 20px; border: solid 1px rgb(0, 68, 204); margin-top: 20px; margin-bottom: 8px;display:block }
    h3 { font-size: 12pt; font-weight: bold; color: #0077cc; margin-top: 18px; margin-bottom: 6px;  }
    h4 { font-size: 11pt; font-weight: bold; color: #3399cc; margin-top: 16px; margin-bottom: 5px; }
    h5 { display:inline-block; padding: 20px; width: 150px; border-radius: 5px 10px; font-size: 11pt; font-weight: bold; color:rgb(255, 255, 255); background:rgb(210, 86, 9); margin-top: 16px; margin-bottom: 5px; }
    p { text-align: justify; line-height: 1.6; }
    .blockquote { background-color: #f4f4f4; padding: 20px; margin: 15px 0; border-left: 5px solid #ccc; }
";
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

// CAPA
$image = base64_encode(file_get_contents('https://professoreugenio.com/img/logo.png'));
$capa = "
<style>
     .capa-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        font-family: Arial, sans-serif;
    }
     .capa-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 90%;
    }
    .capa-content h1 {
        font-size: 32pt;
        margin: 20px 0;
        color: #333;
    }
    .capa-content h3 {
        font-size: 18pt;
        margin: 10px 0;
        font-weight: 500;
        color: #555;
    }
    .capa-content h4 {
        font-size: 14pt;
        margin: 5px 0;
        font-weight: 400;
        color: #555;
    }
    .capa-header {
        height: 80px;
        background-color: {$rwNome['bgcolor']};
        margin-bottom: 200px;
    }
    
    .capa-footer {
        height: 80px;
        background-color: {$rwNome['bgcolor']};
        margin-top: 200px;
    }
</style>
<div class='capa-wrapper'>
    <div class='capa-header'></div>

    <div class='capa-content'>
        <h3>Curso de {$rwNome['nome']}</h3>
       
        <h1>{$rwNome['modulo']}-{$idmodulo}</h1>
        <h3>MASTER CLASS DE {$rwNome['nome']}</h3>
        <h4>Professor Eugênio</h4>
        <h4 style='font-size: 12pt; color: #000;'>Site: professoreugenio.com</h4>
        <img src='data:image/png;base64,{$image}' width='130' style='margin-top: 30px;' />
    </div>

    <div class='capa-footer'></div>
</div>";
$mpdf->WriteHTML($capa);
$mpdf->AddPage();

// CONTEÚDO
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
foreach ($fetch as $key => $value) {
    $titulo = htmlspecialchars($value['titulo']) . $value['codigopublicacoes'];
    $texto = htmlspecialchars_decode($value['texto']);
    $texto = str_replace('--break--', '<hr style="margin: 30px 0;">', $texto);

    // Marca os títulos para o índice
    $texto = preg_replace_callback('/<h([2-4])>(.*?)<\/h\1>/i', function ($matches) {
        $nivel = $matches[1];
        $texto = strip_tags($matches[2]); // remove tags internas
        return "<h{$nivel} toc-entry='{$texto}'>{$matches[2]}</h{$nivel}>";
    }, $texto);

    $html = "<h1 toc-entry='Aula " . ($key + 1) . " - $titulo'>Aula " . ($key + 1) . " - $titulo</h1>";
    $html .= "<div>$texto</div>";
    $mpdf->AddPage();
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
}

// TOC no final
$mpdf->TOCpagebreakByArray([
    'links' => true,
    'toc-preHTML' => '<h1 style="text-align: center;">Índice</h1>',
    'toc-bookmarkText' => 'Índice',
    'paging' => true,
]);

// Saída do PDF
$mpdf->Output("modulo_{$rwNome['modulo']}.pdf", "I");
