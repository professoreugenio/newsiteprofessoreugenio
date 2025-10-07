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
    echo '<p class="text-muted">Nenhum arquivo enviado até o momento.</p>';
    exit;
}

foreach ($arquivos as $arq) {
    $imagem = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$imagem";

    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5);

    echo '<div class="card shadow-sm mb-4">';

    // Header com data e hora
    echo "<div class='card-header text-white bg-gradient' style='background-color:#800080;'>
            <small><strong>Enviado em:</strong> $data às $hora</small>
          </div>";

    echo '<div class="card-body">';

    // Arquivo: imagem ou botão
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid rounded shadow-sm mb-3' style='max-width:100%;'>
              </a>";
    } else {
        echo "<a href='$url' class='btn btn-outline-secondary mb-3' target='_blank'>
                <i class='bi bi-download me-1'></i> Baixar arquivo <span class='badge bg-light text-dark'>$ext</span>
              </a>";
    }

    // Formulário de exclusão
    echo "<form onsubmit='return excluirArquivo(this);'>
            <input type='hidden' name='idanexo' value='{$arq['codigoatividadeanexos']}'>
            <button type='submit' class='btn btn-sm btn-danger'>
                <i class='bi bi-trash'></i> Excluir
            </button>
          </form>";

    echo '</div>'; // card-body
    echo '</div>'; // card
}
