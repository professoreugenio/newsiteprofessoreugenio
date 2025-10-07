<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
try {
    // Método obrigatório
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
        exit;
    }
    $con = config::connect();
    // Entrada
    $idUsuarioIn   = trim($_POST['idUsuario'] ?? '');
    $idUsuarioIn = encrypt($idUsuarioIn, $action = 'd');
    $emailIn       = trim($_SESSION['emailUsuario'] ?? '');     // opcional (fallback)
    $senha         = trim($_POST['senha'] ?? '');
    // Validação de senha
    if (strlen($senha) < 6) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'A senha deve ter pelo menos 6 caracteres.']);
        exit;
    }
    // Tenta obter o ID descriptografando (quando vier criptografado na sessão/página)
    $idUsuario = $idUsuarioIn ? encrypt($idUsuarioIn, 'd') : null;
    // Buscar usuário por ID (preferencial) ou por E-mail (fallback)
    $usuario = null;
    if ($idUsuario) {
        $stmt = $con->prepare("SELECT codigocadastro, email FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
        $stmt->bindParam(':id', $idUsuario);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$usuario && $emailIn) {
        $stmt = $con->prepare("SELECT codigocadastro, email FROM new_sistema_cadastro WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $emailIn);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Se encontramos o usuário, atualizamos senha e chave
    if ($usuario) {
        $idUsuario = $usuario['codigocadastro'];
        $email     = $usuario['email'];
        // Conforme seu padrão:
        // $senhaenc = encrypt($email . "&" . $senha, 'e');
        // $chave    = strtoupper(md5($email . "&" . $senha));
        $senhaenc = encrypt($email . "&" . $senha, 'e');
        $chave    = strtoupper(md5($email . "&" . $senha));
        $upd = $con->prepare("
            UPDATE new_sistema_cadastro
               SET senha = :senha, chave = :chave
             WHERE codigocadastro = :id
            LIMIT 1
        ");
        $upd->execute([
            ':senha' => $senhaenc,
            ':chave' => $chave,
            ':id'    => $idUsuario
        ]);
        // Atualiza sessão
        $_SESSION['idUsuario']    = encrypt($idUsuario, 'e');
        $_SESSION['emailUsuario'] = $email;
        echo json_encode(['status' => 'ok', 'mensagem' => 'Senha definida com sucesso.']);
        exit;
    }
    /**
     * Caso não exista cadastro, INSERE usando seu esqueleto.
     * Tentamos preencher campos a partir da SESSION, e usamos defaults seguros quando não existirem.
     * Obs.: Se seu fluxo SEMPRE cria o cadastro antes, este branch não será usado na prática,
     * mas deixei implementado por garantia.
     */
    // Dados auxiliares (tentativas de origem)
    $email = $emailIn ?: ($_SESSION['emailUsuario'] ?? null);
    $nome  = $_SESSION['nomeUsuario'] ?? 'Aluno';
    if (!$email) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'E-mail não informado para criação do cadastro.']);
        exit;
    }
    // Campos complementares (defaults)
    $idadm       = $_SESSION['idadm'] ?? 0; // ajuste se tiver admin logado
    $chaveturma  = $_SESSION['chaveTurma'] ?? null; // sua sessão já guarda essa info
    $idturma     = $chaveturma; // se sua modelagem usa o mesmo código para turma
    $codigo      = $chaveturma;
    $datanascimento = $_POST['datanascimento'] ?? null;
    $celular        = $_POST['celular'] ?? ($_SESSION['celularUsuario'] ?? null);
    $estado         = $_POST['estado'] ?? null;
    $data = date('Y-m-d');
    $hora = date('H:i:s');
    // Gera pasta
    if (!function_exists('mesabreviado')) {
        // fallback simples, caso a função não esteja disponível
        function mesabreviado($d)
        {
            $map = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $m = (int)date('n', strtotime($d));
            return $map[$m - 1] ?? date('m', strtotime($d));
        }
    }
    $pasta = mesabreviado(date('Y-m-d')) . "_" . date("Ymd") . time();
    // Senha e chave no padrão do seu projeto
    $senhaenc = encrypt($email . "&" . $senha, 'e');
    $chave    = strtoupper(md5($email . "&" . $senha));
    // INSERT conforme seu esqueleto
    $queryInsert = $con->prepare("
        INSERT INTO new_sistema_cadastro (
            codadmin,
            codigo,
            turma_sc,
            turma,
            nome,
            email,
            datanascimento_sc,
            celular,
            estado,
            senha,
            chave,
            pastasc,
            data_sc,
            hora_sc
        ) VALUES (
            :codadmin,
            :codigo,
            :turma_sc,
            :turma,
            :nome,
            :email,
            :datanascimento,
            :celular,
            :estado,
            :senha,
            :chave,
            :pasta,
            :data_sc,
            :hora_sc
        )
    ");
    $queryInsert->bindParam(":codadmin",       $idadm);
    $queryInsert->bindParam(":codigo",         $codigo);
    $queryInsert->bindParam(":turma_sc",       $idturma);
    $queryInsert->bindParam(":turma",          $idturma);
    $queryInsert->bindParam(":nome",           $nome);
    $queryInsert->bindParam(":email",          $email);
    $queryInsert->bindParam(":datanascimento", $datanascimento);
    $queryInsert->bindParam(":celular",        $celular);
    $queryInsert->bindParam(":estado",         $estado);
    $queryInsert->bindParam(":senha",          $senhaenc);
    $queryInsert->bindParam(":chave",          $chave);
    $queryInsert->bindParam(":pasta",          $pasta);
    $queryInsert->bindParam(":data_sc",        $data);
    $queryInsert->bindParam(":hora_sc",        $hora);
    $queryInsert->execute();
    $novoId = $con->lastInsertId();
    // Atualiza sessão
    $_SESSION['idUsuario']    = encrypt($novoId, 'e');
    $_SESSION['emailUsuario'] = $email;
    $_SESSION['nomeUsuario']  = $nome;
    echo json_encode(['status' => 'ok', 'mensagem' => 'Cadastro criado e senha definida com sucesso.']);
    exit;
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.']);
    exit;
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha ao processar dados.']);
    exit;
}
