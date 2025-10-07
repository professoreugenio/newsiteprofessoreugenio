<?php
// Proteção básica (ajuste para seu padrão de includes, se necessário)
// Consulta vídeos do módulo
$stmt = $con->prepare("SELECT * FROM a_curso_videoaulas WHERE idmodulocva = :idmodulo ORDER BY codigovideos ASC");
$stmt->bindParam(':idmodulo', $codigomodulo, PDO::PARAM_INT);
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-0">
    <h2 class="mb-4">Vídeos do Módulo</h2>
    <div class="list-group shadow-sm">
        <?php if (count($videos) == 0): ?>
            <div class="alert alert-info">Nenhum vídeo encontrado para este módulo.</div>
        <?php else: ?>
            <?php foreach ($videos as $video):
                $videoPath = "../videos/publicacoes/{$video['pasta']}/{$video['video']}";
                $pasta720 = "../videos/publicacoes/{$video['pasta']}/720";
            ?>
                <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap py-3">
                    <div class="flex-grow-1">
                        <div>
                            <strong class="text-primary"><?= htmlspecialchars($video['video']) ?></strong>
                            <small class="text-muted ms-2"><?= htmlspecialchars($video['totalhoras']) ?> horas</small>
                        </div>
                        <div>
                            <span class="badge bg-secondary"><?= htmlspecialchars($video['pasta']) ?></span>
                            <span class="text-muted ms-2" style="font-size: 0.92em;"><?= $videoPath ?></span>
                        </div>
                    </div>
                    <div>
                        <form method="post" class="d-inline" action="" onsubmit="return criaPasta720(this, '<?= $pasta720 ?>');">
                            <input type="hidden" name="pasta" value="<?= htmlspecialchars($video['pasta']) ?>">
                            <button type="submit" class="btn btn-outline-success btn-sm" title="Criar pasta 720 na pasta do vídeo">
                                <i class="bi bi-folder-plus"></i> Criar pasta 720
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    function showToastBootstrap(message, success = true) {
        let toastContainer = document.getElementById('custom-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'custom-toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '24px';
            toastContainer.style.right = '24px';
            toastContainer.style.zIndex = '1080';
            document.body.appendChild(toastContainer);
        }

        // Remove toasts antigos se existirem
        toastContainer.innerHTML = '';

        let toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${success ? 'success' : 'danger'} border-0 show`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.style.minWidth = '220px';

        toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

        toastContainer.appendChild(toast);

        // Remove toast depois de 3s
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function criaPasta720(form, pasta720Path) {
        event.preventDefault();
        var pasta = form.pasta.value;
        var btn = form.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Aguarde...';

        fetch('config_curso1.0/ajax_criarPasta720.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'pasta=' + encodeURIComponent(pasta)
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-success');
                    btn.innerHTML = '<i class="bi bi-check-circle"></i> Pasta 720 criada';
                    showToastBootstrap('Pasta 720 criada com sucesso!', true);
                } else {
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-danger');
                    btn.innerHTML = '<i class="bi bi-x-circle"></i> Erro';
                    showToastBootstrap('Não foi possível criar a pasta 720. ' + (res.msg || ''), false);
                }
            })
            .catch(e => {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-danger');
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Erro';
                showToastBootstrap('Erro ao criar pasta 720. ' + e, false);
            });
        return false;
    }
</script>