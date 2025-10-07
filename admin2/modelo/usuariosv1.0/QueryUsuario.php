<?php

// Verifica se o ID foi passado
if (!isset($_GET['idUsuario']) || empty($_GET['idUsuario'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID do usuário não fornecido.']);
    exit;
}

$idUsuario = $dec = encrypt($_GET['idUsuario'], $action = 'd');

// Consulta segura com prepared statement
try {
    $pdo = config::connect();
    $stmt = $pdo->prepare("
        SELECT 
            codigocadastro,
            pastasc,
            nome,
            possuipc,
            imagem200,
            email,
            emailanterior,
            emailbloqueio,
            bloqueiopost,
            datanascimento_sc,
            telefone,
            estado,
            celular,
            senha,
            data_sc
        FROM new_sistema_cadastro
        WHERE codigocadastro = :id
        LIMIT 1
    ");
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo "<div class='alert alert-danger'>Usuário não encontrado.</div>";
        return;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Atribuição com validações e valores padrão
    $codigoCadastro     = $row['codigocadastro']     ?? '';
    $concordatermo     = $row['concordotermo']     ?? '';
    $pastasc            = $row['pastasc']            ?? '';
    $nome               = $row['nome']               ?? 'Nome não informado';
    $possuipc           = $row['possuipc']           ?? null;
    $imagem200          = $row['imagem200']          ?? 'sem-imagem.jpg';
    $email              = $row['email']              ?? '';
    $emailanterior      = $row['emailanterior']      ?? '';
    $emailbloqueio      = $row['emailbloqueio']      ?? '';
    $bloqueiopost       = $row['bloqueiopost']       ?? '';
    $datanascimento_sc  = $row['datanascimento_sc']  ?? '';
    $telefone           = $row['telefone']           ?? '';
    $estado             = $row['estado']             ?? '';
    $celular            = $row['celular']            ?? '';
    $senha              = $row['senha']              ?? '';
    $dataCadastro       = $row['data_sc']            ?? '';
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro na consulta: " . $e->getMessage() . "</div>";
    return;
}


// Consulta da última turma do usuário
$ultimaTurma = '';

try {
    $stmtTurma = $pdo->prepare("
        SELECT t.nometurma 
        FROM new_sistema_inscricao_PJA i
        INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
        WHERE i.codigousuario = :idUsuario
        ORDER BY i.data_ins DESC
        LIMIT 1
    ");
    $stmtTurma->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmtTurma->execute();

    if ($stmtTurma->rowCount() > 0) {
        $ultimaTurma = $stmtTurma->fetchColumn();
    } else {
        $ultimaTurma = 'Nenhuma turma registrada';
    }
} catch (PDOException $e) {
    $ultimaTurma = 'Erro ao buscar turma';
}
