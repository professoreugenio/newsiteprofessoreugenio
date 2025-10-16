<?php
// ----------------- Alunos da turma -----------------
$queryAlunos = $con->prepare("
    SELECT c.codigocadastro, c.nomecurso, c.pastasc, c.imagem50
    FROM new_sistema_cadastro c
    INNER JOIN new_sistema_inscricao_PJA i 
        ON i.codigousuario = c.codigocadastro
    WHERE i.chaveturma = :chaveturma
    ORDER BY c.nomecurso ASC
");
$queryAlunos->bindParam(":chaveturma", $chaveturmaUser);
$queryAlunos->execute();
$alunos = $queryAlunos->fetchAll(PDO::FETCH_ASSOC);

// ----------------- Mapa: quem respondeu nesta aula -----------------
// Traz todos os alunos que possuem ao menos 1 resposta em qualquer questão da aula atual ($codigoaula)
$mapRespondidos = [];
$sqlResp = $con->prepare("
    SELECT r.idalunoqr
    FROM a_curso_questionario_resposta r
    INNER JOIN a_curso_questionario q
        ON q.codigoquestionario = r.idquestionarioqr
    WHERE q.idpublicacaocq = :aula
    GROUP BY r.idalunoqr
");
$sqlResp->execute([':aula' => (int)$codigoaula]);
while ($row = $sqlResp->fetch(PDO::FETCH_ASSOC)) {
    $mapRespondidos[(int)$row['idalunoqr']] = true;
}
?>

<style>
    /* Suaviza os não respondidos (sem borda, mais translúcidos, levemente dessaturados) */
    .aluno-nao-respondido img {
        opacity: .45;
        filter: grayscale(.2) saturate(.7);
        border: none !important;
        box-shadow: none !important;
    }

    .aluno-nao-respondido small {
        opacity: .6;
    }

    /* Mantém espaçamento/centralização já usados */
</style>

<div class="container mt-4">
    <h5 class="mb-3 text-white text-center">Atividades por Aluno</h5>

    <!-- Avatares centralizados -->
    <div class="d-flex justify-content-center gap-4 flex-wrap mb-4">
        <?php foreach ($alunos as $aluno):
            $idAluno = (int)$aluno['codigocadastro'];
            $temResposta = !empty($mapRespondidos[$idAluno]);

            // Foto
            if ($aluno['imagem50'] === 'usuario.jpg') {
                $foto = "/fotos/usuarios/usuario.png";
            } else {
                $foto = "/fotos/usuarios/{$aluno['pastasc']}/{$aluno['imagem50']}";
            }

            $primeiroNome = explode(" ", trim($aluno['nome']))[0];
            // Classes condicionais
            $wrapperClass = "btn p-0 text-center aluno-item";
            $imgClass = "rounded-circle";
            $imgExtra = "style=\"width:50px;height:50px;object-fit:cover;\"";
            if ($temResposta) {
                // respondeu -> mantém destaque, borda e sombra
                $imgClass .= " border border-light shadow-sm";
                $btnStateAttr = ""; // clicável normal
            } else {
                // não respondeu -> estilo translúcido (sem borda)
                $wrapperClass .= " aluno-nao-respondido";
                $btnStateAttr = ""; // ainda pode clicar para ver “pendente”
            }
        ?>
            <button type="button"
                class="<?= $wrapperClass ?>"
                data-idaluno="<?= $idAluno; ?>"
                data-nome="<?= htmlspecialchars($aluno['nome']); ?>"
                data-respondido="<?= $temResposta ? '1' : '0' ?>"
                title="<?= $temResposta ? 'Ver atividades respondidas' : 'Nenhuma resposta nesta aula' ?>">
                <div class="d-flex flex-column align-items-center" title="<?= $aluno['nome']; ?>">
                    <img src="<?= $foto; ?>"
                        alt="<?= htmlspecialchars($aluno['nome']); ?>"
                        class="<?= $imgClass ?>" <?= $imgExtra ?>>
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

            alvo.innerHTML = `
        <div class="py-4 text-center">
          <div class="spinner-border" role="status"></div>
          <div class="mt-2">Carregando atividades de ${nome.split(' ')[0]}...</div>
        </div>`;

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