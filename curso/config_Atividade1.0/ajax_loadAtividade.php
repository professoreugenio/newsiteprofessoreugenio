<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

/* -------- Identifica usuário logado (cookie) -------- */
if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], 'd');
} elseif (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], 'd');
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}
$expUser = explode("&", $decUser);
$idUser  = (int)($expUser[0] ?? 0);

/* -------- Método -------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

/* -------- Entradas -------- */
$idAluno      = (int)($_POST['idaluno'] ?? 0);
$idPublicacao = (int)($_POST['idpublicacao'] ?? 0);
$idModulo     = (int)($_POST['idmodulo'] ?? 0);

/* -------- 1) Consulta anexos da atividade -------- */
$sqlAnexos = "SELECT *
              FROM a_curso_AtividadeAnexos
              WHERE idalulnoAA = :aluno
                AND idpublicacacaoAA = :pub
                AND idmoduloAA = :mod
              ORDER BY codigoatividadeanexos DESC";
$stmt = $con->prepare($sqlAnexos);
$stmt->execute([
    ':aluno' => $idAluno,
    ':pub'   => $idPublicacao,
    ':mod'   => $idModulo
]);
$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$arquivos) {
    echo '<p class="text-white">Nenhum arquivo enviado até o momento.</p>';
    exit;
}

/* -------- 2) Busca comentários de todos os anexos (com fallback para admin) -------- */
$idsAnexos = array_map(static fn($a) => (int)$a['codigoatividadeanexos'], $arquivos);
$comentariosByAnexo = [];

if (!empty($idsAnexos)) {
    $placeholders = implode(',', array_fill(0, count($idsAnexos), '?'));

    $sqlComentarios = "
        SELECT
            c.codigoatividadecomentario,
            c.idfileAnexoAAC,
            c.iduserdeAAC,
            c.textoAAC,
            c.dataAAC,
            c.horaAAC,
            COALESCE(u.nome, a.nome)          AS nome_autor,
            COALESCE(u.pastasc, a.pastasu)    AS pasta_autor,
            COALESCE(u.imagem50, a.imagem50)  AS foto_autor,
            COALESCE(u.codigocadastro, a.codigousuario) AS id_autor
        FROM a_curso_AtividadeComentario c
        LEFT JOIN new_sistema_cadastro u ON u.codigocadastro = c.iduserdeAAC
        LEFT JOIN new_sistema_usuario  a ON a.codigousuario  = c.iduserdeAAC
        WHERE c.idfileAnexoAAC IN ($placeholders)
        ORDER BY c.dataAAC ASC, c.horaAAC ASC, c.codigoatividadecomentario ASC
    ";
    $stc = $con->prepare($sqlComentarios);
    $stc->execute($idsAnexos);
    $comentarios = $stc->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comentarios as $com) {
        $idan = (int)$com['idfileAnexoAAC'];

        $pastaAutor = (string)($com['pasta_autor'] ?? '');
        $fotoAutor  = (string)($com['foto_autor'] ?? 'usuario.png');

        // Checa arquivo no FS e define caminho web
        $fsCheck = "../../fotos/usuarios/{$pastaAutor}/{$fotoAutor}";
        $webPath = "../fotos/usuarios/{$pastaAutor}/{$fotoAutor}";
        if (empty($fotoAutor) || !is_file($fsCheck)) {
            $webPath = "../fotos/usuarios/usuario.png";
        }

        $comentariosByAnexo[$idan][] = [
            'id'        => (int)$com['codigoatividadecomentario'],
            'id_autor'  => (int)($com['id_autor'] ?? $com['iduserdeAAC'] ?? 0),
            'nome'      => (string)($com['nome_autor'] ?? 'Usuário'),
            'texto'     => (string)($com['textoAAC'] ?? ''),
            'data'      => (string)($com['dataAAC'] ?? ''),
            'hora'      => (string)($com['horaAAC'] ?? ''),
            'foto'      => $webPath,
        ];
    }
}

/* -------- 3) Dados do aluno (avatar do topo do card) -------- */
$aluno = $con->prepare("SELECT nome, pastasc, imagem50 FROM new_sistema_cadastro WHERE codigocadastro = :id");
$aluno->execute([':id' => $idAluno]);
$dadosAluno = $aluno->fetch(PDO::FETCH_ASSOC);

$nomeAluno  = $dadosAluno['nome'] ?? 'Aluno';
$pastaAluno = $dadosAluno['pastasc'] ?? '';
$fotoAluno  = $dadosAluno['imagem50'] ?? 'usuario.png';

$fsAluno  = "../../fotos/usuarios/{$pastaAluno}/{$fotoAluno}";
$webAluno = "../fotos/usuarios/{$pastaAluno}/{$fotoAluno}";
$fotoAlunoPath = (!empty($fotoAluno) && is_file($fsAluno)) ? $webAluno : "../fotos/usuarios/usuario.png";
?>

<div class="row g-4">
    <?php foreach ($arquivos as $arq):
        $idanexo = (int)$arq['codigoatividadeanexos'];
        $nome    = htmlspecialchars($arq['fotoAA'] ?? '', ENT_QUOTES, 'UTF-8');
        $pasta   = htmlspecialchars($arq['pastaAA'] ?? '', ENT_QUOTES, 'UTF-8');
        $ext     = strtolower($arq['extensaoAA'] ?? '');
        $urlRel  = "../../fotos/atividades/{$pasta}/{$nome}";

        $dataPub = !empty($arq['dataenvioAA']) ? date('d/m/Y', strtotime($arq['dataenvioAA'])) : '--/--/----';
        $horaPub = !empty($arq['horaenvioAA']) ? substr($arq['horaenvioAA'], 0, 5) : '--:--';
    ?>
        <div class="col-12">
            <div class="card shadow-lg border-0 text-white" style="background-color:#023047;">
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <!-- Coluna esquerda: aluno + anexo + input comentário + excluir -->
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= htmlspecialchars($fotoAlunoPath, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8') ?>"
                                    class="rounded-circle border border-white me-2" width="46" height="46">
                                <div class="fw-bold"><?= htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>

                            <h6 class="mb-3"><i class="bi bi-clock me-1"></i>Publicado em <?= $dataPub ?> às <?= $horaPub ?></h6>

                            <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])): ?>
                                <a href="<?= htmlspecialchars($urlRel, ENT_QUOTES, 'UTF-8') ?>" data-lightbox="atividade_<?= $idPublicacao ?>">
                                    <img src="<?= htmlspecialchars($urlRel, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid rounded border mb-3" alt="Anexo da atividade">
                                </a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($urlRel, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-light w-100 mb-3" target="_blank">
                                    <i class="bi bi-file-earmark-text"></i> Baixar arquivo (<?= strtoupper($ext) ?>)
                                </a>
                            <?php endif; ?>

                            <!-- Enviar comentário -->
                            <form class="d-flex gap-2" onsubmit="return enviarComentario(<?= $idanexo ?>, this);">
                                <input type="text" name="texto" class="form-control form-control-sm border-0 shadow-sm"
                                    placeholder="Adicionar comentário..." required>
                                <button class="btn btn-sm btn-light shadow-sm">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>

                            <!-- Excluir anexo -->
                            <form class="mt-3" onsubmit="return excluirArquivo(this);">
                                <input type="hidden" name="idanexo" value="<?= $idanexo ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </form>
                        </div>

                        <!-- Coluna direita: comentários (bubbles, horário no topo) -->
                        <div class="col-md-4">
                            <div id="comentarios_<?= $idanexo ?>" class="rounded p-3" style="min-height:120px;max-height:400px;overflow-y:auto;">
                                <?php if (!empty($comentariosByAnexo[$idanexo])): ?>
                                    <?php foreach ($comentariosByAnexo[$idanexo] as $c):
                                        // Sanitizações e formatos
                                        $textoSafe = nl2br(htmlspecialchars($c['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
                                        $dataC = !empty($c['data']) ? date('d/m/Y', strtotime($c['data'])) : '--/--/----';
                                        $horaC = !empty($c['hora']) ? substr($c['hora'], 0, 5) : '--:--';

                                        $nomeCompletoRaw = $c['nome'] ?? 'Usuário';
                                        $nomeCompleto    = htmlspecialchars($nomeCompletoRaw, ENT_QUOTES, 'UTF-8');
                                        $primeiroNomeRaw = strtok($nomeCompletoRaw, ' ');
                                        $nomeCurto       = htmlspecialchars($primeiroNomeRaw ?: $nomeCompletoRaw, ENT_QUOTES, 'UTF-8');

                                        $foto  = htmlspecialchars($c['foto'] ?? '../fotos/usuarios/usuario.png', ENT_QUOTES, 'UTF-8');
                                        $idAutor = (int)$c['id_autor'];
                                    ?>

                                        <?php if ($idUser === $idAutor): ?>
                                            <!-- Sua mensagem (direita): horário em cima à direita -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-end">
                                                    <div class="small text-white-50 mb-1">
                                                        <small>em <?= $dataC ?> às <?= $horaC ?></small>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <div class="bg-light text-dark p-2" style="max-width:85%; border-radius:8px 0 8px 8px;">
                                                        <div class="text-end" style="min-width:100px">
                                                            <i><?= $textoSafe ?></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Mensagem de outro (esquerda): horário em cima à esquerda -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-start">
                                                    <img src="<?= $foto ?>" class="rounded-circle me-2 d-none d-sm-block" width="40" height="40" alt="<?= $nomeCurto ?>">
                                                    <div class="w-100">
                                                        <div class="small text-white-50 mb-1">
                                                            <small>em <?= $dataC ?> às <?= $horaC ?></small>
                                                        </div>
                                                        <div class="text-dark p-2" style="background-color:#e9e9deff; border-radius:0 8px 8px 8px;">
                                                            <div class="mb-1">
                                                                <strong title="<?= $nomeCompleto ?>"><?= $nomeCurto ?></strong>
                                                            </div>
                                                            <div><i><?= $textoSafe ?></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-white-50">Sem comentários ainda.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><!-- row -->
                </div><!-- card-body -->
            </div><!-- card -->
        </div><!-- col-12 -->
    <?php endforeach; ?>
</div><!-- row g-4 -->