<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <?php
    $titulodados = "INSCRIÇÃO ESPECIAL PARA NOVOS ALUNOS";
    $descricaodados = ('Área Exclusiva para alunos e MEMBROS do curso PROFESSOREUGENIO e Cursos Institucionais');
    $imgMidia = "https://professoreugenio.com/img/compartilhalogin.jpg";
    ?>
    <?php require 'head/head.php'; ?>
    <?php require 'head/head_midiassociais.php'; ?>
    <link rel="stylesheet" href="mycss/login.css">
    <style>
        :root {
            /* Paleta do Eugênio (ajustada p/ claro) */
            --brand-h1: #00BB9C;
            --brand-h2: #FF9C00;
            --brand-bg: #112240;
            --brand-text: #0f172a;
            /* texto principal mais escuro p/ fundo claro */
            --surface: #f3f6fb;
            /* base clara de inputs/card */
            --surface-2: #e8eef7;
            /* bordas e dividers */
            --ink-muted: #5b6b83;
            /* textos secundários */
            --primary-50: #ecf3ff;
            --primary-100: #d9e8ff;
            --primary-400: #6aa9ff;
            --primary-500: #3a84ff;
            --success-500: #00BB9C;
        }

        html[data-bs-theme="dark"] body {
            /* fundo geral ainda escuro, mas com gradiente mais suave */
            background:
                radial-gradient(1200px 600px at 20% 10%, #1a2a52 0%, #172a5a 35%, #153062 55%, var(--brand-bg) 100%),
                #0f1e3a;
            color: #eaf1ff;
        }

        .modal-body {
            color: #000000;
        }

        h1,
        .h1 {
            color: var(--brand-h1);
        }

        h2,
        .h2 {
            color: var(--brand-h2);
        }

        /* ===== Card do formulário (claro sobre fundo escuro) ===== */
        .signup-card {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 1.25rem;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .35);
            overflow: hidden;

            /* “glass light” */
            background: linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(255, 255, 255, .88));
            backdrop-filter: saturate(140%) blur(6px);
            color: var(--brand-text);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-400) 0%, var(--success-500) 100%);
            color: #fff;
            padding: 1.05rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .18);
        }

        .input-icon {
            position: relative;
        }

        .input-icon .bi {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            opacity: .8;
            color: #5c6a85;
        }

        .input-icon input {
            padding-left: 2.25rem;
        }

        /* ===== Campos claros ===== */
        .form-control,
        .form-select {
            background-color: var(--surface);
            color: var(--brand-text);
            border: 1px solid var(--surface-2);
            transition: all .15s ease;
        }

        .form-control::placeholder {
            color: #8a9ab6;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            color: #0b1220;
            border-color: var(--primary-400);
            box-shadow: 0 0 0 .2rem rgba(58, 132, 255, .20);
        }

        .form-text,
        .small-muted,
        .help-text {
            color: #6a7a95;
        }

        /* Selects de data (ficam mais “pill”) */
        #dianasc,
        #mesnasc,
        #anonasc {
            background-color: #fff;
            border-color: #d7e2f2;
            border-radius: .65rem;
        }

        /* Input-group do olho da senha */
        .input-group .input-group-text {
            background: #eef3fb;
            border-color: #d7e2f2;
            color: #4c5a74;
        }

        #toggleSenha {
            border-color: #d7e2f2;
        }

        #toggleSenha:hover {
            background: #eef3fb;
        }

        /* Erro */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .15) !important;
        }

        /* Barra de progresso (força da senha) */
        .progress {
            height: 8px;
            background: #e9eff9;
            border-radius: 999px;
        }

        /* cores Bootstrap já funcionam (bg-danger/warning/info/success) */

        /* Divider mais suave */
        .divider {
            height: 1px;
            background: #e1e8f5;
            margin: 1rem 0 1.25rem;
        }

        /* Botão primário com gradiente claro e efeito hover */
        .btn-primary {
            background-image: linear-gradient(180deg, var(--primary-400), var(--primary-500));
            border: 1px solid #2f6fdb;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(58, 132, 255, .25);
        }

        .btn-primary:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(58, 132, 255, .30);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Link dos Termos em destaque legível */
        a[data-bs-toggle="modal"] {
            color: #0d6efd !important;
        }

        a[data-bs-toggle="modal"]:hover {
            text-decoration: underline;
        }

        /* Voltar ao topo */
        .back-to-top {
            border: 1px solid rgba(255, 255, 255, .35);
            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(4px);
        }
    </style>

</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <?php $pg = "0";
   // require 'modulos/nav.php'; ?>

    <div class="container-xxl py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card signup-card wow fadeInUp" data-wow-delay="0.1s">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus fs-4"></i>
                            <h5 class="mb-0">Nova Inscrição</h5>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <div id="showresult"></div>

                            <form method="post" id="formcadastraaluno" novalidate>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="chaveCadastro" class="form-label">Chave de Inscrição <span class="text-danger">*</span></label>
                                        <div class="input-icon">
                                            <i class="bi bi-key"></i>
                                            <input type="text" id="chaveCadastro" name="chaveCadastro" class="form-control" placeholder="Informe a chave recebida" value="<?= $_GET['key'] ?? '' ?>" required autocomplete="one-time-code">
                                        </div>
                                        <div class="help-text">Esta chave valida seu acesso ao curso.</div>
                                    </div>

                                    <div class="col-12">
                                        <label for="nomeCadastro" class="form-label">Nome completo <span class="text-danger">*</span></label>
                                        <div class="input-icon">
                                            <i class="bi bi-person"></i>
                                            <input type="text" id="nomeCadastro" name="nomeCadastro" class="form-control" placeholder="Ex.: Maria Silva" oninput="Upercase()" required autocomplete="name">
                                        </div>
                                        <div id="result2"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="celularCadastro" class="form-label">Celular <span class="text-danger">*</span></label>
                                        <div class="input-icon">
                                            <i class="bi bi-phone"></i>
                                            <input type="tel" id="celularCadastro" name="celularCadastro" class="form-control" placeholder="(00) 00000-0000" required autocomplete="tel">
                                        </div>
                                        <div id="result3"></div>
                                    </div>

                                    <!-- Data de nascimento em 3 campos -->
                                    <div class="col-md-6">
                                        <label class="form-label">Data de nascimento <span class="text-danger">*</span></label>
                                        <div class="row g-2">
                                            <div class="col-4">
                                                <select id="dianasc" name="dianasc" class="form-select" required></select>
                                            </div>
                                            <div class="col-4">
                                                <select id="mesnasc" name="mesnasc" class="form-select" required></select>
                                            </div>
                                            <div class="col-4">
                                                <select id="anonasc" name="anonasc" class="form-select" required></select>
                                            </div>
                                        </div>
                                        <input type="hidden" id="datanascimento" name="datanascimento" value="">
                                    </div>

                                    <div class="col-12">
                                        <label for="emailCadastro" class="form-label">Seu melhor e-mail <span class="text-danger">*</span></label>
                                        <div class="input-icon">
                                            <i class="bi bi-envelope"></i>
                                            <input type="email" id="emailCadastro" name="emailCadastro" class="form-control" placeholder="seuemail@exemplo.com" oninput="Lowercase()" required autocomplete="email">
                                        </div>
                                        <div id="result4"></div>
                                    </div>

                                    <div class="col-12">
                                        <label for="senhaCadastro" class="form-label">Crie uma senha <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" id="senhaCadastro" name="senhaCadastro" class="form-control" placeholder="Mínimo 8 caracteres" aria-describedby="passwordHelp" required>
                                            <button class="btn btn-outline-secondary" type="button" id="toggleSenha" aria-label="Mostrar/ocultar senha">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="progress mt-2" role="progressbar" aria-label="Força da senha" aria-valuemin="0" aria-valuemax="100">
                                            <div id="pwdMeter" class="progress-bar" style="width: 0%"></div>
                                        </div>
                                        <div id="passwordHelp" class="form-text">Use letras maiúsculas e minúsculas, números e símbolos.</div>
                                        <div id="result5"></div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" value="1" id="aceiteTermos" required>
                                            <label class="form-check-label " for="aceiteTermos">
                                                Declaro que li e concordo com os
                                                <a href="#" style="color:yellow" data-bs-toggle="modal" data-bs-target="#modalTermos">Termos de Uso *</a>.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="divider"></div>

                                    <div class="col-12">
                                        <button class="w-100 btn btn-lg btn-primary" name="btn_cadastrarAluno" value="cadastro" id="btn_cadastrarAluno" type="button">
                                            <div id="loadbt">Avançar</div>
                                        </button>
                                        <div class="small-muted mt-2">Seus dados estão protegidos e não serão compartilhados.</div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Termos -->
    <div class="modal fade" id="modalTermos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" style="color: #00b894;">Termos de Uso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body small ">
                    <h6 class="mb-3" style="color:#00BB9C;">Termos de Uso – Acesso ao Curso</h6>
                    <p>Ao realizar sua inscrição, o aluno declara estar ciente de que:</p>

                    <ul class="mt-2">
                        <li><strong>Período de Acesso:</strong> o acesso ao curso será válido durante todo o período contratado de acordo com a modalidade escolhida (curso individual, assinatura anual ou vitalícia, quando disponível).</li>
                        <li><strong>Conteúdo Disponível:</strong> durante a vigência do acesso, o aluno terá direito a:
                            <ul>
                                <li>Aulas e lições em vídeo;</li>
                                <li>Atividades e exercícios propostos;</li>
                                <li>Arquivos e materiais para download;</li>
                                <li>Recursos extras liberados pelo professor.</li>
                            </ul>
                        </li>
                        <li><strong>Uso Pessoal:</strong> o acesso é individual, sendo proibido compartilhar login e senha com terceiros.</li>
                        <li><strong>Atualizações:</strong> novos conteúdos e atualizações poderão ser disponibilizados dentro do período contratado, sem custos adicionais.</li>
                        <li><strong>Encerramento do Acesso:</strong> ao término do período contratado, o acesso às aulas, atividades e arquivos será automaticamente encerrado, salvo em caso de renovação ou contratação de nova modalidade.

                            <p>Atenciosamente,</p>
                            <p>Professor Eugênio</p>

                        </li>
                    </ul>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="showmodal"></div>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <!-- jQuery Mask -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script src="scripts_inscricao/ajax_inscricao.js"></script>

    <script>
        // Mostrar/ocultar senha
        document.getElementById('toggleSenha').addEventListener('click', function() {
            var senhaInput = document.getElementById('senhaCadastro');
            var icon = this.querySelector('i');
            if (senhaInput.type === "password") {
                senhaInput.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
                this.setAttribute('aria-label', 'Ocultar senha');
            } else {
                senhaInput.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
                this.setAttribute('aria-label', 'Mostrar senha');
            }
        });

        // Máscara de celular
        $(function() {
            $('#celularCadastro').mask('(00) 00000-0000');
        });

        // ====== Data de Nascimento (DIA/MÊS/ANO) ======
        const $dia = document.getElementById('dianasc');
        const $mes = document.getElementById('mesnasc');
        const $ano = document.getElementById('anonasc');
        const $hiddenData = document.getElementById('datanascimento');

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function diasNoMes(mes, ano) {
            return new Date(ano, mes, 0).getDate();
        } // mes 1-12

        function popularMeses() {
            const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $mes.innerHTML = '<option value="" selected disabled>Mês</option>';
            for (let i = 1; i <= 12; i++) {
                const opt = document.createElement('option');
                opt.value = pad(i);
                opt.textContent = `${pad(i)} - ${meses[i-1]}`;
                $mes.appendChild(opt);
            }
        }

        function popularAnos() {
            const anoAtual = new Date().getFullYear();
            const maxAno = anoAtual - 10; // mínimo 10 anos
            const minAno = 1930;
            $ano.innerHTML = '<option value="" selected disabled>Ano</option>';
            for (let a = maxAno; a >= minAno; a--) {
                const opt = document.createElement('option');
                opt.value = String(a);
                opt.textContent = String(a);
                $ano.appendChild(opt);
            }
        }

        function popularDias() {
            const ano = parseInt($ano.value || new Date().getFullYear(), 10);
            const mes = parseInt(($mes.value || '01'), 10);
            const total = diasNoMes(mes, ano);
            const keep = $dia.value;
            $dia.innerHTML = '<option value="" selected disabled>Dia</option>';
            for (let d = 1; d <= total; d++) {
                const v = pad(d);
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                $dia.appendChild(opt);
            }
            if (keep && parseInt(keep, 10) <= total) {
                $dia.value = keep;
            }
        }

        function atualizarHidden() {
            if ($dia.value && $mes.value && $ano.value) {
                $hiddenData.value = `${$ano.value}-${$mes.value}-${$dia.value}`; // YYYY-MM-DD
            } else {
                $hiddenData.value = '';
            }
        }
        // Inicialização
        (function() {
            popularMeses();
            popularAnos();
            popularDias();
            [$mes, $ano].forEach(el => el.addEventListener('change', () => {
                popularDias();
                atualizarHidden();
            }));
            [$dia].forEach(el => el.addEventListener('change', atualizarHidden));
        })();

        // Upercase / Lowercase
        function Upercase() {
            var input = document.getElementById("nomeCadastro");
            input.value = input.value.toUpperCase();
        }

        function Lowercase() {
            var input = document.getElementById("emailCadastro");
            input.value = input.value.toLowerCase();
        }
        window.Upercase = Upercase;
        window.Lowercase = Lowercase;

        // Validação leve no botão (mantém teu AJAX)
        document.getElementById('btn_cadastrarAluno').addEventListener('click', function() {
            const required = ['chaveCadastro', 'nomeCadastro', 'celularCadastro', 'emailCadastro', 'senhaCadastro'];
            let ok = true;
            required.forEach(id => {
                const el = document.getElementById(id);
                if (!el.value) {
                    el.classList.add('is-invalid');
                    ok = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });
            if (!$hiddenData.value) {
                [$dia, $mes, $ano].forEach(e => e.classList.add('is-invalid'));
                ok = false;
            } else {
                [$dia, $mes, $ano].forEach(e => e.classList.remove('is-invalid'));
            }
            if (ok) {
                document.getElementById('formcadastraaluno').setAttribute('data-ready', '1');
            }
        });

        // Medidor de força da senha (corrigido)
        const pwd = document.getElementById('senhaCadastro');
        const meter = document.getElementById('pwdMeter');

        function scorePassword(p) {
            let s = 0;
            if (!p) return 0;
            if (p.length >= 8) s += 25;
            if (/[A-Z]/.test(p)) s += 15;
            if (/[a-z]/.test(p)) s += 15;
            if (/\d/.test(p)) s += 15; // dígito
            if (/[^\w\s]/.test(p)) s += 30; // símbolo
            return Math.min(s, 100);
        }

        function meterColor(v) {
            if (v < 30) return 'bg-danger';
            if (v < 60) return 'bg-warning';
            if (v < 85) return 'bg-info';
            return 'bg-success';
        }
        pwd.addEventListener('input', function() {
            const v = scorePassword(this.value);
            meter.style.width = v + '%';
            meter.className = 'progress-bar ' + meterColor(v);
        });
    </script>
</body>

</html>