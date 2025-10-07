<?php
// Data do filtro (default: hoje)
$dataFiltro = isset($_GET['data']) && $_GET['data'] ? $_GET['data'] : date('Y-m-d');
list($ano, $mes, $dia) = explode('-', $dataFiltro);

// Consulta alunos aniversariantes (independente de turma/curso)
$stmt = config::connect()->prepare("
    SELECT codigocadastro, nome, email, pastasc, imagem200, emailbloqueio, celular, senha, datanascimento_sc
    FROM new_sistema_cadastro
    WHERE MONTH(datanascimento_sc) = :mes AND DAY(datanascimento_sc) = :dia
    ORDER BY nome
");
$stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
$stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
$stmt->execute();

function fotoAlunoUrl($pasta, $imagem)
{
    $urlFoto = "https://professoreugenio.com/fotos/usuarios/{$pasta}/{$imagem}";
    if (!$imagem) return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    $headers = @get_headers($urlFoto);
    if ($headers && strpos($headers[0], '200') !== false) {
        return $urlFoto;
    } else {
        return "https://professoreugenio.com/fotos/usuarios/usuario.jpg";
    }
}

function linkWhats($cel, $msg)
{
    $numero = preg_replace('/\D/', '', $cel);
    if ($numero && substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
    return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : false;
}
?>
<!-- FILTRO DATA DE ANIVERSÁRIO -->
<form method="get" class="d-flex align-items-center gap-2 mb-4">
    <label for="data" class="fw-bold">Data do Aniversário:</label>
    <input type="date" name="data" id="data" value="<?= htmlspecialchars($dataFiltro) ?>" class="form-control" style="width:180px" onchange="this.form.submit()">
    <span class="badge bg-primary ms-3"><?= $stmt->rowCount() ?> aniversariante<?= $stmt->rowCount() == 1 ? '' : 's' ?></span>
</form>

<ul class="list-group">
    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
        $nomeArr = explode(' ', trim($row['nome']));
        $nomeExib = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
        $foto = fotoAlunoUrl($row['pastasc'], $row['imagem200']);
        $emailBloq = intval($row['emailbloqueio']);
        $temWhats = !empty($row['celular']);
        $email = htmlspecialchars($row['email']);
        $msgBday = "🎉 *Parabéns, {$nomeExib}!* 🎂\nHoje é um dia especial e eu desejo a você muita saúde, alegria e conquistas!\nAproveite seu aniversário, conte sempre comigo!\n\nAbraços,\nProfessor Eugênio";
        $linkWhatsBday = $temWhats ? linkWhats($row['celular'], $msgBday) : '#';
    ?>
        <li class="list-group-item py-3 px-2 mb-2 rounded shadow-sm">
            <div class="row align-items-center gx-2">
                <!-- Esquerda: foto + dados -->
                <div class="col-md-9 d-flex align-items-center">
                    <img src="<?= $foto ?>" class="rounded-circle me-3" style="width:56px; height:56px; object-fit:cover; border:2px solid #eee;">
                    <div>
                        <div>
                            <span class="fw-bold fs-6"><?= $nomeExib ?></span>
                            <span class="badge bg-warning text-dark ms-2">
                                <i class="bi bi-cake"></i>
                                <?= date('d/m/Y', strtotime($row['datanascimento_sc'])) ?> - Aniversário!
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
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuBtn<?= $row['codigocadastro'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-send"></i> Enviar Parabéns
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuBtn<?= $row['codigocadastro'] ?>">
                            <?php if ($temWhats): ?>
                                <li>
                                    <a class="dropdown-item" target="_blank" href="<?= $linkWhatsBday ?>">
                                        <i class="bi bi-whatsapp text-success"></i> WhatsApp Parabéns
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" target="_blank" href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= $email ?>&su=Feliz%20Aniversário!&body=<?= urlencode("🎉 Parabéns, {$nomeExib}!\nHoje é o seu dia! Que você tenha um novo ano de vida incrível e cheio de realizações. Conte sempre comigo.\nAbraços, Prof. Eugênio") ?>">
                                    <i class="bi bi-envelope-paper"></i> E-mail Parabéns
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
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