<h3 class="text-left">
    <?php $enclink = encrypt("327&" . $idCurso . "&" . $idmodulo . "&0&0&0&0", $action = 'e');  ?>

    <button class="btn btn-success" onclick='window.location.href="https://professoreugenio.com/action.php?mdl=<?php echo $enclink;  ?>"'><?php echo $titulomodulo;  ?></button>

</h3>
<h1 class="content-title">
    Lição <?php echo $ordempub;  ?> | <?php echo $tituloPublicacao;  ?>

</h1>
<?php echo $assinante;  ?>