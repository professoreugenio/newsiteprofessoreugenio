<?php
// ...seu código já postado...
$linkGrupoWhats = $linkWhatsapp ?? '';

// Consulta (deixe o fetchAll para emails, e depois execute novamente pro resto do código do form!)
$stmt = config::connect()->prepare("
    SELECT 
        i.codigoinscricao, i.codigousuario, i.chaveturma, 
        c.nome, c.email, c.pastasc, c.imagem200, c.emailbloqueio, c.celular
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    WHERE i.chaveturma = :chaveturma
    ORDER BY c.nome
");
$stmt->bindParam(':chaveturma', $ChaveTurma);
$stmt->execute();

// Lista de e-mails da turma (sem duplicar/sem vazio)
$emailsBCC = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rowEmail) {
    if (!empty($rowEmail['email'])) {
        $emailsBCC[] = trim($rowEmail['email']);
    }
}
$stmt->execute(); // Reset para usar o $stmt em outros loops abaixo, se precisar

function mailtoBCC($bccArray, $assunto, $corpo)
{
    $bcc = implode(',', $bccArray);
    return 'https://mail.google.com/mail/?view=cm&fs=1&bcc=' . rawurlencode($bcc) .
        '&su=' . rawurlencode($assunto) .
        '&body=' . rawurlencode($corpo);
}
?>

<!-- Botões para WhatsApp e E-mail -->
<div class="mb-4 d-flex gap-3">
    <!-- Botão Grupo WhatsApp -->
    <!-- Botão WhatsApp Grupo -->
    <button type="button"
        class="btn btn-success me-2"
        id="btnWhatsGrupo"
        data-msg-selector="#mensagem"
        data-link="<?= $linkGrupoWhats ?>">
        <i class="bi bi-whatsapp me-2"></i>WhatsApp
    </button>

    <script>
        document.getElementById('btnWhatsGrupo').addEventListener('click', function() {


            let msg = $('#mensagem').summernote('code');
            // Copia apenas o texto (sem HTML)
            let temp = document.createElement('textarea');
            temp.value = msg.replace(/<[^>]+>/g, '').trim();
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            mostrarToast('Mensagem copiada! Agora cole no grupo 😉');
            // Abre o grupo WhatsApp
            window.open(document.getElementById('btnWhatsGrupo').dataset.link, '_blank');
        });
    </script>
    <script>
        function mostrarToast(msg) {
            let toast = document.createElement('div');
            toast.className = "toast align-items-center text-bg-success border-0 position-fixed top-0 start-50 translate-middle-x mt-4 z-3";
            toast.style.zIndex = "1080";
            toast.style.minWidth = "240px";
            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
            document.body.appendChild(toast);
            var toastObj = new bootstrap.Toast(toast, {
                delay: 2500
            });
            toastObj.show();
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }
    </script>


    <!-- Botão E-mail Todos -->
    <button type="button" class="btn btn-primary d-flex align-items-center" id="btnEnviarTodosEmails">
        <i class="bi bi-envelope me-2"></i> Enviar E-mail para Todos
    </button>
</div>

<form id="formMensagem" method="post" action="">
    <!-- ...restante do seu form... -->
</form>

<script>
    // Ao clicar, monta e abre Gmail com BCC, título e corpo do formulário
    $('#btnEnviarTodosEmails').on('click', function() {
        let assunto = $('#titulo').val().trim();
        // Se usar summernote:
        let corpo = $('#mensagem').summernote ? $('#mensagem').summernote('code') : $('#mensagem').val();
        // Remove tags HTML para não bugar no Gmail:
        let corpoTxt = corpo.replace(/<br\s*\/?>/gi, '\n').replace(/<\/?[^>]+(>|$)/g, "");
        if (!assunto || !corpoTxt) {
            alert('Preencha o título e a mensagem antes de enviar o e-mail.');
            return;
        }
        let bcc = <?= json_encode($emailsBCC) ?>;
        let url = "<?= mailtoBCC($emailsBCC, 'TITULO', 'TEXTO') ?>";
        // Substituir o TÍTULO e TEXTO pelo que está no form, JS:
        url = url.replace(
            encodeURIComponent('TITULO'),
            encodeURIComponent(assunto)
        ).replace(
            encodeURIComponent('TEXTO'),
            encodeURIComponent(corpoTxt)
        );
        window.open(url, '_blank');
    });
</script>


<div class="container py-4">
    <h4 class="mb-4"><i class="bi bi-chat-text me-2"></i>*Enviar mensagem para os alunos da turma <span class="text-primary"><?= htmlspecialchars($Nometurma) ?></span></h4>


    <?php require 'cursosv1.0/formMsg.php' ?>
    <?php require 'cursosv1.0/formMsgJS.php'; ?>

</div>