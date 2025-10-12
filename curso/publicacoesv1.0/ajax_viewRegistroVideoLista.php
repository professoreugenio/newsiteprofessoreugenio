<?php

/**
 * Lista últimas views do vídeo (nomes lado a lado)
 * Parâmetros (POST):
 *  - idpublicacao (int)
 *  - chaveyoutube (string 11 chars)
 */

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método não permitido.');
    }

    $idpublicacao = (int)($_POST['idpublicacao'] ?? 0);
    $chaveyoutube = trim($_POST['chaveyoutube'] ?? '');

    if ($idpublicacao <= 0 || !preg_match('/^[A-Za-z0-9_-]{11}$/', $chaveyoutube)) {
        http_response_code(400);
        exit('Parâmetros inválidos.');
    }

    $con = config::connect();

    // Busca as últimas 100 views desse vídeo/ publicação
    $sql = "
      SELECT 
          v.idusuariovc,
          v.chaveturmavc,
          v.datavc,
          v.horavc,
          c.nome AS nome_aluno,
          t.nometurma AS nome_turma
      FROM a_site_view_conteudo v
      LEFT JOIN new_sistema_cadastro c 
             ON c.codigocadastro = v.idusuariovc
      LEFT JOIN new_sistema_cursos_turmas t 
             ON t.chave = v.chaveturmavc
      WHERE v.idpublicacaovc = :idpub
        AND v.chaveyoutubevc   = :chave
      ORDER BY v.datavc DESC, v.horavc DESC
      LIMIT 100
    ";

    $st = $con->prepare($sql);
    $st->bindValue(':idpub', $idpublicacao, PDO::PARAM_INT);
    $st->bindValue(':chave', $chaveyoutube, PDO::PARAM_STR);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo '<div class="text-secondary small">Nenhum clique registrado ainda.</div>';
        exit;
    }

    // Render: nomes lado a lado como "chips" com tooltip (turma, data e hora)
?>
    <div class="d-flex flex-wrap gap-2 align-items-start">
        <?php foreach ($rows as $r):
            // Exibe apenas as duas primeiras palavras do nome
            $nomeCompleto = $r['nome_aluno'] ?: '';
            $partes = preg_split('/\s+/', trim($nomeCompleto));
            $nome = implode(' ', array_slice($partes, 0, 2));
            $turma = $r['nome_turma'] ?: 'Turma';
            $data  = $r['datavc'] ?: '';
            $hora  = $r['horavc'] ?: '';
            $title = $turma . ' • ' . $data . ' ' . $hora;
            if ($nome):
        ?>
                <span
                    class="badge rounded-pill bg-secondary-subtle border"
                    data-bs-toggle="tooltip"
                    data-bs-title="<?= e($title) ?>">
                    <?= e($nome) ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <script>
        // Inicializa tooltips neste fragmento
        (function() {
            const triggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            triggerList.forEach(function(el) {
                new bootstrap.Tooltip(el);
            });
        })();
    </script>
<?php

} catch (Throwable $e) {
    http_response_code(500);
    echo '<div class="text-danger small">Erro ao carregar views.</div>';
}
