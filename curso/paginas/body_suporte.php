<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>

<?php
// Utilitário simples para escapar
if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Tenta obter o nome do usuário de variáveis já existentes/sessão
$nomeAluno = $nome ?? ($nmUser ?? 'aluno');
$nomeTurma = $nomeTurma ?? ($nmTurma ?? 'turma não identificada');

// Link WhatsApp (55 + DDD 85 + número 996537577) com mensagem pré-preenchida
$whatsNumber  = '5585996537577';
$whatsMessage = rawurlencode('*Suporte*:
' . $saudacao . ' 
Meu nome é: ' . $nomeAluno . '
Do curso : ' . $nomeTurma . '
Descrevo os seguintes problemas apresentados:
.');
$whatsLink    = "https://wa.me/{$whatsNumber}?text={$whatsMessage}";
?>

<style>
    /* Paleta padrão do projeto */
    :root {
        --brand-h1: #00BB9C;
        --brand-h2: #FF9C00;
        --brand-bg: #112240;
        --brand-text: #ffffff;
    }

    body {
        background: var(--brand-bg);
    }

    .suporte-hero {
        background: radial-gradient(1200px 600px at 10% 10%, rgba(0, 187, 156, .25), transparent 50%),
            radial-gradient(1200px 600px at 90% 20%, rgba(255, 156, 0, .18), transparent 50%);
        border-radius: 20px;
        padding: 2rem;
        color: var(--brand-text);
        position: relative;
        overflow: hidden;
    }

    .suporte-hero .badge-safe {
        background: rgba(0, 187, 156, .15);
        border: 1px solid rgba(0, 187, 156, .5);
        color: #CFFAF0;
    }

    .suporte-hero h1 {
        color: var(--brand-h1);
        font-weight: 800;
        letter-spacing: .3px;
    }

    .suporte-hero p.lead {
        color: #e9f7f4;
        font-size: 1.05rem;
    }

    .card-suporte {
        background: #0f1c3a;
        border: 1px solid rgba(255, 255, 255, .06);
        color: var(--brand-text);
        border-radius: 16px;
    }

    .card-suporte .list-group-item {
        background: transparent;
        color: #dfe7ff;
        border-color: rgba(255, 255, 255, .06);
    }

    .btn-whats {
        background: #25D366;
        border: none;
        color: #0b1a12;
        font-weight: 700;
    }

    .btn-whats:hover {
        filter: brightness(1.03);
    }

    .btn-contato {
        background: var(--brand-h2);
        border: none;
        color: #1b1100;
        font-weight: 700;
    }

    /* Ilustração amigável (SVG inline) */
    .friendly-illu {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        background: radial-gradient(60% 60% at 40% 35%, #00bb9c 0%, #007a66 60%, #005a4d 100%);
        box-shadow: 0 8px 40px rgba(0, 0, 0, .35), inset 0 0 40px rgba(0, 0, 0, .25);
        position: relative;
    }

    .friendly-illu:before,
    .friendly-illu:after {
        content: "";
        position: absolute;
        background: #fff;
        border-radius: 50%;
    }

    .friendly-illu:before {
        /* olho esquerdo */
        width: 18px;
        height: 18px;
        left: 42px;
        top: 58px;
        box-shadow: 0 0 0 4px rgba(0, 0, 0, .15);
    }

    .friendly-illu:after {
        /* olho direito */
        width: 18px;
        height: 18px;
        right: 40px;
        top: 58px;
        box-shadow: 0 0 0 4px rgba(0, 0, 0, .15);
    }

    .friendly-smile {
        width: 76px;
        height: 36px;
        border-bottom-left-radius: 90px;
        border-bottom-right-radius: 90px;
        border: 5px solid #fff;
        border-top: 0;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: 45px;
        box-shadow: 0 6px 0 rgba(0, 0, 0, .15);
    }
</style>

<!-- Conteúdo -->
<section id="Corpo" class="py-4">
    <div class="container">
        <!-- Título -->
        <div class="text-center mb-4" data-aos="fade-up">
            <h4 class="mt-2 mb-2 text-white">
                <i class="bi bi-life-preserver me-2"></i> Suporte
            </h4>
        </div>

        <!-- Hero / Mensagem principal -->
        <div class="suporte-hero mb-4" data-aos="fade-up" data-aos-delay="50">
            <div class="row align-items-center g-4">
                <div class="col-md-8">
                    <span class="badge rounded-pill badge-safe mb-2">
                        <i class="bi bi-shield-check me-1"></i> Ambiente seguro • Equipe técnica ativa
                    </span>
                    <h1 class="h3 mb-3">Olá, <?= e(ucwords($nomeAluno)); ?>! <span class="d-inline-block ms-1">👋</span></h1>
                    <p class="lead mb-3">
                        Parece que houve algum problema no seu acesso. Não foi possível acessar sua conta neste momento.
                        Entre em contato com o <strong>suporte</strong> e forneça detalhes do ocorrido; nossa equipe técnica
                        normalmente resolve em poucos minutos.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= e($whatsLink); ?>" class="btn btn-whats rounded-pill px-3 py-2" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-2"></i> Falar no WhatsApp (85) 99653-7577
                        </a>
                        <?php $lkContato = '../pagina_contato.php?v=ZFZDdkVwN2RDa0plVWdienNUQTRqdz09&1757607279'; ?>
                        <a href="<?= e($lkContato); ?>" class="btn btn-contato rounded-pill px-3 py-2">
                            <i class="bi bi-envelope-paper-heart me-2"></i> Ir para a página de contato
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="d-inline-block friendly-illu" aria-hidden="true">
                        <div class="friendly-smile"></div>
                    </div>
                    <div class="small text-white-50 mt-2">
                        <i class="bi bi-stars me-1"></i> Atendimento humanizado
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocos de orientação -->
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card card-suporte h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-info-circle me-2 text-warning"></i> O que você pode nos informar
                        </h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><i class="bi bi-check-circle me-2"></i> Curso e turma que está tentando acessar</li>
                            <li class="list-group-item"><i class="bi bi-check-circle me-2"></i> Mensagem de erro (se houver) ou tela onde parou</li>
                            <li class="list-group-item"><i class="bi bi-check-circle me-2"></i> Data e horário aproximados do problema</li>
                            <li class="list-group-item"><i class="bi bi-check-circle me-2"></i> Navegador e dispositivo (ex.: Chrome no Windows)</li>
                        </ul>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a class="btn btn-sm btn-outline-light" href="<?= e($whatsLink); ?>" target="_blank" rel="noopener">
                            <i class="bi bi-chat-left-text me-1"></i> Enviar essas informações pelo WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
                <div class="card card-suporte h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-lightning-charge me-2 text-success"></i> Soluções rápidas que você pode tentar
                        </h5>
                        <ol class="mb-0 ps-3">
                            <li class="mb-2">Saia da conta e faça login novamente.</li>
                            <li class="mb-2">Atualize a página (<kbd>Ctrl</kbd> + <kbd>F5</kbd>).</li>
                            <li class="mb-2">Teste em outro navegador (Chrome/Edge/Firefox).</li>
                            <li class="mb-2">Limpe o cache do navegador e tente de novo.</li>
                        </ol>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a class="btn btn-sm btn-outline-light" href="<?= e($lkContato); ?>">
                            <i class="bi bi-envelope me-1"></i> Falar pelo formulário de contato
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aviso de prazo expirado (opcional, informativo) -->
        <div class="alert alert-warning mt-4 border-0" role="alert" data-aos="fade-up" data-aos-delay="200" style="background:rgba(255,156,0,.15); color:#ffe9c7;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Caso o acesso tenha expirado por prazo de matrícula, nossa equipe pode orientar a regularização rapidamente.
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.AOS) AOS.init({
            duration: 700,
            once: true
        });

        // Mantido do seu padrão: abre o modal 1x ao dia, se existir na página
        const el = document.getElementById('modalAulasAtuais');
        if (el && window.bootstrap) {
            const hoje = new Date().toISOString().slice(0, 10);
            const ultimaData = localStorage.getItem('aulasAtuaisData');
            if (ultimaData !== hoje) {
                const modal = new bootstrap.Modal(el);
                modal.show();
                localStorage.setItem('aulasAtuaisData', hoje);
            }
        }
    });
</script>

<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>

<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>

<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>