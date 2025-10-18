<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Metadados básicos -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Curso de Excel para Concursos — Professor Eugênio</title>
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

    <!-- Estilos do tema -->
    <style>
        :root {
            --c-h1: #00BB9C;
            /* h1 */
            --c-h2: #FF9C00;
            /* títulos de seção (visual <h2>, sem usar a tag) */
            --c-bg: #112240;
            /* fundo principal */
            --c-text: #ffffff;
            /* textos gerais */
            --c-card: #0d1a34;
            /* fundo de cards */
            --c-muted: #9fb1d1;
            /* texto secundário */
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
            scroll-behavior: smooth;
        }

        /* Navbar fixa */
        .navbar {
            backdrop-filter: saturate(140%) blur(6px);
            background: rgba(17, 34, 64, .85);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .navbar .nav-link {
            color: #e6f2ff;
        }

        .navbar .nav-link:hover {
            color: var(--c-h1);
        }

        /* Utilitários de títulos (evitando <h2>) */
        .heading-1 {
            color: var(--c-h1);
            font-weight: 800;
            letter-spacing: .2px;
        }

        .heading-2 {
            color: var(--c-h2);
            font-weight: 800;
            letter-spacing: .2px;
            font-size: clamp(1.2rem, 2.5vw, 1.6rem);
            text-transform: uppercase;
        }

        .lead-muted {
            color: var(--c-muted);
        }

        /* Hero */
        .hero {
            position: relative;
            min-height: 78vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(1200px 500px at 10% 10%, rgba(0, 187, 156, .18), transparent 70%),
                radial-gradient(1000px 500px at 90% 20%, rgba(255, 156, 0, .12), transparent 70%),
                linear-gradient(180deg, rgba(0, 0, 0, .08), rgba(0, 0, 0, .18));
        }

        .hero-card {
            background: linear-gradient(135deg, rgba(13, 26, 52, .95), rgba(13, 26, 52, .75));
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .35);
        }

        /* Cards padrão */
        .card-dark {
            background: var(--c-card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
        }

        .icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #203357, #0d1a34);
            border: 1px solid rgba(255, 255, 255, .1);
        }

        /* Grade do curso */
        .mod-acc .accordion-button {
            background: #0f2244;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: none !important;
        }

        .mod-acc .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #153060, #0f2244);
            color: #fff;
        }

        .check {
            color: #54e1c3;
        }

        /* CTA Final */
        .cta {
            background:
                radial-gradient(900px 420px at 15% 30%, rgba(0, 187, 156, .22), transparent 70%),
                radial-gradient(800px 420px at 85% 20%, rgba(255, 156, 0, .18), transparent 70%),
                linear-gradient(180deg, rgba(0, 0, 0, .12), rgba(0, 0, 0, .22));
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        /* Botões */
        .btn-cta {
            background: linear-gradient(135deg, #FF9C00, #ffb547);
            color: #112240;
            font-weight: 800;
            border: none;
            box-shadow: 0 10px 24px rgba(255, 156, 0, .25);
        }

        .btn-cta:hover {
            filter: brightness(1.05);
            color: #0b1730;
        }

        .btn-outline-soft {
            border: 1px solid rgba(255, 255, 255, .25);
            color: #e9f3ff;
        }

        .btn-outline-soft:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff;
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #e9f3ff;
        }

        /* Pequenas melhorias de espaçamento */
        section {
            padding: 72px 0;
        }

        .small {
            font-size: .92rem;
        }
    </style>

    <style>
        :root {
            --c-h1: #00BB9C;
            --c-h2: #FF9C00;
            --c-bg: #112240;
            --c-text: #ffffff;
            --c-card: #0d1a34;
            --c-muted: #9fb1d1;
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
        }

        .mod-acc .accordion-button {
            background: #0f2244;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .08);
            box-shadow: none !important;
        }

        .mod-acc .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #153060, #0f2244);
            color: #fff;
        }

        /* 🔹 Correção de contraste dentro da grade */
        .mod-acc .accordion-body {
            color: #e9f3ff;
            /* texto principal mais claro */
            background-color: rgba(13, 26, 52, .6);
        }

        .mod-acc .accordion-body ul li {
            color: #e9f3ff;
            margin-bottom: 6px;
            list-style-type: disc;
            margin-left: 20px;
        }

        .mod-acc .accordion-body ul li::marker {
            color: var(--c-h2);
            /* marcador laranja */
        }

        /* Demais estilos mantidos */
        .check {
            color: #54e1c3;
        }

        .card-dark {
            background: var(--c-card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
        }
    </style>

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
                    <h1 class="heading-1 display-5 mb-3">
                        Gabarite Excel nas provas de concurso.
                    </h1>
                    <p class="lead lead-muted mb-4">
                        Aprenda exatamente o que cai nas provas: funções mais cobradas (SOMA, MÉDIA, SE, PROCV/PROCX,
                        CONT.SE, MÁX/MÍN), formatações, gráficos, atalhos e interpretação de questões — com simulados e
                        material para download.
                    </p>
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
        <div class="container">
            <div class="text-center mb-4" data-aos="fade-up">
                <div class="heading-2">Você vai dominar</div>
                <p class="lead lead-muted mb-0">Conteúdo focado no que as bancas mais cobram, com prática orientada.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="0">
                    <div class="card-dark p-4 h-100">
                        <div class="icon-badge mb-3"><i class="bi bi-123 fs-4"></i></div>
                        <div class="fw-bold mb-1">Funções Essenciais</div>
                        <p class="small text-white-50 mb-0">SOMA, MÉDIA, SE, CONT.SE, MÁX, MÍN, PROCV/XLOOKUP e muito
                            mais.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="50">
                    <div class="card-dark p-4 h-100">
                        <div class="icon-badge mb-3"><i class="bi bi-bar-chart-fill fs-4"></i></div>
                        <div class="fw-bold mb-1">Gráficos & Tabelas</div>
                        <p class="small text-white-50 mb-0">Tabelas, Tabela Dinâmica, filtros e gráficos cobrados em
                            editais.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card-dark p-4 h-100">
                        <div class="icon-badge mb-3"><i class="bi bi-lightning fs-4"></i></div>
                        <div class="fw-bold mb-1">Atalhos & Velocidade</div>
                        <p class="small text-white-50 mb-0">Ganhe tempo nas questões com os atalhos certos na hora
                            certa.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="zoom-in" data-aos-delay="150">
                    <div class="card-dark p-4 h-100">
                        <div class="icon-badge mb-3"><i class="bi bi-mortarboard-fill fs-4"></i></div>
                        <div class="fw-bold mb-1">Simulados & Certificado</div>
                        <p class="small text-white-50 mb-0">Simulados com correção comentada e certificado ao concluir.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== SOBRE ===================== -->
    <section id="sobre">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="heading-2 mb-2">Sobre o Curso</div>
                    <p class="mb-3">
                        O <strong>Curso de Excel para Concursos</strong> foi construído especificamente para o contexto
                        das bancas mais recorrentes. Você irá do essencial ao avançado que efetivamente aparece nas
                        provas, com <strong>exercícios direcionados</strong>, bancos de questões e
                        <strong>simulados</strong> que simulam o tempo e o estilo dos exames.
                    </p>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="bi bi-check2-circle check me-2"></i>Acesso anual ou vitalício (defina
                            no seu checkout)</li>
                        <li class="mb-2"><i class="bi bi-check2-circle check me-2"></i>Material PDF e planilhas para
                            download</li>
                        <li class="mb-2"><i class="bi bi-check2-circle check me-2"></i>Aulas ao vivo + gravadas na
                            plataforma</li>
                        <li class="mb-2"><i class="bi bi-check2-circle check me-2"></i>Suporte direto com o professor
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="card-dark p-4 h-100">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-badge"><i class="bi bi-person-video3 fs-4"></i></div>
                            <div>
                                <div class="fw-bold">Para quem é:</div>
                                <p class="small text-white-50 mb-3">
                                    Concurseiros iniciantes ou intermediários que querem <strong>acertar mais
                                        questões</strong> de Excel nas provas e reduzir o tempo de resolução.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-badge"><i class="bi bi-flag fs-4"></i></div>
                            <div>
                                <div class="fw-bold">Objetivo:</div>
                                <p class="small text-white-50 mb-0">
                                    Fazer você dominar as <strong>funções e recursos mais cobrados</strong>, interpretar
                                    enunciados com segurança e resolver questões com método.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            Módulo 1 — Fundamentos que Mais Caem
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
                            <a class="btn btn-cta btn-lg" href="#inscricao">
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