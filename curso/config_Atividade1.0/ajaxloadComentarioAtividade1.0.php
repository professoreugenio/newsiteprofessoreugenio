<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

// ---- Identifica usuário logado (cookie) ----
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], 'd');
} elseif (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], 'd');
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}

$expUser = explode("&", $decUser);
$idUser  = (int)($expUser[0] ?? 0);

// ---- Método ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

// ---- Entrada ----
$idfile = (int)($_POST['idfile'] ?? 0);
if ($idfile === 0) {
    exit('<p class="text-muted">Nenhum comentário disponível.</p>');
}

// (Opcional) limite de comentários: se quiser usar, envie via POST 'limit'
// $limit = max(0, (int)($_POST['limit'] ?? 0));

// ---- Consulta única com fallback para admin ----
// Traz nome/pasta/foto do autor tanto de new_sistema_cadastro quanto de new_sistema_usuario
$sql = "
    SELECT
        c.codigoatividadecomentario,
        c.idfileAnexoAAC,
        c.iduserdeAAC,
        c.textoAAC,
        c.dataAAC,
        c.horaAAC,
        COALESCE(u.nome, a.nome)        AS nome_autor,
        COALESCE(u.pastasc, a.pastasu)  AS pasta_autor,
        COALESCE(u.imagem50, a.imagem50) AS foto_autor,
        COALESCE(u.codigocadastro, a.codigousuario) AS id_autor
    FROM a_curso_AtividadeComentario c
    LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.iduserdeAAC
    LEFT JOIN new_sistema_usuario  a ON a.codigousuario  = c.iduserdeAAC
    WHERE c.idfileAnexoAAC = :idfile
    ORDER BY c.dataAAC ASC, c.horaAAC ASC, c.codigoatividadecomentario ASC
";
$stmt = $con->prepare($sql);
$stmt->execute([':idfile' => $idfile]);
// Se quiser limitar, use (cuidado com LIMIT parametrizado):
// $sql .= ($limit > 0 ? " LIMIT $limit" : "");

$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$comentarios) {
    echo '<p class="text-muted">Nenhum comentário ainda.</p>';
    exit;
}

// ---- Renderização ----
foreach ($comentarios as $c) {
    $texto = nl2br(htmlspecialchars($c['textoAAC'] ?? '', ENT_QUOTES, 'UTF-8'));

    $data = !empty($c['dataAAC']) ? date('d/m/Y', strtotime($c['dataAAC'])) : '--/--/----';
    $hora = !empty($c['horaAAC']) ? substr($c['horaAAC'], 0, 5) : '--:--';

    $nomeCompleto = htmlspecialchars($c['nome_autor'] ?? 'Usuário', ENT_QUOTES, 'UTF-8');
    $nome = strtok($nomeCompleto, ' ');
    if ($nome === false) {
        $nome = $nomeCompleto;
    }

    $pasta = $c['pasta_autor'] ?? '';
    $img   = $c['foto_autor']  ?? 'usuario.png';
    $idAutor = (int)($c['id_autor'] ?? $c['iduserdeAAC'] ?? 0);

    // Caminhos de arquivo/URL (FS usa "../../", exibição usa "../")
    $fotoFs  = "../../fotos/usuarios/{$pasta}/{$img}";
    $fotoWeb = "../fotos/usuarios/{$pasta}/{$img}";
    $foto    = (!empty($img) && is_file($fotoFs)) ? $fotoWeb : "../fotos/usuarios/usuario.png";

    if ($idUser === $idAutor) {
        // Mensagem do próprio usuário (direita, sem avatar)
        echo "<div class='d-flex align-items-start mb-3'>";
        echo "  <div class='bg-light text-dark p-2 w-100' style='border-radius: 8px 0 8px 8px;'>";
        echo "    <div class='small text-muted mb-1' style='text-align:right'><small> em {$data} às {$hora}</small> <strong title='{$nomeCompleto}'>{$nome}</strong></div>";
        echo "    <div style='text-align:right'><i>{$texto}</i></div>";
        echo "  </div>";
        echo "</div>";
    } else {
        // Mensagem de outro usuário (esquerda, com avatar)
        echo "<div class='d-flex align-items-start mb-3'>";
        echo "  <img src='{$foto}' class='rounded-circle me-2' width='40' height='40' alt='{$nome}'>";
        echo "  <div class='text-dark p-2 w-100' style='background-color:#e9e9deff; border-radius:0 8px 8px 8px'>";
        echo "    <div class='small text-muted mb-1'><strong title='{$nomeCompleto}'>{$nome}</strong><small> em {$data} às {$hora}</small></div>";
        echo "    <div><i>{$texto}</i></div>";
        echo "  </div>";
        echo "</div>";
    }
}
