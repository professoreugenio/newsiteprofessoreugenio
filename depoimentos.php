<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php';

// ------- Variáveis esperadas (podem vir de outro include/página) -------
$codigomodulo = $codigomodulo ?? (isset($_GET['mod']) ? (int)$_GET['mod'] : null);
$codigoaula   = $codigoaula   ?? (isset($_GET['aula']) ? (int)$_GET['aula'] : null);
$nomeModulo   = $nomeModulo   ?? '';
$tituloAula   = $tituloAula   ?? '';

$con = config::connect();

// ------- Nomes do Módulo e da Lição (fallback) -------
if (empty($nomeModulo) && !empty($codigomodulo)) {
    $qM = $con->prepare("SELECT modulo AS nome FROM new_sistema_modulos_PJA WHERE codigomodulos = :idm LIMIT 1");
    $qM->bindValue(':idm', (int)$codigomodulo, PDO::PARAM_INT);
    $qM->execute();
    $nomeModulo = ($qM->fetch(PDO::FETCH_ASSOC)['nome'] ?? 'Módulo');
}
if (empty($tituloAula) && !empty($codigoaula)) {
    $qA = $con->prepare("SELECT titulopa AS titulo FROM new_sistema_publicacoes_PJA WHERE idpublicacaopa = :ida LIMIT 1");
    $qA->bindValue(':ida', (int)$codigoaula, PDO::PARAM_INT);
    $qA->execute();
    $tituloAula = ($qA->fetch(PDO::FETCH_ASSOC)['titulo'] ?? 'Lição');
}

// ------- Consulta de Depoimentos -------
// Apenas visíveis e com permissão = 1 (conforme solicitado).
// [NOVO] Junta a inscrição mais recente por usuário e puxa o nome do curso.
$sql = "
    SELECT 
        f.codigoforum,
        f.idusuarioCF,
        f.idartigoCF,
        f.idcodforumCF,
        f.textoCF,
        f.visivelCF,
        f.permissaoCF,
        f.dataCF,
        f.horaCF,
        f.destaqueCF,
        c.nome,
        c.pastasc,
        c.imagem50,
        cur.nomecurso AS nomecurso_recente  -- [NOVO]
    FROM a_curso_forum f
    LEFT JOIN new_sistema_cadastro c 
           ON c.codigocadastro = f.idusuarioCF

    /* [NOVO] Subconsulta para pegar a data mais recente de inscrição por usuário */
    LEFT JOIN (
        SELECT i1.codigousuario, i1.codcurso_ip, i1.data_ins
        FROM new_sistema_inscricao_PJA i1
        INNER JOIN (
            SELECT codigousuario, MAX(data_ins) AS max_data
            FROM new_sistema_inscricao_PJA
            GROUP BY codigousuario
        ) imax ON imax.codigousuario = i1.codigousuario 
              AND imax.max_data = i1.data_ins
    ) ult ON ult.codigousuario = f.idusuarioCF

    /* [NOVO] Nome do curso da inscrição mais recente */
    LEFT JOIN new_sistema_cursos cur 
           ON cur.codigocursos = ult.codcurso_ip

    WHERE f.visivelCF   = 1
      AND f.permissaoCF = 1
      AND (:idaula IS NULL OR f.idcodforumCF = :idaula)
    ORDER BY f.destaqueCF DESC, f.dataCF DESC, f.horaCF DESC, f.codigoforum DESC
";
$q = $con->prepare($sql);
$q->bindValue(':idaula', !empty($codigoaula) ? (int)$codigoaula : null, !empty($codigoaula) ? PDO::PARAM_INT : PDO::PARAM_NULL);
$q->execute();
$mensagens = $q->fetchAll(PDO::FETCH_ASSOC);

// ------- Helpers -------
function fotoUsuario50(array $row): string
{
    if ($row['imagem50'] != "usuario.jpg" && !empty($row['imagem50'])) {
        return "/fotos/usuarios/{$row['pastasc']}/{$row['imagem50']}";
    }
    return "/fotos/usuarios/usuario.png";
}
function brData(?string $dataYmd): string
{
    if (!$dataYmd) return '';
    $p = explode('-', $dataYmd);
    return (count($p) === 3) ? ($p[2] . '/' . $p[1] . '/' . $p[0]) : $dataYmd;
}

// ------- Metadados sociais (Open Graph / Twitter) -------
$baseUrl  = (($_SERVER['HTTPS'] ?? '') === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$currPath = strtok(($_SERVER['REQUEST_URI'] ?? '/depoimentos.php'), '?');
$canonical = $baseUrl . $currPath . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

$pageTitle = "Depoimentos de Alunos • Professor Eugênio";
if (!empty($nomeModulo)) $pageTitle = "Depoimentos – {$nomeModulo} • Professor Eugênio";
if (!empty($tituloAula)) $pageTitle = "Depoimentos – {$tituloAula} • Professor Eugênio";

$ogDesc   = "Veja depoimentos reais dos alunos do curso do Professor Eugênio. Qualidade, suporte e resultados.";
if (!empty($mensagens)) {
    $primeiroTexto = trim((string)($mensagens[0]['textoCF'] ?? ''));
    if ($primeiroTexto !== '') {
        $ogDesc = mb_substr(strip_tags($primeiroTexto), 0, 140, 'UTF-8') . (mb_strlen($primeiroTexto, 'UTF-8') > 140 ? '…' : '');
    }
}
$ogImage = "https://professoreugenio.com/img/depoimentos.jpg";
$siteName = "Professor Eugênio";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($ogDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= htmlspecialchars($siteName) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDesc) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:image:alt" content="Depoimentos de alunos do Professor Eugênio">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDesc) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico">

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Fonte (opcional) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-1: #00BB9C;
            --brand-2: #FF9C00;
            --bg-card: #112240;
            --text: #ffffff;
            --muted: #A3B1C2;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, rgba(0, 187, 156, .20), transparent 60%), #0c1833;
            color: var(--text);
        }

        .navbar {
            backdrop-filter: saturate(130%) blur(8px);
            background: rgba(17, 34, 64, .8) !important;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .navbar-brand img {
            height: 34px;
        }

        .section-head h1 {
            color: var(--brand-1);
            font-weight: 800;
            letter-spacing: .2px;
        }

        .section-head .lead {
            color: var(--muted);
        }

        .depo-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(12, 1fr);
        }

        @media (max-width: 575.98px) {
            .depo-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 576px) and (max-width: 991.98px) {
            .depo-grid {
                grid-template-columns: repeat(8, 1fr);
            }
        }

        @media (min-width: 992px) {
            .depo-grid {
                grid-template-columns: repeat(12, 1fr);
            }
        }

        .depo-card {
            grid-column: span 4;
            background: linear-gradient(145deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .02));
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            padding: 18px;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        @media (max-width: 991.98px) {
            .depo-card {
                grid-column: span 4;
            }
        }

        @media (max-width: 575.98px) {
            .depo-card {
                grid-column: span 4;
            }
        }

        .depo-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
            border-color: rgba(0, 187, 156, .35);
        }

        .depo-head {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .depo-head .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            flex: 0 0 50px;
            border: 2px solid rgba(255, 255, 255, .15);
        }

        .depo-name {
            font-weight: 600;
            color: #fff;
        }

        .depo-date {
            color: var(--muted);
            font-size: .85rem;
        }

        .depo-star {
            margin-left: auto;
            font-size: 1.05rem;
            color: #ffcc66;
        }

        .depo-text {
            background: #0f203f;
            border: 1px solid rgba(255, 255, 255, .06);
            color: #e6f3ff;
            border-radius: 12px;
            padding: 14px;
            margin-top: 12px;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .badge-soft {
            background: rgba(0, 187, 156, .14);
            color: var(--brand-1);
            border: 1px solid rgba(0, 187, 156, .25);
        }

        .badge-curso {
            /* [NOVO] Destaque para o nome do curso */
            background: rgba(255, 156, 0, .18);
            color: var(--brand-2);
            border: 1px solid rgba(255, 156, 0, .35);
            font-weight: 600;
        }

        .site-footer {
            color: var(--muted);
            border-top: 1px solid rgba(255, 255, 255, .08);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <img src="https://professoreugenio.com/img/logosite.png" alt="Professor Eugênio">
                <span class="fw-semibold">Professor Eugênio</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav"
                aria-controls="topNav" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="topNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/#cursos">Cursos</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= htmlspecialchars($currPath) ?>">Depoimentos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/#contato">Contato</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo -->
    <main class="container" style="padding-top: 92px; padding-bottom: 40px;">
        <div class="section-head mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h2 mb-1"><?= htmlspecialchars($nomeModulo ?: 'Depoimentos dos Alunos') ?></h1>
                    <p class="lead mb-0">
                        <?= htmlspecialchars($tituloAula ? ('Lição: ' . $tituloAula) : 'Experiências reais de quem já estudou com a gente.') ?>
                    </p>
                </div>
                <span class="badge badge-soft rounded-pill px-3 py-2">
                    <i class="bi bi-chat-quote me-1"></i> Depoimentos
                </span>
            </div>
        </div>

        <?php if (empty($mensagens)): ?>
            <div class="alert alert-warning bg-opacity-10 border-0 text-white" role="alert" style="background:#5c3d00;">
                Nenhum depoimento público encontrado para esta lição.
            </div>
        <?php else: ?>
            <div class="depo-grid">
                <?php foreach ($mensagens as $m):
                    $img    = fotoUsuario50($m);
                    $nomeCompleto = trim((string)($m['nome'] ?? 'Usuário'));
                    $partesNome = preg_split('/\s+/', $nomeCompleto);
                    $nome = isset($partesNome[0]) ? $partesNome[0] : '';
                    if (isset($partesNome[0])) {
                        $nome = ' ' . $partesNome[0];
                    }
                    $texto  = trim((string)($m['textoCF'] ?? ''));
                    $data   = brData($m['dataCF'] ?? '');
                    $hora   = $m['horaCF'] ?? '';
                    $fav    = (int)($m['destaqueCF'] ?? 0) === 1;
                    $curso  = trim((string)($m['nomecurso_recente'] ?? '')); // [NOVO]
                ?>
                    <article class="depo-card">
                        <header class="depo-head">
                            <img class="avatar" src="<?= htmlspecialchars($img) ?>" alt="Foto de <?= htmlspecialchars($nome) ?>">
                            <div>
                                <div class="depo-name d-flex align-items-center gap-2 flex-wrap">
                                    <span><?= htmlspecialchars($nome) ?></span>
                                    <?php if ($curso !== ''): ?>
                                        <span class="badge badge-curso rounded-pill"><?= htmlspecialchars($curso) ?></span> <!-- [NOVO] -->
                                    <?php endif; ?>
                                </div>
                                <div class="depo-date"><?= $data ?> <?= $hora ? '· ' . htmlspecialchars($hora) : '' ?></div>
                            </div>
                            <?php if ($fav): ?>
                                <i class="bi bi-star-fill depo-star" title="Depoimento em destaque"></i>
                            <?php endif; ?>
                        </header>
                        <div class="depo-text"><?= nl2br(htmlspecialchars($texto)) ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Rodapé -->
    <footer class="site-footer py-4">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>© <?= date('Y') ?> <strong>Professor Eugênio</strong>. Todos os direitos reservados.</div>
            <div class="small">Qualidade de ensino, suporte próximo e foco em resultados.</div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JSON-LD para SEO (ItemList de depoimentos) -->
    <?php if (!empty($mensagens)):
        $items = [];
        $pos = 1;
        foreach ($mensagens as $m) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'item' => [
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => trim((string)($m['nome'] ?? 'Aluno')) ?: 'Aluno'],
                    'datePublished' => ($m['dataCF'] ?? '') . (isset($m['horaCF']) && $m['horaCF'] ? ('T' . $m['horaCF']) : ''),
                    'reviewBody' => trim((string)($m['textoCF'] ?? ''))
                ]
            ];
        }
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'ItemList',
            'name'     => 'Depoimentos de Alunos',
            'itemListElement' => $items
        ];
    ?>
        <script type="application/ld+json">
            <?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
        </script>
    <?php endif; ?>

</body>

</html>