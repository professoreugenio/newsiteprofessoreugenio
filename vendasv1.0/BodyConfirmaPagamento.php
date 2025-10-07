<?php
// Verificação básica
if (!isset($_SESSION['idUsuario']) || !isset($_SESSION['emailUsuario'])) {
    header("Location: pagina_vendas.php");
    exit;
}

?>

<?php
$nome = $_SESSION['nomeUsuario'] ?? 'Aluno';
$email = $_SESSION['emailUsuario'] ?? '';
$idUsuario = $_SESSION['idUsuario'] ?? '';
$plano = $_SESSION['plano'];
$valorFinal = $_SESSION['valorFinal'];
?>

<div class="text-center mb-5">
    <h4 class="text-success">🎉 Parabéns, <?= htmlspecialchars($nome) ?>!</h4>
    <p class="lead">Sua inscrição foi confirmada com sucesso para o
    <h5 class="text-success"><?= $plano ?></h5>

</div>


<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow p-4 bg-secondary bg-opacity-10">
            <h5 class="mb-3">🔐 Crie sua senha de acesso</h5>
            <form id="formSenha" action="vendasv1.0/ajax_criarSenha.php" method="post">
                <input type="hidden" name="idUsuario" value="<?= htmlspecialchars($idUsuario) ?>">

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="senha" name="senha" required>
                </div>

                <div class="mb-3">
                    <label for="confirmar" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" id="confirmar" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Salvar Senha e Acessar Curso</button>
            </form>

            <div id="msgRetorno" class="mt-3 text-center"></div>
        </div>
    </div>
</div>

<script>
    document.getElementById('formSenha').addEventListener('submit', function(e) {
        e.preventDefault();

        const senha = document.getElementById('senha').value;
        const confirmar = document.getElementById('confirmar').value;
        const idUsuario = document.querySelector('[name="idUsuario"]').value;

        if (senha !== confirmar) {
            document.getElementById('msgRetorno').innerHTML = '<div class="text-danger">As senhas não coincidem.</div>';
            return;
        }

        fetch('vendasv1.0/ajax_criarSenha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idUsuario=${idUsuario}&senha=${encodeURIComponent(senha)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById('msgRetorno').innerHTML = '<div class="text-success">Senha criada com sucesso! Redirecionando...</div>';
                    setTimeout(() => {
                        window.location.href = 'login_aluno.php';
                    }, 2000);
                } else {
                    document.getElementById('msgRetorno').innerHTML = '<div class="text-danger">' + data.mensagem + '</div>';
                }
            });
    });
</script>