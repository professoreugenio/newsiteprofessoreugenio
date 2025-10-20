<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seleção de Plano — Curso de Excel para Concursos | Professor Eugênio</title>
    <meta name="description"
        content="Escolha o plano do Curso de Excel para Concursos e prossiga para o pagamento. Opções Anual e Vitalício, Pix/Cartão/Boleto.">
    <link rel="canonical" href="https://professoreugenio.com/selecionar_plano">

    <!-- CSS: Bootstrap / Icons / AOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Estilos do tema (paleta padrão) -->
    <style>
        :root {
            --c-h1: #00BB9C;
            /* h1 */
            --c-h2: #FF9C00;
            /* títulos tipo <h2> */
            --c-bg: #112240;
            /* fundo */
            --c-text: #ffffff;
            /* texto */
            --c-card: #0d1a34;
            /* cards */
            --c-muted: #9fb1d1;
        }

        body {
            background: var(--c-bg);
            color: var(--c-text);
            scroll-behavior: smooth;
        }

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

        .small-muted {
            color: var(--c-muted);
        }

        section {
            padding: 64px 0;
        }

        .card-dark {
            background: var(--c-card);
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 1rem;
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            color: #e9f3ff;
        }

        /* Barra de etapas (50% - etapa 2 de 4) */
        .steps {
            height: 6px;
            background: #1b2d55;
            border-radius: 999px;
            position: relative;
            overflow: hidden;
        }

        .steps>.progress {
            height: 100%;
            background: var(--c-h1);
            width: 50%;
        }

        /* Cartões de planos */
        .plan {
            position: relative;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
            border: 1px solid rgba(255, 255, 255, .12);
        }

        .plan:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
        }

        .plan.recommended {
            border: 1px solid rgba(255, 156, 0, .55);
            box-shadow: 0 12px 32px rgba(255, 156, 0, .18);
        }

        .plan .price {
            color: #00BB9C;
        }

        .plan .check {
            color: #54e1c3;
        }

        .plan .radio-wrap {
            border: 1px dashed rgba(255, 255, 255, .18);
            border-radius: .75rem;
            padding: .5rem .75rem;
            background: rgba(17, 34, 64, .35);
        }

        .plan input[type="radio"] {
            width: 1.15rem;
            height: 1.15rem;
            accent-color: #FF9C00;
            cursor: pointer;
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

        /* Tabela de comparação */
        .table-compare {
            --bs-table-color: #e9f3ff;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, .12);
        }

        .table-compare th,
        .table-compare td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <!-- ===================== NAV ===================== -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.html"><i class="bi bi-microsoft me-1"></i> Professor
                Eugênio</a>
            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navMain" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#planos">Planos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#comparativo">Comparativo</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-cta" href="#prosseguir"><i
                                class="bi bi-lightning-charge-fill me-1"></i> Continuar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===================== HEADER / ETAPA ===================== -->
    <section class="pt-5">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <span class="badge badge-soft rounded-pill px-3 py-2 small mb-3">
                        <i class="bi bi-journal-check me-1"></i> Etapa 2 de 4 — Seleção de Plano
                    </span>
                    <div class="heading-1 mb-2">Escolha seu Plano</div>
                    <p class="small-muted mb-2">Selecione abaixo a opção ideal para você. Você poderá pagar via Pix,
                        Cartão ou Boleto na próxima etapa.</p>
                    <div class="steps mt-3">
                        <div class="progress"></div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="card-dark p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-white-50">Curso</div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Vagas
                                Limitadas</span>
                        </div>
                        <div class="fs-5 fw-bold mt-1">Excel para Concursos</div>
                        <div class="small text-white-50">Aulas ao vivo + gravadas • PDFs • Simulados • Certificado</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== PLANOS ===================== -->
    <section id="planos">
        <div class="container">
            <div class="row g-4">
                <!-- Plano Anual -->
                <div class="col-md-6" data-aos="zoom-in">
                    <div class="card-dark plan h-100 p-4">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="heading-2 mb-1">Plano Anual</div>
                                <div class="price display-6 fw-bold">R$ 39,90/mês</div>
                                <div class="small text-white-50">Cobrança recorrente • Cancela quando quiser</div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#cdeee7;">Mais popular</span>
                        </div>

                        <ul class="small mt-3 mb-3">
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso por 12 meses</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Aulas ao vivo + gravadas</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>PDFs e simulados</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Certificação digital</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Suporte direto</li>
                        </ul>

                        <div class="radio-wrap d-flex align-items-center justify-content-between">
                            <label class="form-check-label" for="plano_anual">
                                <strong>Selecionar Plano Anual</strong>
                            </label>
                            <input type="radio" name="plano" id="plano_anual" value="anual" aria-label="Plano Anual">
                        </div>

                        <div class="d-grid mt-3">
                            <button class="btn btn-outline-soft btn-lg btn-select" data-plan="anual"
                                data-valor="R$ 39,90/mês">
                                <i class="bi bi-check-circle me-2"></i> Selecionar Anual
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plano Vitalício -->
                <div class="col-md-6" data-aos="zoom-in" data-aos-delay="50">
                    <div class="card-dark plan recommended h-100 p-4">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="heading-2 mb-1">Plano Vitalício</div>
                                <div class="price display-6 fw-bold">R$ 85,00</div>
                                <div class="small text-white-50">Pagamento único • Acesso permanente</div>
                            </div>
                            <span class="badge rounded-pill text-dark" style="background:#FF9C00;">Melhor
                                custo-benefício</span>
                        </div>

                        <ul class="small mt-3 mb-3">
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Acesso vitalício ao conteúdo
                            </li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Aulas ao vivo + gravadas</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>PDFs e simulados</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Certificação digital</li>
                            <li class="mb-1"><i class="bi bi-check2-circle me-2 check"></i>Suporte direto</li>
                        </ul>

                        <div class="radio-wrap d-flex align-items-center justify-content-between">
                            <label class="form-check-label" for="plano_vitalicio">
                                <strong>Selecionar Plano Vitalício</strong>
                            </label>
                            <input type="radio" name="plano" id="plano_vitalicio" value="vitalicio"
                                aria-label="Plano Vitalício" checked>
                        </div>

                        <div class="d-grid mt-3">
                            <button class="btn btn-cta btn-lg btn-select" data-plan="vitalicio" data-valor="R$ 85,00">
                                <i class="bi bi-check-circle-fill me-2"></i> Selecionar Vitalício
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso -->
            <div class="text-center small small-muted mt-4" data-aos="fade-up">
                Você poderá escolher <strong>Pix, Cartão ou Boleto</strong> na próxima etapa.
            </div>
        </div>
    </section>

    <!-- ===================== COMPARATIVO ===================== -->
    <section id="comparativo">
        <div class="container">
            <div class="text-center mb-3" data-aos="fade-up">
                <div class="heading-2">Comparativo rápido</div>
                <p class="small-muted mb-0">As duas opções entregam o mesmo conteúdo. A diferença está no período de
                    acesso e forma de cobrança.</p>
            </div>

            <div class="table-responsive" data-aos="fade-up" data-aos-delay="50">
                <table class="table table-compare align-middle">
                    <thead>
                        <tr>
                            <th width="38%">Recurso</th>
                            <th class="text-center">Anual</th>
                            <th class="text-center">Vitalício</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Acesso à plataforma</td>
                            <td class="text-center">12 meses</td>
                            <td class="text-center">Ilimitado</td>
                        </tr>
                        <tr>
                            <td>Aulas ao vivo + gravadas</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>PDFs, planilhas e simulados</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>Certificação digital</td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                            <td class="text-center"><i class="bi bi-check2-circle check"></i></td>
                        </tr>
                        <tr>
                            <td>Forma de pagamento</td>
                            <td class="text-center">Recorrente mensal</td>
                            <td class="text-center">Único (uma vez)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ===================== CTA / PROSSEGUIR ===================== -->
    <section id="prosseguir">
        <div class="container">
            <div class="card-dark p-4" data-aos="fade-up">
                <div class="row gy-3 align-items-center">
                    <div class="col-lg-8">
                        <div class="heading-2 mb-1">Pronto para continuar?</div>
                        <p class="mb-0 small-muted">Clique em <strong>Prosseguir para Pagamento</strong>. Mantemos seus
                            dados salvos para agilizar a finalização.</p>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-grid d-md-flex gap-2 justify-content-lg-end">
                            <a class="btn btn-outline-soft btn-lg" href="inscricao.html">
                                <i class="bi bi-arrow-left-circle me-2"></i> Voltar
                            </a>
                            <button class="btn btn-cta btn-lg" id="btnProsseguir">
                                <i class="bi bi-credit-card me-2"></i> Prosseguir para Pagamento
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="small text-white-50 mt-3">
                Dúvidas? <a class="link-light link-underline-opacity-0" target="_blank" rel="noopener"
                    href="https://wa.me/5585XXXXXXXX?text=Tenho%20d%C3%BAvidas%20sobre%20o%20plano%20do%20Curso%20de%20Excel%20para%20Concursos">Fale
                    no WhatsApp</a>.
            </p>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
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

        // ---------- Estado / Persistência ----------
        // Chaves salvas na etapa 1 (inscrição)
        const FIELDS = ['nome', 'email', 'telefone', 'objetivo', 'idCurso', 'idTurma', 'utm'];

        function getStore(k) {
            try {
                return localStorage.getItem('insc_' + k) || '';
            } catch (e) {
                return '';
            }
        }

        function setStore(k, v) {
            try {
                localStorage.setItem('insc_' + k, v);
            } catch (e) {}
        }

        // Plano selecionado (default: vitalício)
        let planoAtual = 'vitalicio';
        let valorPlanoFmt = 'R$ 85,00';

        // Marca rádio ao clicar no botão "Selecionar"
        document.querySelectorAll('.btn-select').forEach(btn => {
            btn.addEventListener('click', (ev) => {
                const plan = btn.dataset.plan;
                const valor = btn.dataset.valor || '';
                const radio = document.querySelector('input[name="plano"][value="' + plan + '"]');
                if (radio) {
                    radio.checked = true;
                }
                planoAtual = plan;
                valorPlanoFmt = valor;
                // persiste
                setStore('planoSelecionado', plan);
                setStore('valorPlanoFmt', valor);
                // feedback rápido
                btn.classList.add('disabled');
                setTimeout(() => btn.classList.remove('disabled'), 400);
            });
        });

        // Se o usuário clicar diretamente no radio
        document.querySelectorAll('input[name="plano"]').forEach(r => {
            r.addEventListener('change', () => {
                planoAtual = r.value;
                valorPlanoFmt = (planoAtual === 'anual') ? 'R$ 39,90/mês' : 'R$ 85,00';
                setStore('planoSelecionado', planoAtual);
                setStore('valorPlanoFmt', valorPlanoFmt);
            });
        });

        // UTM carry-over (se vier via querystring)
        const params = new URLSearchParams(location.search);
        const utm = params.get('utm');
        if (utm) {
            setStore('utm', utm);
        }

        // ---------- Próxima etapa ----------
        document.getElementById('btnProsseguir').addEventListener('click', () => {
            // Garante que há um plano marcado
            const checked = document.querySelector('input[name="plano"]:checked');
            if (!checked) {
                alert('Selecione um plano para continuar.');
                return;
            }
            // Monta payload para querystring (ajuste nomes conforme back-end)
            const data = {
                plano: checked.value, // 'anual' | 'vitalicio'
                valorPlanoFmt: valorPlanoFmt, // ex.: "R$ 39,90/mês" | "R$ 85,00"
                idCurso: getStore('idCurso') || 'excel-concursos',
                idTurma: getStore('idTurma') || 'turma-2025-01',
                nome: getStore('nome') || '',
                email: getStore('email') || '',
                telefone: getStore('telefone') || '',
                objetivo: getStore('objetivo') || '',
                utm: getStore('utm') || ''
            };

            // Persiste para uso na página de pagamento
            setStore('planoSelecionado', data.plano);
            setStore('valorPlanoFmt', data.valorPlanoFmt);

            // Redireciona para sua página de pagamento
            const qs = new URLSearchParams(data).toString();
            window.location.href = 'vendaPagamento.php?' + qs;
        });
    </script>
</body>

</html>