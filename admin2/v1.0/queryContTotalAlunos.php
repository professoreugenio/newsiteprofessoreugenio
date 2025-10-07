<?php
// Contagem de usuários ativos
$sql = "SELECT COUNT(*) AS total FROM new_sistema_cadastro ";
$stmt = $con->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$cont = $row['total'];
$stmtTotalAlunos = config::connect()->query("SELECT COUNT(*) AS total FROM new_sistema_cadastro");
$totalAlunos = $stmtTotalAlunos->fetchColumn();
