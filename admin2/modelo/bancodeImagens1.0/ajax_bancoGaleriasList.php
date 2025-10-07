<?php

/**
 * bancoimagens1.0/ajax_bancoGaleriasList.php
 * Retorna o HTML da lista UL de galerias do Banco de Imagens.
 * Saída: <ul>...</ul> (ou um alerta caso vazio)
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

// Helper para escapar HTML
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Helper da foto do admin (ajuste o caminho se necessário)
function montarFotoAdmin($pasta = '', $img = '')
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    if ($img === '' || $img === 'usuario.jpg') {
        return '/fotos/usuarios/usuario.jpg';
    }
    if ($pasta !== '') {
        return '/fotos/usuarios/' . $pasta . '/' . $img;
    }
    return '/fotos/usuarios/' . $img;
}

try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    // Opcional: suporte a busca (q) via GET
    $q = trim($_GET['q'] ?? '');
    $where = '';
    $params = [];

    if ($q !== '') {
        // Busca por título ou descrição
        $where = "WHERE (b.tituloBI LIKE :q OR b.descricaoBI LIKE :q)";
        $params[':q'] = '%' . $q . '%';
    }

    $sql = "
    SELECT 
      b.codigobancoimagens,
      b.tituloBI,
      b.descricaoBI,
      b.pastaBI,
      b.idadminBI,
      b.dataBI,
      b.horaBI,
      u.nome AS admin_nome,
      u.pastasu,
      u.imagem200
    FROM a_site_banco_imagens b
    LEFT JOIN new_sistema_usuario u 
      ON u.codigousuario = b.idadminBI
    $where
    ORDER BY b.dataBI DESC, b.horaBI DESC
  ";

    $stmt = $con->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
    $stmt->execute();
    $galerias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$galerias) {
        echo '<div class="alert alert-secondary border-0 m-0">Nenhuma galeria cadastrada.</div>';
        exit;
    }

    ob_start();
?>
    <ul class="list-group list-group-flush rounded overflow-hidden">
        <?php foreach ($galerias as $g):
            $idEnc     = encrypt($g['codigobancoimagens'], 'e');
            $fotoAdmin = montarFotoAdmin($g['pastasu'] ?? '', $g['imagem200'] ?? '');
            $titulo    = $g['tituloBI'] ?: 'Galeria Sem Título';
            $descricao = $g['descricaoBI'] ?: '';
            $dataBI    = $g['dataBI'] ? (new DateTime($g['dataBI']))->format('d/m/Y') : '';
            $horaBI    = $g['horaBI'] ?: '';
            $adminNome = $g['admin_nome'] ?: 'Admin';
        ?>
            <li class="list-group-item bg-dark text-light py-3" style="border-color:rgba(255,255,255,.06);">
                <div class="d-flex gap-3 align-items-center justify-content-between flex-wrap">

                    <!-- ESQUERDA -->
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= e($fotoAdmin) ?>" alt="Admin" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;border:2px solid rgba(255,255,255,.2);">
                        <div>
                            <div class="fw-bold"><?= e($titulo) ?></div>
                            <?php if ($descricao): ?>
                                <div class="text-secondary small"><?= e($descricao) ?></div>
                            <?php endif; ?>
                            <div class="small mt-1">
                                <span class="badge bg-secondary me-1"><i class="bi bi-calendar3"></i> <?= e($dataBI) ?></span>
                                <span class="badge bg-secondary me-1"><i class="bi bi-clock"></i> <?= e($horaBI) ?></span>
                                <span class="badge bg-info text-dark"><i class="bi bi-person-circle"></i> <?= e($adminNome) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- DIREITA: Ações -->
                    <div class="d-flex gap-2">
                        <button
                            class="btn btn-warning btn-sm text-dark btnEditarGaleria"
                            data-id="<?= e($idEnc) ?>"
                            data-titulo='<?= e($titulo) ?>'
                            data-descricao='<?= e($descricao) ?>'
                            title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <button
                            class="btn btn-danger btn-sm btnExcluirGaleria"
                            data-id="<?= e($idEnc) ?>"
                            data-titulo='<?= e($titulo) ?>'
                            title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>

                        <a
                            class="btn btn-primary btn-sm"
                            href="bancoimagens_galeria.php?id=<?= e($idEnc) ?>"
                            title="Acessar galeria">
                            <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>

                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
    echo ob_get_clean();
    exit;
} catch (\PDOException $e) {
    echo '<div class="alert alert-danger border-0 m-0">Erro ao carregar galerias: ' . e($e->getMessage()) . '</div>';
    exit;
} catch (\Throwable $t) {
    echo '<div class="alert alert-danger border-0 m-0">Falha inesperada ao listar.</div>';
    exit;
}
