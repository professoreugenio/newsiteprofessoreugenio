<?php
/* ===========================
 * MÓDULO: require_Modulosbusca.php (estilizado)
 * - Mantém sua consulta (curso sem filtro de turma)
 * - Apenas melhora o visual com CSS e pequenos ajustes de classes
 * =========================== */

if (!isset($con) || !$con instanceof PDO) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '') {
    echo '<div class="alert alert-info">Digite um termo para buscar.</div>';
    return;
}

/* Monte seu contexto como preferir — aqui sigo seu exemplo */
$idCurso = (int)($idCurso ?? 0);

/* 1) Buscar módulos de origem do curso (sem turma) */
$sqlMod = "
  SELECT DISTINCT idmoduloorigem
  FROM a_aluno_publicacoes_cursos
  WHERE idcursopc = :idCurso
    AND idmoduloorigem IS NOT NULL
    AND idmoduloorigem <> 0
";
$stMod = $con->prepare($sqlMod);
$stMod->bindValue(':idCurso', (int)$idCurso, PDO::PARAM_INT);
$stMod->execute();
$mods = $stMod->fetchAll(PDO::FETCH_COLUMN, 0);

if (!$mods) {
    echo '<div class="alert alert-warning mb-0">Nenhum módulo de origem encontrado para este curso.</div>';
    return;
}

/* 2) Placeholders e params */
$ph = [];
$params = [
    ':idCurso' => (int)$idCurso,
    ':q'       => '%' . mb_strtolower(preg_replace('/\s+/', ' ', $q)) . '%',
];
foreach ($mods as $i => $m) {
    $key = ':m' . $i;
    $ph[] = $key;
    $params[$key] = (int)$m;
}
$inClause = implode(',', $ph);

/* 3) Pesquisa nas publicações dos módulos de origem e liga ao apc (mesmo curso) */
$sql = "
SELECT
  p.codigopublicacoes,
  p.titulo,
  p.olho,
  p.tag,
  p.texto,
  p.codmodulo_sp,
  apc.idmoduloorigem,
  apc.idmodulopc,
  apc.idpublicacaopc
FROM new_sistema_publicacoes_PJA AS p
INNER JOIN a_aluno_publicacoes_cursos AS apc
        ON apc.idpublicacaopc = p.codigopublicacoes
       AND apc.idcursopc     = :idCurso
WHERE p.codmodulo_sp IN ($inClause)
  AND (
        LOWER(COALESCE(p.titulo,'')) LIKE :q
     OR LOWER(COALESCE(p.olho  ,'')) LIKE :q
     OR LOWER(COALESCE(p.tag   ,'')) LIKE :q
     OR LOWER(COALESCE(p.texto ,'')) LIKE :q
  )
GROUP BY
  p.codigopublicacoes, p.titulo, p.olho, p.tag, p.texto, p.codmodulo_sp,
  apc.idmoduloorigem, apc.idmodulopc, apc.idpublicacaopc
ORDER BY p.titulo ASC
LIMIT 100
";

$st = $con->prepare($sql);
foreach ($params as $k => $v) {
    $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$st->execute();
$resultados = $st->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* ====== ESTILO ESCOPADO AO MÓDULO ====== */
    #buscaConteudo {
        --brand-1: #00BB9C;
        /* h1 */
        --brand-2: #FF9C00;
        /* h2 */
        --bg: #0d1321;
        --card: #112240;
        --text: #e2e8f0;
        --muted: #94a3b8;
        --line: rgba(255, 255, 255, .08);
        --tag: #0ea5e9;
    }

    #buscaConteudo .bc-subtle {
        color: var(--muted);
        font-size: .9rem;
    }

    #buscaConteudo .bc-title {
        color: var(--brand-1);
        letter-spacing: .2px;
    }

    #buscaConteudo .bc-search .form-control {
        background: #0b1220;
        color: var(--text);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: .8rem 1rem;
    }

    #buscaConteudo .bc-search .form-control::placeholder {
        color: #6b7280;
    }

    #buscaConteudo .bc-search .form-control:focus {
        border-color: var(--brand-1);
        box-shadow: 0 0 0 .2rem rgba(0, 187, 156, .15);
    }

    #buscaConteudo .btn-accent {
        background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
        color: #fff;
        border: 0;
        border-radius: 12px;
        padding: .7rem 1.1rem;
        font-weight: 600;
        transition: transform .15s ease, filter .2s ease;
    }

    #buscaConteudo .btn-accent:hover {
        filter: brightness(1.05);
        transform: translateY(-1px);
    }

    #buscaConteudo .bc-card {
        background: radial-gradient(1200px 400px at -10% -10%, rgba(2, 0, 5, 0.53), transparent 40%),
            var(--card);
        border: 1px solid var(--line);
        color: var(--text);
        border-radius: 18px;
        padding: 1.2rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .25);
        position: relative;
        overflow: hidden;
    }

    #buscaConteudo .bc-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-left: 4px solid var(--brand-1);
        opacity: .9;
        pointer-events: none;
    }

    #buscaConteudo .bc-card:hover {
        transform: translateY(-2px);
        transition: transform .2s ease;
    }

    #buscaConteudo .bc-olho {
        color: #cbd5e1;
        margin-bottom: .2rem;
    }

    #buscaConteudo .bc-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
    }

    #buscaConteudo .bc-tag {
        background: linear-gradient(90deg, var(--tag), #38bdf8);
        color: #00121a;
        border: 0;
        border-radius: 999px;
        padding: .25rem .6rem;
        font-weight: 600;
        font-size: .8rem;
    }

    #buscaConteudo .bc-meta {
        color: var(--muted);
        font-size: .85rem;
        border-top: 1px dashed var(--line);
        padding-top: .6rem;
        margin-top: .6rem;
    }

    #buscaConteudo .bc-divider {
        height: 3px;
        width: 68px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
    }

    a {
        text-decoration: none;
        color: ivory;
    }
</style>

<div id="buscaConteudo">
    <!-- Barra para refinar a busca -->
    <section class="search-again mb-4 bc-search">


        <form class="row g-2" method="get" action="">
            <div class="col-md-9">
                <input type="search" class="form-control form-control-lg" name="q"
                    value="<?= htmlspecialchars($q) ?>"
                    placeholder="Refinar busca (título, olho, tag, texto)…">
            </div>
            <div class="col-12 col-md-auto">
                <button class="btn-accent">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>

        <div class="d-flex align-items-center justify-content-between mt-2">
            <!-- <div>
                <h2 class="h5 m-0 bc-title">Resultados da busca</h2>
                <div class="bc-divider mt-1"></div>
            </div> -->
            <div class="bc-subtle">
                Termo: <strong><?= htmlspecialchars($q) ?></strong> • <?= count($resultados) ?> resultado(s)
            </div>
        </div>
    </section>



    <?php if (empty($resultados)): ?>
        <div class="alert alert-warning mb-0">Nenhum conteúdo encontrado.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($resultados as $key =>  $r):
                $encidModulo     = encrypt($r['idmodulopc'], 'e');
                $encidPublicacao = encrypt($r['idpublicacaopc'], 'e'); /* usa o apc no link, conforme solicitado */
                $var = $idUser . "&" . $idCurso . "&" . $idTurma . "&" . $r['idmodulopc'] . "&" . $r['idpublicacaopc'];
                $encPub = encrypt($var, 'e');
                $url = "actionCurso.php?pubult={$encPub}";
                $olho = (string)($r['olho'] ?? '');
                $tag  = (string)($r['tag'] ?? '');
                $n = $key + 1;
            ?>
                <div class="col-12" data-aos="fade-up">
                    <div class="bc-card">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                            <div class="pe-md-3">
                                <h5 class="mb-2">
                                    <a href="<?= htmlspecialchars($url) ?>">
                                        <?= $n ?>. <?= htmlspecialchars($r['titulo'] ?: '—') ?> <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </h5>



                                <?php if ($tag !== ''):
                                    $tags = preg_split('/[;,#]+|\s+#/u', $tag);
                                    $tags = array_filter(array_map('trim', (array)$tags));
                                ?>

                                <?php endif; ?>
                            </div>

                            <div class="ms-md-auto">
                                <a href="<?= htmlspecialchars($url) ?>" class="btn-accent">
                                    Abrir conteúdo <i class="bi bi-box-arrow-up-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <div class="bc-meta">
                            <i class="bi bi-diagram-3"></i> Módulo origem: <?= (int)$r['idmoduloorigem'] ?> ·
                            <i class="bi bi-diagram-3-fill"></i> Módulo (apc): <?= (int)$r['idmodulopc'] ?> ·
                            <i class="bi bi-hash"></i> Publicação (apc): <?= (int)$r['idpublicacaopc'] ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>