<?php
// Contagem de usuários ativos
$sql = "SELECT COUNT(*) AS total FROM new_sistema_cursos WHERE visivelsc = 1";
$stmt = $con->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$conttl = $row['total'];
?>
                <?php
                // Contagem de usuários ativos
                $sql = "SELECT COUNT(*) AS total FROM new_sistema_cursos WHERE visivelsc = 1 AND onlinesc = 1";
                $stmt = $con->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $conton = $row['total'];
                ?>