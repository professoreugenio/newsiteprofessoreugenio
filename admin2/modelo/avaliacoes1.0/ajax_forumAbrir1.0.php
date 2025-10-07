<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

function montarFotoUsuario(?string $pasta, ?string $img): string
{
    $pasta = trim((string)$pasta);
    $img   = trim((string)$img);
    if ($pasta !== '' && $img !== '') {
        return "/fotos/usuarios/{$pasta}/{$img}";
    }
    return "/admin2/v1.0/img/user50.png"; // ajuste se necessário
}

$codigo = isset($_POST['codigoForum']) ? (int)$_POST['codigoForum'] : 0;

try {
    if ($codigo <= 0) throw new Exception('ID inválido');

    // Atualiza acessadoCF = 1
    $up = config::connect()->prepare("UPDATE a_curso_forum SET acessadoCF = 1 WHERE codigoForum = :id LIMIT 1");
    $up->bindParam(':id', $codigo, PDO::PARAM_INT);
    $up->execute();

    // Busca conteúdo + joins (usuário e publicação)
    $st = config::connect()->prepare("
        SELECT 
            f.textoCF, f.dataCF, f.horaCF,
            u.nome AS nomeUsuario, u.imagem50 AS img50, u.pastasc AS pastaUsuario,
            p.titulo AS tituloAula
        FROM a_curso_forum f
        LEFT JOIN new_sistema_cadastro u 
            ON u.codigocadastro = f.idusuarioCF
        LEFT JOIN new_sistema_publicacoes_PJA p 
            ON p.codigopublicacoes = f.idartigoCF
        WHERE f.codigoForum = :id
        LIMIT 1
    ");
    $st->bindParam(':id', $codigo, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) throw new Exception('Registro não encontrado');

    $html = '<div class="mb-2 small text-muted"><i class="bi bi-calendar2-week me-1"></i>'
        . htmlspecialchars($row['dataCF'] ?? '') . ' <i class="bi bi-dot mx-1"></i>'
        . '<i class="bi bi-clock me-1"></i>' . htmlspecialchars($row['horaCF'] ?? '') . '</div>';
    $html .= '<div class="fs-6">' . nl2br(htmlspecialchars($row['textoCF'] ?? '')) . '</div>';

    $nome = (string)($row['nomeUsuario'] ?? 'Aluno');
    $foto = montarFotoUsuario($row['pastaUsuario'] ?? '', $row['img50'] ?? '');
    $tit  = (string)($row['tituloAula'] ?? '-');

    echo json_encode(['ok' => true, 'html' => $html, 'nome' => $nome, 'foto' => $foto, 'titulo' => $tit]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
