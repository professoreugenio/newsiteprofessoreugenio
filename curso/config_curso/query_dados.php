<?php
// Verifica qual cookie está disponível e descriptografa
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], 'd');
} elseif (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], 'd');
} else {
    // Redireciona para o index se nenhum cookie estiver disponível
    header("Location: ../index.php");
    exit();
}
// Separa os dados do usuário descriptografado
$expUser = explode("&", $decUser);
if (count($expUser) >= 4) {
    $idUser = $expUser[0] ?? '';
    $idTurma = $expUser[4] ?? '';
    $chaveturmaUser = $expUser[5] ?? '';
} else {
    // Trate o erro conforme o contexto: log, redirecionamento, exceção, etc.
    echo "Erro: dados do usuário incompletos.";
    exit;
}
/** usuário Admin */
$query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario = :id");
$query->bindParam(":id", $idUser);
$query->execute();
$rwUser = $query->fetch(PDO::FETCH_ASSOC);
if ($rwUser) {
    $codigoUser = $rwUser['codigousuario'];
    $foto = !empty($rwUser['imagem200']) ? $rwUser['imagem200'] : $rwUser['imagem50'];
    if ($foto != "usuario.jpg") {
        $pasta = isset($rwUser['pastasu']) ? $rwUser['pastasu'] : '';
        $expimg = explode(".", $foto); // não está sendo usado, mas mantido se precisar do tipo
        $imgUser = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
    } else {
        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    }
    $nmUser = nome($rwUser['nome'], $n = "2");
    $mdcad = md5($rwUser['codigousuario']);
} else {
    $query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:id ");
    $query->bindParam(":id", $idUser);
    $query->execute();
    $rwUser = $query->fetch(PDO::FETCH_ASSOC);
    if (isset($rwUser) && is_array($rwUser)) {
        $codigoUser = $rwUser['codigocadastro'];
        $foto = !empty($rwUser['imagem200']) ? $rwUser['imagem200'] : $rwUser['imagem50'];
        if ($foto != "usuario.jpg") {
            $pasta = isset($rwUser['pastasc']) ? $rwUser['pastasc'] : '';
            $expimg = explode(".", $foto); // não está sendo usado, mas mantido se precisar do tipo
            $imgUser = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
        } else {
            $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
        }
    }
    $nmUser = nome($rwUser['nome'], $n = "2");
    $mdcad = md5($rwUser['codigocadastro']);
}
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
} else if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');

    $queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idsubcat");
    $queryTurma->bindParam(":idsubcat", $idTurma);
    $queryTurma->execute();
    $rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);
    if ($rwTurma) {
        $nomeTurma    = $rwTurma['nometurma'] ?? '';
        $chaveTurma   = $rwTurma['chave'] ?? '';
        $idCurso      = $rwTurma['codcursost'] ?? '';
        $comercial    = $rwTurma['comercialt'] ?? '';
        $datainicio   = $rwTurma['datainiciost'] ?? '';
        $datafim      = $rwTurma['datafimst'] ?? '';
        $tipocurso    = $rwTurma['tipocurso'] ?? '';
        $horainicio   = $rwTurma['horainiciost'] ?? '';
        $horafim      = $rwTurma['horafimst'] ?? '';
        $cargahoraria = $rwTurma['cargahorariasct'] ?? '';
        $aulas        = $rwTurma['aulasst'] ?? '';
        $lkwhats      = $rwTurma['linkwhatsapp'] ?? '';
    } else {
        // Tratar o caso de turma não encontrada

        // Ou registrar o erro
        // error_log("Turma com ID $idTurma não encontrada.");
    }
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}
$aut1 = "1";
$aut2 = "1";
$aut3 = "1";
$aut4 = "1";
$mascote = "1";

$modulo = "";
/** PERMISSÕES */
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

/**TURMA */
$queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idsubcat");
$queryTurma->bindParam(":idsubcat", $idTurma);
$queryTurma->execute();

$rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);

if ($rwTurma) {
    $nomeTurma    = $rwTurma['nometurma'] ?? '';
    $chaveTurma   = $rwTurma['chave'] ?? '';
    $idCurso      = $rwTurma['codcursost'] ?? '';
    $comercial    = $rwTurma['comercialt'] ?? '';
    $datainicio   = $rwTurma['datainiciost'] ?? '';
    $datafim      = $rwTurma['datafimst'] ?? '';
    $tipocurso    = $rwTurma['tipocurso'] ?? '';
    $horainicio   = $rwTurma['horainiciost'] ?? '';
    $horafim      = $rwTurma['horafimst'] ?? '';
    $cargahoraria = $rwTurma['cargahorariasct'] ?? '';
    $aulas        = $rwTurma['aulasst'] ?? '';
    $lkwhats      = $rwTurma['linkwhatsapp'] ?? '';
} else {
    // Tratar o caso de turma não encontrada

    // Ou registrar o erro
    // error_log("Turma com ID $idTurma não encontrada.");
}
$aut1 = "1";
$aut2 = "1";
$aut3 = "1";
$aut4 = "1";
$mascote = "1";
$expUser = explode("&", $decUser);
$codigoUsuario =  $expUser['0'] ?? '';
$codigoTumra = $expUser['4'] ?? '';

/** DADOS DO CURSO */
if (isset($_COOKIE['nav'])) {
    $decNavdados = encrypt($_COOKIE['nav'], $action = 'd');
    $expNavDados = explode("&", $decNavdados);
    $valida = "No ok";
    if (count($expNavDados) >= 4) {

        $idPagina = (isset($expNavDados[0]) && !empty($expNavDados[0])) ? $expNavDados[0] : 0;
        $idCurso = (isset($expNavDados[1]) && !empty($expNavDados[1])) ? $expNavDados[1] : 0;
        $idTurma = (isset($expNavDados[2]) && !empty($expNavDados[2])) ? $expNavDados[2] : 0;
        $idModulo = (isset($expNavDados[3]) && !empty($expNavDados[3])) ? $expNavDados[3] : 0;
        $startModo = (isset($expNavDados[6]) && !empty($expNavDados[6])) ? $expNavDados[6] : 0;
        $valida = "ok";
    } else {
        // Tratar erro, logar ou redirecionar
        // die("Dados inválidos ou corrompidos.");
        $idPagina = 0;
        $idCurso = 0;
        $idTurma = 0;
        $idModulo = 0;
        $startModo = 0;
        $valida = "no ok";
    }
}
