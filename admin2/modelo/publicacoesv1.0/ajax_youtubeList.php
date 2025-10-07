<?php

/** LISTA de vídeos da publicação (favorito em destaque, demais em grid) */
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$pub = (int)($_POST['codpublicacao'] ?? 0);
if ($pub <= 0) {
    echo '<div class="alert alert-warning">Publicação inválida.</div>';
    exit;
}

$con = config::connect();
$q = $con->prepare("SELECT codigoyoutube, codpublicacao_sy, url_sy, chavetube_sy, titulo_sy, canal_sy, visivel_sy, favorito_sy, data_sy, hora_sy
                    FROM new_sistema_youtube_PJA
                    WHERE codpublicacao_sy=:p
                    ORDER BY favorito_sy DESC, data_sy DESC, hora_sy DESC");
$q->execute([':p' => $pub]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo '<div class="text-center text-muted py-4"><i class="bi bi-inbox me-1"></i>Nenhum vídeo cadastrado.</div>';
    exit;
}

/** Helper de iframe (robusto YouTube/Vimeo) */
function iframeEmbed(string $canal, string $chave): string
{
    $canal = strtolower(trim($canal));
    $chave = trim($chave);

    // Fallback: deduz canal pela "cara" da chave
    if ($canal !== 'youtube' && $canal !== 'vimeo') {
        if (ctype_digit($chave)) {
            $canal = 'vimeo';
        } else {
            $canal = 'youtube';
        }
    }

    if ($canal === 'youtube') {
        $chave = preg_replace('~[^A-Za-z0-9_-]~', '', $chave);
        $src = "https://www.youtube.com/embed/" . $chave;
    } else {
        $chave = preg_replace('~\D~', '', $chave);
        $src = "https://player.vimeo.com/video/" . $chave;
    }

    return '<div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
              <iframe src="' . $src . '" allowfullscreen loading="lazy"
                      referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>';
}

// Bloco destaque (primeiro favorito, se houver)
$favorito = null;
foreach ($rows as $r) {
    if ((int)$r['favorito_sy'] === 1) {
        $favorito = $r;
        break;
    }
}

if ($favorito) {
    echo '<div class="mb-4 p-3 border rounded-3 bg-light">';
    echo '  <div class="d-flex align-items-center justify-content-between mb-2">';
    echo '    <h6 class="mb-0"><i class="bi bi-star-fill text-warning me-1"></i>Vídeo favorito</h6>';
    echo '    <span class="badge text-bg-success">' . ($favorito['visivel_sy'] ? 'Visível' : 'Oculto') . '</span>';
    echo '  </div>';
    echo '  <div class="row g-3 align-items-start">';
    echo '    <div class="col-lg-7">' . iframeEmbed($favorito['canal_sy'], $favorito['chavetube_sy']) . '</div>';
    echo '    <div class="col-lg-5">';
    echo '      <div class="card border-0 shadow-sm"><div class="card-body">';
    echo '        <div class="mb-2"><span class="badge text-bg-secondary">' . h($favorito['canal_sy']) . '</span></div>';
    echo '        <div class="mb-2">';
    echo '          <label class="form-label small mb-1">Título</label>';
    echo '          <input type="text" class="form-control inp-titulo" value="' . h($favorito['titulo_sy']) . '">';
    echo '        </div>';
    echo '        <div class="mb-2">';
    echo '          <label class="form-label small mb-1">URL</label>';
    echo '          <input type="text" class="form-control inp-url" value="' . h($favorito['url_sy']) . '">';
    echo '        </div>';
    echo '        <div class="row g-2 mb-3">';
    echo '          <div class="col-6">';
    echo '            <div class="form-label small mb-1">Visível</div>';
    echo '            <div class="btn-group w-100" role="group">';
    echo '              <input class="btn-check inp-visivel" type="radio" name="vis_fav" id="visfav1" value="1" ' . ($favorito['visivel_sy'] ? 'checked' : '') . '>';
    echo '              <label class="btn btn-outline-success" for="visfav1">Sim</label>';
    echo '              <input class="btn-check inp-visivel" type="radio" name="vis_fav" id="visfav0" value="0" ' . (!$favorito['visivel_sy'] ? 'checked' : '') . '>';
    echo '              <label class="btn btn-outline-secondary" for="visfav0">Não</label>';
    echo '            </div>';
    echo '          </div>';
    echo '          <div class="col-6">';
    echo '            <div class="form-label small mb-1">Favorito</div>';
    echo '            <div class="btn-group w-100" role="group">';
    echo '              <input class="btn-check inp-favorito" type="radio" name="fav_fav" id="favfav1" value="1" checked>';
    echo '              <label class="btn btn-outline-warning" for="favfav1"><i class="bi bi-star-fill"></i></label>';
    echo '              <input class="btn-check inp-favorito" type="radio" name="fav_fav" id="favfav0" value="0">';
    echo '              <label class="btn btn-outline-secondary" for="favfav0"><i class="bi bi-star"></i></label>';
    echo '            </div>';
    echo '          </div>';
    echo '        </div>';
    echo '        <div class="d-flex gap-2">';
    echo '          <button class="btn btn-success btn-salvar-video" data-id="' . (int)$favorito['codigoyoutube'] . '"><i class="bi bi-check2-circle me-1"></i>Salvar</button>';
    echo '          <button class="btn btn-outline-danger btn-excluir-video" data-id="' . (int)$favorito['codigoyoutube'] . '"><i class="bi bi-trash3 me-1"></i>Excluir</button>';
    echo '        </div>';
    echo '      </div></div>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}

// Grid dos demais
echo '<div class="row g-3">';
foreach ($rows as $r) {
    if ($favorito && (int)$r['codigoyoutube'] === (int)$favorito['codigoyoutube']) continue;
    $id = (int)$r['codigoyoutube'];
    $nomeRadio = 'vg' . $id;
    $nomeFav   = 'fv' . $id;
    echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
    echo '  <div class="card h-100 shadow-sm" data-id="' . $id . '">';
    echo '    <div class="card-img-top">' . iframeEmbed($r['canal_sy'], $r['chavetube_sy']) . '</div>';
    echo '    <div class="card-body">';
    echo '      <div class="d-flex justify-content-between align-items-center mb-2">';
    echo '        <span class="badge text-bg-secondary">' . h($r['canal_sy']) . '</span>';
    echo '        ' . ((int)$r['favorito_sy'] === 1 ? '<span class="badge text-bg-warning"><i class="bi bi-star-fill"></i></span>' : '') . '
        ';
    echo '      </div>';
    echo '      <input type="text" class="form-control form-control-sm mb-2 inp-titulo" value="' . h($r['titulo_sy']) . '" placeholder="Título">';
    echo '      <input type="text" class="form-control form-control-sm mb-2 inp-url" value="' . h($r['url_sy']) . '" placeholder="URL">';
    echo '      <div class="row g-2">';
    echo '        <div class="col-6">';
    echo '          <div class="btn-group w-100" role="group" aria-label="Visível">';
    echo '            <input class="btn-check inp-visivel" type="radio" name="' . $nomeRadio . '" id="v1_' . $id . '" value="1" ' . ((int)$r['visivel_sy'] === 1 ? 'checked' : '') . '>';
    echo '            <label class="btn btn-outline-success btn-sm" for="v1_' . $id . '">Visível</label>';
    echo '            <input class="btn-check inp-visivel" type="radio" name="' . $nomeRadio . '" id="v0_' . $id . '" value="0" ' . ((int)$r['visivel_sy'] === 0 ? 'checked' : '') . '>';
    echo '            <label class="btn btn-outline-secondary btn-sm" for="v0_' . $id . '">Oculto</label>';
    echo '          </div>';
    echo '        </div>';
    echo '        <div class="col-6">';
    echo '          <div class="btn-group w-100" role="group" aria-label="Favorito">';
    echo '            <input class="btn-check inp-favorito" type="radio" name="' . $nomeFav . '" id="f1_' . $id . '" value="1" ' . ((int)$r['favorito_sy'] === 1 ? 'checked' : '') . '>';
    echo '            <label class="btn btn-outline-warning btn-sm" for="f1_' . $id . '"><i class="bi bi-star-fill"></i></label>';
    echo '            <input class="btn-check inp-favorito" type="radio" name="' . $nomeFav . '" id="f0_' . $id . '" value="0" ' . ((int)$r['favorito_sy'] === 0 ? 'checked' : '') . '>';
    echo '            <label class="btn btn-outline-secondary btn-sm" for="f0_' . $id . '"><i class="bi bi-star"></i></label>';
    echo '          </div>';
    echo '        </div>';
    echo '      </div>';
    echo '    </div>';
    echo '    <div class="card-footer bg-white border-0 d-flex gap-2 justify-content-end">';
    echo '      <button class="btn btn-sm btn-outline-success btn-salvar-video" data-id="' . $id . '"><i class="bi bi-check2"></i></button>';
    echo '      <button class="btn btn-sm btn-outline-danger btn-excluir-video" data-id="' . $id . '"><i class="bi bi-trash3"></i></button>';
    echo '    </div>';
    echo '  </div>';
    echo '</div>';
}
echo '</div>';
