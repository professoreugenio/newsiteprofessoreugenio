<?php
define('BASEPATH', true);
require_once __DIR__ . '/vendor/autoload.php';
require_once '../conexao/class.conexao.php';
require_once '../autenticacao.php';

if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
} else if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}

$aut1 = "1";
$aut2 = "1";
$aut3 = "1";
$aut4 = "1";
$mascote = "1";
$expUser = explode("&", $decUser);
$idUser =  $expUser['0'];
$idTurma = "";
$chaveturmaUser = "";
$modulo = "";
if (!empty($expUser['4'])) {
    $idTurma = $expUser['4'];
    $chaveturmaUser = $expUser['5'];
    $modulo = $expUser['6'];
}
$query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:id ");
$query->bindParam(":id", $idUser);
$query->execute();
$rwUser = $query->fetch(PDO::FETCH_ASSOC);
if ($rwUser) {
    $nmUser =  nome($rwUser['nome'], $n = "2");
    $codigoUser = $rwUser['codigocadastro'];
    $pasta = $rwUser['pastasc'];
    $mascote = $rwUser['mascote'];

    $fotoUser = $rwUser['imagem50'];
    $nmUser =  nome($rwUser['nome'], $n = "2");
    $mdcad = md5($rwUser['codigocadastro']);
    $imgUser = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $fotoUser;
    if ($fotoUser == "usuario.jpg") {
        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    }
} else {
    $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario=:id ");
    $query->bindParam(":id", $idUser);
    $query->execute();
    $rwUser = $query->fetch(PDO::FETCH_ASSOC);
    $codigoUser = $rwUser['codigousuario'];
    $pastaAdm = $rwUser['pastasu'];
    $fotoAdm = $rwUser['imagem200'];
    $nmUser =  nome($rwUser['nome'], $n = "2");
    $mdcad = md5($rwUser['codigousuario']);
    $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    if ($fotoAdm != "usuario.jpg") {
        $imgUser = $raizSite . "/fotos/usuarios/" . $pastaAdm . "/" . $fotoAdm;
    }
}

$query = $con->prepare("SELECT * FROM a_aluno_permissoes WHERE idalunop = :idaluno ");
$query->bindParam(":idaluno", $codigoUser);
$query->execute();
$rwConsulta = $query->fetch(PDO::FETCH_ASSOC);
if (!$rwConsulta) {
    $con = config::connect();
    $queryInsert = $con->prepare("INSERT 
INTO a_aluno_permissoes (idalunop,datap,horap)
VALUES (:idaluno,:datap,:horap)");
    $queryInsert->bindParam(":datap", $data);
    $queryInsert->bindParam(":horap", $hora);
    $queryInsert->bindParam(":idaluno", $codigoUser);
    $queryInsert->execute();
} else {

    $aut1 = $rwConsulta['autorize1'];
    $aut2 = $rwConsulta['autorize2'];
    $aut3 = $rwConsulta['autorize3'];
    $aut4 = $rwConsulta['autorize4'];
    $aut5 = $rwConsulta['autorize5'];
    $aut6 = $rwConsulta['autorize6'];
    $aut7 = $rwConsulta['autorize7'];
    $aut8 = $rwConsulta['autorize8'];
}

$queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idsubcat");
$queryTurma->bindParam(":idsubcat", $idTurma);
$queryTurma->execute();
$rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);

$nomeTurma     = $rwTurma['nometurma'] ?? '';
$chaveTurma    = $rwTurma['chave'] ?? '';
$idCurso       = $rwTurma['codcursost'] ?? '';
$comercial     = $rwTurma['comercialt'] ?? '';
$datainicio    = $rwTurma['datainiciost'] ?? '';
$datafim       = $rwTurma['datafimst'] ?? '';
$tipocurso     = $rwTurma['tipocurso'] ?? '';
$horainicio    = $rwTurma['horainiciost'] ?? '';
$horafim       = $rwTurma['horafimst'] ?? '';
$cargahoraria  = $rwTurma['cargahorariasct'] ?? '';
$aulas         = $rwTurma['aulasst'] ?? '';
$lkwhats       = $rwTurma['linkwhatsapp'] ?? '';
$descricao     = $rwTurma['texto'] ?? '';

function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ====== DADOS DINÂMICOS (podem vir de GET/POST/DB) ====== */
$nome       = $nmUser ?? 'Aluno Exemplo';
$curso      = $nomeTurma  ?? $nomeTurma;
$carga      = $cargahoraria  ?? '100 horas';
$cidade     = 'Maracanaú-CE';
$dataBR     = $_GET['data']   ?? date('d/m/Y');
$codigo     = $_GET['codigo'] ?? strtoupper(substr(md5(uniqid('', true)), 0, 10));
$professor  = 'Professor Eugênio'   ?? 'Professor Eugênio';
$logoUrl    = $_GET['logo']   ?? 'https://professoreugenio.com/img/logo.png';
$validaUrl  = $_GET['valida'] ?? ('https://professoreugenio.com/validar?codigo=' . $codigo);


/* ====== CSS (tela, impressão e PDF) ====== */
$css = '
@page { size: A4; margin: 12mm; }
body { background:#0d1b2a; margin:0; font-family:"DejaVu Serif", Georgia, "Times New Roman", serif; }
.preview { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
.certificate { position:relative; width:210mm; max-width:100%; aspect-ratio:1/0.707; background:#fff; color:#111;
  border:14px double #00BB9C; box-shadow:0 10px 30px rgba(0,0,0,.25); padding:24mm; overflow:hidden; }
.certificate:before { content:""; position:absolute; inset:12mm; border:1.5mm solid #FF9C00; opacity:.6; pointer-events:none; }
.brand-ribbon { position:absolute; top:0; left:0; right:0; height:10mm; background:linear-gradient(90deg,#00BB9C,#00c7a9); }
.header { display:flex; align-items:center; gap:10mm; margin-top:12mm; }
.header img.logo { height:24mm; }
.title { text-align:center; letter-spacing:.15em; font-weight:700; font-size:22pt; color:#112240; margin:8mm 0 2mm; }
.subtitle { text-align:center; font-size:12pt; color:#4b5563; margin-bottom:10mm; }
.body { font-size:12pt; line-height:1.6; text-align:justify; }
.body .aluno { display:block; margin:6mm 0 2mm; font-size:20pt; font-weight:700; text-align:center; color:#112240; }
.meta { display:flex; gap:10mm; margin:10mm 0 12mm; }
.meta .card { flex:1; border:1px solid #e5e7eb; border-radius:8px; padding:6mm; }
.signatures { display:flex; gap:12mm; align-items:end; margin-top:10mm; }
.sign { flex:1; text-align:center; }
.sign .line { border-top:1px solid #111; margin-top:18mm; }
.qrbox { position:absolute; right:18mm; bottom:18mm; text-align:center; font-size:9pt; color:#374151; }
.validate { font-size:9pt; color:#374151; margin-top:2mm; word-break:break-all; }
.badge { position:absolute; left:-22mm; top:50%; transform:rotate(-90deg) translateY(-50%); transform-origin:left center;
  background:#112240; color:#fff; padding:4mm 8mm; letter-spacing:.2em; font-weight:700; }
.footer { position:absolute; left:24mm; right:24mm; bottom:12mm; display:flex; justify-content:space-between; font-size:9pt; color:#6b7280; }
.controls { position:fixed; inset:16px auto auto 16px; z-index:1000; display:flex; gap:8px; }
.btn { display:inline-block; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial; font-size:14px; padding:10px 14px; border-radius:10px; border:1px solid #d1d5db; background:#fff; cursor:pointer; }
.btn-primary{ background:#00BB9C; color:#fff; border-color:#00a88d; }
.btn-dark{ background:#112240; color:#fff; border-color:#0c1a30; }
@media print { body{ background:#fff; } .controls{ display:none !important; } .certificate{ box-shadow:none; } }
';

/* ====== HTML do certificado (usado na tela e no PDF) ====== */
$html = '
<div class="certificate">
  <div class="brand-ribbon"></div>
  <div class="badge">CERTIFICADO</div>

  <div class="header">
    <img class="logo" src="' . e($logoUrl) . '" alt="Logo">
    <div style="flex:1">
      <h1 class="title">CERTIFICADO DE CONCLUSÃO</h1>
      <div class="subtitle">Certificado nº ' . e($codigo) . '</div>
    </div>
  </div>

  <div class="body">
    <p>Certificamos que</p>
    <span class="aluno">' . e($nome) . '</span>
    <p>concluiu com êxito o <strong>' . e($curso) . '</strong>, totalizando <strong>' . e($carga) . '</strong>, demonstrando domínio dos conteúdos de informática e práticas aplicadas.</p>

    <div class="meta">
      <div class="card">
        <div><strong>Local e Data</strong></div>
        <div>' . e($cidade) . ', ' . e($dataBR) . '</div>
      </div>
      <div class="card">
        <div><strong>Código de Verificação</strong></div>
        <div>' . e($codigo) . '</div>
      </div>
      <div class="card">
        <div><strong>Plataforma</strong></div>
        <div>professoreugenio.com</div>
      </div>
    </div>

    <div class="signatures">
      <div class="sign">
        <div class="line"></div>
        <div><strong>' . e($professor) . '</strong><br>Instrutor Responsável</div>
      </div>
      <div class="sign">
        <div class="line"></div>
        <div><strong>Coordenação</strong><br>professoreugenio.com</div>
      </div>
    </div>
  </div>

  <div class="qrbox">
    // <img src="' . e($qrUrl) . '" width="120" height="120" alt="QR Code">
    // <div class="validate">Verificação: ' . e($validaUrl) . '</div>
  </div>

  <div class="footer">
    <div>Documento gerado eletronicamente. Válido com o código de verificação.</div>
    <div>&copy; ' . date('Y') . ' Professor Eugênio</div>
  </div>
</div>
';

/* ====== Se for PDF, renderiza via mPDF ====== */
if (isset($_GET['pdf'])) {
    require __DIR__ . '/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_left'   => 12,
        'margin_right'  => 12,
        'margin_top'    => 12,
        'margin_bottom' => 12
    ]);
    $mpdf->SetTitle('Certificado - ' . $nome);
    $mpdf->SetAuthor('Professor Eugênio');
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->SetWatermarkText('Professor Eugênio');
    $mpdf->showWatermarkText = true;
    $mpdf->watermarkTextAlpha = 0.1;

    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    $filename = 'Certificado - ' . $nome . '.pdf';
    $dest = (isset($_GET['download']) ? 'D' : 'I'); // ?pdf=1&download=1 para baixar
    $mpdf->Output($filename, $dest);
    exit;
}

/* ====== Pré-visualização em tela (com botões) ====== */
$qs = $_GET;
unset($qs['pdf'], $qs['download']);
$queryString = http_build_query($qs);
$hrefPdf      = '?pdf=1' . ($queryString ? '&' . $queryString : '');
$hrefPdfDown  = '?pdf=1&download=1' . ($queryString ? '&' . $queryString : '');
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Certificado — Pré-visualização</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        <?php echo $css; ?>
    </style>
</head>

<body>
    <div class="controls">
        <a class="btn btn-primary" href="<?php echo e($hrefPdf); ?>">Gerar PDF</a>
        <a class="btn btn-dark" href="<?php echo e($hrefPdfDown); ?>">Baixar PDF</a>
       
        <button class="btn" onclick="window.print()">Imprimir</button>
    </div>
    <div class="preview">
        <?php echo $html; ?>
    </div>
</body>

</html>