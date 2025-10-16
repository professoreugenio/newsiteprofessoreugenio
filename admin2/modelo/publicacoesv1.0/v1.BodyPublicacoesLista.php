<?php if (!defined('BASEPATH')) define('BASEPATH', true); ?>
<?php if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__)); ?>
<?php require_once APP_ROOT . '/conexao/class.conexao.php'; ?>
<?php require_once APP_ROOT . '/autenticacao.php'; ?>

<!-- Campo de busca -->
<?php echo $idCurso;  ?>
<form method="get" class="mb-3">
    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']); ?>">
    <input type="hidden" name="md" value="<?= htmlspecialchars($_GET['md']); ?>">

    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group">
                <input type="text" name="busca" class="form-control"
                    placeholder="Buscar por título ou conteúdo..."
                    value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </div>

        <div class="col-auto">
            <?php $apenasHoje = isset($_GET['hoje']) && $_GET['hoje'] == '1'; ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="chkHoje" name="hoje" value="1" <?= $apenasHoje ? 'checked' : '' ?>>
                <label class="form-check-label" for="chkHoje"><strong>hoje</strong></label>
            </div>
        </div>
        
    </div>
</form>

<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>

<!-- Navegação por aulas (mantida) -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <?php
    $aulaAtual = isset($_GET['aula']) ? intval($_GET['aula']) : 1;
    for ($i = 1; $i <= 9; $i++):
        $classe = $aulaAtual === $i ? 'btn-primary' : 'btn-outline-primary';
        $link = "cursos_publicacoes.php?id={$_GET['id']}&md={$_GET['md']}&aula=$i";
    ?>
        <a href="<?= $link ?>" class="btn <?= $classe ?> btn-sm"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>

<?php
// ====== CONSULTA (alterada para buscar em qualquer aula quando aplicável) ======
$busca           = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$aulaSelecionada = isset($_GET['aula']) ? intval($_GET['aula']) : 1;
$apenasHoje      = isset($_GET['hoje']) && $_GET['hoje'] == '1';

// Só filtra por aula quando NÃO estiver buscando e NÃO for "somente hoje"
$filtraPorAula = ($busca === '' && !$apenasHoje);

$baseSql = "
    SELECT codigopublicacoes, titulo, ordem, visivel, texto, dataatualizacao, aula
    FROM new_sistema_publicacoes_PJA
    WHERE codigocurso_sp = :idcurso
      AND codmodulo_sp  = :idmodulo
";

if ($filtraPorAula) {
    $baseSql .= " AND aula = :aula";
}

$condBuscas = [];
if ($busca !== '') {
    $condBuscas[] = "(titulo LIKE :busca OR texto LIKE :busca)";
}
if ($apenasHoje) {
    $condBuscas[] = "DATE(dataatualizacao) = CURDATE()";
}
if (!empty($condBuscas)) {
    $baseSql .= " AND " . implode(" AND ", $condBuscas);
}

$sql = $baseSql . " ORDER BY visivel DESC, aula ASC, ordem ASC";

$stmt = config::connect()->prepare($sql);
$stmt->bindParam(":idcurso", $idCurso, PDO::PARAM_INT);
$stmt->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
if ($filtraPorAula) {
    $stmt->bindParam(":aula", $aulaSelecionada, PDO::PARAM_INT);
}
if ($busca !== '') {
    $buscaLike = '%' . $busca . '%';
    $stmt->bindParam(":busca", $buscaLike, PDO::PARAM_STR);
}
$stmt->execute();
?>

<?php if ($stmt->rowCount() > 0): ?>
    <h5><?= (int)$stmt->rowCount(); ?> Resultados para <?= htmlspecialchars($Nomemodulo); ?></h5>
    <?php $hoje = date('Y-m-d'); ?>

    <!-- TOPO: Selecionar todos + Copiar selecionadas -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="chkSelecionarTodos">
            <label class="form-check-label" for="chkSelecionarTodos">
                Selecionar todos
            </label>
        </div>

        <button type="button" class="btn btn-sm btn-primary" id="btnAbrirModalCopiar"
            data-bs-toggle="modal" data-bs-target="#modalCopiarPublicacoes" disabled>
            <i class="bi bi-files"></i> Copiar selecionadas
        </button>
    </div>

    <ul class="list-group">
        <?php $n = 0; ?>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
            $idPub            = (int)$row['codigopublicacoes'];
            $encId            = encrypt($idPub, $action = 'e');
            $nm               = $row['titulo'];
            $ordem            = $row['ordem'];
            $status           = $row['visivel'];
            $dataAtualizacao  = $row['dataatualizacao'] ?? '';
            $destacar         = (substr($dataAtualizacao, 0, 10) == $hoje) ? 'bg-info bg-opacity-25 border-info' : '';
            $n++;
            $duracao          = 500 + ($n * 100);
            ?>
            <?php
            // Script que calcula $youtube baseado no id da publicação (mantido)
            require 'publicacoesv1.0/contYoutube.php';
            ?>

            <li class="list-group-item flex-column mb-2 shadow-sm rounded <?= $destacar ?>" id="publi_<?= $idPub ?>">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <!-- Checkbox da publicação (novo) -->
                        <input class="form-check-input me-2 chkPub" type="checkbox"
                            value="<?= $idPub ?>" data-idpub="<?= $idPub ?>" aria-label="Selecionar publicação">

                        <i class="bi bi-file-text text-success fs-5"></i>
                        <a href="cursos_publicacaoEditar.php?id=<?= $_GET['id']; ?>&md=<?= $_GET['md']; ?>&pub=<?= $encId; ?>"
                            class="text-decoration-none fw-semibold text-dark me-3">
                            <?= (int)$row['aula']; ?>.<?= $ordem; ?> <?= $youtube; ?> <?= $idPub; ?> <?= htmlspecialchars($nm); ?>
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Badge ordem -->
                        <span class="badge bg-secondary rounded-pill me-2"><?= $ordem; ?></span>
                        <!-- Seta cima -->
                        <button class="btn btn-light btn-sm py-0 px-1 me-1" title="Subir"
                            onclick="mudarOrdemPublicacao(<?= $idPub ?>, 'up')">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <!-- Seta baixo -->
                        <button class="btn btn-light btn-sm py-0 px-1 me-3" title="Descer"
                            onclick="mudarOrdemPublicacao(<?= $idPub ?>, 'down')">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <!-- Globo visível -->
                        <a href="javascript:void(0);"
                            data-id="<?= $idPub; ?>"
                            data-status="<?= $status; ?>"
                            onclick="togglePublicacaoVisivel(this)"
                            class="ms-2"
                            data-bs-toggle="tooltip"
                            title="Clique para <?= $status == 1 ? 'ocultar' : 'tornar visível' ?>">
                            <i class="bi bi-globe-americas fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </a>
                    </div>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>

<?php else: ?>
    <p>Nenhuma publicação encontrada.</p>
<?php endif; ?>

<!-- MODAL: Copiar Publicações -->
<div class="modal fade" id="modalCopiarPublicacoes" tabindex="-1" aria-labelledby="modalCopiarPublicacoesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalCopiarPublicacoesLabel">
                    <i class="bi bi-files"></i> Copiar publicações selecionadas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <?php
                // Cursos destino
                $pdo = config::connect();
                $sqlCursos = "SELECT codigocategorias, nome FROM new_sistema_categorias_PJA WHERE lixeirasc != 1 ORDER BY nome";
                $cursosStmt = $pdo->query($sqlCursos);
                $cursos = $cursosStmt ? $cursosStmt->fetchAll(PDO::FETCH_ASSOC) : [];
                ?>

                <div class="mb-3">
                    <label class="form-label">Curso de destino</label>
                    <select id="selCursoDestino" class="form-select" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($cursos as $c): ?>
                            <option value="<?= (int)$c['codigocategorias'] ?>">
                                <?= htmlspecialchars($c['nome']) ?>
                                <?= (int)$c['codigocategorias']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Módulo de destino</label>
                    <select id="selModuloDestino" class="form-select" required disabled>
                        <option value="">Selecione o curso primeiro...</option>
                    </select>
                </div>

                <div class="form-text">
                    Os itens selecionados serão copiados para o curso/módulo de destino, preservando <em>aula</em>, <em>ordem</em> e <em>visibilidade</em>.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarCopia" class="btn btn-primary" disabled>
                    <i class="bi bi-clipboard2-check"></i> Copiar agora
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== Persistência de aula selecionada (mantido)
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const aulaURL = params.get('aula');
        const ultimaAula = localStorage.getItem('ultimaAulaSelecionada');
        // Salva sempre a aula atual (se houver)
        if (aulaURL) localStorage.setItem('ultimaAulaSelecionada', aulaURL);
        // Se não tiver aula na URL, mas tiver no localStorage, redireciona
        if (!aulaURL && ultimaAula && parseInt(ultimaAula) >= 1 && parseInt(ultimaAula) <= 9) {
            params.set('aula', ultimaAula);
            // Observação: quando houver busca/hoje, o filtro por aula é ignorado no SQL (conforme regra acima)
            window.location.search = params.toString();
        }
    });

    // ===== Visibilidade (mantido)
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
                    // Atualize apenas o ícone sem recarregar tudo!
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
                } else {
                    alert('Erro ao atualizar visibilidade!');
                }
            });
    }

    // ===== Ordem (mantido)
    function mudarOrdemPublicacao(id, direcao) {
        fetch('publicacoesv1.0/ajax_publicacao_mudar_ordem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id + '&direcao=' + direcao
            })
            .then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    location.reload();
                } else {
                    alert('Não foi possível alterar a ordem.');
                }
            });
    }

    // ===== Selecionar todos / habilitar botão copiar
    const chkTodos = document.getElementById('chkSelecionarTodos');
    const btnAbrirModal = document.getElementById('btnAbrirModalCopiar');

    function atualizarEstadoBotaoCopiar() {
        const marcados = document.querySelectorAll('.chkPub:checked');
        btnAbrirModal.disabled = marcados.length === 0;
    }

    if (chkTodos) {
        chkTodos.addEventListener('change', () => {
            document.querySelectorAll('.chkPub').forEach(ch => ch.checked = chkTodos.checked);
            atualizarEstadoBotaoCopiar();
        });
    }
    document.addEventListener('change', (e) => {
        if (e.target.classList && e.target.classList.contains('chkPub')) {
            atualizarEstadoBotaoCopiar();
        }
    });

    // ===== Modal: carrega módulos ao escolher curso
    const selCursoDestino = document.getElementById('selCursoDestino');
    const selModuloDestino = document.getElementById('selModuloDestino');
    const btnConfirmarCopia = document.getElementById('btnConfirmarCopia');

    function toggleBtnConfirmar() {
        btnConfirmarCopia.disabled = !(selCursoDestino.value && selModuloDestino.value);
    }

    if (selCursoDestino) {
        selCursoDestino.addEventListener('change', () => {
            const idCurso = selCursoDestino.value;
            selModuloDestino.innerHTML = '<option value="">Carregando...</option>';
            selModuloDestino.disabled = true;
            toggleBtnConfirmar();

            if (!idCurso) {
                selModuloDestino.innerHTML = '<option value="">Selecione o curso primeiro...</option>';
                return;
            }

            fetch('publicacoesv1.0/ajax_listarModulosDestino.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'idcurso=' + encodeURIComponent(idCurso)
                })
                .then(r => r.json())
                .then(ret => {
                    if (!ret.sucesso) throw new Error('Falha ao listar módulos.');
                    selModuloDestino.innerHTML = '<option value="">Selecione...</option>';
                    ret.opcoes.forEach(o => {
                        const opt = document.createElement('option');
                        opt.value = o.id;
                        opt.textContent = o.nome;
                        selModuloDestino.appendChild(opt);
                    });
                    selModuloDestino.disabled = false;
                    toggleBtnConfirmar();
                })
                .catch(() => {
                    selModuloDestino.innerHTML = '<option value="">Erro ao carregar módulos</option>';
                    selModuloDestino.disabled = true;
                });
        });
    }
    if (selModuloDestino) {
        selModuloDestino.addEventListener('change', toggleBtnConfirmar);
    }

    // ===== Disparo da cópia
    if (btnConfirmarCopia) {
        btnConfirmarCopia.addEventListener('click', () => {
            const ids = Array.from(document.querySelectorAll('.chkPub:checked'))
                .map(ch => ch.getAttribute('data-idpub'));
            if (!ids.length) return;

            const idCursoDestino = selCursoDestino.value;
            const idModuloDestino = selModuloDestino.value;
            if (!idCursoDestino || !idModuloDestino) return;

            const formData = new URLSearchParams();
            formData.append('idcurso', idCursoDestino);
            formData.append('idmodulo', idModuloDestino);
            formData.append('idorigem_modulo', '<?= (int)$idModulo ?>'); // módulo atual (origem)
            formData.append('idcurso_origem', '<?= (int)$idCurso ?>'); // curso atual (origem)
            ids.forEach(id => formData.append('ids[]', id));

            btnConfirmarCopia.disabled = true;

            fetch('publicacoesv1.0/ajax_copiarPublicacoes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(r => r.json())
                .then(ret => {
                    btnConfirmarCopia.disabled = false;
                    if (ret.sucesso) {
                        alert(`Cópia concluída: ${ret.copiadas} registro(s). Ignorados (já existiam): ${ret.ignoradas}.`);
                        const modalEl = document.getElementById('modalCopiarPublicacoes');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();
                        document.querySelectorAll('.chkPub').forEach(ch => ch.checked = false);
                        btnAbrirModal.disabled = true;
                        if (chkTodos) chkTodos.checked = false;
                    } else {
                        alert(ret.mensagem || 'Não foi possível copiar.');
                    }
                })
                .catch(() => {
                    btnConfirmarCopia.disabled = false;
                    alert('Erro de comunicação ao copiar.');
                });
        });
    }
</script>