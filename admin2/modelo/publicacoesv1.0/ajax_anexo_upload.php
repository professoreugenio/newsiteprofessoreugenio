<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$codpublicacao = intval($_POST['codpublicacao'] ?? 0);
$idmodulo = intval($_POST['idmodulo'] ?? 0);
$titulo = trim($_POST['titulopa'] ?? '');
$pastapub = trim($_POST['pastapub'] ?? '');
$visivel = 1;
$data = date('Y-m-d');
$hora = date('H:i:s');

if ($codpublicacao <= 0 || $idmodulo <= 0 || !$titulo || empty($_FILES['arquivo']['name'])) {
    echo json_encode(['sucesso' => false, 'msg' => 'Dados insuficientes']);
    exit;
}

$allow = ['xlsx', 'docx', 'pdf', 'doc', 'xls', 'pptx', 'ppsx', 'ppt', 'pps', 'txt', 'otf', 'ttf', 'jpg', 'png', 'jpeg', 'psd', 'cdr', 'eps', 'ai', 'html', 'php', 'js', 'rar', 'zip', 'pbix', 'bat', 'json'];

$dir0 = "../../../anexos";
$dir1 = $dir0 . "/publicacoes";
$uploadDir = $dir1 . "/" . $pastapub;

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

$arquivo_name = $_FILES['arquivo']['name'];
$arquivo_tmp  = $_FILES['arquivo']['tmp_name'];
$arquivo_size = intval($_FILES['arquivo']['size']);
$extension = strtolower(pathinfo($arquivo_name, PATHINFO_EXTENSION));

if (!in_array($extension, $allow)) {
    echo json_encode(['sucesso' => false, 'msg' => 'Extensão não permitida']);
    exit;
}

$newName = uniqid('ax_', true) . "." . $extension;
$dest = $uploadDir . "/" . $newName;

if (!move_uploaded_file($arquivo_tmp, $dest)) {
    echo json_encode(['sucesso' => false, 'msg' => 'Falha no upload']);
    exit;
}

$num = 0; // se você usa contagem/ordem de imagem, ajuste aqui

$query = $con->prepare("INSERT INTO new_sistema_publicacoes_anexos_PJA
 (codpublicacao,idmodulo_pa,anexopa,numimg,titulopa,visivel,extpa,sizepa,pastapa,datapa,horapa)
 VALUES (:codpublicacao,:idmodulo,:arquivo,:numimg,:titulopa,:visivel,:ext,:size,:pasta,:datapf,:horapf)");

$query->bindParam(":codpublicacao", $codpublicacao);
$query->bindParam(":idmodulo", $idmodulo);
$query->bindParam(":arquivo", $newName);
$query->bindParam(":numimg", $num);
$query->bindParam(":titulopa", $titulo);
$query->bindParam(":visivel", $visivel);
$query->bindParam(":ext", $extension);
$query->bindParam(":size", $arquivo_size);
$query->bindParam(":pasta", $pastapub);
$query->bindParam(":datapf", $data);
$query->bindParam(":horapf", $hora);

$ok = $query->execute();
echo json_encode(['sucesso' => $ok]);
