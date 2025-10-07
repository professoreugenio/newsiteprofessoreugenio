<?php
// Consulta alunos da turma
$queryAlunos = $con->prepare("
    SELECT c.codigocadastro, c.nome, c.pastasc, c.imagem50
    FROM new_sistema_cadastro c
    INNER JOIN new_sistema_inscricao_PJA i 
        ON i.codigousuario = c.codigocadastro
    WHERE i.chaveturma = :chaveturma
    ORDER BY c.nome ASC
");
$queryAlunos->bindParam(":chaveturma", $chaveturmaUser);
$queryAlunos->execute();
$alunos = $queryAlunos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h5 class="mb-3 text-white text-center">Atividades por Aluno</h5>

    <!-- Avatares centralizados -->
    <div class="d-flex justify-content-center gap-4 flex-wrap mb-4">
        <?php foreach ($alunos as $aluno):
            
                if($aluno['imagem50']=='usuario.jpg') :
                $foto = "/fotos/usuarios/usuario.png";
                else:
                $foto = "/fotos/usuarios/{$aluno['pastasc']}/{$aluno['imagem50']}";
                endif;
            $primeiroNome = explode(" ", trim($aluno['nome']))[0];
        ?>
            <button type="button"
                class="btn p-0 text-center aluno-item"
                data-idaluno="<?= (int)$aluno['codigocadastro']; ?>"
                data-nome="<?= htmlspecialchars($aluno['nome']); ?>"
                title="Ver atividades de <?= htmlspecialchars($aluno['nome']); ?>">
                <div class="d-flex flex-column align-items-center">
                    <img src="<?= $foto; ?>"
                        alt="<?= htmlspecialchars($aluno['nome']); ?>"
                        class="rounded-circle border border-light shadow-sm"
                        style="width:50px;height:50px;object-fit:cover;">
                    <small class="text-white mt-1"><?= htmlspecialchars($primeiroNome); ?></small>
                </div>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- AQUI a lista será carregada -->
    <div id="listaatividades" class="bg-dark text-white rounded-4 p-3 border border-secondary">
        <div class="text-center opacity-75">Selecione um aluno acima para ver as atividades do módulo.</div>
    </div>
</div>

<script>
    document.querySelectorAll('.aluno-item').forEach(btn => {
        btn.addEventListener('click', () => {
            const idAluno = btn.getAttribute('data-idaluno');
            const nome = btn.getAttribute('data-nome') || 'Aluno';
            const alvo = document.getElementById('listaatividades');

            // Loading no container
            alvo.innerHTML = `
      <div class="py-4 text-center">
        <div class="spinner-border" role="status"></div>
        <div class="mt-2">Carregando atividades de ${nome.split(' ')[0]}...</div>
      </div>`;

            // Chama o endpoint passando aluno e módulo atual
            fetch('config_Atividade1.0/ajax_curso_modulo_ViewAtividadesRespondidas4.0.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        idaluno: idAluno,
                        idaula: '<?= (int)$codigoaula; ?>'
                    })
                })
                .then(r => r.text())
                .then(html => alvo.innerHTML = html)
                .catch(() => {
                    alvo.innerHTML = `<div class="alert alert-danger m-0">Erro ao carregar as atividades.</div>`;
                });
        });
    });
</script>