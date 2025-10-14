<?php
$query = $con->prepare("SELECT * FROM new_sistema_inscricao_PJA WHERE chaveturma = :chave AND codigousuario=:iduser ");
$query->bindParam(":chave", $chaveturmaUser);
$query->bindParam(":iduser", $codigoUser);
$query->execute();
$rwIscricao = $query->fetch(PDO::FETCH_ASSOC);
$dataprazo = "";
if ($rwIscricao) {
    $userassin = $rwIscricao['renovacaosi'];
    $andamento = $rwIscricao['andamentosi'];
    $dataprazo = $rwIscricao['dataprazosi'];
    $idCursoInscricao = $rwIscricao['codcurso_ip'];
}
?>

<div class="d-flex justify-content-center gap-3 mt-3">
    <!-- Botão WhatsApp -->
    <a href="https://api.whatsapp.com/send?text=Confira%20esse%20conteúdo:%20<?php echo $paginaatual;  ?>"
        target="_blank" class="btn btn-success">
        <i class="bi bi-whatsapp"></i>
    </a>
    <!-- Botão Facebook -->
    <!-- <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $paginaatual;  ?>" -->
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $paginaatual;  ?>" target="_blank"
        class="btn btn-primary">
        <i class="bi bi-facebook"></i>
    </a>
    <!-- Botão Twitter -->
    <a href="https://twitter.com/intent/tweet?text=Confira%20esse%20conteúdo!%20<?php echo $paginaatual;  ?>"
        target="_blank" class="btn btn-info">
        <i class="bi bi-twitter"></i>
    </a>
</div>
<?php if (!empty($codigoUser)) {
    if ($codigoUser == '1') {
?>
        <div class="container">
            <h1 class="mb-2">
                Sistema de Pesquisa
                <?php echo $idmodulo;  ?>

                <?php
                if (empty($idmodulo)) {
                    $idmodulo = "0";
                };
               echo $nrAula = numerodaaula($decModulo, $idTurma, $data);
                ?>
                <i class="bi bi-search"></i>
            </h1>
            <input type="text" id="search" class="form-control my-3" placeholder="Digite para pesquisar...">
            <ul id="results" class="list-group mt-3"></ul>
        </div>
        <script>
            $(document).ready(function() {
                $('#search').on('input', function() {
                    let query = $(this).val();
                    if (query.length > 2) {
                        $.ajax({
                            url: 'search.php',
                            method: 'POST',
                            data: {
                                query: query
                            },
                            success: function(data) {
                                $('#results').html(data);
                            }
                        });
                    } else {
                        $('#results').html('');
                    }
                });
            });
        </script>
<?php }
} ?>
<div id="texto">
    <?php
    $string = htmlspecialchars_decode($textoPublicacao);
    $localiza = "<pre>";
    $substitue = '<div style="position:relative"><div class="msgprint">Copiar <i class="fa fa-copy" aria-hidden="true"></i></div></div> <pre>';
    $textoPublicacao = str_replace($localiza, $substitue, $string);
    ?>
    <?php
    $search = '--break--';
    $replace = '<div class="text-break"></div>';
    $textoPublicacao = str_replace($search, $replace, $textoPublicacao);
    $textoPublicacao = removerparametros($textoPublicacao, $parametros = []);
    ?>
    <?php
    $textoPublicacao = str_replace('<h2', '<div style="height="40px">&nbsp;</div><h2', $textoPublicacao);
    ?>
    <?php echo $textoPublicacao; ?>
</div>