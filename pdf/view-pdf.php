<?php
define('BASEPATH', true);
require_once __DIR__ . '/vendor/autoload.php';
require_once '../conexao/class.conexao.php';
require_once '../autenticacao.php';

use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

// --- Funções util ---
function removeEmojis($text)
{
    return preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]++/u', '', (string)$text);
}
function onlyFilenameSafe($s)
{
    $s = trim((string)$s);
    $s = preg_replace('/[^\w\s\-\.\p{L}\p{N}]+/u', '_', $s);
    $s = preg_replace('/\s+/u', '_', $s);
    return $s ?: 'arquivo';
}

// --- Param decodificado com fallback ---
$var = isset($_GET['var']) ? (string)$_GET['var'] : '';
$dec = $var !== '' ? encrypt($var, 'd') : '';
$exp = $dec !== '' ? explode("&", $dec) : [];
$idmodulo = isset($exp[2]) && $exp[2] !== '' ? $exp[2] : null;

if (!$idmodulo) {
    http_response_code(400);
    exit('Parâmetro inválido.');
}

// --- Conexão & consulta cabeçalho do módulo ---
$con = config::connect();

// JOIN explícito e colunas nomeadas
$qModulo = $con->prepare("
    SELECT 
        m.codigomodulos,
        m.modulo,
        m.bgcolorsm,
        c.nomecurso,
        COALESCE(c.bgcolor, '#e5eef5') AS bgcolor
    FROM new_sistema_modulos_PJA AS m
    INNER JOIN new_sistema_cursos AS c 
        ON m.codcursos = c.codigocursos 
    WHERE m.codigomodulos = :idmodulo
    LIMIT 1
");
$qModulo->bindParam(":idmodulo", $idmodulo, PDO::PARAM_INT);
$qModulo->execute();
$rwNome = $qModulo->fetch(PDO::FETCH_ASSOC);

if (!$rwNome) {
    http_response_code(404);
    exit('Módulo não encontrado.');
}

// --- mPDF ---
$mpdf = new Mpdf([
    'default_font' => 'dejavusans',
    'format'       => 'A4',
    'tempDir'      => __DIR__ . '/tmp' // opcional, ajuda em servers restritos
]);
$mpdf->autoScriptToLang = true;
$mpdf->autoLangToFont   = true;

// --- CSS: inclui defaults para listas (corrige o warning) ---
$css = "
body { font-family: dejavusans; font-size: 10pt; }
p  { margin: 0; padding: 0; }
h1 { color: #050d14; font-size: 16pt; }
h2 { color: #050d14; font-size: 12pt; background-color: #dee7ee; padding: 5px; margin-bottom: 10px; }
h3 { color: #050d14; font-size: 12pt; }
h5 { color: #050d14; font-size: 12pt; background-color: #c0deda; padding: 5px; margin-bottom: 10px; width: 300px; }

/* >>> Correção para listas: define SEMPRE o list-style-type */
ul, ol { margin: 0 0 10px 0; padding-left: 18px; }
ul { list-style-type: disc; }
ol { list-style-type: decimal; }
ul ul { list-style-type: circle; }
ul ul ul { list-style-type: square; }
li { margin: 4px 0; }

/* Layout capa */
.titulo-capa { text-align: center; margin-top: 200px; }
.marca-agua {
    position: absolute; font-size: 50pt; color: #cccccc;
    transform: rotate(-45deg); top: 300px; left: 100px; z-index: -1;
}
.pagina { page-break-after: always; }
.tituloPublicacao { border: 1px solid #050d14; padding: 5px; margin-bottom: 10px; }
.capa-header { height: 80px; background-color: {$rwNome['bgcolorsm']}; margin-bottom: 200px; }
.capa-footer { height: 80px; background-color: {$rwNome['bgcolorsm']}; margin-top: 300px; }
.capa-wrapper { position: relative; width: 100%; height: 100%; font-family: Arial, sans-serif; }
";

$mpdf->SetHTMLHeader('<div style="text-align:right;font-size:10pt;color:#888;">' . htmlspecialchars($rwNome['modulo']) . ' | Página {PAGENO}/{nbpg}</div>');
$mpdf->SetHTMLFooter('<div style="text-align:center;font-size:10pt;color:#aaa;">www.professoreugenio.com | Página {PAGENO}</div>');

// --- CAPA ---
$image = base64_encode(@file_get_contents('https://professoreugenio.com/img/logo.png') ?: '');
$mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);

$modulo = htmlspecialchars($rwNome['modulo']);
$nome   = htmlspecialchars($rwNome['nome']);

$mpdf->WriteHTML("
<div class='capa-wrapper'>
  <div class='capa-header'></div>
  <div class='titulo-capa'>
    <h2 style='font-size:24pt'>{$modulo}</h2>
    <h3 style='font-size:20pt'>{$nome}</h3>
    <h4>Professor Eugênio</h4>
    <h4 style='font-size: 12pt; color: #000;'>Site: professoreugenio.com</h4>"
    . ($image ? "<img src='data:image/png;base64,{$image}' width='130' style='margin-top:30px;' />" : "")
    . "
  </div>
  <div class='capa-footer'></div>
</div>
<div class='pagina'></div>
", HTMLParserMode::HTML_BODY);

// --- ÍNDICE ---
$mpdf->TOCpagebreak([
    'links'            => true,
    'toc-preHTML'      => '<h1>Índice</h1>',
    'toc-bookmarkText' => 'Índice',
    'toc-pagenum-style' => '1'
]);

// --- BUSCA AULAS (preferência: publicações do módulo) ---
// $qAulas = $con->prepare("
//     SELECT codigopublicacoes, titulo, texto, aula, ordem
//     FROM new_sistema_publicacoes_PJA
//     WHERE codmodulo_sp = :idmodulo AND visivel = '1'
//     ORDER BY ordem, aula
// ");

$qAulas = $con->prepare("
    SELECT * FROM a_aluno_publicacoes_cursos, new_sistema_publicacoes_PJA
    WHERE idmodulopc = :idmodulo 
    AND aulaliberadapc = :publico 
    AND codigopublicacoes = idpublicacaopc
    AND a_aluno_publicacoes_cursos.visivelpc = '1'
    ORDER BY  ordempc
");
$publico = '1';

$qAulas->bindParam(':idmodulo', $idmodulo, PDO::PARAM_INT);
$qAulas->bindParam(':publico', $publico, PDO::PARAM_INT);
$qAulas->execute();
$fetch = $qAulas->fetchAll(PDO::FETCH_ASSOC);

if (!$fetch) {
    $qAulas = $con->prepare("
        SELECT p.codigopublicacoes, p.titulo, p.texto, pc.ordempc AS ordem, p.aula
        FROM a_aluno_publicacoes_cursos pc
        INNER JOIN new_sistema_publicacoes_PJA p ON p.codigopublicacoes = pc.idpublicacaopc
        WHERE pc.idmodulopc = :idmodulo AND pc.visivelpc = '1'
        ORDER BY pc.ordempc
        LIMIT 20
    ");
    $qAulas->bindParam(':idmodulo', $idmodulo, PDO::PARAM_INT);
    $qAulas->execute();
    $fetch = $qAulas->fetchAll(PDO::FETCH_ASSOC);
}

// --- CONTEÚDO ---
$total = count($fetch);
foreach ($fetch as $i => $value) {
    $numeroAula = $i + 1;

    $tituloRaw = (string)($value['titulo'] ?? '');
    $titulo = htmlspecialchars_decode($tituloRaw, ENT_QUOTES);
    $mpdf->TOC_Entry($numeroAula . ' - ' . strip_tags($titulo));

    $mpdf->WriteHTML(
        '<h1 class="tituloPublicacao">' . $numeroAula . ' - ' . $titulo . '</h1>',
        HTMLParserMode::HTML_BODY
    );

    $texto = htmlspecialchars_decode((string)($value['texto'] ?? ''), ENT_QUOTES);
    $texto = removeEmojis($texto);
    // separador amigável
    $texto = str_replace('--break--', '<hr style="margin: 30px 0;">', $texto);

    // OBS.: se o Summernote gerar <ul> sem estilo, nosso CSS já cobre.
    $mpdf->WriteHTML($texto, HTMLParserMode::HTML_BODY);

    if ($i < $total - 1) {
        $mpdf->AddPage();
    }
}

// --- SAÍDA ---
$mpdf->SetTitle('PDF com Capa e Índice');

$nomeArquivo = 'modulo_'
    . onlyFilenameSafe($rwNome['nome'])
    . '_'
    . onlyFilenameSafe($rwNome['modulo'])
    . '.pdf';

$mpdf->Output($nomeArquivo, 'I'); // inline
