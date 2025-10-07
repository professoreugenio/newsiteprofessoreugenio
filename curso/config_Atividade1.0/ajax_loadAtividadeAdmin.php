<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

$idAluno = intval($_POST['idaluno'] ?? 0);
$idPublicacao = intval($_POST['idpublicacao'] ?? 0);
$idModulo = intval($_POST['idmodulo'] ?? 0);

// Consulta os arquivos enviados pelo aluno
$stmt = $con->prepare("SELECT * FROM a_curso_AtividadeAnexos 
    WHERE idalulnoAA = :aluno AND idpublicacacaoAA = :pub AND idmoduloAA = :mod 
    ORDER BY codigoatividadeanexos DESC");
$stmt->execute([
    ':aluno' => $idAluno,
    ':pub' => $idPublicacao,
    ':mod' => $idModulo
]);
$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$arquivos) {
    echo '<p class="text-muted">Nenhuma atividade enviada por este aluno até o momento.</p>';
    exit;
}

// Consulta dados do aluno
$stmtAluno = $con->prepare("SELECT nome, imagem50, pastasc FROM new_sistema_cadastro WHERE codigocadastro = :id");
$stmtAluno->bindValue(":id", $idAluno);
$stmtAluno->execute();
$dadosAluno = $stmtAluno->fetch(PDO::FETCH_ASSOC);

$nomeAluno = htmlspecialchars($dadosAluno['nome'] ?? 'Aluno');
$foto = (!empty($dadosAluno['imagem50']) && file_exists("../../fotos/usuarios/{$dadosAluno['pastasc']}/{$dadosAluno['imagem50']}"))
    ? "../../fotos/usuarios/{$dadosAluno['pastasc']}/{$dadosAluno['imagem50']}"
    : "../../fotos/usuarios/usuario.png";

// Estrutura
echo '<div class="mb-4">';
echo "<h5 class='text-primary mb-4'><img src='$foto' class='rounded-circle me-2' style='width:40px;height:40px;object-fit:cover;'> Atividades de <strong>$nomeAluno</strong></h5>";
echo '<div class="row g-4">';

foreach ($arquivos as $arq) {
    $idanexo = intval($arq['codigoatividadeanexos']);
    $imagem = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$imagem";

    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5);

    echo '<div class="col-12">';
    echo '<div class="card shadow-sm border-0" style="background-color:#f8f9fa;">';
    echo '<div class="card-body">';
    echo '<div class="row g-3">';

    // Coluna esquerda - imagem
    echo '<div class="col-md-8">';
    echo "<p class='small text-secondary mb-1'>📸 Atividade publicada em <strong>$data</strong> às <strong>$hora</strong></p>";

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid rounded border w-100'>
              </a>";
    } else {
        echo "<a href='$url' class='btn btn-outline-secondary w-100' target='_blank'>
                <i class='bi bi-file-earmark-text'></i> Baixar arquivo ($ext)
              </a>";
    }

    echo '</div>'; // col-md-8

    // Coluna direita - comentários
    echo '<div class="col-md-4">';
    echo "<div id='comentarios_$idanexo' class='mb-3'></div>";
    echo "<form class='d-flex gap-2' onsubmit='return enviarComentario($idanexo, this);'>
            <input type='text' name='texto' class='form-control form-control-sm' placeholder='Comentário do professor...' required>
            <button class='btn btn-sm btn-success' title='Enviar'><i class='bi bi-chat-left-text'></i></button>
          </form>";
    echo '</div>'; // col-md-4

    echo '</div>'; // row
    echo '</div>'; // card-body
    echo '</div>'; // card
    echo '</div>'; // col
}

echo '</div>'; // row g-4
echo '</div>';
