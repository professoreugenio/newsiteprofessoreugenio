<?php
// Consulta: lista de alunos da turma
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

// Função para pegar último acesso e contagem de dias
function getUltimoAcesso($con, $idAluno, $idTurma)
{
    $q = $con->prepare("SELECT datara FROM a_site_registraacessos WHERE idusuariora = :idAluno AND idturmara = :idTurma ORDER BY datara DESC LIMIT 1");
    $q->bindParam(':idAluno', $idAluno);
    $q->bindParam(':idTurma', $idTurma);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['datara']) {
        $dataAcesso = new DateTime($row['datara']);
        $hoje = new DateTime();
        $dias = $dataAcesso->diff($hoje)->days;
        return [date('d/m/Y', strtotime($row['datara'])), $dias];
    }
    return [null, null];
}

// Função link WhatsApp
function linkWhats($cel, $msg)
{
    $numero = preg_replace('/\D/', '', $cel);
    if ($numero && substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
    return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : false;
}
?>
<h5 class="mb-4">
    <i class="bi bi-people-fill me-2 text-primary"></i>
    <?= $stmt->rowCount() ?> Aluno(s) para <span class="fw-bold"><?= htmlspecialchars($Nometurma) ?></span>
    <?php if ($codadm == 1): ?>
        <?= $idCurso ?>
        <?= $idTurma ?>
        <?= $ChaveTurma ?>
    <?php endif; ?>
</h5>
<ul class="list-group">

    <?php

    function fotoAlunoUrl($pasta, $imagem)
    {
        $urlFoto = "https://professoreugenio.com/fotos/usuarios/{$pasta}/{$imagem}";
        if (!$imagem) return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
        // Verifica se o arquivo realmente existe no servidor remoto (HTTP 200)
        $headers = @get_headers($urlFoto);
        if ($headers && strpos($headers[0], '200') !== false) {
            return $urlFoto;
        } else {
            return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
        }
    }

    ?>
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <?php
        $idAluno = $row['codigousuario'];
        $nomeArr = explode(' ', trim($row['nome']));
        $nomeExib = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
        $foto = fotoAlunoUrl($row['pastasc'], $row['imagem200']);


        list($ultimoAcesso, $diasAcesso) = getUltimoAcesso($con, $idAluno, $idTurma);
        $emailBloq = intval($row['emailbloqueio']);
        $temWhats = !empty($row['celular']);
        $email = htmlspecialchars($row['email']);
        // Links dropdown
        $msgSaudacao = "*Olá {$nomeExib}*, aqui é o professor Eugênio! Tudo bem?";
        $msgSenha = "*Olá {$nomeExib}*, segue sua senha de acesso: [SENHA_AQUI]";
        $msgRedes = "*Olá {$nomeExib}*, me siga nas redes sociais! Instagram: @professoreugenio";
        $emailPromo = "https://mail.google.com/mail/?view=cm&fs=1&to={$email}&su=Promoção de cursos&body=Olá%20{$nomeExib},%20aproveite%20nossa%20promoção%20de%20cursos!";
        $emailMotiv = "https://mail.google.com/mail/?view=cm&fs=1&to={$email}&su=Motivação%20para%20seus%20estudos&body=Olá%20{$nomeExib},%20continue%20se%20dedicando,%20você%20vai%20alcançar%20seus%20objetivos!";
        ?>
        <li class="list-group-item py-3 px-2 mb-2 rounded shadow-sm">
            <div class="row align-items-center gx-2">
                <!-- Esquerda: foto + dados -->
                <div class="col-md-9 d-flex align-items-center">
                    <img src="<?= $foto ?>" class="rounded-circle me-3" style="width:56px; height:56px; object-fit:cover; border:2px solid #eee;">
                    <div>
                        <div>
                            <a href="historico_aluno.php?id=<?= urlencode($idAluno) ?>" class="fw-bold fs-6 text-decoration-none" target="_blank">
                                <?= $nomeExib ?>
                            </a>
                            <span class="badge bg-light text-dark ms-2">
                                <i class="bi bi-clock"></i>
                                <?= $ultimoAcesso ? "$ultimoAcesso" : 'Sem acesso' ?>
                                <?php if ($diasAcesso !== null): ?>
                                    <span class="small text-muted">(há <?= $diasAcesso ?> dia<?= $diasAcesso != 1 ? 's' : '' ?>)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="small mt-1">
                            <span>
                                <i class="bi bi-envelope<?= $emailBloq ? '-slash text-danger' : '-at text-success' ?>" title="<?= $emailBloq ? 'E-mail bloqueado' : 'E-mail liberado' ?>"></i>
                                <?= $email ?>
                            </span>
                            <span class="ms-3">
                                <?php if ($temWhats): ?>
                                    <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                <?php else: ?>
                                    <i class="bi bi-whatsapp text-secondary"></i> Sem WhatsApp
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <!-- Direita: Dropdown -->
                <div class="col-md-3 text-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuBtn<?= $idAluno ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-send"></i> Enviar Mensagem
                        </button>
                        <?php $plural = ($diasAcesso != 1) ? 's' : '';
                        ?>
                        <?php

                        // Mensagem WhatsApp motivacional sobre o último acesso

                        if ($ultimoAcesso) {
                            $msgAcesso = "{$saudacao} *{$nomeExib}*,
do curso de *{$Nometurma}*
Aqui é o professor Eugênio! Tudo bem?
Notei que seu último acesso à nossa plataforma foi há {$diasAcesso} dia{$plural}. Espero que esteja tudo bem com você!
Quero te lembrar da importância de continuar os estudos e dizer que estou sempre à disposição para tirar qualquer dúvida. O seu aprendizado é muito importante para mim!
Se precisar de qualquer ajuda, é só me chamar!

*Acesse sua área do aluno pelo link abaixo:*
https://professoreugenio.com/login_aluno.php?ts={$ts}
Um grande abraço,
_Prof. Eugênio_
";
                        } else {
                            $msgAcesso = "{$saudacao} *{$nomeExib}*, aqui é o professor Eugênio!\n
                            do curso de *{$Nometurma}*\n
Percebi que ainda não registros de seu acesso às aulas recentemente.
Quero te incentivar a dar o primeiro passo e começar seus estudos na plataforma!
Lembre-se: estou sempre disponível para tirar suas dúvidas e apoiar no que precisar.
Você não está sozinho(a) nessa jornada — conte comigo!

*Acesse sua área do aluno pelo link abaixo:*
https://professoreugenio.com/login_aluno.php?ts=1752622481\n

Um grande abraço,
_Prof. Eugênio_
";
                        }
                        $linkAcessoWhats = $temWhats ? linkWhats($row['celular'], $msgAcesso) : '#';


                        ?>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuBtn<?= $idAluno ?>">
                            <?php if ($temWhats): ?>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="<?= linkWhats($row['celular'], $msgSaudacao) ?>">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp Saudação
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="<?= linkWhats($row['celular'], $msgSenha) ?>">
                                        <i class="bi bi-key"></i> WhatsApp Recuperar Senha
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="<?= linkWhats($row['celular'], $msgRedes) ?>">
                                        <i class="bi bi-instagram"></i> WhatsApp Siga nas Redes
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="<?= $linkAcessoWhats ?>">
                                        <i class="bi bi-clock-history text-warning"></i> WhatsApp Último Acesso/Motivação
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" target="_blank" href="<?= $emailPromo ?>">
                                    <i class="bi bi-envelope-paper"></i> E-mail Promoção de Cursos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" target="_blank" href="<?= $emailMotiv ?>">
                                    <i class="bi bi-emoji-smile"></i> E-mail Motivacional
                                </a>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </li>
    <?php endwhile; ?>
</ul>

<!-- Tooltips Bootstrap -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>