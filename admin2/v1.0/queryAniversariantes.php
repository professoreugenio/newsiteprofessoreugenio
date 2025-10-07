 <?php
    // Data de hoje
    $mesAtual = date('m');
    $diaAtual = date('d');

    // Consulta: total de aniversariantes hoje
    $stmtAni = config::connect()->prepare("
    SELECT COUNT(*) 
    FROM new_sistema_cadastro 
    WHERE MONTH(datanascimento_sc) = :mes
      AND DAY(datanascimento_sc) = :dia
      AND datanascimento_sc IS NOT NULL
      AND datanascimento_sc != '0000-00-00'
");
    $stmtAni->bindValue(':mes', $mesAtual, PDO::PARAM_INT);
    $stmtAni->bindValue(':dia', $diaAtual, PDO::PARAM_INT);
    $stmtAni->execute();
    $totalAlunosAni = $stmtAni->fetchColumn();
    ?>