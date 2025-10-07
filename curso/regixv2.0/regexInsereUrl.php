<?php
// Pré: $con (PDO), $pagina, $nav já definidos no script chamador

// --- Normalização básica da URL ---
$pagina = (string)($pagina ?? '');
$pagina = trim($pagina);
if ($pagina === '' || $pagina === '/') {
    $pagina = '/index.php';
}
// remove barra final (exceto raiz e arquivos)
if ($pagina !== '/' && substr($pagina, -1) === '/') {
    $pagina = rtrim($pagina, '/');
}

// Opcional: force minúsculas se seu sistema não diferencia
// $pagina = strtolower($pagina);

// Opcional: limite de tamanho para caber no schema (ajuste se necessário)
$pagina = mb_substr($pagina, 0, 255);
$nav    = (string)($nav ?? '0');
$nav    = mb_substr($nav, 0, 255);

// --- UPSERT por urlsru ---
// Se 'urlsru' é UNIQUE, use ON DUPLICATE KEY UPDATE para evitar o erro 1062
$sql = "
    INSERT INTO a_site_registraurl (urlsru, varsru)
    VALUES (:urlsru, :varsru)
    ON DUPLICATE KEY UPDATE
        varsru = VALUES(varsru)
";

$stmt = $con->prepare($sql);
$stmt->bindParam(':urlsru', $pagina);
$stmt->bindParam(':varsru', $nav);

try {
    $stmt->execute();
} catch (PDOException $e) {
    // Como fallback, se seu MySQL não aceitar ON DUPLICATE KEY, use SELECT/UPDATE
    if ((int)$e->getCode() === 23000) {
        $sel = $con->prepare("SELECT codigoregistrourl FROM a_site_registraurl WHERE urlsru = :urlsru LIMIT 1");
        $sel->bindParam(':urlsru', $pagina);
        $sel->execute();
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $upd = $con->prepare("UPDATE a_site_registraurl SET varsru = :varsru WHERE codigoregistrourl = :id");
            $upd->bindParam(':varsru', $nav);
            $upd->bindParam(':id', $row['codigoregistrourl']);
            $upd->execute();
        } else {
            // Último recurso: ignorar duplicado
            $ign = $con->prepare("INSERT IGNORE INTO a_site_registraurl (urlsru, varsru) VALUES (:urlsru, :varsru)");
            $ign->bindParam(':urlsru', $pagina);
            $ign->bindParam(':varsru', $nav);
            $ign->execute();
        }
    } else {
        // Repropaga se for outro erro
        throw $e;
    }
}
