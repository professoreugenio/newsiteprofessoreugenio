<?php if ($qtdFotos > 0): ?>
    <!-- Grid básico (visão padrão na página) -->
    <div class="row g-3">
        <?php foreach ($fotos as $foto):
            $idFoto   = $foto['codigomfotos'];
            $encId    = encrypt($idFoto, 'e');
            $caminho  = "/fotos/publicacoes/{$foto['pasta']}/{$foto['foto']}";
            $favorito = (int)($foto['favorito_pf'] ?? 0);
        ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="border rounded shadow-sm position-relative p-1 h-100 d-flex flex-column">
                    <img src="<?= $caminho ?>"
                        data-img="<?= $caminho ?>"
                        data-fav="<?= $favorito ?>"
                        class="img-thumbnail miniatura-foto mb-2"
                        alt="Foto"
                        style="cursor:pointer; aspect-ratio:1/1; object-fit:cover;">

                    <div class="d-flex justify-content-between mt-auto">
                        <button class="btn btn-sm btn-outline-danger btnExcluirFoto" data-id="<?= $encId ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button class="btn btn-sm <?= $favorito ? 'btn-warning' : 'btn-outline-secondary' ?> btnFavoritarFoto" data-id="<?= $encId ?>">
                            <i class="bi bi-star<?= $favorito ? '-fill' : '' ?>"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">Nenhuma foto enviada ainda.</div>
<?php endif; ?>




<div id="painelFotos" class="painel-fotos" aria-hidden="true">
    <div class="painel-fotos-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-images"></i>
            <strong>Imagens da publicação</strong>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Filtro por favorito -->
            <select id="filtroFavoritas" class="form-select form-select-sm" title="Filtrar">
                <option value="todas">Todas</option>
                <option value="favoritas">Favoritas</option>
            </select>

            <!-- Botão para página completa -->
            <a class="btn btn-sm btn-outline-primary"
                href="cursos_publicacaoFotos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>

            <!-- Fechar painel -->
            <button id="fecharPainelFotos" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <div class="painel-fotos-body">
        <?php if ($qtdFotos > 0): ?>
            <?php foreach ($fotos as $foto):
                $idFoto   = $foto['codigomfotos'];
                $encId    = encrypt($idFoto, 'e');
                $caminho  = "/fotos/publicacoes/{$foto['pasta']}/{$foto['foto']}";
                $favorito = (int)($foto['favorito_pf'] ?? 0);
            ?>
                <div class="item-foto" data-fav="<?= $favorito ?>" data-id="<?= $encId ?>">
                    <img src="<?= $caminho ?>" alt="Foto" class="lb-trigger" data-img="<?= $caminho ?>">
                    <div class="flex-grow-1">
                        <!-- <div class="small text-muted">/fotos/publicacoes/<?= htmlspecialchars($foto['pasta']) ?>/</div> -->
                        <!-- <div class="text-truncate" style="max-width: 220px;"><?= htmlspecialchars($foto['foto']) ?></div> -->
                        <?php if ($favorito): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Favorita</span>
                        <?php endif; ?>
                    </div>
                    <div class="acoes">
                        <button class="btn btn-sm btn-outline-danger btnExcluirFoto" data-id="<?= $encId ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <a class="btn btn-sm btn-outline-primary"
                            href="cursos_publicacaoFotos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $_GET['pub'] ?>">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info m-2">Nenhuma foto encontrada.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox-overlay" aria-hidden="true">
    <button class="btn btn-light lightbox-fechar" id="fecharLightbox"><i class="bi bi-x-lg"></i></button>
    <img src="" alt="Visualização">
</div>

<!-- JS (jQuery opcional — use se já está no projeto) -->
<script>
    (function() {
        const painel = document.getElementById('painelFotos');
        const abrir = document.getElementById('abrirPainelFotos');
        const fechar = document.getElementById('fecharPainelFotos');
        const lightbox = document.getElementById('lightbox');
        const lbImg = lightbox.querySelector('img');
        const fecharLb = document.getElementById('fecharLightbox');
        const filtroFavoritas = document.getElementById('filtroFavoritas');

        // Abrir/fechar painel
        if (abrir) abrir.addEventListener('click', function(e) {
            e.preventDefault();
            painel.classList.add('aberto');
            painel.setAttribute('aria-hidden', 'false');
        });
        if (fechar) fechar.addEventListener('click', function() {
            painel.classList.remove('aberto');
            painel.setAttribute('aria-hidden', 'true');
        });

        // Fechar painel com ESC
        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && painel.classList.contains('aberto')) {
                painel.classList.remove('aberto');
                painel.setAttribute('aria-hidden', 'true');
            }
            if (ev.key === 'Escape' && lightbox.classList.contains('aberto')) {
                lightbox.classList.remove('aberto');
                lightbox.setAttribute('aria-hidden', 'true');
            }
        });

        // Lightbox: abrir
        document.addEventListener('click', function(e) {
            const t = e.target;
            if (t.classList.contains('lb-trigger') || t.classList.contains('miniatura-foto')) {
                const src = t.getAttribute('data-img') || t.getAttribute('src');
                lbImg.src = src;
                lightbox.classList.add('aberto');
                lightbox.setAttribute('aria-hidden', 'false');
            }
        });

        // Lightbox: fechar
        if (fecharLb) fecharLb.addEventListener('click', function() {
            lightbox.classList.remove('aberto');
            lightbox.setAttribute('aria-hidden', 'true');
            lbImg.src = '';
        });
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                lightbox.classList.remove('aberto');
                lightbox.setAttribute('aria-hidden', 'true');
                lbImg.src = '';
            }
        });

        // Filtro: favoritas / todas (funciona tanto no grid quanto no painel)
        if (filtroFavoritas) filtroFavoritas.addEventListener('change', function() {
            const val = this.value; // 'todas' | 'favoritas'
            const itensPainel = painel.querySelectorAll('.item-foto');
            const itensGrid = document.querySelectorAll('.miniatura-foto');

            // Painel
            itensPainel.forEach(el => {
                const fav = el.getAttribute('data-fav') === '1';
                el.style.display = (val === 'favoritas' && !fav) ? 'none' : '';
            });

            // Grid
            itensGrid.forEach(img => {
                const fav = img.getAttribute('data-fav') === '1';
                const card = img.closest('.col-6, .col-sm-4, .col-md-3, .col-lg-2');
                if (card) card.style.display = (val === 'favoritas' && !fav) ? 'none' : '';
            });
        });

        // Excluir (AJAX)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnExcluirFoto');
            if (!btn) return;

            const encId = btn.getAttribute('data-id');
            if (!encId) return;

            if (!confirm('Deseja realmente excluir esta imagem?')) return;

            // Requisição AJAX simples (fetch)
            fetch('publicacoesv1.0/ajax_publicacaoExcluirFoto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        id: encId
                    }).toString()
                })
                .then(r => r.text())
                .then(resp => {
                    // Sucesso esperado: '1' (ajuste conforme seu backend)
                    if (resp.trim() === '1') {
                        // Remove do painel
                        const item = document.querySelector('.item-foto[data-id="' + encId + '"]');
                        if (item) item.remove();

                        // Remove do grid
                        const allBtns = document.querySelectorAll('.btnExcluirFoto[data-id="' + encId + '"]');
                        allBtns.forEach(b => {
                            const col = b.closest('.col-6, .col-sm-4, .col-md-3, .col-lg-2');
                            if (col) col.remove();
                        });
                    } else {
                        alert('Não foi possível excluir a imagem. Código: ' + resp);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro na exclusão. Tente novamente.');
                });
        });

        // (Opcional) Favoritar visual: apenas efeito local no botão (sem persistir)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnFavoritarFoto');
            if (!btn) return;
            // Aqui só alternamos visualmente. Se quiser, faça AJAX para favoritar de verdade.
            btn.classList.toggle('btn-warning');
            btn.classList.toggle('btn-outline-secondary');
            const icon = btn.querySelector('i');
            if (icon) icon.classList.toggle('bi-star-fill');
            if (icon) icon.classList.toggle('bi-star');

            // Atualiza atributo data-fav nas instâncias
            const encId = btn.getAttribute('data-id');
            // Painel
            const itemPainel = document.querySelector('.item-foto[data-id="' + encId + '"]');
            if (itemPainel) {
                const cur = itemPainel.getAttribute('data-fav') === '1';
                itemPainel.setAttribute('data-fav', cur ? '0' : '1');
            }
            // Grid (miniatura)
            const gridBtn = document.querySelector('.col-6 .btnFavoritarFoto[data-id="' + encId + '"], .col-sm-4 .btnFavoritarFoto[data-id="' + encId + '"], .col-md-3 .btnFavoritarFoto[data-id="' + encId + '"], .col-lg-2 .btnFavoritarFoto[data-id="' + encId + '"]');
            if (gridBtn) {
                const img = gridBtn.closest('.col-6, .col-sm-4, .col-md-3, .col-lg-2')?.querySelector('img.miniatura-foto');
                if (img) {
                    const fav = img.getAttribute('data-fav') === '1';
                    img.setAttribute('data-fav', fav ? '0' : '1');
                }
            }
        });
    })();
</script>