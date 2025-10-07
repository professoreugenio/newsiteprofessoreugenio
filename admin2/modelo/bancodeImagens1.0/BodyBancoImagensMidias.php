<?php


// Helpers
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}
// Monta URL pública da imagem (ajuste este caminho para o seu storage real)
function urlMidia($pasta, $arquivo)
{
    $pasta = trim((string)$pasta);
    $arquivo = trim((string)$arquivo);
    return '/fotos/bancoimagens/' . $pasta . '/' . $arquivo; // ajuste se necessário
}

// Admin logado (cookie criptografado)
$decadm = encrypt($_COOKIE['adminuserstart'] ?? '', 'd');
$expadm = explode("&", $decadm);
$codadm = $expadm[0] ?? '';

// Recebe o ID da galeria (criptografado) e faz decrypt
$idEnc = $_GET['id'] ?? '';
if ($idEnc === '') {
    echo '<div class="alert alert-danger m-3">ID da galeria não informado.</div>';
    exit;
}
try {
    $idGal = encrypt($idEnc, 'd');
} catch (\Throwable $e) {
    echo '<div class="alert alert-danger m-3">ID inválido.</div>';
    exit;
}
if (!is_numeric($idGal) || (int)$idGal <= 0) {
    echo '<div class="alert alert-danger m-3">ID inválido.</div>';
    exit;
}
$idGal = (int)$idGal;

// Busca galeria
try {
    $con = config::connect();
    try {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\Throwable $e) {
    }

    $sql = "SELECT b.codigobancoimagens, b.tituloBI, b.descricaoBI, b.pastaBI, b.idadminBI, b.dataBI, b.horaBI,
                 u.nome as admin_nome, u.pastasu, u.imagem200
          FROM a_site_banco_imagens b
          LEFT JOIN new_sistema_usuario u ON u.codigousuario = b.idadminBI
          WHERE b.codigobancoimagens = :id
          LIMIT 1";
    $st = $con->prepare($sql);
    $st->bindParam(':id', $idGal, PDO::PARAM_INT);
    $st->execute();
    if ($st->rowCount() === 0) {
        echo '<div class="alert alert-warning m-3">Galeria não encontrada.</div>';
        exit;
    }
    $gal = $st->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $t) {
    echo '<div class="alert alert-danger m-3">Falha ao carregar galeria.</div>';
    exit;
}

$tituloBI = $gal['tituloBI'] ?: 'Galeria';
$descricaoBI = $gal['descricaoBI'] ?: '';
$pastaBI = $gal['pastaBI'] ?: '';
$adminCriador = $gal['admin_nome'] ?: 'Admin';
$dataBI = $gal['dataBI'] ? (new DateTime($gal['dataBI']))->format('d/m/Y') : '';
$horaBI = $gal['horaBI'] ?: '';

?>




<div class="container py-3">
    

    <!-- Upload -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
           
            <div class="text-secondary small">
                Pasta: <code><?= e($pastaBI) ?></code> • Criada em <?= e($dataBI) ?> às <?= e($horaBI) ?> • por <?= e($adminCriador) ?>
            </div>
            <?php if ($descricaoBI): ?>
                <div class="small mt-1 text-muted"><?= e($descricaoBI) ?></div>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalUpload">
                <i class="bi bi-cloud-arrow-up me-1"></i> Adicionar imagem
            </button>
            <a href="javascript:history.back();" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>



    <!-- Grid de mídias -->
    <div id="gridMidias" class="mb-4">
        <!-- carregado via AJAX -->
    </div>

</div>

<!-- MODAL: Lightbox -->
<div class="modal fade" id="modalLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h6 class="modal-title"><i class="bi bi-aspect-ratio me-2"></i>Visualização</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="lightboxImg" src="" alt="Imagem">
                </div>
                <div class="d-flex flex-wrap gap-2 small text-secondary" id="lightboxMeta">
                    <!-- meta preenchida via JS -->
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <a id="lightboxDownload" href="#" class="btn btn-primary btn-sm" download><i class="bi bi-download"></i> Baixar</a>
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- MODAL: Upload de Imagens -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formUploadMidias" class="modal-content bg-dark text-light" enctype="multipart/form-data" novalidate>
            <div class="modal-header border-secondary">
                <h6 class="modal-title"><i class="bi bi-cloud-arrow-up me-2"></i>Adicionar Imagem</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selecione as imagens</label>
                    <input type="file" class="form-control" name="imagens[]" accept="image/*" multiple required>
                    <div class="form-text text-secondary">Formatos aceitos: JPG, PNG, WEBP, GIF. Máx. 25MB por arquivo.</div>
                </div>

                <!-- Hidden fields necessários -->
                <input type="hidden" name="idgaleria" value="<?= e($idEnc) ?>">
                <input type="hidden" name="idadmin" value="<?= e($codadm) ?>">

                <!-- Barra de progresso -->
                <div class="progress progress-sm d-none" id="uploadProgress">
                    <div class="progress-bar" role="progressbar" style="width:0%">0%</div>
                </div>
            </div>

            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-upload me-1"></i> Enviar
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    (function() {
        const idEnc = <?= json_encode($idEnc) ?>;
        const pastaBI = <?= json_encode($pastaBI) ?>;

        // Carrega lista de mídias
        function carregarMidias() {
            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoMidiasList.php",
                method: "GET",
                data: {
                    id: idEnc
                }, // id da galeria (criptografado). O endpoint deverá usar a pastaBI vinculada.
                dataType: "html",
                success: function(html) {
                    $("#gridMidias").html(html);
                },
                error: function() {
                    $("#gridMidias").html('<div class="text-danger small">Falha ao carregar mídias.</div>');
                }
            });
        }

        // Upload com barra de progresso
        $("#formUploadMidias").on("submit", function(e) {
            e.preventDefault();
            const $form = $(this)[0];
            const formData = new FormData($form);

            const $prog = $("#uploadProgress");
            const $bar = $prog.find(".progress-bar");
            $prog.removeClass("d-none");
            $bar.css("width", "0%").text("0%");

            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoMidiasUpload.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percent = Math.round((evt.loaded / evt.total) * 100);
                            $bar.css("width", percent + "%").text(percent + "%");
                        }
                    }, false);
                    return xhr;
                },
                success: function(r) {
                    try {
                        r = typeof r === 'string' ? JSON.parse(r) : r;
                    } catch (e) {}
                    if (r && r.status === 'ok') {
                        $form.reset();
                        $prog.addClass("d-none");
                        carregarMidias();
                    } else {
                        alert(r && r.mensagem ? r.mensagem : "Erro ao enviar.");
                    }
                },
                error: function() {
                    alert("Falha na requisição.");
                }
            });
        });

        // Excluir mídia
        $(document).on("click", ".btnExcluirMidia", function() {
            const idMidia = $(this).data("id"); // id da mídia CRIPTO
            const nomeArq = $(this).data("nome") || 'arquivo';

            if (!confirm("Excluir a imagem \"" + nomeArq + "\"?")) {
                return;
            }
            $.ajax({
                url: "bancodeImagens1.0/ajax_bancoMidiaDelete.php",
                method: "POST",
                dataType: "json",
                data: {
                    idmidia: idMidia
                }, // CRIPTO (o endpoint faz decrypt)
                success: function(r) {
                    if (r && r.status === 'ok') {
                        carregarMidias();
                    } else {
                        alert(r && r.mensagem ? r.mensagem : "Erro ao excluir.");
                    }
                },
                error: function() {
                    alert("Falha na requisição.");
                }
            });
        });

        // Lightbox: abrir
        $(document).on("click", ".thumb-open", function() {
            const src = $(this).data("src"); // URL grande
            const nome = $(this).data("nome");
            const tam = $(this).data("size");
            const data = $(this).data("data");
            const hora = $(this).data("hora");
            const admin = $(this).data("admin");
            $("#lightboxImg").attr("src", src);
            $("#lightboxDownload").attr("href", src).attr("download", nome);
            $("#lightboxMeta").html(`
      <span class="badge bg-secondary"><i class="bi bi-hdd"></i> ${tam}</span>
      <span class="badge bg-secondary"><i class="bi bi-calendar3"></i> ${data}</span>
      <span class="badge bg-secondary"><i class="bi bi-clock"></i> ${hora}</span>
      <span class="badge bg-info text-dark"><i class="bi bi-person-circle"></i> ${admin}</span>
      <span class="badge bg-dark border border-secondary"><i class="bi bi-file-earmark-image"></i> ${nome}</span>
    `);
            const modal = new bootstrap.Modal(document.getElementById('modalLightbox'));
            modal.show();
        });

        // Inicial
        carregarMidias();
    })();
</script>