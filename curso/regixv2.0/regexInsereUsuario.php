<?php
if ($idusuario !== "0" && !empty($idusuario)):

    // Datas/horas atuais
    date_default_timezone_set('America/Fortaleza');
    $hoje = date('Y-m-d');
    $agora = date('H:i:s');

    // Helper: cria/atualiza cookie por 360 dias
    $criaCookieAcesso = function (string $ip, string $idusuario, string $chaveRegix, string $idturma, string $data, string $hora) {
        $payload = $ip . "&" . $idusuario . "&" . $chaveRegix . "&" . $idturma . "&" . $data . "&" . $hora;
        $key = encrypt($payload, 'e');
        $expira = time() + (60 * 60 * 24 * 360);
        // path '/', SameSite=Lax (opcional) — ajuste se necessário
        setcookie('registraacessos', $key, [
            'expires'  => $expira,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
    };

    // Helper: remove cookie
    $limpaCookieAcesso = function () {
        setcookie('registraacessos', '', time() - 3600, '/');
    };

    // Helper: insere um novo acesso
    $insereAcesso = function (PDO $con, string $idusuario, string $chaveRegix, string $ip, string $data, string $hora) {
        $sql = "INSERT INTO a_site_registrausuario (usuarioru, chaveru, ipru, dataru, horaru)
                VALUES (:usuario, :chaveru, :ipru, :dataru, :horaru)";
        $st = $con->prepare($sql);
        $st->bindParam(":usuario", $idusuario);
        $st->bindParam(":chaveru", $chaveRegix);
        $st->bindParam(":ipru", $ip);
        $st->bindParam(":dataru", $data);
        $st->bindParam(":horaru", $hora);
        $st->execute();
    };

    // Verifica se já existe registro HOJE para este usuário+chave+ip
    $sqlHoje = "SELECT 1 FROM a_site_registrausuario
                WHERE usuarioru = :usuarioru AND chaveru = :chaveru AND ipru = :ipru AND dataru = :dataru
                LIMIT 1";
    $checkHoje = $con->prepare($sqlHoje);
    $checkHoje->bindParam(":usuarioru", $idusuario);
    $checkHoje->bindParam(":chaveru", $chaveRegix);
    $checkHoje->bindParam(":ipru", $ip);
    $checkHoje->bindParam(":dataru", $hoje);
    $checkHoje->execute();
    $jaTemHoje = (bool)$checkHoje->fetchColumn();

    // Lógica baseada no cookie
    $precisaInserir = false;

    if (!empty($_COOKIE['registraacessos'])) {
        // Tenta decodificar cookie
        $dec = encrypt($_COOKIE['registraacessos'], 'd');
        $parts = explode("&", (string)$dec);

        // Esperado: ip & idusuario & chaveRegix & idturma & data & hora
        if (count($parts) >= 6) {
            $cookieData = [
                'ip'      => $parts[0],
                'idu'     => $parts[1],
                'chave'   => $parts[2],
                'turma'   => $parts[3],
                'data'    => $parts[4],
                'hora'    => $parts[5],
            ];

            // Se o cookie é de outro usuário/chave/ip, ignora e força um novo
            $cookieIncompativel = ($cookieData['idu'] !== (string)$idusuario) ||
                ($cookieData['chave'] !== (string)$chaveRegix) ||
                ($cookieData['ip'] !== (string)$ip);

            if ($cookieIncompativel) {
                // Limpa e força novo registro + novo cookie
                $limpaCookieAcesso();
                if (!$jaTemHoje) $precisaInserir = true;
                $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
            } else {
                // Cookie compatível: verifica validade por data
                if ($cookieData['data'] < $hoje) {
                    // Expirado (data anterior a hoje) -> limpa, insere novo acesso e renova cookie
                    $limpaCookieAcesso();
                    if (!$jaTemHoje) $precisaInserir = true;
                    $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
                } else {
                    // Cookie ainda é de hoje -> apenas garante que existe registro de hoje
                    if (!$jaTemHoje) $precisaInserir = true;
                    // (Opcional) renovar validade do cookie mantendo a mesma data/hora:
                    $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $cookieData['data'], $cookieData['hora']);
                }
            }
        } else {
            // Cookie inválido -> limpa e cria novo
            $limpaCookieAcesso();
            if (!$jaTemHoje) $precisaInserir = true;
            $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
        }
    } else {
        // Não há cookie -> cria e, se não houver registro de hoje, insere
        if (!$jaTemHoje) $precisaInserir = true;
        $criaCookieAcesso($ip, $idusuario, $chaveRegix, $idturma, $hoje, $agora);
    }

    if ($precisaInserir) {
        $insereAcesso($con, $idusuario, $chaveRegix, $ip, $hoje, $agora);
    }

endif;
