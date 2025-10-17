<?php
// Filtros por Ano e Turma
$stmtAnos = config::connect()->query("SELECT DISTINCT YEAR(data_ins) AS ano FROM new_sistema_inscricao_PJA ORDER BY ano DESC");
$anos = $stmtAnos->fetchAll(PDO::FETCH_COLUMN);

$stmtTurmas = config::connect()->query("
    SELECT t.chave, t.nometurma 
    FROM new_sistema_cursos_turmas t
    INNER JOIN new_sistema_inscricao_PJA i ON i.chaveturma = t.chave
    GROUP BY t.chave, t.nometurma
    ORDER BY t.nometurma

    DESC LIMIT 0,30
");
$turmas = $stmtTurmas->fetchAll(PDO::FETCH_ASSOC);

$anoSel   = isset($_GET['ano'])   ? intval($_GET['ano'])   : 0;
$turmaSel = isset($_GET['turma']) ? $_GET['turma']         : '';
$busca    = isset($_GET['busca']) ? trim($_GET['busca'])   : '';

// Monta as condições de filtro
$whereArr = [];
$params = [];
if ($busca) {
    $whereArr[] = "(c.nome LIKE :busca OR c.email LIKE :busca OR c.celular LIKE :busca)";
    $params[':busca'] = "%$busca%";
}
if ($anoSel) {
    $whereArr[] = "YEAR(i.data_ins) = :ano";
    $params[':ano'] = $anoSel;
}
if ($turmaSel) {
    $whereArr[] = "i.chaveturma = :turma";
    $params[':turma'] = $turmaSel;
}
$whereSql = $whereArr ? 'WHERE ' . implode(' AND ', $whereArr) : '';

// Paginação
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$porPagina = 50;
$offset = ($pagina - 1) * $porPagina;

// Consulta total para paginação
$stmtCount = config::connect()->prepare("
    SELECT COUNT(*) as total
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
    $whereSql
");
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalAlunos = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalAlunos / $porPagina);

// Consulta cursos (dropdown lateral direita)
$stmtCursos = config::connect()->query("
    SELECT DISTINCT t.codigoturma, t.nometurma
    FROM new_sistema_cursos_turmas t
    INNER JOIN new_sistema_inscricao_PJA i ON i.chaveturma = t.chave
    ORDER BY t.nometurma
");
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

// Consulta alunos paginados (aplicando filtros!)
$sqlAlunos = "
    SELECT i.codigoinscricao, i.codigousuario, i.chaveturma, i.data_ins, 
           c.nome, c.email, c.celular, c.pastasc, c.imagem200, t.nometurma, t.codigoturma, c.datanascimento_sc
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
    $whereSql
    ORDER BY i.data_ins DESC
    LIMIT $porPagina OFFSET $offset
";
$stmt = config::connect()->prepare($sqlAlunos);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();

// Função foto (fallback dinâmico)
function fotoAlunoUrl($pasta, $imagem)
{
    $urlFoto = "https://professoreugenio.com/fotos/usuarios/{$pasta}/{$imagem}";
    if (!$imagem) return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    $headers = @get_headers($urlFoto);
    if ($headers && strpos($headers[0], '200') !== false) {
        return $urlFoto;
    } else {
        return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    }
}
?>

<!-- FILTROS TOPO -->
<form method="get" class="row row-cols-lg-auto g-2 align-items-center mb-3">
    <div class="col">
        <select name="ano" class="form-select" onchange="this.form.submit()">
            <option value="0">Todos os Anos</option>
            <?php foreach ($anos as $ano): ?>
                <option value="<?= $ano ?>" <?= $anoSel == $ano ? 'selected' : '' ?>><?= $ano ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <select name="turma" class="form-select" onchange="this.form.submit()">
            <option value="">Todas as Turmas</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?= htmlspecialchars($t['chave']) ?>" <?= $turmaSel == $t['chave'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nometurma']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col">
        <input type="text" name="busca" class="form-control" style="max-width:320px" placeholder="Buscar por nome, e-mail ou celular..." value="<?= htmlspecialchars($busca) ?>">
    </div>
    <div class="col">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
    </div>
</form>

<!-- PAGINAÇÃO -->
<?php if ($totalPaginas > 1): ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <?php if ($pagina > 1): ?>
                <a class="btn btn-outline-secondary btn-sm me-2" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>"><i class="bi bi-chevron-left"></i> Voltar</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"
                    class="btn btn-sm <?= $i == $pagina ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a class="btn btn-outline-secondary btn-sm ms-2" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">Avançar <i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <!-- Dropdown lateral direita: Cursos -->
        <div>
            <div class="dropdown">
                <button class="btn btn-outline-dark dropdown-toggle" type="button" id="dropdownCursos" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list"></i> Cursos
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownCursos">
                    <?php foreach ($cursos as $curso): ?>
                        <li><span class="dropdown-item"><?= htmlspecialchars($curso['nometurma']) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- LISTA EM CARDS -->
<div class="row g-3 mb-4">
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <?php
        $dataNascimento = $row['datanascimento_sc'] ?? null;
        $encIdUsuario = encrypt($row['codigousuario'], 'e');
        $idade = '';
        if ($dataNascimento && $dataNascimento != '0000-00-00') {
            $dtNasc = new DateTime($dataNascimento);
            $hoje = new DateTime();
            $idade = $dtNasc->diff($hoje)->y;
        }
        $nomeArr = explode(' ', trim($row['nome']));
        $nomeExib = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
        $foto = fotoAlunoUrl($row['pastasc'], $row['imagem200']);
        $dataIns = new DateTime($row['data_ins']);
        $hoje = new DateTime();
        $dias = $dataIns->diff($hoje)->days;
        $cursoNome = htmlspecialchars($row['nometurma']);

        // WhatsApp
        $celular = preg_replace('/\D/', '', $row['celular']);
        if ($celular && substr($celular, 0, 2) !== '55') $celular = '55' . $celular;
        $linkWhats = $celular ? 'https://wa.me/' . $celular : false;

        // Mensagens/E-mails (pode deixar igual ao seu original)
        $msgSaud = "*Olá {$nomeExib}*, aqui é o professor Eugênio! Tudo bem?";
        $msgSenha = "*Olá {$nomeExib}*, segue sua senha de acesso: [SENHA_AQUI]";
        $msgRedes = "*Olá {$nomeExib}*, me siga nas redes sociais! Instagram: @professoreugenio";
        $emailPromo = "https://mail.google.com/mail/?view=cm&fs=1&to={$row['email']}&su=Promoção de cursos&body=Olá%20{$nomeExib},%20aproveite%20nossa%20promoção%20de%20cursos!";
        $emailMotiv = "https://mail.google.com/mail/?view=cm&fs=1&to={$row['email']}&su=Motivação%20para%20seus%20estudos&body=Olá%20{$nomeExib},%20continue%20se%20dedicando,%20você%20vai%20alcançar%20seus%20objetivos!";
        ?>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body d-flex align-items-center p-3">
                    <img src="<?= $foto ?>" width="50" height="50" class="rounded-circle shadow border me-3" style="object-fit:cover;" alt="Foto">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <a href="alunoTurmas.php?idUsuario=<?= $encIdUsuario ?>" class="fw-bold fs-5 text-decoration-none text-dark">
                                <?= $nomeExib ?>
                            </a>
                            <?php if ($idade): ?>
                                <span class="badge bg-info text-dark ms-2"><?= $idade ?> ano<?= $idade > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="small mb-1 text-muted">
                            <i class="bi bi-calendar-check me-1"></i><?= date('d/m/Y', strtotime($row['data_ins'])) ?>
                            <span class="mx-1">•</span>
                            <i class="bi bi-hourglass-split me-1"></i><?= $dias ?> dia<?= $dias != 1 ? 's' : '' ?> desde inscrição
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark px-2 py-1"><i class="bi bi-mortarboard"></i> <?= $cursoNome ?></span>
                            <!-- E-mail oculto -->
                            <span class="badge bg-light text-dark px-2 py-1">
                                <i class="bi bi-envelope"></i>
                                <span class="ms-1 email-obscured" style="cursor:pointer;" data-email="<?= htmlspecialchars($row['email']) ?>">
                                    <i class="bi bi-eye-slash" title="Mostrar e-mail"></i>
                                    <span class="email-real d-none"><?= htmlspecialchars($row['email']) ?></span>
                                </span>
                            </span>
                            <!-- Celular oculto -->
                            <?php if ($celular): ?>
                                <span class="badge bg-success bg-opacity-25 text-success px-2 py-1">
                                    <i class="bi bi-whatsapp"></i>
                                    <span class="ms-1 tel-obscured" style="cursor:pointer;" data-tel="<?= "+" . substr($celular, 0, 2) . " " . substr($celular, 2) ?>">
                                        <i class="bi bi-eye-slash" title="Mostrar telefone"></i>
                                        <span class="tel-real d-none"><?= "+" . substr($celular, 0, 2) . " " . substr($celular, 2) ?></span>
                                    </span>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary px-2 py-1"><i class="bi bi-phone-x"></i> WhatsApp não informado</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
    // Oculta/mostra e-mail ao clicar no olho
    document.querySelectorAll('.email-obscured').forEach(function(span) {
        span.addEventListener('click', function() {
            let icon = span.querySelector('i');
            let emailReal = span.querySelector('.email-real');
            if (emailReal.classList.contains('d-none')) {
                emailReal.classList.remove('d-none');
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                icon.setAttribute('title', 'Ocultar e-mail');
            } else {
                emailReal.classList.add('d-none');
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                icon.setAttribute('title', 'Mostrar e-mail');
            }
        });
    });
    // Oculta/mostra telefone ao clicar no olho
    document.querySelectorAll('.tel-obscured').forEach(function(span) {
        span.addEventListener('click', function() {
            let icon = span.querySelector('i');
            let telReal = span.querySelector('.tel-real');
            if (telReal.classList.contains('d-none')) {
                telReal.classList.remove('d-none');
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                icon.setAttribute('title', 'Ocultar telefone');
            } else {
                telReal.classList.add('d-none');
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                icon.setAttribute('title', 'Mostrar telefone');
            }
        });
    });
</script>


<!-- Paginação base -->
<?php if ($totalPaginas > 1): ?>
    <div class="d-flex justify-content-center my-4">
        <?php if ($pagina > 1): ?>
            <a class="btn btn-outline-secondary btn-sm me-2" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>"><i class="bi bi-chevron-left"></i> Voltar</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"
                class="btn btn-sm <?= $i == $pagina ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($pagina < $totalPaginas): ?>
            <a class="btn btn-outline-secondary btn-sm ms-2" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">Avançar <i class="bi bi-chevron-right"></i></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>