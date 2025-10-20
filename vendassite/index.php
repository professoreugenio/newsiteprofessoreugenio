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
    <title>Curso <?= $nomeTurma ?> — Professor Eugênio</title>
    <meta name="description"
        content="Domine Excel para gabaritar questões de concursos: funções, gráficos, tabelas, atalhos e simulados. Aulas online, material para download e certificação.">
    <link rel="canonical" href="https://professoreugenio.com/curso-excel-concursos">

    <!-- Open Graph / Twitter (compartilhamento) -->
    <meta property="og:title" content="Curso de Excel para Concursos — Professor Eugênio">
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
                        <i class="bi bi-trophy me-1"></i> Curso de Excel para Concursos
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
                        <!-- Substitua o poster pelo seu thumb -->
                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden border border-1 border-light">
                            <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Apresentação do Curso"
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

            <div class="accordion mod-acc" id="accGrade">

                <!-- Módulo 1 -->
                <div class="accordion-item card-dark mb-3" data-aos="fade-up">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse"
                            data-bs-target="#m1">
                            Módulo 1 — Fundamentos que Mais Caem ,<?= $_SESSION['nav'] ?><?= $idCursoVenda ?>
                        </button>
                    </h2>
                    <div id="m1" class="accordion-collapse collapse show" data-bs-parent="#accGrade">
                        <div class="accordion-body">
                            <ul class="mb-0 small">
                                <li>Introdução, interface e atalhos básicos</li>
                                <li>Formatação de células, números e datas</li>
                                <li>Funções SOMA, MÉDIA, MÍN, MÁX e CONT.SE</li>
                                <li>Preenchimento rápido e referências absolutas/relativas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Módulo 2 -->
                <div class="accordion-item card-dark mb-3" data-aos="fade-up" data-aos-delay="50">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-semibold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#m2">
                            Módulo 2 — Lógica, Procura e Contagem
                        </button>
                    </h2>
                    <div id="m2" class="accordion-collapse collapse" data-bs-parent="#accGrade">
                        <div class="accordion-body">
                            <ul class="mb-0 small">
                                <li>SE, SE aninhado e IFS (equivalentes)</li>
                                <li>PROCV / XLOOKUP (PROC.X) e CORRESP</li>
                                <li>CONT.SES, SOMA.SE/SOMA.SES</li>
                                <li>Erros comuns e pegadinhas de banca</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Módulo 3 -->
                <div class="accordion-item card-dark mb-3" data-aos="fade-up" data-aos-delay="100">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-semibold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#m3">
                            Módulo 3 — Tabelas, Gráficos e Tabela Dinâmica
                        </button>
                    </h2>
                    <div id="m3" class="accordion-collapse collapse" data-bs-parent="#accGrade">
                        <div class="accordion-body">
                            <ul class="mb-0 small">
                                <li>Construção de tabelas e filtros</li>
                                <li>Gráficos mais cobrados em edital</li>
                                <li>Introdução à Tabela Dinâmica</li>
                                <li>Interpretação de questões com figuras</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Módulo 4 -->
                <div class="accordion-item card-dark mb-3" data-aos="fade-up" data-aos-delay="150">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-semibold collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#m4">
                            Módulo 4 — Simulados e Estratégias de Prova
                        </button>
                    </h2>
                    <div id="m4" class="accordion-collapse collapse" data-bs-parent="#accGrade">
                        <div class="accordion-body">
                            <ul class="mb-0 small">
                                <li>Simulado 1 (comentado)</li>
                                <li>Simulado 2 (comentado)</li>
                                <li>Técnicas para ganhar tempo em questões de Excel</li>
                                <li>Checklist pré-prova</li>
                            </ul>
                        </div>
                    </div>
                </div>

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
        </div>
    </section>

    <!-- ===================== CTA FINAL ===================== -->
    <section id="cta" class="cta">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <div class="heading-2 mb-2">Inscreva-se Agora</div>
                    <p class="mb-1">
                        Garanta seu acesso ao conteúdo completo, participe das aulas ao vivo e pratique com nossos
                        simulados.
                    </p>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><i class="bi bi-check2-circle check me-2"></i>Acesso imediato à plataforma</li>
                        <li class="mb-1"><i class="bi bi-check2-circle check me-2"></i>Atualizações inclusas</li>
                        <li class="mb-1"><i class="bi bi-check2-circle check me-2"></i>7 dias de garantia</li>
                    </ul>
                </div>
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small text-white-50 mb-1">Plano recomendado</div>
                                <div class="fs-3 fw-bold">Excel para Concursos</div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas
                                Limitadas</span>
                        </div>
                        <div class="display-6 fw-bold my-2" style="color:#00BB9C;">R$ 39,90/mês</div>
                        <div class="small text-white-50 mb-3">ou Vitalício por R$ 85,00</div>
                        <div class="d-grid gap-2">
                            <a class="btn btn-cta btn-lg" href="vendas_inscricao.html">
                                <i class="bi bi-cart-check me-2"></i> Fazer minha inscrição
                            </a>
                            <a class="btn btn-outline-soft btn-lg"
                                href="https://wa.me/5585XXXXXXXX?text=Tenho%20d%C3%BAvidas%20sobre%20o%20Curso%20de%20Excel%20para%20Concursos"
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