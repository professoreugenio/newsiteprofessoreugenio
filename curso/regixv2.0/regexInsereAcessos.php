<?php
// Pré: $con (PDO), $pagina, $nav, $ip, $chaveRegix, $idusuario, $idturma, $contar já definidos
// Compat: aceita $dispositivoAcesso ou $dispositoAcesso
$dispositivo = $dispositivoAcesso ?? $dispositoAcesso ?? '1';

// Datas/horas (America/Fortaleza)
date_default_timezone_set('America/Fortaleza');
$hoje = date('Y-m-d');
$agora = date('H:i:s');

// Caso venham pré-definidos, usa; se não, usa agora
$data = isset($data) ? $data : $hoje;
$hora = isset($hora) ? $hora : $agora;

// Helpers de cookie (mesmo payload do outro módulo)
$criaCookieAcesso = function (string $ip, string $idusuario, string $chaveRegix, string $idturma, string $data, string $hora) {
    $payload = $ip . "&" . $idusuario . "&" . $chaveRegix . "&" . $idturma . "&" . $data . "&" . $hora;
    $key = encrypt($payload, 'e');
    $expira = time() + (60 * 60 * 24 * 360);
    setcookie('registraacessos', $key, [
        'expires'  => $expira,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
};
$limpaCookieAcesso = function () {
    setcookie('registraacessos', '', time() - 3600, '/');
};

// --- Política por cookie: força INSERT novo quando virou o dia ---
$precisaInserirHoje = false; // força INSERT de acesso para o dia
if (!empty($_COOKIE['registraacessos'])) {
    $dec = encrypt($_COOKIE['registraacessos'], 'd');
    $parts = explode("&", (string)$dec);
    // Esperado: ip & idusuario & chaveRegix & idturma & data & hora
    if (count($parts) >= 6) {
        $ck = [
            'ip'    => $parts[0],
            'idu'   => $parts[1],
            'chave' => $parts[2],
            'turma' => $parts[3],
            'data'  => $parts[4],
            'hora'  => $parts[5],
        ];

        $incompativel = ($ck['ip'] !== (string)$ip) ||
            ($ck['idu'] !== (string)$idusuario) ||
            ($ck['chave'] !== (string)$chaveRegix);

        if ($incompativel) {
            // Usuário/sessão/ip diferentes -> renova cookie, e se não houver registro de hoje, força INSERT
            $limpaCookieAcesso();
            $precisaInserirHoje = true; // garantimos registro "primeiro do dia" dessa sessão
            $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
            $data = $hoje; // garante trabalhar no dia atual
            $hora = $agora;
        } else {
            // Mesma sessão: se o cookie é de dia anterior, renova e força INSERT do dia
            if ($ck['data'] < $hoje) {
                $limpaCookieAcesso();
                $precisaInserirHoje = true;
                $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
                $data = $hoje;
                $hora = $agora;
            } else {
                // Cookie é de hoje: não forçamos INSERT, mas podemos renovar validade (mantendo data/hora do cookie)
                $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $ck['data'], $ck['hora']);
                // Mantém $data como hoje; se veio diferente, normaliza:
                $data = $hoje;
            }
        }
    } else {
        // Cookie inválido -> limpa e força INSERT de hoje
        $limpaCookieAcesso();
        $precisaInserirHoje = true;
        $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
        $data = $hoje;
        $hora = $agora;
    }
} else {
    // Não há cookie: força o primeiro INSERT do dia e cria cookie
    $precisaInserirHoje = true;
    $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
    $data = $hoje;
    $hora = $agora;
}

// -------------------- Normalização mínima das strings --------------------
$pagina = (string)($pagina ?? '');
$pagina = trim($pagina);
if ($pagina === '' || $pagina === '/') {
    $pagina = '/index.php';
}
if ($pagina !== '/' && substr($pagina, -1) === '/') {
    $pagina = rtrim($pagina, '/');
}
$pagina = mb_substr($pagina, 0, 255);
$nav    = (string)($nav ?? '0');
$nav    = mb_substr($nav, 0, 255);

// 1) Tenta localizar a URL pelo par (urlsru, varsru)
$query = $con->prepare("
    SELECT codigoregistrourl 
    FROM a_site_registraurl 
    WHERE urlsru = :url AND varsru = :var 
    LIMIT 1
");
$query->bindParam(":url", $pagina);
$query->bindParam(":var", $nav);
$query->execute();
$rwUrl = $query->fetch(PDO::FETCH_ASSOC);

// 2) Se não achou, busca só por urlsru
if (!$rwUrl) {
    $query = $con->prepare("
        SELECT codigoregistrourl 
        FROM a_site_registraurl 
        WHERE urlsru = :url 
        LIMIT 1
    ");
    $query->bindParam(":url", $pagina);
    $query->execute();
    $rwUrl = $query->fetch(PDO::FETCH_ASSOC);
}

// 3) Se ainda não existir, UPSERT e relê o ID
if (!$rwUrl) {
    $upsert = $con->prepare("
        INSERT INTO a_site_registraurl (urlsru, varsru)
        VALUES (:urlsru, :varsru)
        ON DUPLICATE KEY UPDATE varsru = VALUES(varsru)
    ");
    $upsert->bindParam(':urlsru', $pagina);
    $upsert->bindParam(':varsru', $nav);
    $upsert->execute();

    $sel = $con->prepare("
        SELECT codigoregistrourl 
        FROM a_site_registraurl 
        WHERE urlsru = :url 
        LIMIT 1
    ");
    $sel->bindParam(':url', $pagina);
    $sel->execute();
    $rwUrl = $sel->fetch(PDO::FETCH_ASSOC);
}

if (!$rwUrl || !isset($rwUrl['codigoregistrourl'])) {
    return; // segurança
}
$idurl = $rwUrl['codigoregistrourl'];

// 4) Verifica se já existe acesso HOJE para a combinação (sessão + URL)
$selHoje = $con->prepare("
    SELECT 1 
    FROM a_site_registraacessos
    WHERE ipra          = :ipra
      AND chavera       = :chavera
      AND idusuariora   = :idusuariora
      AND dispositivora = :dispositivo
      AND idurlra       = :idurlra
      AND datara        = :datara
    LIMIT 1
");
$selHoje->bindParam(":ipra", $ip);
$selHoje->bindParam(":chavera", $chaveRegix);
$selHoje->bindParam(":idusuariora", $idusuario);
$selHoje->bindParam(":dispositivo", $dispositivo);
$selHoje->bindParam(":idurlra", $idurl);
$selHoje->bindParam(":datara", $data); // $data está normalizado para $hoje
$selHoje->execute();
$jaTemHoje = (bool)$selHoje->fetchColumn();

// 5) Insere o acesso se (a) não existir hoje OU (b) cookie virou o dia e pedimos um novo primeiro registro
if (!$jaTemHoje || $precisaInserirHoje) {
    $ins = $con->prepare("
        INSERT INTO a_site_registraacessos (
            ipra, chavera, idusuariora, idturmara, dispositivora,
            idurlra, contarra, datara, horara
        ) VALUES (
            :ipra, :chavera, :idusuariora, :idturmara, :dispositivo,
            :idurlra, :contarra, :datara, :horara
        )
    ");
    $ins->bindParam(":ipra", $ip);
    $ins->bindParam(":chavera", $chaveRegix);
    $ins->bindParam(":idusuariora", $idusuario);
    $ins->bindParam(":idturmara", $idturma);
    $ins->bindParam(":dispositivo", $dispositivo);
    $ins->bindParam(":idurlra", $idurl);
    $ins->bindParam(":contarra", $contar);
    $ins->bindParam(":datara", $data);
    $ins->bindParam(":horara", $hora);
    $ins->execute();
}

// 6) Atualiza contagem e hora final (sempre que houver hit)
$upd = $con->prepare("
    UPDATE a_site_registraacessos 
       SET contarra   = contarra + 1, 
           horafinalra = :horafinal,
           idturmara   = :idturma,
           idusuariora = :idusuariora
     WHERE ipra          = :ipra 
       AND chavera       = :chavera 
       AND dispositivora = :dispositivo 
       AND idurlra       = :idurlra 
       AND datara        = :datara
");
$upd->bindParam(":horafinal", $agora);
$upd->bindParam(":idturma", $idturma);
$upd->bindParam(":idusuariora", $idusuario);
$upd->bindParam(":ipra", $ip);
$upd->bindParam(":chavera", $chaveRegix);
$upd->bindParam(":dispositivo", $dispositivo);
$upd->bindParam(":idurlra", $idurl);
$upd->bindParam(":datara", $hoje); // garante atualizar o dia de hoje
$upd->execute();
