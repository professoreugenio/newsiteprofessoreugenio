
<script>
    // Disponibiliza o ID do administrador no JS
    const COD_ADM = <?= (int)$codadm ?>;
</script>

<?php
// ========================== CONFIG & HELPERS ==========================
date_default_timezone_set('America/Fortaleza');

// Filtro: 0 = não acessadas; 1 = acessadas
$filtroAcessado = isset($_GET['acessado']) && $_GET['acessado'] == '1' ? 1 : 0;

// Helper: foto do usuário (módulo admin)
function montarFotoUsuario(?string $pasta, ?string $img): string
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    if ($img === 'usuario.jpg' || $img === '' || $img === null) {
        return '../../fotos/usuarios/usuario.jpg';
    }
    if ($pasta !== '' && $img !== '') {
        return '../../fotos/usuarios/' . $pasta . '/' . $img;
    }
    return '../../fotos/usuarios/usuario.jpg';
}

// Saudação simples
$h = (int)date('G');
$saudacao = ($h < 12) ? 'Bom dia' : (($h < 18) ? 'Boa tarde' : 'Boa noite');

// Normaliza celular (usa DDI 55 como padrão Brasil)
function normalizarCelularBR(?string $cel): string
{
    $d = preg_replace('/\D+/', '', (string)$cel);
    if ($d === '') return '';
    // Se já começar com 55, mantém; do contrário, prefixa 55
    if (strpos($d, '55') === 0) return $d;
    return '55' . $d;
}

// Monta link do WhatsApp já com número e texto
function montarLinkWhatsapp(?string $celular, string $mensagem): string
{
    $num = normalizarCelularBR($celular);
    if ($num === '') {
        // sem número: abre só com o texto
        return 'https://wa.me/?text=' . rawurlencode($mensagem);
    }
    return 'https://wa.me/' . $num . '?text=' . rawurlencode($mensagem);
}

// ============================ CONSULTA PRINCIPAL ============================
$stmt = config::connect()->prepare("
    SELECT 
        f.codigoForum, f.idusuarioCF, f.idartigoCF, f.idcodforumCF,
        f.textoCF, f.visivelCF, f.acessadoCF, f.dataCF, f.destaqueCF, f.horaCF,
        f.permissaoCF, u.codigocadastro,
        u.nome AS nomeUsuario, u.imagem50 AS img50, u.pastasc AS pastaUsuario, u.celular,
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

// ============================ LINKS DO TOPO ============================
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

    #forumConteudoView {
        min-height: 120px;
    }

    #forumConteudoEdit {
        display: none;
    }

    /* Badges suaves para permissão */
    .badge-soft {
        border: 1px solid rgba(0, 0, 0, .08);
        padding: .35rem .6rem;
        border-radius: 999px;
        font-size: .78rem;
    }

    .badge-soft-success {
        background: #e9f9f5;
        color: #0f5132;
        border-color: rgba(16, 185, 129, .25);
    }

    .badge-soft-warning {
        background: #fff7e6;
        color: #664d03;
        border-color: rgba(255, 159, 67, .25);
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
        <a href="https://professoreugenio.com/depoimentos.php" target="_blank" class="btn btn-success">
            <i class="bi bi-chat-quote"></i> Abrir Depoimentos
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
            $id     = (int)$rw['codigoForum'];
            $txt    = trim((string)$rw['textoCF']);
            $prev   = mb_strimwidth(strip_tags($txt), 0, 180, '...');
            $dt     = htmlspecialchars($rw['dataCF'] ?? '');
            $hr     = htmlspecialchars($rw['horaCF'] ?? '');
            $vis    = (int)$rw['visivelCF'] === 1;
            $encIdUsuario    =  encrypt($rw['codigocadastro'], $action = 'e');
            $nome   = htmlspecialchars($rw['nomeUsuario'] ?? 'Aluno');
            $nomePartes = explode(' ', $nome);
            $nomeCurto = $nomePartes[0];
            $titulo = htmlspecialchars($rw['tituloAula'] ?? 'Aula sem título');

            // Foto
            if (($rw['img50'] ?? '') === 'usuario.jpg'):
                $foto = '../../fotos/usuarios/' . ($rw['img50'] ?? 'usuario.jpg');
            else:
                $foto = montarFotoUsuario($rw['pastaUsuario'] ?? '', $rw['img50'] ?? '');
            endif;

            // Permissão
            $permOK = ((int)($rw['permissaoCF'] ?? 0) === 1);

            // idUser encryptado para incluir no link
            $idUser = (string)($rw['idusuarioCF'] ?? '');
            $idEnc  = encrypt($idUser, 'e'); // ajuste o segundo parâmetro se sua função usar outro padrão

            // Link de liberação (conforme solicitado, domínio/path fornecido)
            $linkLiberacao = "https://professoreugenio.com/depoimentonovo.php?idUser={$idEnc}";

            // Mensagem do WhatsApp conforme pedido
            $mensagemWA = "{$saudacao} {$nomeCurto}. Aqui é o professor Eugênio. Verifique seus depoimentos enviados para Liberação. Clique no link : {$linkLiberacao}";

            // Número do aluno (new_sistema_cadastro.celular) -> monta o link do WhatsApp
            $hrefWA = montarLinkWhatsapp($rw['celular'] ?? '', $mensagemWA);
        ?>
            <div class="list-group-item list-group-item-action py-3">
                <div class="d-flex w-100 align-items-start gap-3">
                    <img src="<?= htmlspecialchars($foto) ?>" class="avatar-40 shadow-sm border" alt="Foto">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 small text-muted mb-1">
                            <a href="alunoAcessos.php?idUsuario=<?= $encIdUsuario ?>" target="_blank">
                                <span class="fw-semibold text-body"><?= $nome ?></span>
                            </a>
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

                        <!-- Indicador de permissão + botão WhatsApp (se necessário) -->
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <?php if ($permOK): ?>
                                <span class="badge-soft badge-soft-success">
                                    <i class="bi bi-unlock me-1"></i> Permissão concedida
                                </span>
                            <?php else: ?>
                                <span class="badge-soft badge-soft-warning">
                                    <i class="bi bi-lock me-1"></i> Sem permissão
                                </span>
                                <a class="btn btn-sm btn-success d-inline-flex align-items-center"
                                    href="<?= htmlspecialchars($hrefWA) ?>" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                        </div>

                        <a href="#"
                            class="text-decoration-none abrir-modal-forum"
                            data-id="<?= $id ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#modalForum">
                            <div class="text-body fw-semibold truncate-2" id="preview-<?= $id ?>"><?= htmlspecialchars($prev) ?></div>
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

<!-- Modal: visualizar/Responder/Editar -->
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
                <!-- Barra de ações do texto -->
                <div class="d-flex justify-content-end gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-warning" id="btnEditarTexto">
                        <i class="bi bi-pencil-square me-1"></i>Editar texto do aluno
                    </button>
                    <div id="grupoEditarTexto" style="display:none;">
                        <button type="button" class="btn btn-sm btn-primary" id="btnSalvarEdicao">
                            <i class="bi bi-save me-1"></i>Salvar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelarEdicao">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                    </div>
                </div>

                <!-- Visualização -->
                <div id="forumConteudoView" class="border rounded p-3 bg-light small">
                    <div class="text-muted"><i class="bi bi-info-circle me-1"></i>Carregando conteúdo...</div>
                </div>

                <!-- Edição -->
                <div id="forumConteudoEdit" class="mt-2">
                    <textarea class="form-control" id="textoEditAluno" rows="7" placeholder="Edite o texto do aluno..."></textarea>
                </div>

                <!-- Guarda o texto bruto -->
                <input type="hidden" id="textoAlunoRaw" value="">

                <hr class="my-4">

                <form id="formRespostaForum" class="mt-3">
                    <!-- Hiddens solicitados -->
                    <input type="hidden" name="codigoForum" id="respCodigoForum" value="">
                    <input type="hidden" name="idusuarioPara" id="idusuarioPara" value="">
                    <input type="hidden" name="idusuarioDe" id="idusuarioDe" value="">

                    <label for="respostaProfessor" class="form-label fw-semibold">
                        Resposta do professor
                    </label>
                    <textarea class="form-control" id="respostaProfessor" name="respostaProfessor" rows="5"
                        placeholder="Escreva sua resposta..."></textarea>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>

                        <!-- Botão: liberar e fechar -->
                        <button type="button" class="btn btn-success" id="btnLiberarFechar">
                            <i class="bi bi-unlock me-1"></i> Liberar e fechar
                        </button>

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

    // Helpers de texto
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function nl2br(str) {
        return String(str).replace(/\n/g, '<br>');
    }

    function formatHtmlFromText(str) {
        return nl2br(escapeHtml(str));
    }

    function truncarPreview(s, max = 180) {
        const txt = s.replace(/\s+/g, ' ').trim();
        return (txt.length > max) ? txt.slice(0, max) + '...' : txt;
    }

    function atualizarPreviewLista(id, textoNovo) {
        const el = document.getElementById('preview-' + id);
        if (el) el.textContent = truncarPreview(textoNovo, 180);
    }

    function entrarModoEdicao() {
        document.getElementById('btnEditarTexto').style.display = 'none';
        document.getElementById('grupoEditarTexto').style.display = 'inline-flex';
        document.getElementById('forumConteudoView').style.display = 'none';
        document.getElementById('forumConteudoEdit').style.display = 'block';
        document.getElementById('textoEditAluno').focus();
    }

    function sairModoEdicao() {
        document.getElementById('btnEditarTexto').style.display = '';
        document.getElementById('grupoEditarTexto').style.display = 'none';
        document.getElementById('forumConteudoView').style.display = 'block';
        document.getElementById('forumConteudoEdit').style.display = 'none';
    }

    // Abrir modal e carregar conteúdo
    $(document).on('click', '.abrir-modal-forum', function(e) {
        e.preventDefault();
        const idForum = $(this).data('id');

        sairModoEdicao();

        $('#forumConteudoView').html('<div class="text-muted"><i class="bi bi-info-circle me-1"></i>Carregando conteúdo...</div>');
        $('#respCodigoForum').val(idForum);
        $('#idusuarioDe').val(COD_ADM || 0);

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumAbrir.php',
            type: 'POST',
            data: {
                codigoForum: idForum
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $('#forumConteudoView').html(r.html || '<em>Sem conteúdo.</em>');
                    $('#textoAlunoRaw').val(r.textoRaw || '');
                    $('#textoEditAluno').val(r.textoRaw || '');

                    $('#modalNomeUsuario').text(r.nome || 'Aluno');
                    $('#modalTituloAula').text(r.titulo || '-');
                    if (r.foto && r.foto !== '') {
                        $('#modalAvatar').attr('src', r.foto);
                    }

                    if (typeof r.idusuarioCF !== 'undefined') {
                        $('#idusuarioPara').val(r.idusuarioCF);
                    } else {
                        $('#idusuarioPara').val('');
                    }

                    if (typeof filtroAcessado !== 'undefined' && filtroAcessado === 0) {
                        $(`.abrir-modal-forum[data-id="${idForum}"]`).closest('.list-group-item').slideUp(200, function() {
                            $(this).remove();
                        });
                    }
                    showToast('Mensagem marcada como acessada.');
                } else {
                    $('#forumConteudoView').html('<div class="text-danger">Erro ao carregar conteúdo.</div>');
                }
            },
            error: function() {
                $('#forumConteudoView').html('<div class="text-danger">Erro de comunicação.</div>');
            }
        });
    });

    // Edição do texto do aluno
    $('#btnEditarTexto').on('click', entrarModoEdicao);
    $('#btnCancelarEdicao').on('click', function() {
        const raw = $('#textoAlunoRaw').val() || '';
        $('#textoEditAluno').val(raw);
        sairModoEdicao();
    });
    $('#btnSalvarEdicao').on('click', function() {
        const id = $('#respCodigoForum').val();
        const novoTexto = $('#textoEditAluno').val();

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumEditarTexto.php',
            type: 'POST',
            data: {
                codigoForum: id,
                novoTexto: novoTexto
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    $('#textoAlunoRaw').val(novoTexto);
                    $('#forumConteudoView').html(formatHtmlFromText(novoTexto));
                    sairModoEdicao();
                    showToast('Texto do aluno atualizado.');
                    if (filtroAcessado === 1) atualizarPreviewLista(id, novoTexto);
                } else {
                    showToast(r?.msg || 'Erro ao salvar edição.');
                }
            },
            error: function() {
                showToast('Falha na requisição.');
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

    // Liberar e fechar (visível = 1)
    $('#btnLiberarFechar').on('click', function() {
        const id = $('#respCodigoForum').val();
        if (!id) {
            showToast('ID do fórum não encontrado.');
            return;
        }

        $.ajax({
            url: 'avaliacoes1.0/ajax_forumLiberar.php',
            type: 'POST',
            data: {
                codigoForum: id
            },
            dataType: 'json',
            success: function(r) {
                if (r && r.ok) {
                    showToast('Mensagem liberada.');
                    const modalEl = document.getElementById('modalForum');
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.hide();
                } else {
                    showToast(r?.msg || 'Não foi possível liberar a mensagem.');
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
        const dados = {
            codigoForum: $('#respCodigoForum').val(),
            respostaProfessor: $('#respostaProfessor').val()
        };

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