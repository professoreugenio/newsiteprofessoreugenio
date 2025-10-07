<!-- ====== PERFIL / UPLOAD (mantém imagem200) ====== -->
<div class="card card-elegant">
    <div class="card-body text-center p-4">

        <?php
        $pasta   = htmlspecialchars($pastasc ?? '');
        $img200  = htmlspecialchars($imagem200 ?? 'usuario.jpg');
        $srcFoto = ($img200 && $img200 !== 'usuario.jpg')
            ? '../../fotos/usuarios/' . $pasta . '/' . $img200
            : '../../fotos/usuarios/usuario.png';
        ?>
        <img id="fotoPreview" src="<?= $srcFoto ?>" alt="Foto do aluno" class="avatar-ring mb-3">

        <div class="text-start">
            <!-- mantém sua estrutura: campo imagem200 -->
            <label class="form-label"><i class="bi bi-image"></i> Imagem (imagem200)</label>
            <div class="input-group mb-2">
                <span class="input-group-text"><i class="bi bi-card-image"></i></span>
                <input type="text" name="imagem200" id="imagem200" class="form-control"
                    value="<?= htmlspecialchars($imagem200) ?>">
            </div>

            <!-- Form de UPLOAD (com hidden idUsuario e pastasc) -->
            <form id="formUploadImagem" enctype="multipart/form-data" class="mt-2">
                <!-- IMPORTANTES para o endpoint -->
                <input type="hidden" name="idUsuario" value="<?= (int)$codigoCadastro ?>">
                <input type="hidden" name="pastasc" value="<?= htmlspecialchars($pastasc) ?>">

                <div class="input-group">
                    <input type="file" name="arquivo" id="arquivo" class="form-control" accept="image/*" required>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-upload"></i> Enviar
                    </button>
                </div>

                <div class="progress mt-2" style="height:6px; display:none;" id="upBarWrap">
                    <div class="progress-bar" id="upBar" style="width:0%"></div>
                </div>

                <div class="help mt-2">
                    Ao concluir o upload, o campo <code>imagem200</code> é atualizado automaticamente.
                </div>
                <small class="text-muted d-block">Pasta: <code><?= htmlspecialchars($pastasc) ?></code></small>
            </form>
        </div>

    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('formUploadImagem');
        const file = document.getElementById('arquivo');
        const barW = document.getElementById('upBarWrap');
        const bar = document.getElementById('upBar');
        const prev = document.getElementById('fotoPreview');
        const img200 = document.getElementById('imagem200');

        // Ajuste se o caminho do seu endpoint for outro
        const UPLOAD_URL = 'usuariosv1.0/ajax_uploadFotoUsuario.php';

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (!file.files.length) return;

                const fd = new FormData(form);

                // Reforço: garante que idUsuario e pastasc vão no payload
                if (!fd.has('idUsuario')) fd.append('idUsuario', '<?= (int)$codigoCadastro ?>');
                if (!fd.has('pastasc')) fd.append('pastasc', '<?= htmlspecialchars($pastasc) ?>');

                // --- (opcional) debug para checar o que está indo:
                // for (const [k,v] of fd.entries()) console.log('FD:', k, v);

                barW.style.display = 'block';
                bar.classList.remove('bg-danger', 'bg-success');
                bar.style.width = '0%';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', UPLOAD_URL, true);

                xhr.upload.onprogress = (ev) => {
                    if (ev.lengthComputable) {
                        const pct = Math.round((ev.loaded / ev.total) * 100);
                        bar.style.width = pct + '%';
                    }
                };

                xhr.onerror = () => {
                    bar.classList.add('bg-danger');
                };

                xhr.onload = () => {
                    let res = {};
                    try {
                        res = JSON.parse(xhr.responseText || '{}');
                    } catch (e) {}

                    if (res && res.ok) {
                        // Atualiza campo imagem200 (nome do arquivo 200px)
                        if (res.nomeArquivo) img200.value = res.nomeArquivo;

                        // Atualiza preview (prioriza URL pública do backend)
                        if (res.urlPublica) {
                            prev.src = res.urlPublica;
                        } else if (img200.value) {
                            // Monta URL local caso não venha urlPublica
                            prev.src = '../../fotos/usuarios/<?= $pasta ?>/' + img200.value;
                        }

                        bar.classList.add('bg-success');
                        bar.style.width = '100%';
                    } else {
                        bar.classList.add('bg-danger');
                    }
                };

                xhr.send(fd);
            });
        }
    })();
</script>