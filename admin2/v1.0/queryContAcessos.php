<?php
$dataSel = isset($_GET['data']) && $_GET['data'] ? $_GET['data'] : date('Y-m-d');

// Para popular o filtro: obtenha as datas disponíveis (opcional, pode limitar ou ordenar por mais recentes)
$stmtDatas = config::connect()->query("SELECT DISTINCT datara FROM a_site_registraacessos ORDER BY datara DESC");
$datasDisponiveis = $stmtDatas->fetchAll(PDO::FETCH_COLUMN);

$stmtAcessos = config::connect()->prepare("
    SELECT 
        a.idusuariora,
        a.idturmara,
        a.datara,
        MIN(a.horara) as primeira_hora,
        c.nomecurso,
        t.nometurma
    FROM a_site_registraacessos a
    INNER JOIN new_sistema_cadastro c ON a.idusuariora = c.codigocadastro
    LEFT JOIN new_sistema_cursos_turmas t ON t.chave = a.idturmara
    WHERE a.datara = :dataSel
    GROUP BY a.idusuariora, a.datara
    ORDER BY primeira_hora ASC, c.nomecurso
");
$stmtAcessos->bindParam(':dataSel', $dataSel);
$stmtAcessos->execute();
$alunosAcesso = $stmtAcessos->fetchAll(PDO::FETCH_ASSOC);
$totalAcessos = count($alunosAcesso);
