<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=utf-8');

if (empty($_POST['idpublicacao'])) {
    echo '<div class="alert alert-warning">ID da publicação não recebido.</div>';
    exit;
}

$idPublicacao = encrypt($_POST['idpublicacao'], 'd');
if (!is_numeric($idPublicacao)) {
    echo '<div class="alert alert-danger">ID da publicação inválido.</div>';
    exit;
}

$con = config::connect();

$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_fotos_PJA WHERE codpublicacao = :id ORDER BY data DESC, hora DESC");
$query->bindParam(":id", $idPublicacao);
$query->execute();
$fotos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (count($fotos) > 0): ?>
    <div class="row g-3">
        <?php foreach ($fotos as $foto): ?>
            <?php
            $idFoto = $foto['codigomfotos'];
            $encId = encrypt($idFoto, 'e');
            $caminho = "/fotos/publicacoes/{$foto['pasta']}/{$foto['foto']}";
            $favorito = $foto['favorito_pf'] ?? 0;
            ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="border rounded shadow-sm position-relative p-1 h-100 d-flex flex-column">
                    <img src="<?= $caminho ?>" data-img="<?= $caminho ?>" class="img-thumbnail miniatura-foto mb-2" alt="Foto" style="cursor:pointer; aspect-ratio: 1/1; object-fit: cover;">

                    <div class="d-flex justify-content-between mt-auto">
                        <button class="btn btn-sm btn-outline-danger btnExcluirFoto" data-id="<?= $encId ?>"><i class="bi bi-trash"></i></button>
                        <button class="btn btn-sm <?= $favorito ? 'btn-warning' : 'btn-outline-secondary' ?> btnFavoritarFoto" data-id="<?= $encId ?>"><i class="bi bi-star<?= $favorito ? '-fill' : '' ?>"></i></button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">Nenhuma imagem enviada até o momento.</div>
<?php endif; ?>

<!-- Modal de Lightbox -->
<div class="modal fade" id="modalViewFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0 p-0">
            <img id="modalImagem" src="" class="img-fluid rounded" alt="Visualização">
        </div>
    </div>
</div>

