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
    $nome = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$nome";

    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5); // hh:mm

    echo '<div class="mb-4">';

    // Data/hora
    echo "<p class='text-muted small mb-1'><strong>Enviado em:</strong> $data às $hora</p>";

    // Imagem ou botão de download
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid border rounded' style='max-width:100%;'>
              </a>";
    } else {
        echo "<a href='$url' class='btn btn-outline-secondary' target='_blank'>
                <i class='bi bi-file-earmark-text'></i> Baixar arquivo ($ext)
              </a>";
    }

    // Botão excluir
    echo "<form class='mt-2' onsubmit='return excluirArquivo(this);'>
            <input type='hidden' name='idanexo' value='{$arq['codigoatividadeanexos']}'>
            <button type='submit' class='btn btn-sm btn-danger'>Excluir</button>
          </form>";

    echo '</div>';
}
