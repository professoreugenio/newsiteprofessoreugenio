<?php
// Consulta dos alunos
$stmt = config::connect()->prepare("
    SELECT codigoinscricao, codigousuario, chaveturma, codigocadastro, nome, liberado_sc, data_ins, email
    FROM new_sistema_inscricao_PJA, new_sistema_cadastro
    WHERE chaveturma = :chaveturma
    AND codigocadastro = codigousuario
    ORDER BY nome
");
$stmt->bindParam(":chaveturma", $ChaveTurma);
$stmt->execute();

// Monta lista de e-mails para envio em massa
$emailsBCC = [];
// Precisamos um array dos dados para a listagem e para o mailto
$listaAlunos = [];
$stmt->execute(); // Garante fetch do início
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($row['email'])) $emailsBCC[] = trim($row['email']);
    $listaAlunos[] = $row;
}
$emailsBCC = implode(',', $emailsBCC);

// Mensagens dos botões
$assuntoAula = "🎬 Assista a Esta Aula Especial!";
$corpoAula = "Curso de ". $Nomecurso."\n\nOlá tudo bem vcomvocê?\n\nVocê já conferiu a nova aula disponível na plataforma?
Aqui vai esta dica de aula:\n\n ******* \n\n Aproveite para aprofundar seus conhecimentos e tirar todas as dúvidas.\n\nAcesse sua área de aluno e assista agora mesmo!\n\nBons estudos!\nProf. Eugênio";

$assuntoPromo = "🚀 Promoção Exclusiva dos Cursos Online!";
$corpoPromo = "Olá!\n\nAproveite a promoção especial dos nossos cursos online e garanta sua matrícula com desconto!\n\nNão perca essa oportunidade de continuar aprendendo.\n\nQualquer dúvida, estou à disposição!\nAbraços,\nProf. Eugênio";

$assuntoLembrete = "⏰ Lembrete: Atividades Pendentes";
$corpoLembrete = "Olá!\n\nEste é um lembrete para que você acesse a plataforma e confira suas atividades pendentes.\n\nMantenha seus estudos em dia e conte comigo para qualquer ajuda.\n\nAbraços,\nProf. Eugênio";

function gmailBCC($emailsBCC, $assunto, $corpo)
{
    return 'https://mail.google.com/mail/?view=cm&fs=1'
        . '&bcc=' . rawurlencode($emailsBCC)
        . '&su=' . rawurlencode($assunto)
        . '&body=' . rawurlencode($corpo);
}

?>

<!-- Botões de envio em massa -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?= gmailBCC($emailsBCC, $assuntoAula, $corpoAula) ?>"
        target="_blank"
        class="btn btn-sm btn-primary shadow-sm d-flex align-items-center">
        <i class="bi bi-play-circle-fill me-2 fs-4"></i>
        Assista esta Aula
    </a>
    <a href="<?= gmailBCC($emailsBCC, $assuntoPromo, $corpoPromo) ?>"
        target="_blank"
        class="btn btn-sm btn-warning shadow-sm d-flex align-items-center">
        <i class="bi bi-stars me-2 fs-4"></i>
        Promoção de Cursos
    </a>
    <a href="<?= gmailBCC($emailsBCC, $assuntoLembrete, $corpoLembrete) ?>"
        target="_blank"
        class="btn btn-sm btn-success shadow-sm d-flex align-items-center">
        <i class="bi bi-bell-fill me-2 fs-4"></i>
        Lembrete de Atividades
    </a>
</div>


<?php if (count($listaAlunos) > 0): ?>
    <h5 class="mb-4">
        <i class="bi bi-people-fill me-2 text-primary"></i>
        <?= count($listaAlunos) ?> Aluno(s) para <span class="fw-bold"><?= htmlspecialchars($Nometurma) ?></span>
    </h5>
    <ul class="list-group">
        <?php foreach ($listaAlunos as $row): ?>
            <?php
            $id = $row['codigoinscricao'];
            $idUser = $row['codigocadastro'];
            $nm = $row['nome'];
            $email = $row['email'];
            $status = $row['liberado_sc'];
            $DtInscricao = $row['data_ins'];

            // Tempo de inscrição
            $dataInscricao = new DateTime($DtInscricao);
            $hoje = new DateTime();
            $diff = $hoje->diff($dataInscricao);
            if ($diff->y >= 1) {
                $tempoTexto = $diff->y . ' ano' . ($diff->y > 1 ? 's' : '');
                $badgeClasse = 'bg-purple text-light';
            } elseif ($diff->m >= 1) {
                $tempoTexto = $diff->m . ' mês' . ($diff->m > 1 ? 'es' : '');
                $badgeClasse = 'bg-primary';
            } elseif ($diff->d >= 7) {
                $semanas = floor($diff->d / 7);
                $tempoTexto = $semanas . ' semana' . ($semanas > 1 ? 's' : '');
                $badgeClasse = 'bg-warning text-dark';
            } elseif ($diff->d >= 1) {
                $tempoTexto = $diff->d . ' dia' . ($diff->d > 1 ? 's' : '');
                $badgeClasse = 'bg-success';
            } else {
                $tempoTexto = 'Hoje';
                $badgeClasse = 'bg-success';
            }

            // Último acesso
            $queryUltimoAcesso = $con->prepare("SELECT * FROM a_aluno_andamento_aula WHERE idalunoaa = :idusuario AND idturmaaa = :idturma ORDER BY dataaa DESC LIMIT 1 ");
            $queryUltimoAcesso->bindParam(":idusuario", $idUser);
            $queryUltimoAcesso->bindParam(":idturma", $idTurma);
            $queryUltimoAcesso->execute();
            $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
            $ultimadata = isset($rwUltAcesso['dataaa']) ? databr($rwUltAcesso['dataaa']) : 'Sem registro';
            $ultihorai = isset($rwUltAcesso['horaaa']) ? horabr($rwUltAcesso['horaaa']) : '';

            // Mensagens individuais (exemplo)
            $videoUrl = "https://youtube.com";
            $msg1 = "{$saudacao} {$nm}, aqui é o professor Eugênio.\nDo curso de {$Nomecurso}\n Tudo bem?\n\nQuero lembrar que suas aulas online já estão disponíveis! Acesse a plataforma e continue seu aprendizado.\n\nSe precisar de ajuda, conte comigo.\n\nAbraços!\nProfessor Eugênio";
            $msg2 = "{$saudacao} {$nm}, aqui é o professor Eugênio.\nDo curso de {$Nomecurso}\n\n\nQuero compartilhar com você um vídeo especial:\n{$videoUrl}\n\nAssista e depois me conte o que achou!\n\nAbraços!\nProfessor Eugênio";
            $msg3 = "{$saudacao} {$nm}, aqui é o professor Eugênio. Tudo bem?\n\nEstou passando para avisar que estamos com promoções especiais em nossos cursos!\n\nNão perca a oportunidade de aprender mais pagando menos.\n\nSe tiver interesse, entre em contato.\n\nAbraços!\nProfessor Eugênio";
            $msg4 = "{$saudacao} {$nm}, aqui é o professor Eugênio.\nDo curso de {$Nomecurso}\n\n\nEstou passando para avisar que temos novos cursos disponíveis na plataforma!\n\nAproveite para se inscrever e ampliar seus conhecimentos.\n\nQualquer dúvida, estou à disposição.\n\nAbraços!\nProfessor Eugênio";
            ?>
            <li class="list-group-item py-3 px-4 shadow-sm rounded-3 mb-3" style="border:1px solid #e5e7eb;">
                <div class="row align-items-center gx-2">
                    <!-- Ícone e Nome -->
                    <div class="col-md-3 d-flex align-items-center">
                        <span class="me-3">
                            <i class="bi bi-mortarboard fs-4 text-primary"></i>
                        </span>
                        <div>
                            <span data-bs-toggle="tooltip" title="<?= htmlspecialchars($nm) ?>" class="fw-bold fs-6">
                                <?= htmlspecialchars(explode(' ', $nm)[0]) ?>
                            </span>
                            <div class="small text-muted">
                                <i class="bi bi-envelope"></i>
                                <?= htmlspecialchars($email) ?>
                            </div>
                        </div>
                    </div>
                    <!-- Inscrição/Tempo -->
                    <div class="col-md-2 d-none d-md-block">
                        <span class="badge <?= $badgeClasse ?>"><?= $tempoTexto ?></span>
                        <div class="small text-muted"><?= date('d/m/Y', strtotime($DtInscricao)) ?></div>
                    </div>
                    <!-- Último acesso -->
                    <div class="col-md-2 d-none d-md-block">
                        <span class="small">Último acesso:</span>
                        <div class="fw-normal"><?= $ultimadata ?> <?= $ultihorai ?></div>
                    </div>
                    <!-- Status -->
                    <div class="col-md-1 d-none d-md-block text-center">
                        <span class="badge <?= $status == 1 ? 'bg-success' : 'bg-danger' ?>">
                            <?= $status == 1 ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </div>
                    <!-- Ações rápidas -->
                    <div class="col-md-4 col-12 mt-2 mt-md-0 text-end">
                        <div class="btn-group" role="group">
                            <a target="_blank"
                                href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($email); ?>&su=Aulas disponíveis&body=<?= urlencode($msg1); ?>"
                                class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Enviar: Aulas Ok">
                                <i class="bi bi-send"></i>
                            </a>
                            <a target="_blank"
                                href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($email); ?>&su=Assista a esta aula&body=<?= urlencode($msg2); ?>"
                                class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Enviar: Vídeo">
                                <i class="bi bi-play-circle"></i>
                            </a>
                            <a target="_blank"
                                href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($email); ?>&su=Promoções&body=<?= urlencode($msg3); ?>"
                                class="btn btn-outline-success btn-sm" data-bs-toggle="tooltip" title="Enviar: Promoção">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                            <a target="_blank"
                                href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($email); ?>&su=Abertas Inscrições curso&body=<?= urlencode($msg4); ?>"
                                class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Enviar: Novos Cursos">
                                <i class="bi bi-bookmark-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="alert alert-warning mt-4">Nenhum aluno cadastrado para <b><?= htmlspecialchars($Nometurma) ?></b>.</div>
<?php endif; ?>

<!-- Tooltips Bootstrap (se não já estiver incluído) -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>