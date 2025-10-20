<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

require_once APP_ROOT . '/conexao/class.conexao.php'; // caso ainda não tenha sido incluído
require_once APP_ROOT . '/autenticacao.php'; // idem

$idCurso = encrypt($_GET['id'], $action = 'd');

// Verifica se é um número válido após a descriptografia
if (!is_numeric($idCurso)) {
    header('Location: index.php');
    exit;
}

$queryCatEdit = $con->prepare("SELECT * FROM new_sistema_cursos WHERE codigocursos = :idcurso");
$queryCatEdit->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
$queryCatEdit->execute();
$rwCurso = $queryCatEdit->fetch(PDO::FETCH_ASSOC);

// Se o curso não foi encontrado, redireciona
if (!$rwCurso) {
    header('Location: index.php');
    exit;
}

// Atribuição segura dos valores
$Bocolor = $rwCurso['bgcolor'];
$Pasta = $rwCurso['pasta'];
$Nomecurso = $rwCurso['nome'];
$Descricao = $rwCurso['descricaosc'];
$Videoyoutube = $rwCurso['youtubeurl'];
$urlterno = $rwCurso['linkexterno'];
$lead = $rwCurso['lead'] ?? '';
$detalhes = $rwCurso['detalhes'] ?? '';
$sobreocurso = $rwCurso['sobreocurso'] ?? '';
///* VENDAS *///
$hero = $rwCurso['heroSC'] ?? '';
$beneficios = $rwCurso['beneficiosSC'] ?? '';
$sobre = $rwCurso['sobreSC'] ?? '';
$cta = $rwCurso['ctaSC'] ?? '';
$chavecurso = $rwCurso['pasta'] ?? '';
$matriz = $rwCurso['matriz'] ?? '0';
$comercial     = ($rwCurso['comercialsc'] == '1') ? "1" : "0";

/** para checkbox */
$chkon   = ($rwCurso['onlinesc'] == '1') ? "checked" : "";
$com     = ($rwCurso['comercialsc'] == '1') ? "checked" : "";
$inst     = ($rwCurso['institucionalsc'] == '1') ? "checked" : "";
$chkv    = ($rwCurso['visivelsc'] == '1') ? "checked" : "";
$chkvh   = ($rwCurso['visivelhomesc'] == '1') ? "checked" : "";
$mtzon   = ($rwCurso['matriz'] == '1') ? "checked" : "";


if (empty($filtro)): $filtro = '0';
endif;
