<?php

/**
 * bancodeImagens1.0/ajax_bancoMidiasList.php
 * Retorna SOMENTE o HTML dos itens de mídia (para #gridMidias) com thumbs 250x250
 * e metadados no rodapé da imagem.
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

// Helpers
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max(0, (float)$bytes);
        $pow   = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow   = min($pow, count($units) - 1);
        $bytes = $bytes / (1024 ** $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// 1) Recebe e valida o ID criptografado
$idEnc = trim($_GET['id'] ?? '');
if ($idEnc === '') {
    echo '<div class="text-secondary">Galeria não informada.</div>';
    exit;
}
try {
    $idGal = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo '<div class="text-danger">ID inválido.</div>';
    exit;
}
if (!is_numeric($idGal) || (int)$idGal <= 0) {
    echo '<div class="text-danger">ID inválido.</div>';
    exit;
}
$idGal = (int)$idGal;

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    // 2) Obtém a pasta da galeria
    $stGal = $con->prepare("SELECT pastaBI FROM a_site_banco_imagens WHERE codigobancoimagens = :id LIMIT 1");
    $stGal->bindParam(':id', $idGal, PDO::PARAM_INT);
    $stGal->execute();
    if ($stGal->rowCount() === 0) {
        echo '<div class="text-warning">Galeria não encontrada.</div>';
        exit;
    }

    $pastaBI = trim((string)$stGal->fetchColumn());
    if ($pastaBI === '') {
        echo '<div class="text-warning">Pasta da galeria não definida.</div>';
        exit;
    }

    // 3) Lista mídias por pasta (JOIN com admin)
    $sql = "
    SELECT 
      m.codigoimagemmidia,
      m.idadminIM,
      m.imagemIM,
      m.pastaIM,
      m.sizeIM,
      m.extensaoIM,
      m.dataIM,
      m.horaIM,
      u.nome AS admin_nome
    FROM a_site_banco_imagensMidias m
    LEFT JOIN new_sistema_usuario u
      ON u.codigousuario = m.idadminIM
    WHERE m.pastaIM = :pasta
    ORDER BY m.dataIM DESC, m.horaIM DESC, m.codigoimagemmidia DESC
  ";
    $st = $con->prepare($sql);
    $st->bindParam(':pasta', $pastaBI, PDO::PARAM_STR);
    $st->execute();
    $midias = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$midias) {
        echo '<div class="text-secondary">Nenhuma imagem enviada ainda.</div>';
        exit;
    }

    foreach ($midias as $m) {
        $idMidEnc = encrypt($m['codigoimagemmidia'], 'e');
        $nomeArq  = $m['imagemIM'] ?? '';
        $size     = (int)($m['sizeIM'] ?? 0);
        $dataIM   = $m['dataIM'] ? date('d/m/Y', strtotime($m['dataIM'])) : '';
        $horaIM   = $m['horaIM'] ?? '';
        $adminNm  = $m['admin_nome'] ?: 'Admin';

        $url = '/fotos/bancoimagens/' . $pastaBI . '/' . $nomeArq; // ajuste a URL pública se necessário
?>
        <div class="item-card">
            <div class="item-head">
                <!-- Ações (canto superior direito) -->
                <div class="item-actions">
                    <a href="<?= e($url) ?>" class="btn btn-primary btn-sm" download title="Baixar">
                        <i class="bi bi-download"></i>
                    </a>
                    <?php if (temPermissao($niveladm, [1,2])): ?>
                        <button
                            type="button"
                            class="btn btn-danger btn-sm btnExcluirMidia"
                            data-id="<?= e($idMidEnc) ?>"
                            data-nome="<?= e($nomeArq) ?>"
                            title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Thumb (250x250) que abre o lightbox -->
                <img
                    src="<?= e($url) ?>"
                    alt="<?= e($nomeArq) ?>"
                    class="thumb-250 thumb-open"
                    data-src="<?= e($url) ?>"
                    data-nome="<?= e($nomeArq) ?>"
                    data-size="<?= e(formatBytes($size)) ?>"
                    data-data="<?= e($dataIM) ?>"
                    data-hora="<?= e($horaIM) ?>"
                    data-admin="<?= e($adminNm) ?>">
            </div>

            <!-- Rodapé/Metadados sob a imagem -->
            <div class="item-footer">
                <div class="item-meta">
                    <div class="line">
                        <span title="Tamanho"><i class="bi bi-hdd"></i> <?= e(formatBytes($size)) ?></span>
                        <span class="sep">•</span>
                        <span><i class="bi bi-calendar3"></i> <?= e($dataIM) ?></span>
                        <span class="sep">•</span>
                        <span><i class="bi bi-clock"></i> <?= e($horaIM) ?></span>
                    </div>
                    <div class="line" title="Enviado por">
                        <i class="bi bi-person-circle"></i> <?= e($adminNm) ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }
} catch (\PDOException $e) {
    echo '<div class="text-danger">Erro ao carregar mídias: ' . e($e->getMessage()) . '</div>';
    exit;
} catch (\Throwable $t) {
    echo '<div class="text-danger">Falha inesperada ao listar mídias.</div>';
    exit;
}
