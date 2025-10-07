<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso container mt-4">

                <div id="atividade"></div>


            </div>
        </div>
        <!-- Coluna da direita: Imagem -->
    </div>
</div>

<!-- jQuery e Bootstrap Toast (se ainda não incluído) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script>
    $(document).ready(function() {
        $('#atividade').load('config_Atividades/AtividadeLoad.php');
    });
</script>