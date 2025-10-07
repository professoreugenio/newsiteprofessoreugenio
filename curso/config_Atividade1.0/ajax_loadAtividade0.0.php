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
    ORDER BY codigoatividadeanexos DESC");

$stmt->execute([
    ':aluno' => $idAluno,
    ':pub' => $idPublicacao,
    ':mod' => $idModulo
]);

$arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$arquivos) {
    echo '<p class="text-white">Nenhum arquivo enviado até o momento.</p>';
    exit;
}

echo '<div class="row g-4">';

foreach ($arquivos as $arq) {
    $idanexo = intval($arq['codigoatividadeanexos']);
    $nome = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$nome";
    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5);

    // Dados do aluno
    $alunoID = intval($arq['idalulnoAA']);
    $aluno = $con->prepare("SELECT nome, pastasc, imagem50 FROM new_sistema_cadastro WHERE codigocadastro = :id");
    $aluno->execute([':id' => $alunoID]);
    $dadosAluno = $aluno->fetch(PDO::FETCH_ASSOC);
    $nomeAluno = $dadosAluno['nome'] ?? 'Aluno';
    $pastaAluno = $dadosAluno['pastasc'] ?? '';
    $fotoAluno = $dadosAluno['imagem50'] ?? 'usuario.png';
    $fotoURL = "/fotos/usuarios/$pastaAluno/$fotoAluno";

    echo '<div class="col-12">';
    echo '  <div class="card shadow-sm text-white" style="background-color: #023047;">';
    echo '    <div class="card-body">';
    echo '      <div class="row g-4">';

    // 🧑 Coluna esquerda com imagem e aluno
    echo '        <div class="col-md-8">';
    echo '          <div class="d-flex align-items-center mb-2">';
    echo "            <img src='$fotoURL' alt='$nomeAluno' class='rounded-circle me-2 border' width='42' height='42'>";
    echo "            <div><strong style='color:#ffffff;'>$nomeAluno</strong></div>";
    echo '          </div>';
    echo "          <h6 class='mb-3 text-light'>Atividade publicada em $data às $hora</h6>";

    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid rounded border w-100'>
              </a>";

        echo "<form class='d-flex gap-2 mt-2' onsubmit='return enviarComentario($idanexo, this);'>
            <input type='text' name='texto' class='form-control form-control-sm' placeholder='Adicionar comentário...' required>
            <button class='btn btn-sm btn-light'><i class='bi bi-send'></i></button>
          </form>";
    } else {
        echo "<a href='$url' class='btn btn-outline-light w-100 mb-2' target='_blank'>
                <i class='bi bi-file-earmark-text'></i> Baixar arquivo ($ext)
              </a>";
    }

    echo "<form class='mt-3' onsubmit='return excluirArquivo(this);'>
            <input type='hidden' name='idanexo' value='$idanexo'>
            <button type='submit' class='btn btn-sm btn-danger'>
                <i class='bi bi-trash'></i> Excluir
            </button>
          </form>";
    echo '        </div>'; // fim coluna esquerda

    // 💬 Coluna direita com comentários
    echo '        <div class="col-md-4">';
    echo "          <div id='comentarios_$idanexo' class='mb-3'></div>";

    echo '        </div>'; // fim coluna direita

    echo '      </div>'; // row
    echo '    </div>'; // card-body
    echo '  </div>'; // card
    echo '</div>'; // col-12
}

echo '</div>'; // row g-4
