<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

$idAluno       = intval($_POST['idaluno'] ?? 0);
$idPublicacao  = intval($_POST['idpublicacao'] ?? 0);
$idModulo      = intval($_POST['idmodulo'] ?? 0);

// 1) Consulta anexos da atividade
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

// Coleta IDs dos anexos para buscar comentários em lote
$idsAnexos = array_map(static fn($a) => (int)$a['codigoatividadeanexos'], $arquivos);
$placeholders = implode(',', array_fill(0, count($idsAnexos), '?'));

// 2) Busca comentários de todos os anexos (com dados do autor)
$comentariosByAnexo = [];
if (!empty($idsAnexos)) {
    $sqlComentarios = "
        SELECT
            c.codigoatividadecomentario,
            c.idfileAnexoAAC,
            c.iduserdeAAC,
            c.iduserparaAAC,
            c.textoAAC,
            c.dataAAC,
            c.horaAAC,
            u.nome       AS nome_autor,
            u.pastasc    AS pasta_autor,
            u.imagem50   AS foto_autor
        FROM a_curso_AtividadeComentario c
        LEFT JOIN new_sistema_cadastro u
               ON u.codigocadastro = c.iduserdeAAC
        WHERE c.idfileAnexoAAC IN ($placeholders)
        ORDER BY c.dataAAC ASC, c.horaAAC ASC, c.codigoatividadecomentario ASC
    ";
    $stc = $con->prepare($sqlComentarios);
    $stc->execute($idsAnexos);
    $comentarios = $stc->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comentarios as $com) {
        $idan = (int)$com['idfileAnexoAAC'];
        if (!isset($comentariosByAnexo[$idan])) {
            $comentariosByAnexo[$idan] = [];
        }

        // Foto do autor do comentário
        $pastaAutor = $com['pasta_autor'] ?? '';
        $fotoAutor  = $com['foto_autor'] ?? 'usuario.png';
        $fotoCaminhoRel = "../../fotos/usuarios/{$pastaAutor}/{$fotoAutor}";
        if (empty($fotoAutor) || !is_file($fotoCaminhoRel)) {
            $fotoCaminhoRel = "../../fotos/usuarios/usuario.png";
        }

        $comentariosByAnexo[$idan][] = [
            'id'         => (int)$com['codigoatividadecomentario'],
            'texto'      => $com['textoAAC'] ?? '',
            'data'       => $com['dataAAC'] ?? '',
            'hora'       => $com['horaAAC'] ?? '',
            'nome_autor' => $com['nome_autor'] ?? 'Usuário',
            'foto_autor' => $fotoCaminhoRel,
        ];
    }
}

// 3) Dados do aluno (para avatar no topo de cada card)
$aluno = $con->prepare("SELECT nome, pastasc, imagem50 FROM new_sistema_cadastro WHERE codigocadastro = :id");
$aluno->execute([':id' => $idAluno]);
$dadosAluno = $aluno->fetch(PDO::FETCH_ASSOC);
$nomeAluno  = $dadosAluno['nome'] ?? 'Aluno';
$pastaAluno = $dadosAluno['pastasc'] ?? '';
$fotoAluno  = $dadosAluno['imagem50'] ?? 'usuario.png';
$fotoAlunoPath = "../../fotos/usuarios/{$pastaAluno}/{$fotoAluno}";
if (empty($fotoAluno) || !is_file($fotoAlunoPath)) {
    $fotoAlunoPath = "../../fotos/usuarios/usuario.png";
}

// 4) Render
echo '<div class="row g-4">';

foreach ($arquivos as $arq) {
    $idanexo = (int)$arq['codigoatividadeanexos'];
    $nome    = htmlspecialchars($arq['fotoAA'] ?? '', ENT_QUOTES, 'UTF-8');
    $pasta   = htmlspecialchars($arq['pastaAA'] ?? '', ENT_QUOTES, 'UTF-8');
    $ext     = strtolower($arq['extensaoAA'] ?? '');
    $urlRel  = "../../fotos/atividades/{$pasta}/{$nome}";

    $data = !empty($arq['dataenvioAA']) ? date('d/m/Y', strtotime($arq['dataenvioAA'])) : '--/--/----';
    $hora = !empty($arq['horaenvioAA']) ? substr($arq['horaenvioAA'], 0, 5) : '--:--';

    echo '<div class="col-12">';
    echo '  <div class="card shadow-lg border-0 text-white" style="background-color:#023047;">';
    echo '    <div class="card-body">';
    echo '      <div class="row g-4 align-items-start">';

    // Coluna esquerda: aluno + anexo + input comentário + excluir
    echo '        <div class="col-md-8">';
    echo '          <div class="d-flex align-items-center mb-3">';
    echo "            <img src='{$fotoAlunoPath}' alt='" . htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8') . "' class='rounded-circle border border-white me-2' width='46' height='46'>";
    echo "            <div class='fw-bold'>" . htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8') . "</div>";
    echo '          </div>';

    echo "          <h6 class='mb-3'><i class='bi bi-clock me-1'></i>Publicado em {$data} às {$hora}</h6>";

    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        echo "<a href='{$urlRel}' data-lightbox='atividade_{$idPublicacao}'>";
        echo "  <img src='{$urlRel}' class='img-fluid rounded border mb-3' alt='Anexo da atividade'>";
        echo "</a>";
    } else {
        echo "<a href='{$urlRel}' class='btn btn-outline-light w-100 mb-3' target='_blank'>";
        echo "  <i class='bi bi-file-earmark-text'></i> Baixar arquivo (" . strtoupper($ext) . ")";
        echo "</a>";
    }

    // Form enviar comentário (mantive seu fluxo existente)
    echo "<form class='d-flex gap-2' onsubmit='return enviarComentario({$idanexo}, this);'>";
    echo "  <input type='text' name='texto' class='form-control form-control-sm border-0 shadow-sm' placeholder='Adicionar comentário...' required>";
    echo "  <button class='btn btn-sm btn-light shadow-sm'><i class='bi bi-send'></i></button>";
    echo "</form>";

    // Botão excluir
    echo "<form class='mt-3' onsubmit='return excluirArquivo(this);'>";
    echo "  <input type='hidden' name='idanexo' value='{$idanexo}'>";
    echo "  <button type='submit' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i> Excluir</button>";
    echo "</form>";

    echo '        </div>'; // fim col-md-8

    // Coluna direita: Comentários já carregados
    echo '        <div class="col-md-4">';
    echo "          <div id='comentarios_{$idanexo}' class='bg-dark bg-opacity-25 rounded p-3' style='min-height:120px;max-height:350px;overflow-y:auto;'>";

    if (!empty($comentariosByAnexo[$idanexo])) {
        foreach ($comentariosByAnexo[$idanexo] as $c) {
            $dataCom = !empty($c['data']) ? date('d/m/Y', strtotime($c['data'])) : '';
            $horaCom = !empty($c['hora']) ? substr($c['hora'], 0, 5) : '';
            echo "  <div class='d-flex align-items-start mb-3'>";
            echo "    <img src='" . htmlspecialchars($c['foto_autor'], ENT_QUOTES, 'UTF-8') . "' class='rounded-circle me-2' width='34' height='34' alt='Avatar autor'>";
            echo "    <div>";
            echo "      <div class='small fw-semibold'>" . htmlspecialchars($c['nome_autor'], ENT_QUOTES, 'UTF-8') . " <span class='text-white-50'>• {$dataCom} {$horaCom}</span></div>";
            echo "      <div class='small'>" . nl2br(htmlspecialchars($c['texto'], ENT_QUOTES, 'UTF-8')) . "</div>";
            echo "    </div>";
            echo "  </div>";
        }
    } else {
        echo "  <div class='text-white-50'>Sem comentários ainda.</div>";
    }

    echo "          </div>";
    echo '        </div>'; // fim col-md-4

    echo '      </div>'; // row
    echo '    </div>'; // card-body
    echo '  </div>'; // card
    echo '</div>'; // col-12
}

echo '</div>'; // row g-4
