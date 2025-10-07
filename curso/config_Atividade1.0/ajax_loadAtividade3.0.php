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

$stmt = $con->prepare("SELECT * FROM a_curso_AtividadeAnexos 
    WHERE idalulnoAA = :aluno AND idpublicacacaoAA = :pub AND idmoduloAA = :mod 
    ORDER BY codigoatividadeanexos DESC LIMIT 0,4");

$stmt->execute([
    ':aluno' => $idAluno,
    ':pub' => $idPublicacao,
    ':mod' => $idModulo
]);

$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$arquivos) {
    echo '<p class="text-muted">Nenhum arquivo enviado até o momento.</p>';
    exit;
}

echo '<div class="row g-4">'; // <-- container com grid
foreach ($arquivos as $arq) {
    $idanexo = intval($arq['codigoatividadeanexos']);
    $nome = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$nome";
    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5);

    echo '<div class="col-12">';
    echo '  <div class="card shadow-sm  text-white" style="background-color: #023047;">';
    echo '    <div class="card-body">';
    echo '      <div class="row g-3">';

    // Coluna esquerda: imagem
    echo '        <div class="col-md-8">';
    echo "          <h6 style='color:#ffffff' class='text-muted mb-2'>Atividade publicada* em $data, $hora</h6>";

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid rounded border w-100'>
              </a>";
    } else {
        echo "<a href='$url' class='btn btn-outline-secondary w-100 mb-2' target='_blank'>
                <i class='bi bi-file-earmark-text'></i> Baixar arquivo ($ext)
              </a>";
    }

    echo "<form class='mt-2' onsubmit='return excluirArquivo(this);'>
            <input type='hidden' name='idanexo' value='$idanexo'>
            <button type='submit' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i> Excluir</button>
          </form>";
    echo '        </div>'; // col-md-5

    // Coluna direita: comentários
    echo '        <div class="col-md-4">';
    echo "          <div id='comentarios_$idanexo' class='mb-3'></div>";

    echo "<form class='d-flex gap-2' onsubmit='return enviarComentario($idanexo, this);'>
            <input type='text' name='texto' class='form-control form-control-sm' placeholder='Adicionar comentário...' required>
            <button class='btn btn-sm btn-primary'><i class='bi bi-send'></i></button>
          </form>";

    echo '        </div>'; // col-md-7
    echo '      </div>';   // row
    echo '    </div>';     // card-body
    echo '  </div>';       // card
    echo '</div>';
}
echo '</div>'; // row g-4
