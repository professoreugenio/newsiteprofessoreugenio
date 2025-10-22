<?php
// Helpers básicos
if (!function_exists('h')) {
  function h($s)
  {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
  }
}
if (!function_exists('toInt')) {
  function toInt($v)
  {
    return (int)filter_var($v, FILTER_VALIDATE_INT) ?: 0;
  }
}

// Garanta conexão ($con) e variáveis base
try {
  if (!isset($con) || !$con instanceof PDO) {
    $con = config::connect();
  }
} catch (Throwable $e) { /* trate seu logger aqui se quiser */
}

$raizSite = $raizSite ?? '';
$pg       = isset($pg) ? (int)$pg : 0;
$ts       = $ts ?? (string)time();

// Dados padrão
$codigoUser = 0;
$nomeUser   = 'Visitante';
$img        = $raizSite . '/fotos/usuarios/usuario.jpg';
$tag        = '';
$nomeTurma  = 'Não definido';
$chaveTurma = '';
$idTurma    = 0;
$idcurso    = 0;

// Funções de carga
function loadUsuarioAdmin(PDO $con, int $id): array
{
  $q = $con->prepare("SELECT nome, imagem200, pastasu FROM new_sistema_usuario WHERE codigousuario = :id LIMIT 1");
  $q->bindValue(':id', $id, PDO::PARAM_INT);
  $q->execute();
  return $q->fetch(PDO::FETCH_ASSOC) ?: [];
}
function loadUsuarioAluno(PDO $con, int $id): array
{
  $q = $con->prepare("SELECT nome, imagem200, pastasc FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
  $q->bindValue(':id', $id, PDO::PARAM_INT);
  $q->execute();
  return $q->fetch(PDO::FETCH_ASSOC) ?: [];
}
function loadTurma(PDO $con, int $id): array
{
  $q = $con->prepare("SELECT nometurma, `chave`, codcursost FROM new_sistema_cursos_turmas WHERE codigoturma = :id LIMIT 1");
  $q->bindValue(':id', $id, PDO::PARAM_INT);
  $q->execute();
  return $q->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Monta imagem segura
function buildUserImg(string $raizSite, ?string $pasta, ?string $foto): string
{
  $foto = $foto ?: 'usuario.jpg';
  if ($foto === 'usuario.jpg') {
    return rtrim($raizSite, '/') . '/fotos/usuarios/usuario.jpg';
  }
  $pasta = $pasta ?: '';
  return rtrim($raizSite, '/') . '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($foto);
}

// Detecta contexto (admin vs aluno) e carrega dados
try {
  if (!empty($_COOKIE['adminstart'])) {
    $dec = encrypt($_COOKIE['adminstart'], $action = 'd');
    $exp = explode('&', (string)$dec);
    // Esperado: [0]=id, ... [4]=idTurma, [5]=chaveTurma
    if (count($exp) >= 1) {
      $codigoUser = toInt($exp[0]);
      $tag = '<span style="color:black">Prof: </span>';
      $nmc = 'sc';

      $u = loadUsuarioAdmin($con, $codigoUser);
      if ($u) {
        $nomeUser = $u['nome'] ?? $nomeUser;
        $img      = buildUserImg($raizSite, $u['pastasu'] ?? null, $u['imagem200'] ?? null);
      }

      if (count($exp) >= 6) {
        $idTurma = toInt($exp[4]);
        $turma   = loadTurma($con, $idTurma);
        if ($turma) {
          $nomeTurma = $turma['titulocatsub'] ?? $nomeTurma;
          $chaveTurma = $turma['chave'] ?? '';
          $idcurso   = toInt($turma['codcursost'] ?? 0);
        }
      }

      // Cabeçalho extra do admin (se necessário)
      @require 'modulos/headusuariostart.php';
    }
  } elseif (!empty($_COOKIE['startusuario'])) {
    $dec = encrypt($_COOKIE['startusuario'], $action = 'd');
    $exp = explode('&', (string)$dec);
    if (count($exp) < 5 || empty($exp[4])) {
      echo '<meta http-equiv="refresh" content="0; url=redesocial/turma.php">';
      exit;
    }

    $codigoUser = toInt($exp[0]);
    $idTurma    = toInt($exp[4]);
    $chaveTurma = (count($exp) >= 6) ? (string)$exp[5] : '';

    $turma = loadTurma($con, $idTurma);
    if ($turma) {
      $nomeTurma = $turma['titulocatsub'] ?? $nomeTurma;
      $chaveTurma = $turma['chave'] ?? $chaveTurma;
      $idcurso   = toInt($turma['codcursost'] ?? 0);
    }

    $u = loadUsuarioAluno($con, $codigoUser);
    if ($u) {
      $nomeUser = $u['nome'] ?? $nomeUser;
      $img      = buildUserImg($raizSite, $u['pastasc'] ?? null, $u['imagem200'] ?? null);
    }
  }
} catch (Throwable $e) {
  // silencioso por enquanto; opcionalmente logar
}
?>

<nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
  <div class="logo">
    <?php if (!empty($_COOKIE['adminstart']) || !empty($_COOKIE['startusuario'])): ?>
      <div class="d-flex align-items-center gap-2" id="userhead">
        <a href="redesocial_turmas/">
          <div class="fotouserNav" style="width:40px; height:40px; border-radius:50%; background-size:cover; background-position:center; background-image:url('<?= h($img) ?>');"></div>
        </a>
        <a href="redesocial_turmas/" class="text-decoration-none">
          <div class="userdados">
            <div class="NomeUser">
              <?= $tag /* intencionalmente HTML */ ?> <?= h(nome($nome = $nomeUser, $n = "2")) ?>
            </div>
            <div class="nomeTurma"><?= h($nomeTurma) ?></div>
            <div class="nomesala"></div>
          </div>
        </a>
        <div id="alertapopupmsg"></div>
      </div>
    <?php else: ?>
      <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <h2 class="m-0 text-primary">
          <img src="img/logo.png" width="160" alt="Professor Eugênio">
        </h2>
      </a>
    <?php endif; ?>
  </div>

  <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Alternar navegação">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarCollapse">
    <div class="navbar-nav ms-auto p-4 p-lg-0">
      <?php
      try {
        $home = '1';
        $stmt = $con->prepare("SELECT codigopaginasadmin, nomepaginapa, ordemsp FROM new_sistema_paginasadmin WHERE home = :home ORDER BY ordemsp");
        $stmt->bindValue(':home', $home, PDO::PARAM_STR);
        $stmt->execute();
        $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($paginas as $idx => $row) {
          $idpgadmin = toInt($row['codigopaginasadmin'] ?? 0);
          $nomePagina = (string)($row['nomepaginapa'] ?? '');
          $ord       = $idx + 1;
          $active    = ($ord === $pg) ? 'active' : '';
          $urlEnc    = encrypt((string)$idpgadmin, $action = 'e');

          if ($nomePagina === 'CURSOS') {
            echo '<div class="nav-item dropdown">';
            echo '<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">CURSOS</a>';
            echo '<div class="dropdown-menu fade-down m-0">';

            // Bloco COMERCIAL = 1
            $stmtc = $con->prepare("SELECT comercialsc, nome, codigocategorias, externosc, codpagesadminsc, pasta 
                                    FROM new_sistema_categorias_PJA 
                                    WHERE codpagesadminsc = :idpg AND visivelhomesc = :vh AND comercialsc = :com 
                                    ORDER BY ordemsc");
            $vh = '1';
            $com = '1';
            $stmtc->bindValue(':idpg', $idpgadmin, PDO::PARAM_INT);
            $stmtc->bindValue(':vh', $vh, PDO::PARAM_STR);
            $stmtc->bindValue(':com', $com, PDO::PARAM_STR);
            $stmtc->execute();
            $catsCom = $stmtc->fetchAll(PDO::FETCH_ASSOC);

            foreach ($catsCom as $cat) {
              $pasta   = (string)($cat['pasta'] ?? '');
              $tipo    = '1';
              $stmtImg = $con->prepare("SELECT codigomidiasfotos FROM new_sistema_midias_fotos_PJA WHERE pasta = :pasta AND tipo = :tipo LIMIT 1");
              $stmtImg->bindValue(':pasta', $pasta, PDO::PARAM_STR);
              $stmtImg->bindValue(':tipo', $tipo, PDO::PARAM_STR);
              $stmtImg->execute();
              $rwImagem = $stmtImg->fetch(PDO::FETCH_ASSOC) ?: [];
              $idpublic = toInt($rwImagem['codigomidiasfotos'] ?? 0);

              $enc = encrypt(
                (string)($cat['codpagesadminsc'] ?? '') . '&' .
                  (string)($cat['codigocategorias'] ?? '') . '&' .
                  (string)$idpublic,
                $action = 'e'
              );

              $externo = toInt($cat['externosc'] ?? 0);
              $link = $externo === 1 ? 'action.php?cursoexterno=' . h($enc) : 'action.php?curso=' . h($enc);

              echo '<a href="' . $link . '" class="dropdown-item">' . h((string)$cat['nome']) . '</a>';
            }

            // Separador
            echo '<a class="dropdown-item disabled">---------</a>';

            // Bloco COMERCIAL = 0 (livre)
            $stmtl = $con->prepare("SELECT comercialsc, nome, codigocategorias, externosc, codpagesadminsc, pasta 
                                    FROM new_sistema_categorias_PJA 
                                    WHERE codpagesadminsc = :idpg AND visivelhomesc = :vh AND comercialsc = :com 
                                    ORDER BY ordemsc");
            $vh = '1';
            $com = '0';
            $stmtl->bindValue(':idpg', $idpgadmin, PDO::PARAM_INT);
            $stmtl->bindValue(':vh', $vh, PDO::PARAM_STR);
            $stmtl->bindValue(':com', $com, PDO::PARAM_STR);
            $stmtl->execute();
            $catsLivres = $stmtl->fetchAll(PDO::FETCH_ASSOC);

            foreach ($catsLivres as $cat) {
              $pasta   = (string)($cat['pasta'] ?? '');
              $tipo    = '1';
              $stmtImg = $con->prepare("SELECT codigomidiasfotos FROM new_sistema_midias_fotos_PJA WHERE pasta = :pasta AND tipo = :tipo LIMIT 1");
              $stmtImg->bindValue(':pasta', $pasta, PDO::PARAM_STR);
              $stmtImg->bindValue(':tipo', $tipo, PDO::PARAM_STR);
              $stmtImg->execute();
              $rwImagem = $stmtImg->fetch(PDO::FETCH_ASSOC) ?: [];
              $idpublic = toInt($rwImagem['codigomidiasfotos'] ?? 0);

              $enc = encrypt(
                (string)($cat['codpagesadminsc'] ?? '') . '&' .
                  (string)($cat['codigocategorias'] ?? '') . '&' .
                  (string)$idpublic,
                $action = 'e'
              );

              $externo = toInt($cat['externosc'] ?? 0);
              $link = $externo === 1 ? 'action.php?cursoexterno=' . h($enc) : 'action.php?curso=' . h($enc);

              echo '<a href="' . $link . '" class="dropdown-item"><span style="color:#ff0080">livre</span> ' . h((string)$cat['nome']) . '</a>';
            }

            echo '</div></div>';
          } else {
            echo '<a href="action.php?idpage=' . h($urlEnc) . '" class="nav-item nav-link ' . h($active) . '">' . h($nomePagina) . '</a>';
          }
        }
      } catch (Throwable $e) {
        // opcionalmente, renderize fallback / log
      }
      ?>
      <a href="busca.php" class="nav-item nav-link" aria-label="Buscar">
        <i class="fa fa-search" aria-hidden="true"></i>
      </a>
    </div>

    <?php if (empty($_COOKIE['startusuario'])): ?>
      <a href="login_aluno.php?ts=<?= h($ts) ?>" class="btn btn-primary py-4 px-lg-5 d-none d-lg-block">
        Login <i class="fa fa-arrow-right ms-3"></i>
      </a>
    <?php endif; ?>
  </div>
</nav>