<?php
// Data do filtro (default: hoje)
$dataFiltro = isset($_GET['data']) && $_GET['data'] ? $_GET['data'] : date('Y-m-d');
list($ano, $mes, $dia) = explode('-', $dataFiltro);
$stmt = config::connect()->prepare("
    SELECT c.codigocadastro, c.nome, c.email, c.pastasc, c.imagem200, c.emailbloqueio, 
           c.celular, c.senha, c.datanascimento_sc,
           (
               SELECT t.nometurma
               FROM new_sistema_inscricao_PJA i
               INNER JOIN new_sistema_cursos_turmas t ON t.chave = i.chaveturma
               WHERE i.codigousuario = c.codigocadastro
               ORDER BY i.data_ins DESC
               LIMIT 1
           ) AS nome_turma
    FROM new_sistema_cadastro c
    WHERE MONTH(c.datanascimento_sc) = :mes AND DAY(c.datanascimento_sc) = :dia
    ORDER BY c.nome
");
$stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
$stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
$stmt->execute();
?>
<?php require 'usuariosv1.0/functionAniversariantes.php' ?>
<?php require 'usuariosv1.0/formBuscaDataAniversariante.php' ?>
<?php require 'usuariosv1.0/listaAlunosAniversariantes.php' ?>
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>