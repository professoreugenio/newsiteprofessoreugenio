<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

try {
    $idpub = isset($_GET['idpublicacao']) ? (int)$_GET['idpublicacao'] : 0;
    if ($idpub <= 0) throw new InvalidArgumentException('Publicação inválida.');

    // Parâmetros extras para compor o link
    $id  = isset($_GET['id'])  ? (string)$_GET['id']  : '';
    $md  = isset($_GET['md'])  ? (string)$_GET['md']  : '';
    $pub = isset($_GET['pub']) ? (string)$_GET['pub'] : '';

    $pdo = config::connect();
    $sql = "SELECT 
                codigoquestionario,
                titulocq,
                ordemcq,
                visivelcq
            FROM a_curso_questionario
            WHERE idpublicacaocq = :idpub
            ORDER BY ordemcq ASC, codigoquestionario ASC";
    $st = $pdo->prepare($sql);
    $st->bindValue(':idpub', $idpub, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo '<div class="alert alert-info mb-0"><i class="bi bi-info-circle me-2"></i>Nenhuma pergunta cadastrada.</div>';
        exit;
    }

    ob_start();
?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:90px">Ordem</th>
                    <th>Pergunta</th>
                    <th style="width:130px" class="text-center">Visível</th>
                    <th style="width:140px" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $cod = (int)$r['codigoquestionario'];
                    $vis = (int)$r['visivelcq'] === 1;
                    $badge = $vis ? 'bg-success' : 'bg-secondary';
                    $txt = $vis ? 'Visível' : 'Oculto';

                    // Monta o link com os params solicitados
                    $link = '/admin2/modelo/cursos_publicacaoQuestionarioView.php'
                        . '?id='  . rawurlencode($id)
                        . '&md='  . rawurlencode($md)
                        . '&pub=' . rawurlencode($pub)
                        . '&idQuest=' . $cod;
                ?>
                    <tr>
                        <td><span class="badge bg-dark-subtle text-dark-emphasis"><?= h($r['ordemcq']) ?></span></td>
                        <td class="fw-semibold">
                            <a class="link-primary text-decoration-none" href="<?= h($link) ?>">
                                <?= h($r['titulocq']) ?>
                            </a>
                        </td>
                        <td class="text-center"><span class="badge <?= $badge ?>"><?= $txt ?></span></td>
                        <td class="text-end">
                            <button type="button"
                                class="btn btn-sm btn-outline-danger btn-del-pergunta"
                                data-cod="<?= $cod ?>">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
    echo ob_get_clean();
} catch (Throwable $th) {
    http_response_code(400);
    echo '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle me-2"></i>'
        . h($th->getMessage()) . '</div>';
}
