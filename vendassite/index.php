<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1)); // ajuste se necessário



/* ===================== INCLUDES DO PROJETO ===================== */
require_once APP_ROOT . '/conexao/class.conexao.php';   // $con = config::connect();
require_once APP_ROOT . '/autenticacao.php';            // se precisar (ex.: utilitários de sessão/login)
// consultas de curso (mantido conforme seu padrão)

/* ===================== CONFIG DE SESSÃO (4 HORAS) ===================== */
const SESSION_TTL = 4 * 3600; // 4 horas em segundos

// Definir cookie de sessão ANTES do start
session_set_cookie_params([
    'lifetime' => SESSION_TTL,
    'path'     => '/',
    'domain'   => '', // ex.: 'professoreugenio.com' se necessário
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();



// (Opcional) Validação simples de ts (±48h) — apenas exemplo
/*
if ($ts !== '' && ctype_digit($ts)) {
    $delta = abs(time() - (int)$ts);
    if ($delta > 172800) { // 48h
        // ts inconsistente; você pode ignorar, limpar ou logar
    }
}
*/
require 'vendasv1.0/query_vendas.php';
/* ===================== (Opcional) LOG DE ENTRADA NO BANCO ===================== */


/* ===================== REDIRECIONA (PRG) ===================== */
// $next = 'vendas_Inscricao.php';
// if (!headers_sent()) {
//     header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
//     header('Pragma: no-cache');
//     header('Location: ' . $next);
//     exit;
// }


?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Metadados básicos -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Curso <?= $nomeCurso ?> — Professor Eugênio</title>
    <meta name="description"
        content="Domine Excel para gabaritar questões de concursos: funções, gráficos, tabelas, atalhos e simulados. Aulas online, material para download e certificação.">
    <link rel="canonical" href="https://professoreugenio.com/curso-excel-concursos">

    <!-- Open Graph / Twitter (compartilhamento) -->
    <meta property="og:title" content="<?= $nomeCurso ?> — Professor Eugênio">
    <meta property="og:description"
        content="Domine Excel para gabaritar questões de concursos. Aulas online, simulados e material para download.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://professoreugenio.com/img/og-excel-concursos.jpg">
    <meta property="og:url" content="https://professoreugenio.com/curso-excel-concursos">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Bootstrap / Icons / AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://professoreugenio.com/vendassite/vendasv1.0/CSS_vendas.css" rel="stylesheet">



</head>

<body>

    <!-- ===================== NAV ===================== -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="#">
                <i class="bi bi-microsoft me-1"></i> Professor Eugênio
            </a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="#hero">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="#beneficios">Benefícios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="#grade">Grade</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-cta" href="#cta">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Inscreva-se
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== HERO ===================== -->
    <section id="hero" class="hero pt-5">
        <div class="container position-relative">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-trophy me-1"></i> Curso de <?= $nomeCurso ?>
                    </span>
                    <?= $hero ?>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#cta" class="btn btn-cta btn-lg">
                            <i class="bi bi-star-fill me-2"></i> Garantir minha vaga
                        </a>
                        <a href="#grade" class="btn btn-outline-soft btn-lg">
                            <i class="bi bi-journal-check me-2"></i> Ver a grade
                        </a>
                    </div>
                    <div class="d-flex gap-3 mt-4 small text-white-50">
                        <div><i class="bi bi-camera-video me-1"></i> Aulas ao vivo + gravadas</div>
                        <div><i class="bi bi-patch-check me-1"></i> Certificação</div>
                        <div><i class="bi bi-file-earmark-arrow-down me-1"></i> PDFs e simulados</div>
                    </div>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="hero-card p-3 p-md-4">
                        <div style="background-color: #ffa500; color: white; font-weight: bold; padding: 2px 16px; border-radius: 8px; display: inline-block;">
                            Aula gratúita
                        </div>

                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden border border-1 border-light">
                            <iframe src="https://www.youtube.com/embed/<?= $youtubeurl ?>" title="Apresentação do Curso"
                                allowfullscreen loading="lazy"></iframe>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="icon-badge"><i class="bi bi-clock fs-4"></i></div>
                            <div class="small">
                                Início imediato • Acesso ao conteúdo gravado • Suporte direto com o professor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== BENEFÍCIOS ===================== -->
    <section id="beneficios">
        <?= $beneficios ?>
    </section>

    <!-- ===================== SOBRE ===================== -->
    <section id="sobre">
        <?= $sobreocurso ?>
    </section>

    <!-- ===================== GRADE DO CURSO ===================== -->
    <section id="grade">
        <div class="container">
            <div class="text-center mb-4" data-aos="fade-up">
                <div class="heading-2">Grade do Curso</div>
                <p class="lead lead-muted mb-0">Conteúdo organizado por módulos. Expanda cada módulo para ver as aulas.
                </p>
            </div>

            <?php
            // Pré-requisitos: $con (PDO) e $idCursoVenda (int) já definidos.
            // Segurança: garantimos inteiro
            $idCursoVenda = (int)($idCursoVenda ?? 0);

            // Helper para escapar HTML
            if (!function_exists('h')) {
                function h(string $s): string
                {
                    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
                }
            }

            // Consulta única com JOINs para reduzir roundtrips
            $sql = "
SELECT
  m.codigomodulos          AS mod_id,
  m.codcursos              AS mod_curso,
  m.modulo                 AS mod_ordem,
  m.nomemodulo             AS mod_nome,
  m.visivelm               AS mod_visivel,
  pc.idpublicacaopc        AS pub_id,
  pc.ordempc               AS pub_ordem,
  pc.visivelpc             AS pub_visivel,
  p.titulo                 AS pub_titulo
FROM new_sistema_modulos_PJA            m
LEFT JOIN a_aluno_publicacoes_cursos    pc
       ON pc.idmodulopc = m.codigomodulos
      AND (pc.visivelpc IS NULL OR pc.visivelpc = 1)
LEFT JOIN new_sistema_publicacoes_PJA   p
       ON p.codigopublicacoes = pc.idpublicacaopc
WHERE m.codcursos = :curso
  AND m.visivelm = '1'
ORDER BY 
  -- 1) ordem do módulo (campo 'modulo' costuma ser ordinal numérico; se for string, ainda ordena ok)
  CASE WHEN m.modulo REGEXP '^[0-9]+$' THEN CAST(m.modulo AS UNSIGNED) ELSE 999999 END,
  m.modulo,
  -- 2) ordem do conteúdo (nulo vai pro fim)
  CASE WHEN pc.ordempc IS NULL THEN 999999 ELSE pc.ordempc END,
  p.titulo
";

            $stmt = $con->prepare($sql);
            $stmt->bindValue(':curso', $idCursoVenda, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupa por módulo
            $modules = [];
            foreach ($rows as $r) {
                $modId    = (int)$r['mod_id'];
                $modNome  = (string)($r['mod_nome'] ?? '');
                $modOrdem = (string)($r['mod_ordem'] ?? ''); // pode ser número/ordem textual
                if (!isset($modules[$modId])) {
                    $modules[$modId] = [
                        'id'     => $modId,
                        'ordem'  => $modOrdem,
                        'nome'   => $modNome,
                        'items'  => []
                    ];
                }
                // Se houver publicação/título, adiciona item
                if (!empty($r['pub_id']) && !empty($r['pub_titulo'])) {
                    $modules[$modId]['items'][] = [
                        'id'    => (int)$r['pub_id'],
                        'ordem' => $r['pub_ordem'] !== null ? (int)$r['pub_ordem'] : 999999,
                        'title' => (string)$r['pub_titulo'],
                    ];
                }
            }

            // Se não tiver módulos visíveis, mostra fallback
            if (empty($modules)) {
                echo '<div class="card-dark p-4"><div class="small text-white-50 mb-0">Conteúdo em atualização. Volte em breve.</div></div>';
                return;
            }

            // Ordena módulos pela chave 'ordem' (numérica quando possível)
            uasort($modules, function ($a, $b) {
                $ai = ctype_digit((string)$a['ordem']) ? (int)$a['ordem'] : PHP_INT_MAX;
                $bi = ctype_digit((string)$b['ordem']) ? (int)$b['ordem'] : PHP_INT_MAX;
                if ($ai === $bi) return strnatcasecmp((string)$a['ordem'], (string)$b['ordem']);
                return $ai <=> $bi;
            });

            // Render do accordion
            ?>
            <div class="accordion mod-acc" id="accGrade">
                <?php
                $idx = 0;
                $delayStep = 50;
                foreach ($modules as $mod) {
                    $idx++;
                    $collapseId = 'm' . $mod['id'];                 // id único estável
                    $isFirst    = ($idx === 1);
                    $showClass  = $isFirst ? ' show' : '';
                    $collapsed  = $isFirst ? '' : ' collapsed';
                    $aosDelay   = ($idx - 1) * $delayStep;
                    $tituloMod  = 'Módulo ' . h((string)$mod['ordem']) . ' — ' . h($mod['nome']);
                ?>
                    <div class="accordion-item card-dark mb-3" data-aos="fade-up" <?= $aosDelay ? ' data-aos-delay="' . $aosDelay . '"' : '' ?>>
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold<?= $collapsed ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#<?= h($collapseId) ?>">
                                <?= $tituloMod ?>
                            </button>
                        </h2>
                        <div id="<?= h($collapseId) ?>" class="accordion-collapse collapse<?= $showClass ?>" data-bs-parent="#accGrade">
                            <div class="accordion-body">
                                <?php if (!empty($mod['items'])): ?>
                                    <ul class="mb-0 small">
                                        <?php foreach ($mod['items'] as $item): ?>
                                            <li><?= h($item['title']) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="small text-white-50">Conteúdo deste módulo será liberado em breve.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>


            <!-- Extras -->
            <div class="row g-4 mt-1">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-download me-2"></i>Materiais</div>
                        <p class="small text-white-50 mb-0">Planilhas-modelo e PDFs para reforço e prática direcionada.
                        </p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="50">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-people me-2"></i>Comunidade</div>
                        <p class="small text-white-50 mb-0">Grupo de suporte e tira-dúvidas com o professor.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-dark p-4 h-100">
                        <div class="fw-bold mb-1"><i class="bi bi-award me-2"></i>Certificação</div>
                        <p class="small text-white-50 mb-0">Certificado digital ao final do curso.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-4" data-aos="fade-up"></div>
                <a href="#cta" class="btn btn-cta btn-lg">
                    <i class="bi bi-star-fill me-2"></i> Garantir minha vaga
                </a>
            </div>
        </div>
        </div>
    </section>

    <!-- ===================== CTA FINAL ===================== -->
    <section id="cta" class="cta">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="heading-2 mb-2">Inscreva-se Agora</div>
                    <?= $cta ?>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-white-50 mb-1">Plano recomendado</div>
                                <div class="fs-3 fw-bold"><?= $nomeCurso ?></div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas
                                Limitadas</span>
                        </div>

                        <?php if ($valoranual > 0): ?>
                            <div class="display-6 fw-bold my-2" style="color:#00BB9C;">R$ <?= $valoranual; ?>/anual</div>
                            <div class="small text-white-50 mb-3">ou Vitalício por R$ <?= $valorvendavitalicia; ?></div>
                        <?php else: ?>
                            <div class="display-6 fw-bold my-2" style="color:#00BB9C;">R$ <?= $valorvendavitalicia; ?></div>
                            <div class="small text-white-50 mb-3">Acesso Vitalício com atualizações</div>
                        <?php endif; ?>
                        <div class="d-grid gap-2">
                            <a class="btn btn-cta btn-lg" href="vendas_inscricao.php">
                                <i class="bi bi-cart-check me-2"></i> Fazer minha inscrição
                            </a>
                            <a class="btn btn-outline-soft btn-lg"
                                href="<?= $linkwhatsapp ?> *<?= $nomeCurso ?>*"
                                target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp me-2"></i> Tirar dúvidas no WhatsApp
                            </a>
                        </div>
                        <div class="d-flex gap-3 mt-3 small text-white-50">
                            <div><i class="bi bi-shield-lock me-1"></i> Compra segura</div>
                            <div><i class="bi bi-credit-card me-1"></i> Pix / Cartão / Boleto</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== RODAPÉ ===================== -->
    <footer class="py-4 border-top border-opacity-25" style="border-color: rgba(255,255,255,.06) !important;">
        <div class="container small text-white-50 d-flex flex-wrap justify-content-between gap-2">
            <div>© <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.</div>
            <div class="d-flex gap-3">
                <a class="link-light link-underline-opacity-0" href="#">Termos</a>
                <a class="link-light link-underline-opacity-0" href="#">Privacidade</a>
                <a class="link-light link-underline-opacity-0" href="#">Contato</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true
        });
        document.getElementById('ano').textContent = new Date().getFullYear();
    </script>
</body>

</html>