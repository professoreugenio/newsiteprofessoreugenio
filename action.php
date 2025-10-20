<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<?php
$addtime = 60 * 60 * 8;
$duracao = time() + $addtime;
?>
<?php
if (!empty($_GET['cursoexterno'])) {
  $dec = encrypt($_GET['cursoexterno'], $action = 'd');
  $exp = explode("&", $dec);
  $idcurso = $exp[1];
  $query = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codcursos = :id ");
  $query->bindParam(":id", $idcurso);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $idmdl = $rwNome['codigomodulos'];
  $var = "327&" . $idcurso . "&" . $idmdl . "&0" . "&0" . "&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('pagina_aulas.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['idpage'])) {
  $con = config::connect();
  $decPagina = encrypt($_GET['idpage'], $action = 'd');
  $query = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE codigopaginasadmin=:cod  ");
  $query->bindParam(":cod", $decPagina);
  $query->execute();
  $rwPage = $query->fetch(PDO::FETCH_ASSOC);
  $encPage = encrypt($decPagina, $action = 'e');
  $var = $rwPage['codigopaginasadmin'] . "&0&0&0&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = $rwPage['linkhome'] . "?v=" . $_GET['idpage'] . "&" . $ts;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['pcursos'])) {
  $con = config::connect();
  $decPagina = encrypt($_GET['pcursos'], $action = 'd');
  $tipo = $_GET['v'];
  $query = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE codigopaginasadmin=:cod  ");
  $query->bindParam(":cod", $decPagina);
  $query->execute();
  $rwPage = $query->fetch(PDO::FETCH_ASSOC);
  $encPage = encrypt($decPagina, $action = 'e');
  $var = $rwPage['codigopaginasadmin'] . "&0&0&0&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = "pagina_cursos.php" . "?v=" . $tipo;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['pcursoson'])) {
  $con = config::connect();
  $decPagina = encrypt($_GET['pcursoson'], $action = 'd');
  $query = $con->prepare("SELECT * FROM new_sistema_paginasadmin WHERE codigopaginasadmin=:cod  ");
  $query->bindParam(":cod", $decPagina);
  $query->execute();
  $rwPage = $query->fetch(PDO::FETCH_ASSOC);
  $encPage = encrypt($decPagina, $action = 'e');
  $var = $rwPage['codigopaginasadmin'] . "&0&0&0&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = "pagina_cursos_online.php" . "?v=" . $_GET['idpage'];
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['curso'])) {
  $con = config::connect();
  $dec = encrypt($_GET['curso'], $action = 'd');
  $exp = explode("&", $dec);
  $decPage = $exp[0];

  $decidpublic = $exp[2] ?? '0';
  if (!empty($exp[1])) {
    $decCurso = $exp[1];
  } else {
    $encPage = encrypt($exp[0], $action = 'e');
    $url = ('pagina_cursos.php?idpage=') . $encPage;
    echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
    exit();
  }
  $queryCatPJA = $con->prepare("SELECT nome,descricaosc,bgcolor,valorsc,pasta,codpagesadminsc,comercialsc FROM new_sistema_categorias_PJA WHERE codigocategorias = :cod  ");
  $queryCatPJA->bindParam(":cod", $decCurso);
  $queryCatPJA->execute();
  $rwPageCurso = $queryCatPJA->fetch(PDO::FETCH_ASSOC);
  $com = $rwPageCurso['comercialsc'];
  $var = $decPage . "&" . $decCurso . "&" . $decidpublic . "&0" . "&0" . "&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('pagina_modulos.php?var=') . $enc . "&ts=" . $ts;
  if ($com == '1') {
    $url = ('pagina_vendas.php?nav=') . $enc . "&ts=" . $ts;
  }
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['mdl'])) {
  $con = config::connect();
  $decModulo = encrypt($_GET['mdl'], $action = 'd');
  $exp = explode("&", $decModulo);
  $page = $exp[0];
  if (empty($exp[1])) {
    echo "Error: Course ID is missing.";
    exit();
  }
  $curso = $exp[1];
  $decModulo = $exp[2];
  $query = $con->prepare("SELECT codcursos,codigomodulos,visivelm, ordemm  FROM new_sistema_modulos_PJA WHERE codigomodulos = :id AND visivelm = '1' ORDER BY ordemm");
  $query->bindParam(":id", $decModulo);
  $query->execute();
  $rwModulo = $query->fetch(PDO::FETCH_ASSOC);
  $var = $exp[0] . "&" . $exp[1] . "&" . $decModulo . "&0" . "&0" . "&0";
  $enc = encrypt($var, $action = 'e');
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('pagina_aulas.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('">');
  exit();
}
if (!empty($_GET['pub'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  $dec = encrypt($_GET['pub'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $ordem = $rwNome['ordem'];
  $var = $exp[0] . "&" . $exp[1] . "&" . $exp[2] . "&" . $exp[3] . "&" . $exp[4];
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('view.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['pubativ'])) {
  $id = encrypt($_GET['pubativ'], $action = 'd');
  $dec = encrypt($_COOKIE['nav'], $action = 'd');
  $exp = explode("&", $dec);
  $var = $exp[0] . "&" . $exp[1] . "&" . $exp[2] . "&" . $exp[3] . "&" . $exp[4] . "&" . $id;
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('view_atividade.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['t'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  $dec = encrypt($_GET['t'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $var = $exp[0] . "&" . $exp[1] . "&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('aulao_photoshop_pro.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['mb'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  if (empty($_COOKIE['nav'])) {
    echo ('<meta http-equiv="refresh" content="0; url=https://professoreugenio.com/index.php">');
    exit();
  }
  $decnav = encrypt($_COOKIE['nav'], $action = 'd');
  $exp = explode("&", $decnav);
  $exp1 = $exp[0];
  $dect = encrypt($_GET['mb'], $action = 'd');
  $exp = explode("&", $dect);
  $exp2 = $exp[0];
  $exp3 = $exp[1];
  $var = $exp1 . "&" . $exp2 . "&" . $exp3 . "&0&0";
  $enc = encrypt($var, $action = 'e');
  $url = ('central_dados.php?var=') . $enc;
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  /*
  */
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['nmb'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  echo $dec = encrypt($_GET['nmb'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $var = $exp[0] . "&" . $exp[1] . "&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('membro_cadastro.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['renova'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  echo $dec = encrypt($_GET['renova'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $var = $exp[0] . "&" . $exp[1] . "&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('tornesemembro.php?var=') . $enc;
  // echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  // exit();
}
if (!empty($_GET['pc'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  echo $dec = encrypt($_GET['pc'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $var = $exp[0] . "&" . $exp[1] . "&0&0&0&0&0";
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('aulao_photoshop_pro_cadastro.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['ts'])) {
  // $dec = encrypt($_GET['pub'], $action = 'd');
  $dec = encrypt($_GET['ts'], $action = 'd');
  $exp = explode("&", $dec);
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
  $query->bindParam(":id", $exp[3]);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $ordem = $rwNome['ordem'];
  $var = $exp[0] . "&" . $exp[1] . "&" . $exp[2] . "&" . $exp[3] . "&" . $exp[4] . "&0&0";
  $enc = encrypt($var, $action = 'e');
  $addtime = 60 * 60 * 5;
  $duracao = time() + $addtime;
  setcookie('nav', $enc, time() + $duracao, '/');
  $url = ('cursos_online.php?var=') . $enc;
  echo ('<meta http-equiv="refresh" content="0; url=') . $url . ('&ts=') . $ts . ('">');
  exit();
}
if (!empty($_GET['ytube'])) {
  $dec = encrypt($_GET['ytube'], $action = 'd');
  $exp = explode("&", $dec);
  $iduser = $exp[1];
  $chaveyt = $exp[0];
  $con = config::connect();
  $query = $con->prepare("SELECT * FROM new_sistema_viewtube WHERE idusersv = :iduser AND chavesv=:chavesv ");
  $query->bindParam(":iduser", $iduser);
  $query->bindParam(":chavesv", $chaveyt);
  $query->execute();
  $rwVytube = $query->fetch(PDO::FETCH_ASSOC);
  if ($rwVytube) {
    $con = config::connect();
    $queryUpdate = $con->prepare("UPDATE new_sistema_viewtube SET datasv=:datasv, horasv=:horasv WHERE idusersv = :id");
    $queryUpdate->bindParam(":datasv", $data);
    $queryUpdate->bindParam(":horasv", $hora);
    $queryUpdate->bindParam(":id", $iduser);
    $queryUpdate->execute();
  } else {
    $con = config::connect();
    $queryInsert = $con->prepare("INSERT INTO new_sistema_viewtube (
  idusersv,
  chavesv,
  datasv,
  horasv
  )VALUES (
    :id,
    :chavesv,
    :datasv,
    :horasv
    )");
    $queryInsert->bindParam(":id", $iduser);
    $queryInsert->bindParam(":chavesv", $chaveyt);
    $queryInsert->bindParam(":datasv", $data);
    $queryInsert->bindParam(":horasv", $hora);
    $queryInsert->execute();
  }
  echo ('<meta http-equiv="refresh" content="0; url=https://www.youtube.com/watch?v=' . $chaveyt . '">');
  exit();
}
?>
<?php
if (!empty($_GET['ctlink'])) {
  echo $dec = encrypt($_GET['ctlink'], $action = 'd');
  $query = $con->prepare("SELECT * FROM a_anuncios WHERE codigopublicacoes  = :campo ");
  $query->bindParam(":campo", $dec);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $linkext = $rwNome['linkexterno'];
  $textowhatsapp = $rwNome['textowhatsapp'];
  $celular = $rwNome['celular'];
  // Remove unwanted characters from the phone number
  $celular = str_replace(['-', '(', ')', ' ', '.'], '', $celular);
  $count = "1";
  $queryInsert = $con->prepare("INSERT INTO a_anuncios_count 
    (idanuncios,count,ipcount,datacount,horacount)
    VALUES 
    (:idanuncios,:count,:ipcount,:datacount,:horacount)");
  $queryInsert->bindParam(":idanuncios", $dec);
  $queryInsert->bindParam(":count", $count);
  $queryInsert->bindParam(":ipcount", $ip);
  $queryInsert->bindParam(":datacount", $data);
  $queryInsert->bindParam(":horacount", $hora);
  $queryInsert->execute();
  if ($queryInsert->rowCount() >= 1) {
    echo '1';
  } else {
    echo '2';
  }
  if (!empty($textowhatsapp)) {
    $link = "https://wa.me/55" . $celular . "?text=" . $textowhatsapp;
    echo ('<meta http-equiv="refresh" content="0; url=') . $link . ('">');
    exit();
  } else {
    if (!empty($linkext)) {
      echo ('<meta http-equiv="refresh" content="0; url=') . $linkext . ('">');
      exit();
    }
  }
}
?>