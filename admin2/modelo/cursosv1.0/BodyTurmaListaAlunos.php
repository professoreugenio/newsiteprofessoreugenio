<?php
// Turmas comerciais para o select (comercial_stc = '1')
$stmtTurmasComerciais = config::connect()->query("
    SELECT nometurma, chave 
    FROM new_sistema_cursos_turmas 
    WHERE comercial_stc = '1'
    ORDER BY nometurma
");
$turmasComerciais = $stmtTurmasComerciais->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex flex-wrap align-items-end gap-2 mb-3">
    <div>
        <label for="turmaComercial" class="form-label mb-1 fw-semibold">
            Inscrever na turma comercial:
        </label>

        <select id="turmaComercial" class="form-select" style="min-width:280px;">
            <option value="">Selecione a turma…</option>
            <?php foreach ($turmasComerciais as $t): ?>
                <option value="<?= htmlspecialchars($t['chave']) ?>">
                    <?= htmlspecialchars($t['nometurma']) ?> (<?= htmlspecialchars($t['chave']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-check ms-1">
        <input class="form-check-input" type="checkbox" id="checkAllAlunos">
        <label class="form-check-label" for="checkAllAlunos">Selecionar todos</label>
    </div>

    <button type="button" id="btnInscreverSelecionados" class="btn btn-success">
        <i class="bi bi-person-plus-fill me-1"></i> Inscrever
    </button>

    <!-- Botão FINALIZAR TURMA (adicione perto dos demais botões do topo) -->

    <?php if ($andamento == '1'): ?>
        <button type="button" id="btnFinalizarTurma" class="btn btn-secondary btn-sm ms-2"
            data-acao="reativar">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reativar turma
        </button>
    <?php else: ?>
        <button type="button" id="btnFinalizarTurma" class="btn btn-danger btn-sm ms-2"
            data-acao="finalizar">
            <i class="bi bi-flag-fill me-1"></i> Finalizar turma
        </button>
    <?php endif; ?>

    <span id="respFinalizarTurma" class="ms-2 small"></span>

    <span id="respFinalizarTurma" class="ms-2 small"></span>

    <div id="respInscricao" class="ms-2 small"></div>
</div>

<?php
define('CHAVE_MASTER_CLASS', '202504211745282608');
define('CHAVE_POWER_BI',   '202504171744941946');
?>
<?php
// Link do grupo do WhatsApp (ajuste conforme o seu grupo)
$linkGrupoWhats = $linkWhatsapp;

// CONSULTA: lista de alunos da turma
// ADIÇÃO: trouxemos c.datanascimento_sc e calculamos idade no SQL (se a data for válida).
$stmt = config::connect()->prepare("
    SELECT 
        i.codigoinscricao, i.dataprazosi, i.data_ins, i.codigousuario, i.chaveturma, 
        c.codigocadastro, c.possuipc, c.nome, c.email, c.pastasc, c.imagem200, 
        c.emailbloqueio, c.celular, c.senha,
        c.datanascimento_sc,
        CASE 
            WHEN c.datanascimento_sc IS NULL OR c.datanascimento_sc = '' THEN NULL
            WHEN STR_TO_DATE(c.datanascimento_sc, '%Y-%m-%d') IS NULL THEN NULL
            ELSE TIMESTAMPDIFF(YEAR, STR_TO_DATE(c.datanascimento_sc, '%Y-%m-%d'), CURDATE())
        END AS idade,
        /* Flags de inscrição especiais */
        EXISTS(
            SELECT 1 FROM new_sistema_inscricao_PJA i2 
            WHERE i2.codigousuario = i.codigousuario 
              AND i2.chaveturma = '202504211745282608'
        ) AS inscrito_masterclass,
        EXISTS(
            SELECT 1 FROM new_sistema_inscricao_PJA i3 
            WHERE i3.codigousuario = i.codigousuario 
              AND i3.chaveturma = '202504171744941946'
        ) AS inscrito_powerbi
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    WHERE i.chaveturma = :chaveturma
    ORDER BY c.nome
");
$stmt->bindParam(':chaveturma', $ChaveTurma);
$stmt->execute();

// Monta lista de e-mails para envio em massa (BCC)
$emailsBCC = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rowEmail) {
    if (!empty($rowEmail['email'])) {
        $emailsBCC[] = trim($rowEmail['email']);
    }
}
// Reset o ponteiro para o fetch do loop abaixo
$stmt->execute();

function mailtoBCC($bccArray, $assunto, $corpo)
{
    $bcc = implode(',', $bccArray);
    return 'https://mail.google.com/mail/?view=cm&fs=1&bcc=' . rawurlencode($bcc) .
        '&su=' . rawurlencode($assunto) .
        '&body=' . rawurlencode($corpo);
}
?>
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

// (Opcional) Fallback em PHP caso idade venha NULL e exista uma data válida
function calcIdade(?string $dataIso): ?int
{
    if (!$dataIso) return null;
    try {
        $n = new DateTime($dataIso);
        $h = new DateTime('today');
        $idade = $n->diff($h)->y;
        return ($idade >= 0 && $idade <= 120) ? $idade : null;
    } catch (Throwable $e) {
        return null;
    }
}
?>
<?php
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
function linkWhats($cel, $msg)
{
    $numero = preg_replace('/\D/', '', $cel);
    if ($numero && substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
    return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : false;
}
?>
<!-- DROPDOWN NO TOPO: ENVIAR PARA GRUPO WHATSAPP -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <div class="dropdown d-inline-block me-2">
            <a href="cursos_TurmasAlunosMensagens.php?id=<?= $_GET['id'] ?>&tm=<?= $_GET['tm'] ?>">Mensagens</a>
            <button class="btn btn-success dropdown-toggle" type="button" id="dropdownGrupoWpp" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownGrupoWpp">
                <li>
                    <a class="dropdown-item" href="<?= $linkGrupoWhats ?>" target="_blank"
                        onclick="navigator.clipboard.writeText('Pessoal, acessem a nova aula disponível na plataforma! Aproveitem para tirar dúvidas no grupo. Abraços, Prof. Eugênio.')">
                        <i class="bi bi-play-circle"></i> Assistam a esta aula
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $linkGrupoWhats ?>" target="_blank"
                        onclick="navigator.clipboard.writeText('Galera, manter o ritmo de estudo é fundamental para o sucesso! Persistam nos estudos e contem comigo para ajudar. Grande abraço!')">
                        <i class="bi bi-emoji-smile"></i> Motivacional ao estudo
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $linkGrupoWhats ?>" target="_blank"
                        onclick="navigator.clipboard.writeText('Novas promoções de cursos disponíveis! Confira na área do aluno ou fale comigo. Vagas limitadas!')">
                        <i class="bi bi-stars"></i> Promoções de Cursos
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $linkGrupoWhats ?>" target="_blank"
                        onclick="navigator.clipboard.writeText('Aproveite nossas ofertas especiais de produtos para complementar seus estudos! Consulte detalhes no grupo.')">
                        <i class="bi bi-bag-check"></i> Promoções de Produtos
                    </a>
                </li>
            </ul>
        </div>
        <!-- Dropdown E-MAIL EM MASSA (GMAIL) -->
        <div class="dropdown d-inline-block">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownGmail" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-envelope-at me-1"></i> E-mail
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownGmail">
                <li>
                    <a class="dropdown-item" target="_blank" href="<?= mailtoBCC($emailsBCC, 'Siga o Professor Eugênio nas Redes Sociais', 'Olá! Siga @professoreugenio no Instagram e fique por dentro de novidades, dicas e oportunidades! Abraços, Prof. Eugênio.') ?>">
                        <i class="bi bi-instagram"></i> Me siga nas redes sociais
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank" href="<?= mailtoBCC($emailsBCC, 'Assista a Esta Aula!', 'Olá! Uma nova aula está disponível na sua plataforma. Acesse e assista agora mesmo. Qualquer dúvida, mande mensagem! Abraços, Prof. Eugênio.') ?>">
                        <i class="bi bi-play-circle"></i> Assistam a esta aula
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank" href="<?= mailtoBCC($emailsBCC, 'Motivação para seus Estudos!', 'Olá! Continue firme nos estudos, seu esforço faz toda diferença! Qualquer dúvida conte comigo. Sucesso!') ?>">
                        <i class="bi bi-emoji-smile"></i> Motivacional ao estudo
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank" href="<?= mailtoBCC($emailsBCC, 'Promoção de Cursos Online!', 'Aproveite nossas promoções especiais em cursos online! Consulte detalhes e garanta sua vaga. Abraços, Prof. Eugênio.') ?>">
                        <i class="bi bi-stars"></i> Promoções de Cursos
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" target="_blank" href="<?= mailtoBCC($emailsBCC, 'Promoção de Produtos para Estudo!', 'Confira nossas ofertas de produtos que vão ajudar ainda mais no seu aprendizado! Fale comigo para mais informações.') ?>">
                        <i class="bi bi-bag-check"></i> Promoções de Produtos
                    </a>
                </li>
            </ul>
        </div>
        <?php require 'usuariosv1.0/modalMensagensemMassa.php' ?>
    </div>
    <div>
        <span class="badge bg-light text-dark fs-6 px-3 py-2"><?= $stmt->rowCount() ?> alunos</span>
    </div>
</div>
<!-- ... (restante da lista de alunos igual ao seu código!) ... -->
<?php if (!empty($datafimst)): ?>
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-warning btn-sm" id="btnAtualizarPrazoTodos">
            <i class="bi bi-calendar-check me-1"></i> Atualizar prazo de todos (+2 dias)
        </button>
        <div id="respostaAtualizacaoPrazo" class="ms-3 small"></div>
    </div>
<?php endif; ?>

<ul class="list-group">
    <?php
    // Reexecute statement para o loop
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        $idAluno = $row['codigousuario'];
        $encIdAluno = $enc = encrypt($row['codigocadastro'], $action = 'e');
        $nomeArr = explode(' ', trim($row['nome']));
        $nomeExib = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
        $nomeAluno = $nomeExib;
        $foto = fotoAlunoUrl($row['pastasc'], $row['imagem200']);
        list($ultimoAcesso, $diasAcesso) = getUltimoAcesso($con, $idAluno, $idTurma);
        $emailBloq = intval($row['emailbloqueio']);
        $temWhats = $row['celular'] ?? 'sem celular';
        $dataPrazo = $row['dataprazosi'] ?? '';
        $dataInscricao = $row['data_ins'] ?? '';
        $decsenha =  $dec = encrypt($row['senha'], $action = 'd');
        $expSenha = explode("&", $decsenha);
        $senha = $expSenha[1] ?? 'não registrado';
        $email = htmlspecialchars($row['email']);
        $possuipc = $row['possuipc'];

        // ADIÇÃO: capturando idade (SQL) com fallback em PHP usando c.datanascimento_sc
        $idade = $row['idade'] ?? null;
        if ($idade === null) {
            $idade = calcIdade($row['datanascimento_sc'] ?? null);
        }

        $msgSaudacao = "*Olá {$nomeExib}*, aqui é o professor Eugênio! Tudo bem?";
        $msgSenha = "*{$saudacao} {$nomeExib}*, \nsegue seus dados de acesso ao portal *professoreugenio.com*: \n\nE-*mail*:{$email}\n*Senha*:{$senha}\n\n Página de login:\n https://professoreugenio.com/login_aluno.php?ts=" . time();
        $msgRedes = "*{$saudacao} {$nomeExib}, tudo bem?
Aproveite para me acompanhar nas redes sociais e ficar por dentro das novidades, dicas e conteúdos gratuitos!
📺 YouTube:
https://www.youtube.com/@professoreugenio
📸 Instagram:
https://instagram.com/professoreugenio
🎵 TikTok:
https://www.tiktok.com/@professoreugeniomci
Conte comigo no seu aprendizado!
Abraço,
Professor Eugênio
        ";
        $emailPromo = "https://mail.google.com/mail/?view=cm&fs=1&to={$email}&su=Promoção de cursos&body=Olá%20{$nomeExib},%20aproveite%20nossa%20promoção%20de%20cursos!";
        $emailMotiv = "https://mail.google.com/mail/?view=cm&fs=1&to={$email}&su=Motivação%20para%20seus%20estudos&body=Olá%20{$nomeExib},%20continue%20se%20dedicando,%20você%20vai%20alcançar%20seus%20objetivos!";
        $plural = ($diasAcesso != 1) ? 's' : '';
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
            $msgAcesso = "{$saudacao} *{$nomeExib}*, aqui é o professor Eugênio!
do curso de *{$Nometurma}*
Percebi que ainda não registros de seu acesso às aulas recentemente.
Quero te incentivar a dar o primeiro passo e começar seus estudos na plataforma!
Lembre-se: estou sempre disponível para tirar suas dúvidas e apoiar no que precisar.
Você não está sozinho(a) nessa jornada — conte comigo!
*Acesse sua área do aluno pelo link abaixo:*
https://professoreugenio.com/login_aluno.php?ts=1752622481
Um grande abraço,
_Prof. Eugênio_
";
        }
        $linkAcessoWhats = $temWhats ? linkWhats($row['celular'], $msgAcesso) : '#';
    ?>
        <li class="list-group-item py-3 px-2 mb-2 rounded shadow-sm">
            <div class="row align-items-center gx-2">
                <!-- Esquerda: foto + dados -->
                <div class="col-md-9 d-flex align-items-center">
                    <!-- Exemplo dentro do seu <li> de cada aluno, perto do avatar ou nome -->
                    <div class="form-check me-2">
                        <input class="form-check-input check-aluno" type="checkbox"
                            value="<?= (int)$idAluno ?>" id="aluno<?= (int)$idAluno ?>">
                    </div>
                    <img src="<?= $foto ?>" class="rounded-circle me-3" style="width:56px; height:56px; object-fit:cover; border:2px solid #eee;">
                    <div>
                        <div>
                            <?php
                            $flagMaster = !empty($row['inscrito_masterclass']);
                            $flagPower  = !empty($row['inscrito_powerbi']);
                            $badgesExtras = '';
                            if ($flagMaster) {
                                $badgesExtras .= '<span class="badge bg-success ms-2" title="Inscrito no Master Class">
        <i class="bi bi-mortarboard-fill me-1"></i> Master Class
    </span>';
                            }
                            if ($flagPower) {
                                $badgesExtras .= '<span class="badge bg-warning text-dark ms-2" title="Inscrito no Power BI">
        <i class="bi bi-bar-chart-fill me-1"></i> Power BI
    </span>';
                            }
                            ?>
                            <a href="alunoAtendimento.php?idUsuario=<?= $encIdAluno ?>&id=<?= $_GET['id']; ?>&tm=<?= $_GET['tm']; ?>" class="fw-bold fs-6 text-decoration-none">
                                <?php if ($possuipc): ?>
                                    <i class="bi bi-pc-display" style="color:#19c37d;" title="Possui computador"></i>
                                <?php else: ?>
                                    <i class="bi bi-pc-display" style="color:#c5c5c5;" title="Não possui computador"></i>
                                <?php endif; ?>
                                <?= $nomeExib ?>
                            </a>

                            <?= $badgesExtras ?>

                            <!-- ADIÇÃO: badge de idade -->
                            <?php if ($idade !== null): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle ms-2" title="Idade do aluno">
                                    <i class="bi bi-cake me-1"></i> <?= (int)$idade ?> anos
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted ms-2" title="Data de nascimento não informada">
                                    <i class="bi bi-cake me-1"></i> —
                                </span>
                            <?php endif; ?>

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
                        <?php
                        $hoje = new DateTime();
                        // ----- Prazo -----
                        $prazo = new DateTime($dataPrazo);
                        $diffPrazo = $hoje->diff($prazo);
                        $diasRestantes = (int)$diffPrazo->format('%r%a');
                        // ----- Inscrição -----
                        $inscricao = new DateTime($dataInscricao);
                        $diffInscricao = $hoje->diff($inscricao);
                        $diasInscricao = (int)$diffInscricao->format('%r%a');
                        ?>
                        <div class="small">
                            <!-- Data de inscrição -->
                            Inscrito em: <?= databr($dataInscricao); ?>
                            <?php if ($diasInscricao === 0): ?>
                                <span class="badge bg-success">Hoje</span>
                            <?php elseif ($diasInscricao > 0 && $diasInscricao <= 7): ?>
                                <span class="badge bg-primary">Nesta semana (há <?= $diasInscricao ?> dia<?= $diasInscricao > 1 ? 's' : '' ?>)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Há <?= $diasInscricao ?> dia<?= $diasInscricao > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                            <br>
                            <!-- Prazo -->
                            Vencimento: <?= databr($dataPrazo); ?>
                            <?php if ($diasRestantes > 0): ?>
                                <span class="text-success">(faltam <?= $diasRestantes ?> dia<?= $diasRestantes != 1 ? 's' : '' ?>)</span>
                            <?php elseif ($diasRestantes == 0): ?>
                                <span class="text-warning">(vence hoje)</span>
                            <?php else: ?>
                                <span class="text-danger">(vencido há <?= abs($diasRestantes) ?> dia<?= abs($diasRestantes) != 1 ? 's' : '' ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Direita: Dropdown -->
                <?php require 'cursosv1.0/require_EnviarMensagens.php'; ?>
            </div>
        </li>
    <?php endwhile; ?>
</ul>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>

<script>
    // Selecionar / desmarcar todos
    document.getElementById('checkAllAlunos').addEventListener('change', function() {
        const marcar = this.checked;
        document.querySelectorAll('.check-aluno').forEach(cb => cb.checked = marcar);
    });

    // Botão de inscrição
    document.getElementById('btnInscreverSelecionados').addEventListener('click', function() {
        const turma = document.getElementById('turmaComercial').value;
        const selecionados = Array.from(document.querySelectorAll('.check-aluno:checked'))
            .map(e => e.value);

        if (!turma) {
            alert('Selecione uma turma comercial.');
            return;
        }
        if (selecionados.length === 0) {
            alert('Selecione ao menos um aluno.');
            return;
        }

        // Feedback UI
        const btn = this;
        const resp = document.getElementById('respInscricao');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processando…';
        resp.textContent = '';

        // Envia via AJAX
        fetch('cursosv1.0/ajax_InscreverEmTurma.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chaveturma: turma,
                    alunos: selecionados
                })
            })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    resp.innerHTML = '<span class="text-success">' + j.msg + '</span>';
                } else {
                    resp.innerHTML = '<span class="text-danger">' + (j.msg || 'Falha ao inscrever.') + '</span>';
                }
            })
            .catch(() => {
                resp.innerHTML = '<span class="text-danger">Erro de comunicação com o servidor.</span>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Inscrever selecionados (prazo 2 dias)';
            });
    });
</script>


<script>
    document.getElementById('btnAtualizarPrazoTodos')?.addEventListener('click', function() {
        if (!confirm("Deseja realmente atualizar o prazo de todos os alunos? (+2 dias após data final do curso)")) {
            return;
        }

        const btn = this;
        const resp = document.getElementById('respostaAtualizacaoPrazo');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Atualizando…';
        resp.innerHTML = '';

        fetch('cursosv1.0/ajax_AtualizarPrazoTodos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chave: '<?= $ChaveTurma ?>'
                })
            })
            .then(r => r.json())
            .then(j => {
                if (j.status === 'ok') {
                    resp.innerHTML = '<span class="text-success">' + j.msg + '</span>';
                } else {
                    resp.innerHTML = '<span class="text-danger">' + (j.msg || 'Erro inesperado.') + '</span>';
                }
            })
            .catch(() => {
                resp.innerHTML = '<span class="text-danger">Falha de conexão.</span>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-calendar-check me-1"></i> Atualizar prazo de todos (+2 dias)';
            });
    });
</script>

<!-- Modal de Pagamento -->
<div class="modal fade" id="modalPagamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formPagamento">
                    <input type="hidden" name="idusuarioCF" id="idusuarioCF">
                    <input type="hidden" name="idturma" id="idturma">

                    <div class="mb-3">
                        <label class="form-label">Tipo de Lançamento</label>
                        <select name="idLancamentoCF" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php
                            $stmt = $con->query("SELECT codigolancamentos, nomelancamentosFL FROM a_curso_financeiroLancamentos WHERE tipoLancamentos = 1");
                            while ($l = $stmt->fetch()) {
                                echo "<option value='{$l['codigolancamentos']}'>{$l['nomelancamentosFL']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" name="valorCF" class="form-control money" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data de Pagamento</label>
                        <input type="date" name="dataFC" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição (opcional)</label>
                        <input type="text" name="descricaoCF" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success w-100">Salvar Pagamento</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('.abrirPagamentoBtn').click(function() {
            const nome = $(this).data('nomealuno');
            const idusuario = $(this).data('idusuario');
            const idturma = $(this).data('idturma');

            $('#modalPagamento').modal('show');
            $('#idusuarioCF').val(idusuario);
            $('#idturma').val(idturma);
        });

        $('#formPagamento').submit(function(e) {
            e.preventDefault();
            const dados = $(this).serialize();

            $.post('cursosv1.0/ajax_FinancieroInsertReceitas.php', dados, function(res) {
                const retorno = JSON.parse(res);
                if (retorno.status === 'ok') {
                    $('#modalPagamento').modal('hide');
                    Toastify({
                        text: "Pagamento registrado com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "#198754"
                        }
                    }).showToast();
                } else {
                    Toastify({
                        text: "Erro ao registrar pagamento.",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "#dc3545"
                        }
                    }).showToast();
                }
            });
        });
    });
</script>


<!-- jQuery Mask (adicionar no <head> ou após jQuery) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $('.money').mask('000.000.000,00', {
        reverse: true
    });
</script>


<script>
    (function() {
        const btn = document.getElementById('btnFinalizarTurma');
        const resp = document.getElementById('respFinalizarTurma');
        if (!btn) return;

        btn.addEventListener('click', function() {
            const acao = btn.dataset.acao; // "finalizar" ou "reativar"
            const msgConfirm = acao === 'finalizar' ?
                'Deseja realmente FINALIZAR esta turma?' :
                'Deseja realmente REATIVAR esta turma?';

            if (!confirm(msgConfirm)) return;

            btn.disabled = true;
            const oldHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processando…';
            resp.textContent = '';

            fetch('cursosv1.0/ajax_FinalizarTurma.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        chave: '<?= $ChaveTurma ?>',
                        acao: acao
                    })
                })
                .then(r => r.json())
                .then(j => {
                    if (j.status === 'ok') {
                        resp.innerHTML = '<span class="text-success">' + j.msg + '</span>';
                        // Alterna o botão no front
                        if (acao === 'finalizar') {
                            btn.dataset.acao = 'reativar';
                            btn.className = 'btn btn-secondary btn-sm ms-2';
                            btn.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Reativar turma';
                        } else {
                            btn.dataset.acao = 'finalizar';
                            btn.className = 'btn btn-danger btn-sm ms-2';
                            btn.innerHTML = '<i class="bi bi-flag-fill me-1"></i> Finalizar turma';
                        }
                    } else {
                        resp.innerHTML = '<span class="text-danger">' + (j.msg || 'Erro inesperado.') + '</span>';
                        btn.innerHTML = oldHtml;
                    }
                })
                .catch(() => {
                    resp.innerHTML = '<span class="text-danger">Falha de conexão.</span>';
                    btn.innerHTML = oldHtml;
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    })();
</script>