<?php

/** BodyPublicacaoVideo.php
 * Módulo de gerenciamento de vídeos por Publicação
 * Requisitos externos fornecidos pela página pai:
 * - $pubId = (int)$idPublicacao;
 * - Conexão disponível via config::connect()
 * - Bootstrap 5+ e (opcional) jQuery já carregados na página principal
 */

if (!isset($pubId)) {
    $pubId = isset($idPublicacao) ? (int)$idPublicacao : 0;
}

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$pdo = null;
try {
    if (class_exists('config')) {
        $pdo = config::connect();
    }
} catch (\Throwable $e) {
    $pdo = null;
}

$videos = [];
if ($pdo && $pubId > 0) {
    $stmt = $pdo->prepare("
        SELECT codigovideos, idpublicacaocva, idmodulocva, video, tipo, numimg, ext, size, pasta, online, totalhoras, favorito_pf, data, hora
        /* Caso você já tenha colunas para título/legenda, inclua aqui:
           , titulo, legendavtt */
        FROM a_curso_videoaulas
        WHERE idpublicacaocva = :pub
        ORDER BY favorito_pf DESC, data DESC, hora DESC, codigovideos DESC
    ");
    $stmt->execute([':pub' => $pubId]);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <h5 class="mb-0">
                <i class="bi bi-collection-play me-2"></i>
                Gerenciar Vídeos da Publicação
            </h5>
            <span class="badge text-bg-light border">
                <i class="bi bi-hash me-1"></i>Publicação: <strong><?= (int)$pubId ?></strong>
            </span>
        </div>

        <!-- Form de Upload / Inserção -->
        <form id="formNovoVideo" class="row g-3" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="idpublicacaocva" value="<?= (int)$pubId ?>">
            <input type="hidden" name="pasta" id="pastaGerada" value="">

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Título do vídeo</label>
                <input type="text" name="titulo" class="form-control" maxlength="200" placeholder="Ex.: Aula 01 - Introdução" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Duração (hh:mm:ss)</label>
                <input type="text" name="totalhoras" class="form-control" placeholder="00:00:00" pattern="^\d{2}:\d{2}:\d{2}$" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">Módulo (opcional)</label>
                <input type="number" name="idmodulocva" class="form-control" min="0" step="1" placeholder="0">
            </div>

            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold">Arquivo de vídeo</label>
                <input type="file" name="arquivo_video" id="arquivo_video" class="form-control" accept="video/*" required>
                <div class="form-text">Caminho final: <code>/videos/publicacoes/&lt;pasta&gt;/&lt;arquivo&gt;</code></div>
            </div>

            <div class="col-12 col-lg-6">
                <label class="form-label fw-semibold">Legenda (SRT ou VTT) (opcional)</label>
                <input type="file" name="arquivo_legenda" id="arquivo_legenda" class="form-control" accept=".srt,.vtt">
                <div class="form-text">Se enviar .srt, converteremos automaticamente para .vtt antes do upload.</div>
            </div>

            <div class="col-12 d-flex gap-3 align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="online" id="online" value="1" checked>
                    <label class="form-check-label" for="online">Visível (online)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="favorito_pf" id="favorito_pf" value="1">
                    <label class="form-check-label" for="favorito_pf">
                        <i class="bi bi-star-fill text-warning me-1"></i>Favorito
                    </label>
                </div>
            </div>

            <!-- Barra de Progresso -->
            <div class="col-12">
                <div class="progress" style="height: 20px;">
                    <div id="barUpload" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i> Enviar vídeo
                </button>
                <button type="button" id="btnLimpar" class="btn btn-outline-secondary">
                    <i class="bi bi-eraser me-1"></i> Limpar
                </button>
            </div>
        </form>

        <hr class="my-4">

        <!-- Lista de Vídeos -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Vídeos inseridos</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnRecarregar">
                <i class="bi bi-arrow-clockwise me-1"></i> Recarregar
            </button>
        </div>

        <div id="listaVideos" class="row g-3">
            <?php if (!$videos): ?>
                <div class="col-12">
                    <div class="alert alert-light border d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        Nenhum vídeo cadastrado para esta publicação.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($videos as $v):
                    $cod = (int)$v['codigovideos'];
                    $pasta = (string)$v['pasta'];
                    $arquivo = (string)$v['video'];
                    $dur = (string)$v['totalhoras'];
                    $visivel = (int)$v['online'] === 1;
                    $fav = (int)$v['favorito_pf'] === 1;
                    $urlVideo = "/videos/publicacoes/{$pasta}/{$arquivo}";
                    // Caso você salve legendas, ajuste 'legendavtt' conforme sua persistência
                    $urlVtt = ""; // ex.: "/videos/publicacoes/{$pasta}/" . pathinfo($arquivo, PATHINFO_FILENAME) . ".vtt";
                ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-3">
                                <div class="row g-3 align-items-start">
                                    <div class="col-12 col-md-6">
                                        <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark-subtle">
                                            <video controls preload="metadata" style="width:100%; height:100%;">
                                                <source src="<?= h($urlVideo) ?>" type="video/mp4">
                                                <?php if (!empty($urlVtt)): ?>
                                                    <track src="<?= h($urlVtt) ?>" kind="subtitles" srclang="pt-br" label="Português (BR)" default>
                                                <?php endif; ?>
                                                Seu navegador não suporta o elemento de vídeo.
                                            </video>
                                        </div>
                                        <div class="small text-muted mt-2">
                                            <code><?= h($urlVideo) ?></code>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <form class="formVideoUpdate" data-id="<?= $cod ?>">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <label class="form-label">Título</label>
                                                    <input type="text" name="titulo" class="form-control" maxlength="200" placeholder="Título do vídeo">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Duração (hh:mm:ss)</label>
                                                    <input type="text" name="totalhoras" class="form-control" value="<?= h($dur ?: '') ?>" placeholder="00:00:00" pattern="^\d{2}:\d{2}:\d{2}$">
                                                </div>
                                                <div class="col-6 d-flex align-items-center gap-3">
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="online" value="1" <?= $visivel ? 'checked' : '' ?>>
                                                        <label class="form-check-label">Visível</label>
                                                    </div>
                                                    <div class="form-check mt-4">
                                                        <input class="form-check-input" type="checkbox" name="favorito_pf" value="1" <?= $fav ? 'checked' : '' ?>>
                                                        <label class="form-check-label">
                                                            <i class="bi bi-star-fill text-warning me-1"></i>Favorito
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <label class="btn btn-outline-secondary btn-sm position-relative">
                                                            <input type="file" class="d-none inputReplaceVideo" accept="video/*">
                                                            <i class="bi bi-arrow-repeat me-1"></i> Trocar vídeo
                                                        </label>

                                                        <label class="btn btn-outline-secondary btn-sm position-relative">
                                                            <input type="file" class="d-none inputReplaceCaption" accept=".srt,.vtt">
                                                            <i class="bi bi-file-earmark-text me-1"></i> Trocar legenda
                                                        </label>

                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="bi bi-save me-1"></i> Salvar alterações
                                                        </button>

                                                        <button type="button" class="btn btn-outline-danger btn-sm btnExcluir" data-id="<?= $cod ?>">
                                                            <i class="bi bi-trash me-1"></i> Excluir
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="small text-muted mt-2">
                                            <span class="me-3"><i class="bi bi-calendar-event me-1"></i><?= h($v['data'] ?? '') ?></span>
                                            <span class="me-3"><i class="bi bi-clock me-1"></i><?= h($v['hora'] ?? '') ?></span>
                                            <span><i class="bi bi-folder2-open me-1"></i><?= h($pasta) ?></span>
                                        </div>
                                    </div>
                                </div> <!-- row -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- #listaVideos -->
    </div>
</div>

<script>
    (function() {
        // Util: nome da pasta "NomMesAbrev_YYYYMMDD-HHMMSS" (pt-BR)
        function gerarNomePasta() {
            const meses = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];
            const d = new Date();
            const mm = meses[d.getMonth()];
            const y = d.getFullYear();
            const pad = n => String(n).padStart(2, '0');
            const stamp = `${y}${pad(d.getMonth()+1)}${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
            return `${mm}_${stamp}`;
        }

        // Converte texto SRT em conteúdo VTT
        function srtToVtt(srtText) {
            // Regras simples: adiciona "WEBVTT" no topo e troca vírgulas por pontos nos timestamps
            const header = "WEBVTT\n\n";
            const body = srtText
                .replace(/\r/g, '')
                .replace(/(\d+)\n(\d{2}:\d{2}:\d{2}),(\d{3}) --> (\d{2}:\d{2}:\d{2}),(\d{3})/g, function(_, idx, h1, ms1, h2, ms2) {
                    return `${idx}\n${h1}.${ms1} --> ${h2}.${ms2}`;
                })
                .replace(/(\d{2}:\d{2}:\d{2}),(\d{3})/g, "$1.$2"); // segurança extra
            return header + body;
        }

        // Helpers UI
        const $form = document.getElementById('formNovoVideo');
        const $bar = document.getElementById('barUpload');
        const $btnLimpar = document.getElementById('btnLimpar');
        const $btnRecarregar = document.getElementById('btnRecarregar');
        const $pasta = document.getElementById('pastaGerada');

        // Define a pasta no carregamento do form
        $pasta.value = gerarNomePasta();

        $btnLimpar?.addEventListener('click', () => {
            $form.reset();
            $bar.style.width = '0%';
            $bar.textContent = '0%';
            $pasta.value = gerarNomePasta();
        });

        $btnRecarregar?.addEventListener('click', () => {
            // Simples: recarrega a página (ou implemente reload via AJAX)
            location.reload();
        });

        // Envio do novo vídeo com progresso e conversão SRT->VTT
        $form?.addEventListener('submit', async (ev) => {
            ev.preventDefault();

            const fd = new FormData($form);

            // Converte legenda SRT (se houver) para VTT antes do envio
            const legFile = document.getElementById('arquivo_legenda')?.files?.[0] || null;
            if (legFile) {
                if (legFile.name.toLowerCase().endsWith('.srt')) {
                    const text = await legFile.text();
                    const vttText = srtToVtt(text);
                    const vttBlob = new Blob([vttText], {
                        type: 'text/vtt'
                    });
                    const base = legFile.name.replace(/\.srt$/i, '');
                    fd.delete('arquivo_legenda');
                    fd.append('legenda_vtt', vttBlob, base + '.vtt'); // backend deve aceitar 'legenda_vtt'
                } else if (legFile.name.toLowerCase().endsWith('.vtt')) {
                    // Renomeia o campo para padronizar no backend, se desejar
                    fd.delete('arquivo_legenda');
                    fd.append('legenda_vtt', legFile, legFile.name);
                }
            }

            // Normaliza checkboxes
            fd.set('online', document.getElementById('online').checked ? '1' : '0');
            fd.set('favorito_pf', document.getElementById('favorito_pf').checked ? '1' : '0');

            // Endpoint de inserção
            const url = 'publicacoesv1.0/ajax_publicacaoVideoInsert.php';

            // Envio via XHR para capturar progresso
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    $bar.style.width = pct + '%';
                    $bar.textContent = pct + '%';
                }
            });

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    try {
                        const ok = xhr.status >= 200 && xhr.status < 300;
                        if (!ok) throw new Error('Falha no upload. Status ' + xhr.status);
                        const resp = JSON.parse(xhr.responseText || '{}');
                        if (resp && resp.sucesso) {
                            // Reset e recarrega lista
                            $form.reset();
                            $pasta.value = gerarNomePasta();
                            $bar.style.width = '0%';
                            $bar.textContent = '0%';
                            // Atualiza rápido: recarrega página (ou refaça a lista via AJAX)
                            location.reload();
                        } else {
                            alert(resp.mensagem || 'Não foi possível concluir o envio.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Erro no processamento do upload.');
                    }
                }
            };

            xhr.send(fd);
        });

        // Ações por cartão de vídeo
        // Salvar alterações (título/duração/online/favorito)
        document.querySelectorAll('.formVideoUpdate').forEach(function(formEl) {
            formEl.addEventListener('submit', async function(ev) {
                ev.preventDefault();
                const id = this.getAttribute('data-id');
                const fd = new FormData(this);
                fd.append('codigovideos', id);

                // Normaliza checkboxes (se não marcados não vêm)
                fd.set('online', this.querySelector('input[name="online"]')?.checked ? '1' : '0');
                fd.set('favorito_pf', this.querySelector('input[name="favorito_pf"]')?.checked ? '1' : '0');

                try {
                    const r = await fetch('publicacoesv1.0/ajax_publicacaoVideoUpdate.php', {
                        method: 'POST',
                        body: fd
                    });
                    const js = await r.json();
                    if (js && js.sucesso) {
                        // Feedback simples
                        alert('Alterações salvas com sucesso.');
                        location.reload();
                    } else {
                        alert(js.mensagem || 'Falha ao salvar.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro ao salvar alterações.');
                }
            });

            // Trocar vídeo
            formEl.querySelector('.inputReplaceVideo')?.addEventListener('change', async function() {
                const id = formEl.getAttribute('data-id');
                const file = this.files?.[0];
                if (!file) return;

                const fd = new FormData();
                fd.append('codigovideos', id);
                fd.append('novo_video', file);

                try {
                    const r = await fetch('publicacoesv1.0/ajax_publicacaoVideoReplace.php', {
                        method: 'POST',
                        body: fd
                    });
                    const js = await r.json();
                    if (js && js.sucesso) {
                        alert('Vídeo substituído.');
                        location.reload();
                    } else {
                        alert(js.mensagem || 'Não foi possível substituir o vídeo.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro ao substituir vídeo.');
                } finally {
                    this.value = '';
                }
            });

            // Trocar legenda
            formEl.querySelector('.inputReplaceCaption')?.addEventListener('change', async function() {
                const id = formEl.getAttribute('data-id');
                const file = this.files?.[0];
                if (!file) return;

                const fd = new FormData();
                fd.append('codigovideos', id);

                // Converter srt -> vtt
                if (file.name.toLowerCase().endsWith('.srt')) {
                    try {
                        const text = await file.text();
                        const vttText = srtToVtt(text);
                        const vttBlob = new Blob([vttText], {
                            type: 'text/vtt'
                        });
                        const base = file.name.replace(/\.srt$/i, '');
                        fd.append('legenda_vtt', vttBlob, base + '.vtt');
                    } catch (e) {
                        console.error(e);
                        alert('Erro ao converter SRT para VTT.');
                        this.value = '';
                        return;
                    }
                } else {
                    // VTT direto
                    fd.append('legenda_vtt', file, file.name);
                }

                try {
                    const r = await fetch('publicacoesv1.0/ajax_publicacaoVideoReplaceCaption.php', {
                        method: 'POST',
                        body: fd
                    });
                    const js = await r.json();
                    if (js && js.sucesso) {
                        alert('Legenda atualizada.');
                        location.reload();
                    } else {
                        alert(js.mensagem || 'Não foi possível atualizar a legenda.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro ao atualizar legenda.');
                } finally {
                    this.value = '';
                }
            });
        });

        // Excluir
        document.querySelectorAll('.btnExcluir').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                if (!confirm('Excluir este vídeo? Esta ação não pode ser desfeita.')) return;
                const fd = new FormData();
                fd.append('codigovideos', id);
                try {
                    const r = await fetch('publicacoesv1.0/ajax_publicacaoVideoDelete.php', {
                        method: 'POST',
                        body: fd
                    });
                    const js = await r.json();
                    if (js && js.sucesso) {
                        alert('Vídeo excluído.');
                        location.reload();
                    } else {
                        alert(js.mensagem || 'Não foi possível excluir.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro ao excluir vídeo.');
                }
            });
        });
    })();
</script>

<style>
    /* Toques visuais leves (Bootstrap 5+ já presente) */
    .progress {
        background: #f1f3f5;
    }

    .progress-bar {
        font-weight: 600;
    }

    .card video {
        outline: none;
    }
</style>