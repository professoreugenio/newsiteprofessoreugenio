<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$pub = intval($_GET['pub'] ?? 0);
$md  = intval($_GET['md'] ?? 0);

$stmt = $con->prepare("SELECT codigomanexos, titulopa, urlpa, anexopa, extpa, sizepa, pastapa, visivel, tipo, datapa, horapa
                       FROM new_sistema_publicacoes_anexos_PJA
                       WHERE codpublicacao = :pub 
                       ORDER BY codigomanexos DESC");
$stmt->execute([':pub' => $pub]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hoje = date('Y-m-d');

function webPath($r)
{
    if (!empty($r['anexopa']) && !empty($r['pastapa'])) {
        return "../../../anexos/publicacoes/" . rawurlencode($r['pastapa']) . "/" . rawurlencode($r['anexopa']);
    }
    return "";
}
function iconForExt($ext, $webPath)
{
    $ext = strtolower(ltrim($ext, '.'));
    $imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $imgExts) && $webPath) {
        return '<img src="' . htmlspecialchars($webPath) . '" class="border rounded" style="width:48px;height:48px;object-fit:cover" alt="anexo">';
    }
    $map = [
        'pdf' => 'bi-filetype-pdf',
        'doc' => 'bi-filetype-doc',
        'docx' => 'bi-filetype-docx',
        'xls' => 'bi-filetype-xls',
        'xlsx' => 'bi-filetype-xlsx',
        'ppt' => 'bi-filetype-ppt',
        'pptx' => 'bi-filetype-pptx',
        'pps' => 'bi-filetype-ppt',
        'ppsx' => 'bi-filetype-pptx',
        'txt' => 'bi-filetype-txt',
        'zip' => 'bi-file-zip',
        'rar' => 'bi-file-zip',
        'json' => 'bi-filetype-json',
        'js' => 'bi-filetype-js',
        'html' => 'bi-filetype-html',
        'php' => 'bi-filetype-php',
        'ai' => 'bi-file-earmark-image',
        'eps' => 'bi-file-earmark-image',
        'psd' => 'bi-file-earmark-image',
        'cdr' => 'bi-file-earmark-image',
        'otf' => 'bi-filetype-otf',
        'ttf' => 'bi-filetype-ttf',
        'pbix' => 'bi-file-earmark-bar-graph',
        'bat' => 'bi-terminal'
    ];
    $icon = $map[$ext] ?? 'bi-file-earmark';
    return '<i class="bi ' . $icon . ' fs-3 text-secondary"></i>';
}

if (empty($rows)) {
    echo '<li class="list-group-item text-center text-muted">Nenhum anexo cadastrado.</li>';
    exit;
}

foreach ($rows as $ax) {
    $web = webPath($ax);
    $thumb = iconForExt($ax['extpa'] ?: pathinfo($ax['anexopa'] ?? '', PATHINFO_EXTENSION), $web);
    $destacar = (substr($ax['datapa'] ?? '', 0, 10) === $hoje) ? 'bg-info bg-opacity-25 border-info' : '';
?>
    <li class="list-group-item d-flex align-items-center justify-content-between <?= $destacar ?>" id="ax_<?= $ax['codigomanexos'] ?>">
        <div class="d-flex align-items-center gap-3">
            <div><?= $thumb ?></div>
            <div>
                <input type="text" class="form-control form-control-sm titulo-ax" data-id="<?= $ax['codigomanexos'] ?>" value="<?= htmlspecialchars($ax['titulopa']) ?>">
                *<?= $ax['urlpa']; ?>*
                <?php if ($ax['urlpa'] != "#"): ?>
                    <a href="<?= htmlspecialchars($ax['urlpa']) ?>" target="_blank" class="small text-muted d-inline-block mt-1"><i class="bi bi-link-45deg"></i> Abrir URL</a>
                <?php elseif ($web): ?>
                    <?php
                    $dir0 = "../../../anexos";
                    $dir1 = $dir0 . "/publicacoes";
                    $uploadDir = $dir1 . "/" . $pastapub; ?>
                    <a href="<?= htmlspecialchars($web) ?>" target="_blank" class="small text-muted d-inline-block mt-1"><i class="bi bi-box-arrow-up-right"></i> Abrir arquivo</a>
                <?php endif; ?>
                <div class="small text-muted mt-1">
                    <span class="me-2">Tipo: <?= htmlspecialchars($ax['tipo'] ?: ($ax['urlpa'] ? 'url' : 'arquivo')) ?></span>
                    <?php if ($ax['sizepa']): ?><span class="me-2"><?= number_format($ax['sizepa'] / 1024, 1, ',', '.') ?> KB</span><?php endif; ?>
                    <?php if ($ax['extpa']): ?><span class="me-2"><?= strtoupper($ax['extpa']) ?></span><?php endif; ?>
                    <?php if ($ax['datapa']): ?><span><?= date('d/m/Y', strtotime($ax['datapa'])) ?> <?= htmlspecialchars($ax['horapa']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="form-check me-2" title="Visível?">
                <input class="form-check-input chk-vis" type="checkbox" data-id="<?= $ax['codigomanexos'] ?>" <?= intval($ax['visivel']) ? 'checked' : '' ?>>
            </div>
            <button class="btn btn-sm btn-outline-primary" onclick="atualizarTitulo(<?= $ax['codigomanexos'] ?>)"><i class="bi bi-save"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="excluirAnexo(<?= $ax['codigomanexos'] ?>)"><i class="bi bi-trash"></i></button>
        </div>
    </li>
<?php
}
