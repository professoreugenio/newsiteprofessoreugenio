<?php
define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';



$idcurso = encrypt($_GET['id'], $action = 'd');
$tipo = $_GET['tipo'];
$query = $con->prepare("
    SELECT 
        categorias.*, fotos.*
    FROM 
        new_sistema_categorias_PJA AS categorias
    INNER JOIN 
        new_sistema_midias_fotos_PJA AS fotos
    ON 
        categorias.pasta = fotos.pasta
    WHERE 
        fotos.codpublicacao = :id 
        AND fotos.tipo = :tipo
");

$query->bindParam(":id", $idcurso);
$query->bindParam(":tipo", $tipo);
$query->execute();

$result = $query->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $pasta = $result['pasta'];
    $foto = $result['foto'];
    $diretorio = $raizSite . "/fotos/midias/" . $pasta;

    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $arquivo = $diretorio . "/" . $foto; ?>

    <div class="img-container">
        <img src="<?= htmlspecialchars($arquivo) ?>" alt="img">
        <?php echo $arquivo;  ?>
        <div class="img-actions" id="genfavdel">
            <button class="img-btn btn-favorito"
                title="Favoritar"
                data-id="<?= htmlspecialchars($_GET['id']) ?>"
                data-tipo="<?= htmlspecialchars($_GET['tipo']) ?>">
                <i class="bi bi-star-fill"></i>
            </button>
            <button class="img-btn btn-excluir"
                title="Excluir"
                data-id="<?= htmlspecialchars($_GET['id']) ?>"
                data-tipo="<?= htmlspecialchars($_GET['tipo']) ?>">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>

    </div>
    <script>
        

        $(document).ready(function() {
            // Função para pegar ID do script
         

            // Delegação para clique nos botões dentro de #showfoto (por segurança caso o conteúdo seja carregado via .load)
            $('#genfavdel').on('click', '.btn-favorito', function() {
                const tipo = $(this).data('tipo');
                const id = $(this).data('id');

                console.log("Favoritar: " + tipo + " " + id);

                $.post('cursosv1.0/ajax_CursoImgFavoritar.php', {
                    idCurso: id,
                    tipo: tipo
                }, function(resposta) {
                    $('#respostaUpload').html(resposta);
                    $('#showfoto').load('cursosv1.0/ajax_CursoImgApresentacaoLoad.php?id=' + encodeURIComponent(id) + '&tipo=' + encodeURIComponent(tipo));
                });
            });

            $('#genfavdel').on('click', '.btn-excluir', function() {
                const tipo = $(this).data('tipo');
                const id = $(this).data('id');
                console.log("Excluir: " + tipo + " " + id);
                if (confirm('Tem certeza que deseja excluir esta imagem?')) {
                    $.post('cursosv1.0/ajax_CursoImgExcluir.php', {
                        idCurso: id,
                        tipo: tipo
                    }, function(resposta) {
                        $('#respostaUpload').html(resposta);
                        $('#showfoto').load('cursosv1.0/ajax_CursoImgApresentacaoLoad.php?id=' + encodeURIComponent(id) + '&tipo=' + encodeURIComponent(tipo));
                    });
                }
            });
        });
    </script>
<?php

} else {
    echo 'Nenhuma foto adicionada para o curso ID: ' . htmlspecialchars($idcurso) . '.';
}
