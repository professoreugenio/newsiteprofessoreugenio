<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'config_default1.0/query_dados.php' ?>
    <?php require 'config_curso1.0/query_curso.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_curso1.0/v2.0/query_publicacoes.php' ?>
    <?php require 'config_curso1.0/query_anexos.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Curso de <?= $nomeTurma; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="v2.0/config_css/config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>
    <link rel="stylesheet" href="v2.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <style>
        .box-licoes {
            display: none;
        }
    </style>

    <style>
        /* Menu lateral fixo à esquerda */
        #sidebar_config {
            width: 50px;
            height: 100vh;
            background-color: #1e1e1e;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px 0;
            z-index: 1000;
        }

        /* Estilização dos botões */
        .sidebar_config-btn {
            background-color: #2c2c2c;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin: 10px 0;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .sidebar_config-btn:hover {
            background-color: #3a3a3a;
        }

        /* Espaço para conteúdo ao lado do menu */
        .content {
            margin-left: 40px;
            padding: 20px;
            color: white;
        }
    </style>
</head>

<body style="min-height: 600px;">
    <?php if ($tipocurso == 0) :
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas">');
        exit();
    endif; ?>

    <!-- Menu lateral -->
    <div id="sidebar_config">
        <button class="sidebar_config-btn" onclick="window.location.href='configuracoes_perfil.php'" title="Perfil">
            <i class="bi bi-person-fill"></i>
        </button>

        <button class="sidebar_config-btn" onclick="window.location.href='configuracoes_foto.php'" title="Foto">
            <i class="bi bi-camera-fill"></i>
        </button>

        <button class="sidebar_config-btn" onclick="window.location.href='configuracoes_turma.php'" title="Turma">
            <i class="bi bi-gear-fill"></i>
        </button>
    </div>
    <!-- Navbar -->
    <?php include 'v2.0/nav.php'; ?>
    <?php $quantAnexo = "0"; ?>
    <?php require 'v2.0/sidebarLateral.php'; ?>
    <?php require 'v2.0/sidebarLateralLicoes.php'; ?>
    <!-- Conteúdo -->
    <main class="container ">
        <div class="row w-100">
            <!-- Conteúdo Principal da Aula -->
            <div class="col-md-6">
                <div class="info-curso">
                    <h3>Editando meus dados</h3>
                    <form action="" style="margin-left:auto;margin-right:auto" id="idformupdate" method="post"
                        enctype="multipart/form-data">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div>
                                        <?php if (!empty($_SESSION['resupdperfil'])) {
                                            echo $_SESSION['resupdperfil'];
                                        } ?>
                                    </div>
                                    <div id="colconteudo">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <input id="nome" name="nome" value="<?php echo $rwUser['nome']; ?>"
                                                type="text" class="form-control" placeholder="Username"
                                                aria-label="Username" aria-describedby="basic-addon1">
                                        </div>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-calendar"></i>
                                            </span>
                                            <input id="datanasc" name="datanasc"
                                                value="<?php echo $rwUser['datanascimento_sc'] ?? ''; ?>"
                                                type="date" class="form-control"
                                                aria-label="Username" aria-describedby="basic-addon1">

                                        </div>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input id="email" readonly name="email"
                                                value="<?php echo $rwUser['email']; ?>" type="email"
                                                class="form-control" placeholder="Email" aria-label="Email"
                                                aria-describedby="basic-addon1">
                                        </div>

                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-telephone"></i>
                                            </span>
                                            <input id="celular" maxlength="11" name="celular"
                                                value="<?php echo $rwUser['celular']; ?>" type="text"
                                                class="form-control" placeholder="85999999999 sem traço"
                                                aria-label="Telefone" aria-describedby="basic-addon1">
                                        </div>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-key"></i>
                                            </span>
                                            <input id="senhaatual" name="senhaatual" type="password"
                                                class="form-control" placeholder="Senha atual" aria-label="Senha Atual">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassword('senhaatual', 'eyeIcon1')">
                                                <i id="eyeIcon1" class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-key"></i>
                                            </span>
                                            <input id="senhanova" name="senhanova" type="password" class="form-control"
                                                placeholder="Senha nova" aria-label="Senha Nova">
                                            <button class="btn btn-outline-secondary" type="button"
                                                onclick="togglePassword('senhanova', 'eyeIcon2')">
                                                <i id="eyeIcon2" class="bi bi-eye"></i>
                                            </button>
                                        </div>

                                        <div class="input-group mb-3">
                                            <legend style="font-size: 14px;">Insira senha atual se for alterar o seu
                                                e-mail</legend>
                                            <span class="input-group-text" id="basic-addon1">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input id="emailnovo" readonly name="emailnovo" value="" type="email"
                                                class="form-control" placeholder="Emailnovo" aria-label="Emailnovo"
                                                aria-describedby="basic-addon1">
                                        </div>

                                        <script>
                                            function togglePassword(inputId, eyeIconId) {
                                                let input = document.getElementById(inputId);
                                                let icon = document.getElementById(eyeIconId);
                                                if (input.type === "password") {
                                                    input.type = "text";
                                                    icon.classList.remove("bi-eye");
                                                    icon.classList.add("bi-eye-slash", "text-warning");
                                                } else {
                                                    input.type = "password";
                                                    icon.classList.remove("bi-eye-slash", "text-warning");
                                                    icon.classList.add("bi-eye");
                                                }
                                            }
                                        </script>

                                        <div class="" style="position: relative;">
                                            <div class="input-group">
                                                <button id="btnupdate" type="button" name="updateperfil" value="upload"
                                                    class="btn btn-success">Atualizar <i
                                                        class="bi bi-save"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    </main>
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
    <script src="scripts/registraacessos.js?<?= time(); ?>"></script>
    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
    <script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
</body>

</html>