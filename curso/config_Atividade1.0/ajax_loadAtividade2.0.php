<?php
// ... (conexão e validação omitidos para foco no layout)

foreach ($arquivos as $arq) {
    $idanexo = intval($arq['codigoatividadeanexos']);
    $nome = htmlspecialchars($arq['fotoAA']);
    $pasta = htmlspecialchars($arq['pastaAA']);
    $ext = strtolower($arq['extensaoAA']);
    $url = "../../fotos/atividades/$pasta/$nome";
    $data = date('d/m/Y', strtotime($arq['dataenvioAA']));
    $hora = substr($arq['horaenvioAA'], 0, 5);

    echo '<div class="card mb-4 shadow-sm">';
    echo '  <div class="card-body">';
    echo '    <div class="row g-3 align-items-start">';

    // Coluna da imagem
    echo '      <div class="col-md-5">';
    echo "        <h6 class='text-muted mb-2'>Atividade publicada em $data, $hora</h6>";
    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
        echo "<a href='$url' data-lightbox='atividade_$idPublicacao'>
                <img src='$url' class='img-fluid rounded border' style='max-width:100%;'>
              </a>";
    } else {
        echo "<a href='$url' class='btn btn-outline-secondary' target='_blank'>
                <i class='bi bi-file-earmark-text'></i> Baixar arquivo ($ext)
              </a>";
    }

    echo "<form class='mt-2' onsubmit='return excluirArquivo(this);'>
            <input type='hidden' name='idanexo' value='$idanexo'>
            <button type='submit' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i> Excluir</button>
          </form>";
    echo '      </div>'; // col esquerda

    // Coluna dos comentários
    echo '      <div class="col-md-7">';
    echo "        <div id='comentarios_$idanexo' class='mb-2'></div>";

    // Campo de envio de novo comentário
    echo "<form class='d-flex align-items-center gap-2' onsubmit='return enviarComentario($idanexo, this);'>
            <input type='text' name='texto' class='form-control form-control-sm' placeholder='Adicionar comentário...' required>
            <button class='btn btn-sm btn-primary'><i class='bi bi-send'></i></button>
          </form>";

    echo '      </div>'; // col direita
    echo '    </div>';   // row
    echo '  </div>';     // card-body
    echo '</div>';       // card

    // JS para carregar os comentários de cada anexo
    echo "<script>carregarComentarios($idanexo);</script>";
}
