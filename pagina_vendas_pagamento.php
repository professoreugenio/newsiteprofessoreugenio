<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <?php require 'config_vendas/v2.0/query_vendas.php' ?>
    <?php $titulodados = $nomeCurso; ?>
    <?php $descricaodados = $descricao; ?>
    <?php $keywordsdados = $descricaodados . ", " . extractWords($descricaodados) . ", professoreugenio, professoreugênio, professor eugênio, 
   professor eugenio, aulas on-line, aulas online, online, aulas"; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- <link rel="stylesheet" href="config_index2/body_index.css"> -->
    <link rel="stylesheet" href="mycss/default.css?<?= time(); ?>">
    <link rel="stylesheet" href="mycss/config.css?<?= time(); ?>">
    <link rel="stylesheet" href="config_vendas/v2.0/CSS_vendas.css?<?= time(); ?>">
    <link rel="stylesheet" href="mycss/nav.css">
    <link rel="stylesheet" href="mycss/animate.min.css">
    <link rel="stylesheet" href="config_default/config.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-W1W8QZFR43');
    </script>




    <!-- Event snippet for Assinatura conversion page -->
    <script>
        gtag('event', 'conversion', {
            'send_to': 'AW-11538602776/_dEFCMnumoAaEJi2hP4q'
        });
    </script>
</head>

<body>
    <?php // include 'config_default/body_navall.php'; 
    ?>
    <div class="container py-4">
        <div class="text-center mb-4">
            <h1 class="hero-title text-gradient text-center fw-bold display-4 mb-4 animate__animated animate__fadeInDown">
                CURSO <?php echo $nomeCurso; ?>
            </h1>
        </div>
        <!-- <div class="card shadow-lg border-0 rounded-4 overflow-hidden px-2 py-2 mb-4">
            <?php //echo $dec = encrypt($_GET['nav'], $action = 'd'); 
            ?>
        </div> -->
        <div class="row g-4 align-items-start">
            <!-- Lado esquerdo: Imagem e preços -->
            <!-- Lado direito: Vídeo e texto -->
            <div class="col-md-7">
                <section class="container">
                    <div class="row align-items-center">
                        <div class="col-md-12">

                            <div class="container py-2 text-center">

                                <div class="row justify-content-center g-4">
                                    <div class="text-center bg-dark text-light p-4 rounded-4 shadow-lg">
                                        <h4 class="text-success mb-3">
                                            🎉 <strong>Parabéns, <?php echo $nmCliente; ?>!</strong>
                                        </h4>
                                        <p class="fs-5">Sua inscrição foi <span class="text-warning fw-bold">realizada com sucesso!</span></p>
                                        <p>Após o pagamento acesse seu E-mail para ativar seu cadastro.</p>

                                        <hr class="border-light">

                                        <h4 class="text-info fw-semibold">
                                            Plano <?php echo $plano; ?> <span class="text-light">por</span> <strong class="text-warning">R$ <?php echo $valor; ?></strong>
                                        </h4>
                                        <h5 class="display-7 fw-bold text-light mb-3">🧾 Forma de Pagamento</h5>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card h-100 bg-dark border-success shadow rounded-4 text-light ">
                                            <div class="card-body text-center">
                                                <i class="bi bi-qr-code-scan display-4 text-success mb-3"></i>

                                                <h5 class="card-title">Pagamento via Pix</h5>
                                                <p class="card-text">Melhor opção à vista.</p>
                                                <button class="btn btn-success px-4 py-2 rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#pixModal">
                                                    <i class="bi bi-cash-coin me-2"></i>Pagar com Pix
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pagamento via Cartão -->
                                    <div class="col-md-6">
                                        <div class="card h-100 bg-dark border-primary shadow rounded-4 text-light">
                                            <div class="card-body text-center">
                                                <i class="bi bi-credit-card-2-front display-4 text-primary mb-3"></i>
                                                <h5 class="card-title">Cartão de Crédito</h5>
                                                <p class="card-text">Parcele em até <strong>12x sem juros</strong>.</p>
                                                <button class="btn btn-primary px-4 py-2 rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#cartaoModal">
                                                    <i class="bi bi-credit-card me-2"></i>Pagar com Cartão
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-5" style="height: 100vh;position: sticky;">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <img src="https://professoreugenio.com/img/vendas/bgvendas2.jpg" class="card-img-top"
                        alt="Imagem da aluna" style="height: 380px; object-fit: cover;">
                    <div class="card-body text-center text-white" style="background: linear-gradient(135deg, <?php echo $bgcolor;  ?>,rgb(31, 31, 30));">
                        <h5 class="card-title">Parabéns!Agora é com você!</h5>
                        <h5 class="card-title"> Aproveite ao máximo esta jornada de aprendizado.
                        </h5>
                        <p>Bons estudos — você fez a escolha certa!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require 'config_default/link_adm.php'; ?>

    <!-- Modal Pix -->
    <!-- Bootstrap Icons (se ainda não estiver incluído) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="modal fade" id="pixModal" tabindex="-1" aria-labelledby="pixModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light rounded-4 shadow-lg border border-success">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-success d-flex align-items-center" id="pixModalLabel">
                        <i class="bi bi-qr-code-scan me-2 fs-4"></i> Pagamento via Pix
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center">
                    <?php require 'config_vendas/v2.0/qr_code.php'; ?>
                    <p class="fw-semibold mt-3 text-light">Chave Pix:</p>

                    <div class="input-group mb-3 shadow-sm">
                        <input type="text" class="form-control bg-secondary text-white border-0" id="pixCode" value="<?php echo $chavepix; ?>" readonly>
                        <button class="btn btn-success" onclick="copiarChavePix()">
                            <i class="bi bi-clipboard"></i> Copiar
                        </button>
                    </div>

                    <small class="text-muted">Copie a chave ou use o QR Code para realizar o pagamento.</small>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Cartão -->
    <!-- CDN Bootstrap Icons (se ainda não estiver incluído) -->


    <div class="modal fade" id="cartaoModal" tabindex="-1" aria-labelledby="cartaoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light rounded-4 shadow-lg border border-warning">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-warning d-flex align-items-center" id="cartaoModalLabel">
                        <i class="bi bi-credit-card-2-front-fill me-2 fs-4"></i> Pagamento com Cartão de Crédito
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="cartao px-2 py-3 text-start">
                        <?php
                        $decuser = encrypt($_COOKIE['dadoscadastro'], $action = 'd');
                        $rwUser = explode("&", $decuser);
                        ?>
                        <hr class="border-secondary">
                        <?php echo $pagueseguro; ?>
                        <script src="https://sdk.mercadopago.com/js/v2"></script>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal de confirmação de cópia -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <div class="modal fade" id="copyModal" tabindex="-1" aria-labelledby="copyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light rounded-4 shadow-lg border border-teal">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-info d-flex align-items-center" id="copyModalLabel">
                        <i class="bi bi-check-circle-fill me-2 text-success fs-4"></i> Código Copiado!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-light">
                    O código Pix foi copiado com sucesso.<br>
                    Agora você pode colá-lo no seu aplicativo de pagamento ou utilizar o QR Code disponível.
                </div>
                <div class="modal-footer border-0 justify-content-end">
                    <button type="button" class="btn btn-outline-success rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-check2-circle me-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>



    <script>
        function copiarChavePix() {
            const pixCode = document.getElementById('pixCode');
            pixCode.select();
            document.execCommand('copy');
            // Exibir o modal de confirmação
            const modal = new bootstrap.Modal(document.getElementById('copyModal'));
            modal.show();
        }
    </script>
    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">&copy;Professor Eugênio 2025 Cursos Online. Todos os direitos reservados.</p>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">

    </script>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
    <script>
    </script>
</body>

</html>