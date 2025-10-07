

<?php
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    require 'config_redesocial/query_cookieStartUsuario.php';
    require 'config_redesocial/query_usuarioAdmin.php';
    require 'config_redesocial/query_fotoAdmin.php';
    require 'config_default1.0/query_turma.php';
} else if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    require 'config_redesocial/query_cookieStartUsuario.php';
    require 'config_redesocial/query_usuario.php';
    require 'config_redesocial/query_fotouser.php';
    require 'config_default1.0/query_turma.php';
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}
// 5) Permissões do aluno
$aut1 = $aut2 = $aut3 = $aut4 = $aut5 = $aut6 = $aut7 = $aut8 = "0";
$mascote = "1";

$mascote = "1";
$expUser = explode("&", $decUser);
$idUser =  $expUser['0'];
$encIdUser = encrypt($idUser, $action = 'e');
$idTurma = "";
$chaveturmaUser = "";
$modulo = "";
if (!empty($expUser['4'])) {
    $idTurma = $expUser['4'] ?? '0';
    $chaveturmaUser = $expUser['5'] ?? '';
    $modulo = $expUser['6'] ?? '';
}
$query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:id ");
$query->bindParam(":id", $idUser);
$query->execute();
$rwUser = $query->fetch(PDO::FETCH_ASSOC);
if ($rwUser) {
    $nmUser =  nome($rwUser['nome'], $n = "2");
    $codigoUser = $rwUser['codigocadastro'];
    $pasta = $rwUser['pastasc'] ?? '';
    $mascote = $rwUser['mascote'] ?? '';
    $dataaniversario = $rwUser['datanascimento_sc'] ?? '';

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

    $aut1 = $rwConsulta['autorize1'] ?? "0";
    $aut2 = $rwConsulta['autorize2'] ?? "0";
    $aut3 = $rwConsulta['autorize3'] ?? "0";
    $aut4 = $rwConsulta['autorize4'] ?? "0";
    $aut5 = $rwConsulta['autorize5'] ?? "0";
    $aut6 = $rwConsulta['autorize6'] ?? "0";
    $aut7 = $rwConsulta['autorize7'] ?? "0";
    $aut8 = $rwConsulta['autorize8'] ?? "0";
}


$comercialDados = !empty($expUser[8]) ? $expUser[8] : '0';

/**aniversário */

// Data de hoje no formato mês-dia
$dataHoje = date('m-d');

$sql = "
    SELECT 
        c.nome AS nome_aluno, 
        t.nometurma AS nome_turma
    FROM new_sistema_cadastro c
    INNER JOIN new_sistema_inscricao_PJA i 
        ON i.codigousuario = c.codigocadastro
    INNER JOIN new_sistema_cursos_turmas t 
        ON t.chave = i.chaveturma
    WHERE DATE_FORMAT(c.datanascimento_sc, '%m-%d') = :dataHoje AND c.codigocadastro = :idusuario AND t.chave = :chaveturma
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':dataHoje', $dataHoje);
$stmt->bindParam(':idusuario', $codigoUser);
$stmt->bindParam(':chaveturma', $chaveturmaUser);
$stmt->execute();

$aniversariantes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$sqlAviso = "
    SELECT 
        c.nome AS nome_aluno, 
        t.nometurma AS nome_turma
    FROM new_sistema_cadastro c
    INNER JOIN new_sistema_inscricao_PJA i 
        ON i.codigousuario = c.codigocadastro
    INNER JOIN new_sistema_cursos_turmas t 
        ON t.chave = i.chaveturma
    WHERE DATE_FORMAT(c.datanascimento_sc, '%m-%d') = :dataHoje AND c.codigocadastro != :idusuario AND t.chave = :chaveturma
";

$stmtAviso = $con->prepare($sqlAviso);
$stmtAviso->bindParam(':dataHoje', $dataHoje);
$stmtAviso->bindParam(':idusuario', $codigoUser);
$stmtAviso->bindParam(':chaveturma', $chaveturmaUser);
$stmtAviso->execute();

$AvisoAniversariante = $stmtAviso->fetchAll(PDO::FETCH_ASSOC);
