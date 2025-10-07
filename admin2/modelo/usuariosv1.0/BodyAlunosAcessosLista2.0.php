<?php
// 1. Filtro de data
$dataSel = isset($_GET['data']) && $_GET['data'] ? $_GET['data'] : date('Y-m-d');

// 2. Datas disponíveis para o filtro (opcional)
$stmtDatas = config::connect()->query("SELECT DISTINCT datara FROM a_site_registraacessos ORDER BY datara DESC");
$datasDisponiveis = $stmtDatas->fetchAll(PDO::FETCH_COLUMN);

// 3. Consulta de acessos do dia
// Pegando 1x cada aluno, primeira turma encontrada, primeira hora de acesso daquele dia
$stmtAcessos = config::connect()->prepare("
    SELECT 
        a.idusuariora,
        a.idturmara,
        a.datara,
        MIN(a.horara) as primeira_hora,
        c.nome,
        t.nometurma
    FROM a_site_registraacessos a
    INNER JOIN new_sistema_cadastro c ON a.idusuariora = c.codigocadastro
    LEFT JOIN new_sistema_cursos_turmas t ON t.chave = a.idturmara
    WHERE a.datara = :dataSel
    GROUP BY a.idusuariora, a.datara
    ORDER BY primeira_hora ASC, c.nome
");
$stmtAcessos->bindParam(':dataSel', $dataSel);
$stmtAcessos->execute();
$alunosAcesso = $stmtAcessos->fetchAll(PDO::FETCH_ASSOC);
$totalAcessos = count($alunosAcesso);
?>

<!-- Filtro de data e total de acessos -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <form method="get" class="d-flex align-items-center gap-2">
        <label for="data" class="form-label fw-semibold mb-0"><i class="bi bi-calendar-event me-2"></i>Data:</label>
        <input type="date" name="data" id="data" class="form-control" style="width: 170px"
            value="<?= htmlspecialchars($dataSel) ?>"
            onchange="this.form.submit()">
        <!-- (Ou use o select se preferir) -->
    </form>
    <div>
        <span class="fs-5 badge bg-primary">
            <i class="bi bi-person-check me-1"></i>
            <?= $totalAcessos ?> acesso<?= $totalAcessos == 1 ? '' : 's' ?> registrado<?= $totalAcessos == 1 ? '' : 's' ?> no dia
            <?= date('d/m/Y', strtotime($dataSel)) ?>
        </span>
    </div>
</div>

<?php if ($totalAcessos > 0): ?>
    <ul class="list-group">
        <?php foreach ($alunosAcesso as $a): ?>
            <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center">
                    <span class="badge rounded-pill bg-secondary me-3 fs-6 py-2 px-3"><?= htmlspecialchars($a['primeira_hora']) ?></span>
                    <div>
                        <span class="fw-bold fs-6"><?= htmlspecialchars($a['nome']) ?></span>
                        <div class="small text-muted"><?= htmlspecialchars($a['nometurma'] ?? '---') ?></div>
                    </div>
                </div>
                <div>
                    <!-- Botão para ver URLs acessadas -->
                    <button class="btn btn-outline-primary btn-sm ver-urls"
                        data-user="<?= $a['idusuariora'] ?>"
                        data-turma="<?= $a['idturmara'] ?>"
                        data-data="<?= $a['datara'] ?>">
                        <i class="bi bi-link-45deg"></i> URLs
                    </button>
                    <!-- Botão para ver IPs -->
                    <button class="btn btn-outline-dark btn-sm ver-ips"
                        data-user="<?= $a['idusuariora'] ?>"
                        data-data="<?= $a['datara'] ?>">
                        <i class="bi bi-wifi"></i> IPs
                    </button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-info my-3">Nenhum acesso registrado nesta data.</div>
<?php endif; ?>

<!-- Modal para URLs -->
<div class="modal fade" id="modalURLs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">URLs Acessadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalURLs">
                <!-- URLs vão aqui -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para IPs -->
<div class="modal fade" id="modalIPs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">IPs Registrados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalIPs">
                <!-- IPs vão aqui -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para Detalhes do IP -->
<div class="modal fade" id="modalDetalhesIP" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do IP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyModalDetalhesIP">
                <!-- Detalhes do IP aqui -->
                <div class="text-center my-4">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2">Buscando informações...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts AJAX para abrir os modais e buscar dados -->
<script>
    // URLs
    document.querySelectorAll('.ver-urls').forEach(btn => {
        btn.addEventListener('click', function() {
            let user = this.dataset.user;
            let turma = this.dataset.turma;
            let data = this.dataset.data;
            fetch('ajax_buscar_urls.php?user=' + user + '&turma=' + turma + '&data=' + data)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('bodyModalURLs').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('modalURLs')).show();
                });
        });
    });

    // IPs
    document.querySelectorAll('.ver-ips').forEach(btn => {
        btn.addEventListener('click', function() {
            let user = this.dataset.user;
            let data = this.dataset.data;
            fetch('ajax_buscar_ips.php?user=' + user + '&data=' + data)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('bodyModalIPs').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('modalIPs')).show();
                });
        });
    });

    // Detalhes do IP (ao clicar em cada IP dentro do modalIPs)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('ip-detalhe')) {
            var ip = e.target.getAttribute('data-ip');
            var modal = new bootstrap.Modal(document.getElementById('modalDetalhesIP'));
            var body = document.getElementById('bodyModalDetalhesIP');
            body.innerHTML = `
                <div class="text-center my-4">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2">Buscando informações...</div>
                </div>
            `;
            modal.show();
            fetch('https://ipinfo.io/' + ip + '/json')
                .then(resp => resp.json())
                .then(data => {
                    let html = `
                        <div class="mb-2"><strong>IP:</strong> ${data.ip || ip}</div>
                        <div class="mb-2"><strong>Cidade:</strong> ${data.city || '-'}</div>
                        <div class="mb-2"><strong>Região:</strong> ${data.region || '-'}</div>
                        <div class="mb-2"><strong>País:</strong> ${data.country || '-'}</div>
                        <div class="mb-2"><strong>Org/Operadora:</strong> ${data.org || '-'}</div>
                        <div class="mb-2"><strong>Hostname:</strong> ${data.hostname || '-'}</div>
                        <div class="mb-2"><strong>Localização:</strong> ${data.loc || '-'}</div>
                        <hr>
                        <a href="https://www.abuseipdb.com/check/${ip}" target="_blank" class="btn btn-outline-danger btn-sm">Verificar reputação</a>
                    `;
                    body.innerHTML = html;
                })
                .catch(() => {
                    body.innerHTML = `<div class="alert alert-danger">Não foi possível buscar informações deste IP.</div>`;
                });
        }
    });
</script>