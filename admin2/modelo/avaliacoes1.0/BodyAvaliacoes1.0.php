<?php
// Filtro: 0 = não acessadas; 1 = acessadas
$filtroAcessado = isset($_GET['acessado']) && $_GET['acessado'] == '1' ? 1 : 0;

// Helper para montar foto com fallback relativo ao módulo
function montarFotoUsuario(?string $pasta, ?string $img): string
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    if ($pasta !== '' && $img !== '') {
        return '../../fotos/usuarios/' . $pasta . '/' . $img;
    }
    return '../../fotos/usuarios/usuario.jpg';
}

// Consulta principal com JOINs (usuário + publicação)
$stmt = config::connect()->prepare("
    SELECT 
        f.codigoForum, f.idusuarioCF, f.idartigoCF, f.idcodforumCF,
        f.textoCF, f.visivelCF, f.acessadoCF, f.dataCF, f.destaqueCF, f.horaCF,
        u.nome AS nomeUsuario, u.imagem50 AS img50, u.pastasc AS pastaUsuario,
        p.titulo AS tituloAula
    FROM a_curso_forum f
    LEFT JOIN new_sistema_cadastro u 
        ON u.codigocadastro = f.idusuarioCF
    LEFT JOIN new_sistema_publicacoes_PJA p 
        ON p.codigopublicacoes = f.idartigoCF
    WHERE f.acessadoCF = :acessado
    ORDER BY f.dataCF DESC, f.horaCF DESC
");
$stmt->bindParam(':acessado', $filtroAcessado, PDO::PARAM_INT);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construção de links dos botões (mantém outros parâmetros)
$urlBase = strtok($_SERVER['REQUEST_URI'], '?');
$qsA = $_GET;
$qsA['acessado'] = 0;
$linkNaoAcessadas = htmlspecialchars($urlBase . '?' . http_build_query($qsA));
$qsB = $_GET;
$qsB['acessado'] = 1;
$linkAcessadas = htmlspecialchars($urlBase . '?' . http_build_query($qsB));
?>

<style>
    .avatar-40 {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .badge-dot {
        font-size: .75rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group" aria-label="Filtro de mensagens">
        <a href="<?= $linkNaoAcessadas ?>" class="btn <?= $filtroAcessado === 0 ? 'btn-warning' : 'btn-outline-warning' ?>">
            <i class="bi bi-envelope-open"></i> Não acessadas
        </a>
        <a href="<?= $linkAcessadas ?>" class="btn <?= $filtroAcessado === 1 ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi bi-envelope-check"></i> Acessadas
        </a>
    </div>
    <h5 class="mb-0">
        <i class="bi bi-chat-square-text me-2"></i>
        <?= $filtroAcessado ? 'Avaliações acessadas' : 'Avaliações não acessadas' ?>
        <span class="badge bg-secondary ms-2"><?= count($itens) ?></span>
    </h5>
</div>

<?php if (count($itens) === 0): ?>
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle me-2"></i> Nenhuma mensagem no filtro atual.
    </div>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($itens as $rw):
            $id    = (int)$rw['codigoForum'];
            $txt   = trim((string)$rw['textoCF']);
            $prev  = mb_strimwidth(strip_tags($txt), 0, 180, '...');
            $dt    = htmlspecialchars($rw['dataCF'] ?? '');
            $hr    = htmlspecialchars($rw['horaCF'] ?? '');
            $vis   = (int)$rw['visivelCF'] === 1;
            $nome  = htmlspecialchars($rw['nomeUsuario'] ?? 'Aluno');
            $titulo = htmlspecialchars($rw['tituloAula'] ?? 'Aula sem título');

            // Regra da foto conforme solicitado
            if (($rw['img50'] ?? '') === 'usuario.jpg'):
                $foto = '../../fotos/usuarios/' . ($rw['img50'] ?? '');
            else:
                $foto = montarFotoUsuario($rw['pastaUsuario'] ?? '', $rw['img50'] ?? '');
            endif;
        ?>
            <div class="list-group-item list-group-item-action py-3">
                <div class="d-flex w-100 align-items-start gap-3">
                    <img src="<?= htmlspecialchars($foto) ?>" class="avatar-40 shadow-sm border" alt="Foto">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mb-1">
                            <span class="fw-semibold text-body"><?= $nome ?></span>
                            <span class="badge bg-light text-secondary border badge-dot">
                                <i class="bi bi-journal-text me-1"></i><?= $titulo ?>
                            </span>
                            <span class="ms-auto">
                                <i class="bi bi-calendar-event me-1"></i><?= $dt ?>
                                <i class="bi bi-dot mx-1"></i>
                                <i class="bi bi-clock me-1"></i><?= $hr ?>
                                <?php if ((int)$rw['destaqueCF'] === 1): ?>
                                    <span class="badge bg-info ms-2">
                                        <i class="bi bi-star-fill me-1"></i>Destaque
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <a href="#"
                            class="text-decoration-none abrir-modal-forum"
                            data-id="<?= $id ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#modalForum">
                            <div class="text-body fw-semibold truncate-2"><?= htmlspecialchars($prev) ?></div>
                        </a>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm <?= $vis ? 'btn-success' : 'btn-outline-secondary' ?> toggle-visivel"
                            data-id="<?= $id ?>"
                            title="<?= $vis ? 'Conteúdo visível' : 'Tornar visível' ?>">
                            <i class="bi <?= $vis ? 'bi-eye-fill' : 'bi-eye-slash' ?>"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-danger excluir-forum"
                            data-id="<?= $id ?>"
                            title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal: visualizar/Responder -->
<div class="modal fade" id="modalForum" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header align-items-start">
                <div class="d-flex align-items-center gap-2">
                    <img id="modalAvatar" src="../../fotos/usuarios/usuario.jpg" class="avatar-40 border" alt="Foto">
                    <div>
                        <div class="fw-semibold" id="modalNomeUsuario">Mensagem do aluno</div>
                        <div class="small text-muted">
                            <i class="bi bi-journal-text me-1"></i><span id="modalTituloAula">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="forumConteudo" class="border rounded p-3 bg-light small" style="min-height: 120px;">
                    <div class="text-muted"><i class="bi bi-info-circle me-1"></i>Carregando conteúdo...</div>
                </div>

                <hr class="my-4">

                <form id="formRespostaForum" class="mt-3">
                    <input type="hidden" name="codigoForum" id="respCodigoForum" value="">
                    <label for="respostaProfessor" class="form-label fw-semibold">
                        Resposta do professor
                    </label>
                    <textarea class="form-control" id="respostaProfessor" name="respostaProfessor" rows="5"
                        placeholder="Escreva sua resposta..."></textarea>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i>Enviar resposta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
    <div id="toastAviso" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">Ação realizada.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    // Filtro atual (0 = não acessadas; 1 = acessadas)
    const filtroAcessado = <?= $filtroAcessado ? 1 : 0 ?>;

    // Toast utilitário
    function showToast(msg) {
        const el = document.getElementById('toastAviso');
        document.getElementById('toastMsg').textContent = msg;
        const t = new bootstrap.Toast(el, {
            delay: 2200
        });
        t.show();
    }

    // Abre modal: carrega conteúdo + dados; marca acessadoCF=1; remove da lista se estamos no filtro "não acessadas"
    $(document).on('click', '.abrir-modal-forum', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#forumConteudo').html('<div class="text-muted"><i class="bi bi-info-circle me-1"></i>Carregando conteúdo...</div>');
        $('#respCodigoForum').val(id);

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumAbrir.php',
            type: 'POST',
            data: {
                codigoForum: id
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $('#forumConteudo').html(r.html || '<em>Sem conteúdo.</em>');
                    $('#modalNomeUsuario').text(r.nome || 'Aluno');
                    $('#modalTituloAula').text(r.titulo || '-');
                    if (r.foto && r.foto !== '') {
                        $('#modalAvatar').attr('src', r.foto);
                    }

                    // Se a lista atual é "não acessadas", remover item após marcar como acessado
                    if (filtroAcessado === 0) {
                        $(`.abrir-modal-forum[data-id="${id}"]`).closest('.list-group-item').slideUp(200, function() {
                            $(this).remove();
                        });
                    }
                    showToast('Mensagem marcada como acessada.');
                } else {
                    $('#forumConteudo').html('<div class="text-danger">Erro ao carregar conteúdo.</div>');
                }
            },
            error: function() {
                $('#forumConteudo').html('<div class="text-danger">Erro de comunicação.</div>');
            }
        });
    });

    // Toggle visibilidade
    $(document).on('click', '.toggle-visivel', function() {
        const btn = $(this);
        const id = btn.data('id');

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumToggleVisivel.php',
            type: 'POST',
            data: {
                codigoForum: id
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    if (r.visivel === 1) {
                        btn.removeClass('btn-outline-secondary').addClass('btn-success')
                            .attr('title', 'Conteúdo visível')
                            .find('i').attr('class', 'bi bi-eye-fill');
                        showToast('Conteúdo tornado visível.');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-outline-secondary')
                            .attr('title', 'Tornar visível')
                            .find('i').attr('class', 'bi bi-eye-slash');
                        showToast('Conteúdo ocultado.');
                    }
                } else {
                    showToast('Não foi possível alterar visibilidade.');
                }
            },
            error: function() {
                showToast('Falha na requisição.');
            }
        });
    });

    // Excluir mensagem
    $(document).on('click', '.excluir-forum', function() {
        const id = $(this).data('id');
        if (!confirm('Tem certeza que deseja excluir esta mensagem?')) return;

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumExcluir.php',
            type: 'POST',
            data: {
                codigoForum: id
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $(`.excluir-forum[data-id="${id}"]`).closest('.list-group-item').slideUp(200, function() {
                        $(this).remove();
                    });
                    showToast('Mensagem excluída.');
                } else {
                    showToast('Não foi possível excluir.');
                }
            },
            error: function() {
                showToast('Falha na requisição.');
            }
        });
    });

    // Enviar resposta do professor
    $('#formRespostaForum').on('submit', function(e) {
        e.preventDefault();
        const dados = $(this).serialize();

        $.ajax({
            url: 'avaliacoes1.0/ajax_responderForum.php',
            type: 'POST',
            data: dados,
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    showToast('Resposta enviada com sucesso.');
                    $('#respostaProfessor').val('');
                } else {
                    showToast(r?.msg || 'Erro ao enviar resposta.');
                }
            },
            error: function() {
                showToast('Falha na requisição.');
            }
        });
    });
</script>