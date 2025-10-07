<?php
// Proteção básica (ajuste para seu padrão de includes, se necessário)
// Consulta vídeos do módulo
$stmt = $con->prepare("SELECT * FROM a_curso_videoaulas WHERE idmodulocva = :idmodulo ORDER BY codigovideos DESC");
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
    // Função AJAX para criar a pasta 720
    function criaPasta720(form, pasta720Path) {
        event.preventDefault();
        if (!confirm("Criar a pasta 720 dentro de:\n" + pasta720Path + " ?")) return false;
        var pasta = form.pasta.value;
        var btn = form.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Aguarde...';

        fetch('ajax_criarPasta720.php', {
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
                } else {
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-danger');
                    btn.innerHTML = '<i class="bi bi-x-circle"></i> Erro';
                    alert('Não foi possível criar a pasta 720.\n' + (res.msg || ''));
                }
            })
            .catch(e => {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-danger');
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Erro';
                alert('Erro ao criar pasta 720.\n' + e);
            });
        return false;
    }
</script>