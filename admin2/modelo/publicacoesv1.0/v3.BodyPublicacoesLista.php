<?php
if (!defined('BASEPATH')) define('BASEPATH', true);
if (!defined('APP_ROOT'))  define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

/**
 * Este módulo espera que id/ md e idCurso / idModulo / $Nomemodulo
 * sejam resolvidos por: admin2/modelo/adm/idcursomodulo.php
 *
 * NÃO incluir <html>, <head> ou <body> aqui (padrão de módulos).
 */

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Carrega curso/módulo (sua rotina padrão)
require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php';

// ----- Parâmetros de filtro -----
$busca          = isset($_GET['busca']) ? trim((string)$_GET['busca']) : '';
$aulaSelecionada = isset($_GET['aula']) ? (int)$_GET['aula'] : 1;
if ($aulaSelecionada < 1) $aulaSelecionada = 1;
$apenasHoje     = (isset($_GET['hoje']) && $_GET['hoje'] == '1');

// ----- SQL -----
$condHoje = $apenasHoje ? " AND DATE(dataatualizacao) = CURDATE() " : "";

if ($busca !== '') {
    $sql = "
    SELECT codigopublicacoes, titulo, ordem, visivel, texto, dataatualizacao, aula
    FROM new_sistema_publicacoes_PJA
    WHERE codigocurso_sp = :idcurso
      AND codmodulo_sp   = :idmodulo
      AND aula           = :aula
      $condHoje
      AND (titulo LIKE :busca OR texto LIKE :busca)
    ORDER BY visivel DESC, aula, ordem ASC
  ";
} else {
    $sql = "
    SELECT codigopublicacoes, titulo, ordem, visivel, texto, dataatualizacao, aula
    FROM new_sistema_publicacoes_PJA
    WHERE codigocurso_sp = :idcurso
      AND codmodulo_sp   = :idmodulo
      AND aula           = :aula
      $condHoje
    ORDER BY visivel DESC, aula, ordem ASC
  ";
}

$stmt = config::connect()->prepare($sql);
$stmt->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
$stmt->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
$stmt->bindParam(":aula", $aulaSelecionada, PDO::PARAM_INT);
if ($busca !== '') {
    $like = '%' . $busca . '%';
    $stmt->bindParam(":busca", $like, PDO::PARAM_STR);
}
$stmt->execute();

$hoje = date('Y-m-d');
?>

<!-- ====== FILTROS / AÇÕES SUPERIORES ====== -->
<div class="gap-2 mb-3">
    <form method="get" class="d-flex align-itens-center flex-wrap gap-2">
        <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
        <input type="hidden" name="md" value="<?= h($_GET['md'] ?? '') ?>">
        <input type="hidden" name="aula" value="<?= h((string)$aulaSelecionada) ?>">

        <div class="form-check align-self-center">
            <input class="form-check-input" type="checkbox" id="apenasHoje" name="hoje" value="1" <?= $apenasHoje ? 'checked' : '' ?>>
            <label class="form-check-label" for="apenasHoje">Atualizadas hoje</label>
        </div>

        <div class="input-group">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por título ou conteúdo..." value="<?= h($busca) ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#ajudaPubli">
        <i class="bi bi-question-circle"></i> Ajuda
    </button>
</div>

<div id="ajudaPubli" class="collapse small mb-3">
    <div class="alert alert-info mb-0">
        <strong>Dicas:</strong> Use setas para mover a ordem; digite a ordem no campo <em>#</em> e pressione <kbd>Enter</kbd> ou clique no ✓ para salvar;
        clique no globo para alternar visibilidade; use <em>Duplicar</em> para copiar para outra aula (mesmo curso/módulo).
    </div>
</div>

<!-- ====== Paginador de Aulas (1–9) ====== -->
<?php
// Caso precise ajustar o range de aulas, altere $maxAulas:
$maxAulas = 9;
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php for ($i = 1; $i <= $maxAulas; $i++):
        $classe = ($aulaSelecionada === $i) ? 'btn-primary' : 'btn-outline-primary';
        $qs = $_GET;
        $qs['aula'] = $i;
        $lnk = 'cursos_publicacoes.php?' . http_build_query($qs);
    ?>
        <a href="<?= h($lnk) ?>" class="btn <?= $classe ?> btn-sm"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php if ($stmt->rowCount() > 0): ?>
    <h5 class="mb-3">
        <?= (int)$stmt->rowCount(); ?> Resultado(s) para <strong><?= h($Nomemodulo ?? 'Módulo') ?></strong>
        <span class="text-muted">— Aula <?= (int)$aulaSelecionada; ?></span>
        <?php if ($apenasHoje): ?><span class="badge bg-info ms-2">Hoje</span><?php endif; ?>
        <?php if ($busca !== ''): ?><span class="badge bg-warning text-dark ms-2">Busca: <?= h($busca) ?></span><?php endif; ?>
    </h5>

    <ul class="list-group">
        <?php $n = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
            $idpub   = (int)$row['codigopublicacoes'];
            $encId   = encrypt($idpub, $action = 'e'); // sua função
            $nm      = (string)$row['titulo'];
            $ordem   = (int)$row['ordem'];
            $status  = (int)$row['visivel'];
            $dtAtt   = (string)($row['dataatualizacao'] ?? '');
            $destacar = (substr($dtAtt, 0, 10) === $hoje) ? 'bg-info bg-opacity-25 border-info' : '';
            $n++;
            $duracao = 500 + ($n * 100);

            // Realce do termo no título (se houver busca)
            $tituloExib = $nm;
            if ($busca !== '') {
                $safe = preg_quote($busca, '/');
                $tituloExib = preg_replace("/($safe)/i", '<mark>$1</mark>', $tituloExib);
            }

            // Contador YouTube/ícone, mantém sua rotina
            $youtube = '';
            require APP_ROOT . '/admin2/modelo/publicacoesv1.0/contYoutube.php'; // ajuste o path conforme sua estrutura
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded <?= $destacar ?>" id="publi_<?= $idpub ?>"
                data-aos="fade-up" data-aos-duration="<?= (int)$duracao ?>">
                <div class="d-flex justify-content-between align-items-center">

                    <!-- Título -->
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-text text-success fs-5 me-2"></i>
                        <a href="cursos_publicacaoEditar.php?id=<?= h($_GET['id'] ?? '') ?>&md=<?= h($_GET['md'] ?? '') ?>&pub=<?= h($encId) ?>"
                            class="text-decoration-none fw-semibold text-dark me-3">
                            <?= (int)$ordem ?>.<?= $youtube ?> <?= (int)$idpub ?> <span class="titulo-pub"><?= $tituloExib ?></span>
                        </a>
                    </div>

                    <!-- Ações -->
                    <div class="d-flex align-items-center">

                        <!-- Ordem precisa -->
                        <div class="input-group input-group-sm me-2" style="width: 100px;">
                            <span class="input-group-text">#</span>
                            <input type="number" class="form-control text-center" min="1" step="1"
                                value="<?= (int)$ordem; ?>"
                                onkeydown="if(event.key==='Enter'){salvarOrdemPrecisao(<?= (int)$idpub ?>, this.value)}"
                                title="Enter para salvar nova ordem">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="salvarOrdemPrecisao(<?= (int)$idpub ?>, this.previousElementSibling.value)"
                                title="Salvar nova ordem">
                                <i class="bi bi-check2"></i>
                            </button>
                        </div>

                        <!-- Seta cima -->
                        <button class="btn btn-light btn-sm py-0 px-1 me-1" title="Subir"
                            onclick="mudarOrdemPublicacao(<?= (int)$idpub ?>, 'up', this)">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <!-- Seta baixo -->
                        <button class="btn btn-light btn-sm py-0 px-1 me-3" title="Descer"
                            onclick="mudarOrdemPublicacao(<?= (int)$idpub ?>, 'down', this)">
                            <i class="bi bi-arrow-down"></i>
                        </button>

                        <!-- Duplicar -->
                        <button class="btn btn-outline-primary btn-sm me-2"
                            onclick="abrirModalDuplicar(<?= (int)$idpub ?>, <?= (int)$aulaSelecionada ?>)"
                            title="Duplicar para outra aula">
                            <i class="bi bi-copy"></i>
                        </button>

                        <!-- Copiar ID -->
                        <button class="btn btn-outline-secondary btn-sm me-3"
                            onclick="copiarTexto('<?= (int)$idpub ?>')"
                            title="Copiar ID">
                            <i class="bi bi-clipboard"></i>
                        </button>

                        <!-- Visibilidade -->
                        <a href="javascript:void(0);"
                            data-id="<?= (int)$idpub; ?>"
                            data-status="<?= (int)$status; ?>"
                            onclick="togglePublicacaoVisivel(this)"
                            class="ms-2"
                            data-bs-toggle="tooltip"
                            title="Clique para <?= $status == 1 ? 'ocultar' : 'tornar visível' ?>">
                            <i class="bi bi-globe-americas fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </a>
                    </div>
                </div>

                <!-- Metadados (opcional) -->
                <div class="small text-muted mt-1">
                    Atualizado em: <?= $dtAtt ? h(date('d/m/Y H:i', strtotime($dtAtt))) : '—' ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-secondary">Nenhuma publicação encontrada para a aula <?= (int)$aulaSelecionada; ?>.</div>
<?php endif; ?>

<!-- ====== MODAL: Duplicar Publicação ====== -->
<div class="modal fade" id="modalDuplicarPubli" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formDuplicarPubli" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duplicar publicação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idpub" id="dup_idpub">
                <input type="hidden" name="idcurso" value="<?= (int)$idCurso ?>">
                <input type="hidden" name="idmodulo" value="<?= (int)$idModulo ?>">

                <div class="mb-3">
                    <label class="form-label">Aula de destino</label>
                    <input type="number" class="form-control" name="aula_destino" id="dup_aula" min="1" max="99" required>
                    <div class="form-text">A cópia será feita no <strong>mesmo curso e módulo</strong>.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Posicionar no final?</label>
                    <select class="form-select" name="no_final" id="dup_final">
                        <option value="1" selected>Sim, após a última</option>
                        <option value="0">Não, informar ordem</option>
                    </select>
                </div>

                <div class="mb-3" id="grp_ordem" style="display:none;">
                    <label class="form-label">Ordem (opcional)</label>
                    <input type="number" class="form-control" name="ordem_destino" min="1" step="1" placeholder="ex.: 10">
                </div>

                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="copiar_fotos" id="dup_fotos" value="1" checked>
                    <label class="form-check-label" for="dup_fotos">Copiar fotos associadas</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="copiar_questionario" id="dup_q" value="1">
                    <label class="form-check-label" for="dup_q">Copiar questionário (se existir)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit">
                    <span class="me-1" id="dup_spin" style="display:none;"><span class="spinner-border spinner-border-sm"></span></span>
                    Duplicar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ====== TOASTS ====== -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080">
    <div id="toastArea"></div>
</div>

<!-- ====== SCRIPTS ====== -->
<script>
    // AOS init (se AOS carregado globalmente)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.AOS) AOS.init({
            duration: 700,
            once: true
        });
    });

    // Persistir última aula no localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const aulaURL = params.get('aula');
        const ultimaAula = localStorage.getItem('ultimaAulaSelecionada');
        // Sempre salvar aula atual
        localStorage.setItem('ultimaAulaSelecionada', aulaURL || '1');
        // Se não tiver aula na URL, mas tiver no localStorage, redireciona
        if (!aulaURL && ultimaAula && parseInt(ultimaAula) >= 1 && parseInt(ultimaAula) <= <?= (int)$maxAulas ?>) {
            params.set('aula', ultimaAula);
            window.location.search = params.toString();
        }
    });

    // Toast utilitário
    function showToast(msg, type = 'success') {
        const id = 't' + Date.now();
        const bg = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-danger');
        const html = `
    <div id="${id}" class="toast align-items-center text-white ${bg} border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
        document.getElementById('toastArea').insertAdjacentHTML('beforeend', html);
        const el = document.getElementById(id);
        const t = new bootstrap.Toast(el, {
            delay: 3000
        });
        t.show();
    }

    // Visibilidade (mantém sua rota)
    function togglePublicacaoVisivel(el) {
        var id = el.getAttribute('data-id');
        var atual = el.getAttribute('data-status');
        var novo = (atual == '1') ? 0 : 1;

        fetch('publicacoesv1.0/ajax_publicacao_toggle_visivel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id) + '&visivel=' + novo
            })
            .then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    var icon = el.querySelector('i');
                    if (novo == 1) {
                        icon.classList.remove('text-danger');
                        icon.classList.add('text-success');
                        el.setAttribute('title', 'Clique para ocultar');
                    } else {
                        icon.classList.remove('text-success');
                        icon.classList.add('text-danger');
                        el.setAttribute('title', 'Clique para tornar visível');
                    }
                    el.setAttribute('data-status', novo);
                    showToast('Visibilidade atualizada.');
                } else {
                    showToast('Erro ao atualizar visibilidade!', 'danger');
                }
            }).catch(() => {
                showToast('Erro de comunicação.', 'danger');
            });
    }

    // Subir/Descer com feedback
    function mudarOrdemPublicacao(id, direcao, btn) {
        if (btn) {
            btn.disabled = true;
            btn.classList.add('disabled');
        }
        fetch('publicacoesv1.0/ajax_publicacao_mudar_ordem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id + '&direcao=' + direcao
            }).then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    location.reload();
                } else {
                    showToast(ret.mensagem || 'Não foi possível alterar a ordem.', 'danger');
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('disabled');
                    }
                }
            })
            .catch(() => {
                showToast('Erro de comunicação.', 'danger');
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                }
            });
    }

    // Ordem precisa
    function salvarOrdemPrecisao(id, nova) {
        const v = parseInt(nova, 10);
        if (isNaN(v) || v < 1) {
            showToast('Informe uma ordem válida.', 'warning');
            return;
        }
        fetch('publicacoesv1.0/ajax_publicacao_mudar_ordem.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id) + '&direcao=precisao&valor=' + encodeURIComponent(v)
        }).then(r => r.json()).then(ret => {
            if (ret.sucesso) {
                showToast('Ordem atualizada.');
                location.reload();
            } else {
                showToast(ret.mensagem || 'Não foi possível atualizar a ordem.', 'danger');
            }
        }).catch(() => showToast('Erro de comunicação.', 'danger'));
    }

    // Duplicar
    function abrirModalDuplicar(idpub, aulaAtual) {
        document.getElementById('dup_idpub').value = idpub;
        document.getElementById('dup_aula').value = aulaAtual;
        const el = document.getElementById('modalDuplicarPubli');
        const modal = new bootstrap.Modal(el);
        modal.show();
    }

    // Mostrar/ocultar campo ordem
    document.addEventListener('DOMContentLoaded', function() {
        const sel = document.getElementById('dup_final');
        if (sel) {
            sel.addEventListener('change', function() {
                document.getElementById('grp_ordem').style.display = (this.value === '0') ? '' : 'none';
            });
        }
    });

    // Submit duplicação
    document.getElementById('formDuplicarPubli').addEventListener('submit', function(e) {
        e.preventDefault();
        const btnSpin = document.getElementById('dup_spin');
        btnSpin.style.display = '';
        fetch('publicacoesv1.0/ajax_publicacaoDuplicar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(new FormData(this)).toString()
            }).then(r => r.json())
            .then(data => {
                btnSpin.style.display = 'none';
                if (data.sucesso) {
                    showToast('Publicação duplicada com sucesso!');
                    const el = document.getElementById('modalDuplicarPubli');
                    bootstrap.Modal.getInstance(el).hide();
                    location.reload();
                } else {
                    showToast(data.mensagem || 'Falha ao duplicar.', 'danger');
                }
            }).catch(() => {
                btnSpin.style.display = 'none';
                showToast('Erro de comunicação.', 'danger');
            });
    });

    // Copiar ID
    function copiarTexto(t) {
        navigator.clipboard.writeText(String(t))
            .then(() => showToast('Copiado para a área de transferência.'))
            .catch(() => showToast('Não foi possível copiar.', 'danger'));
    }
</script>