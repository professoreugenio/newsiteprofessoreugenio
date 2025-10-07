<?php

/** MÓDULO: cursos_publicacaoYoutube.php
 *  Requisitos:
 *  - A página principal já inclui conexão/autenticação e fornece $idPublicacao (int).
 *  - Tabela: new_sistema_youtube_PJA
 *      codpublicacao_sy, url_sy, chavetube_sy, titulo_sy, canal_sy,
 *      visivel_sy, favorito_sy, data_sy, hora_sy
 *  - Recebe: $idPublicacao
 */

if (!isset($idPublicacao)) {
    echo '<div class="alert alert-warning">ID da publicação não informado.</div>';
    return;
}
$pubId = (int)$idPublicacao;
?>

<div class="container-fluid" data-aos="fade-up">
    <div class="row g-3">
        <!-- Formulário de cadastro -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="bi bi-youtube me-2"></i>Vídeo (YouTube/Vimeo)</h5>
                        <span class="badge text-bg-secondary">Pub: <?= (int)$pubId ?></span>
                    </div>
                    <hr class="mt-3 mb-3">

                    <form id="formVideo" class="row gy-2 gx-2">
                        <input type="hidden" name="codpublicacao_sy" value="<?= (int)$pubId ?>">

                        <div class="col-md-5">
                            <label class="form-label mb-1">URL do Vídeo (YouTube ou Vimeo)</label>
                            <input type="url" name="url_sy" class="form-control" placeholder="https://www.youtube.com/watch?v=... ou https://vimeo.com/..." required>
                            <div class="form-text">Cole a URL completa do vídeo.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label mb-1">Título</label>
                            <input type="text" name="titulo_sy" class="form-control" placeholder="Título do vídeo" maxlength="250" required>
                        </div>

                        <div class="col-md-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label mb-1 d-block">Visível</label>
                                    <div class="btn-group w-100" role="group" aria-label="Visível?">
                                        <input type="radio" class="btn-check" name="visivel_sy" id="vis1" value="1" checked>
                                        <label class="btn btn-outline-success" for="vis1">Sim</label>
                                        <input type="radio" class="btn-check" name="visivel_sy" id="vis0" value="0">
                                        <label class="btn btn-outline-secondary" for="vis0">Não</label>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label class="form-label mb-1 d-block">Favorito</label>
                                    <div class="btn-group w-100" role="group" aria-label="Favorito?">
                                        <input type="radio" class="btn-check" name="favorito_sy" id="fav1" value="1">
                                        <label class="btn btn-outline-warning" for="fav1"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" class="btn-check" name="favorito_sy" id="fav0" value="0" checked>
                                        <label class="btn btn-outline-secondary" for="fav0"><i class="bi bi-star"></i></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Adicionar Vídeo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="mb-0"><i class="bi bi-collection-play me-2"></i>Vídeos cadastrados</h5>
                        <button id="btnReload" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-repeat me-1"></i>Atualizar
                        </button>
                    </div>
                    <div id="listaVideos">
                        <!-- carregado via AJAX -->
                        <div class="text-center py-4 text-muted"><i class="bi bi-hourglass-split me-1"></i>Carregando…</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Template simples para toast -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080">
    <div id="toastMsg" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastBody">Pronto.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script>
    (function() {
        const pubId = <?= (int)$pubId ?>;
        const $lista = document.getElementById('listaVideos');
        const $form = document.getElementById('formVideo');
        const $btnReload = document.getElementById('btnReload');
        const toastEl = document.getElementById('toastMsg');
        const toastBody = document.getElementById('toastBody');
        let bsToast;

        function showToast(msg, cls) {
            if (!bsToast) bsToast = new bootstrap.Toast(toastEl, {
                delay: 2200
            });
            toastEl.className = 'toast align-items-center border-0 ' + (cls || 'text-bg-dark');
            toastBody.textContent = msg;
            bsToast.show();
        }

        function serializeForm(form) {
            const fd = new FormData(form);
            return new URLSearchParams(fd).toString();
        }

        function loadLista() {
            $lista.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-arrow-clockwise me-1"></i>Atualizando…</div>';
            fetch('publicacoesv1.0/ajax_youtubeList.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        codpublicacao: pubId
                    })
                })
                .then(r => r.text())
                .then(html => {
                    $lista.innerHTML = html;
                    wireRowActions();
                })
                .catch(() => {
                    $lista.innerHTML = '<div class="alert alert-danger">Falha ao carregar a lista.</div>';
                });
        }

        function wireRowActions() {
            // Editar (salvar linha)
            document.querySelectorAll('.btn-salvar-video').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('[data-id]');
                    if (!row) return;
                    const id = row.getAttribute('data-id');
                    const url = row.querySelector('.inp-url').value.trim();
                    const titulo = row.querySelector('.inp-titulo').value.trim();
                    const visivel = row.querySelector('.inp-visivel:checked')?.value || '0';
                    const favorito = row.querySelector('.inp-favorito:checked')?.value || '0';
                    const params = new URLSearchParams({
                        id: id,
                        url_sy: url,
                        titulo_sy: titulo,
                        visivel_sy: visivel,
                        favorito_sy: favorito,
                        codpublicacao_sy: pubId
                    });
                    fetch('publicacoesv1.0/ajax_youtubeUpdate.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                            },
                            body: params.toString()
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j.ok) {
                                showToast('Vídeo atualizado.', 'text-bg-success');
                                loadLista();
                            } else {
                                showToast(j.msg || 'Erro ao atualizar.', 'text-bg-danger');
                            }
                        })
                        .catch(() => showToast('Falha de rede.', 'text-bg-danger'));
                });
            });

            // Excluir
            document.querySelectorAll('.btn-excluir-video').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('[data-id]');
                    if (!row) return;
                    const id = row.getAttribute('data-id');
                    if (!confirm('Excluir este vídeo?')) return;
                    fetch('publicacoesv1.0/ajax_youtubeDelete.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                            },
                            body: new URLSearchParams({
                                id: id,
                                codpublicacao_sy: pubId
                            }).toString()
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j.ok) {
                                showToast('Vídeo excluído.', 'text-bg-success');
                                loadLista();
                            } else {
                                showToast(j.msg || 'Erro ao excluir.', 'text-bg-danger');
                            }
                        })
                        .catch(() => showToast('Falha de rede.', 'text-bg-danger'));
                });
            });
        }

        // Submit do formulário (INSERT)
        $form.addEventListener('submit', function(e) {
            e.preventDefault();
            const body = serializeForm(this);
            fetch('publicacoesv1.0/ajax_youtubeInsert.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                    },
                    body
                })
                .then(r => r.json())
                .then(j => {
                    if (j.ok) {
                        showToast('Vídeo adicionado!', 'text-bg-success');
                        this.reset();
                        // defaults
                        this.querySelector('#vis1').checked = true;
                        this.querySelector('#fav0').checked = true;
                        loadLista();
                    } else {
                        showToast(j.msg || 'Erro ao adicionar.', 'text-bg-danger');
                    }
                })
                .catch(() => showToast('Falha de rede.', 'text-bg-danger'));
        });

        $btnReload.addEventListener('click', loadLista);

        // Inicial
        loadLista();
    })();
</script>