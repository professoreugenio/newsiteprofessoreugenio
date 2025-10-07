<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<!-- https://professoreugenio.com/curso/actionCurso.php?mdl=bUFIazN0SnI1TkFKVzRPSlBlZjRRUT09&pub=5207 -->

<head>
    <?php require 'config_default1.0/query_dados.php' ?>
    <?php require 'config_default1.0/query_afiliado.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>
    <?php if ($idCurso == '143'):
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas/">');
        exit();
    endif;  ?>
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
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">

    <link rel="stylesheet" href="config_default1.0/Css_dropdownsModulos.css">
    <link rel="stylesheet" href="config_default1.0/CSS_linkAdmin.css?time=<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_cardsCurso.css?time=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="v2.0/CSS_sidebarLateral.css?<?= time(); ?>"> -->
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">

    <link rel="stylesheet" href="config_default1.0/CSS_default.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_modalCaderno.css?<?php echo time(); ?>">

    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="afiliadosv1.0/CSS_afiliados.css?<?php echo time(); ?>">


</head>

<body>
    <?php include 'v2.0/nav.php'; ?>


    <section id="Corpo" class="py-4">
        <div class="container">
            <!-- NAV SUPERIOR: home | extrato | histórico | perfil -->
            <?php require 'afiliadosv1.0/require_Menunav.php' ?>
            <?php require 'afiliadosv1.0/body_afiliadoHistorico.php'
            ?>
        </div>
    </section>


    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php if ($idUser != '1') : ?>

        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>

    <?php require 'v2.0/footer.php'; ?>
    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>
    <script src="config_turmas1.0/JS_accessturma.js?<?= time() ?>"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js?<?= time() ?>"></script>

    <!-- Bootstrap/AOS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 700,
            once: true
        });
    </script>

    <script>
        (function() {
            // Se não tem dados, abre modal automaticamente
            const temDados = <?= $temDadosAfiliado ? 'true' : 'false' ?>;
            if (!temDados) {
                const m = new bootstrap.Modal(document.getElementById('modalDadosAfiliado'));
                m.show();
            }

            // Salvar dados do afiliado (AJAX)
            const btn = document.getElementById('btnSalvarAff');
            const form = document.getElementById('formDadosAfiliado');
            const msg = document.getElementById('affMsg');

            btn?.addEventListener('click', async () => {
                msg.textContent = 'Salvando...';
                const fd = new FormData(form);
                try {
                    const r = await fetch('afiliadosv1.0/ajax_salvarDadosAfiliado.php', {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const js = await r.json();
                    if (js?.ok) {
                        msg.innerHTML = '<span class="text-success"><i class="bi bi-check2-circle me-1"></i>Dados salvos com sucesso.</span>';
                        setTimeout(() => location.reload(), 800);
                    } else {
                        msg.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>' + (js?.msg || 'Falha ao salvar') + '</span>';
                    }
                } catch (e) {
                    msg.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Erro na requisição.</span>';
                }
            });

            // (Opcional) preencher a label da chave se você já tiver a lógica da chave do afiliado
            // document.getElementById('affKeyLabel').textContent = '<?= isset($rwChave['chave']) ? $rwChave['chave'] : '—' ?>';
        })();
    </script>

    <script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>

</body>

</html>