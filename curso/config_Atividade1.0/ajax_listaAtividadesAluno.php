<?php
// config_Atividade1.0/ajax_listaAtividadesAluno.php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

$idaluno  = (int)($_POST['idaluno']  ?? 0);
$idmodulo = (int)($_POST['idmodulo'] ?? 0);

if ($idaluno <= 0 || $idmodulo <= 0) {
    http_response_code(400);
    exit('<div class="alert alert-warning m-0">Parâmetros inválidos.</div>');
}

try {
    $con = config::connect();

    /**
     * 1) LISTAR TODAS AS LIÇÕES DO MÓDULO
     *    Base: a_aluno_publicacoes_cursos (pc) → todas as publicações do módulo
     *    Título: new_sistema_publicacoes_PJA (p)
     */
    $sqlLicoes = "
        SELECT 
            pc.idpublicacaopc        AS idpub,
            pc.ordempc,
            COALESCE(p.titulo, CONCAT('Publicação #', pc.idpublicacaopc)) AS titulo
        FROM a_aluno_publicacoes_cursos pc
        LEFT JOIN new_sistema_publicacoes_PJA p
               ON p.codigopublicacoes = pc.idpublicacaopc
        WHERE pc.idmodulopc = :idmodulo
        GROUP BY pc.idpublicacaopc, pc.ordempc, p.titulo
        ORDER BY pc.ordempc ASC, pc.idpublicacaopc ASC
    ";
    $stL = $con->prepare($sqlLicoes);
    $stL->bindParam(':idmodulo', $idmodulo, PDO::PARAM_INT);
    $stL->execute();
    $licoes = $stL->fetchAll(PDO::FETCH_ASSOC);

    if (!$licoes) {
        echo '<div class="alert alert-info m-0">Nenhuma lição encontrada para este módulo.</div>';
        exit;
    }

    // Montagem do HTML (será injetado em #listaatividades)
    ob_start();
?>
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Lição</th>
                    <th class="text-center" style="width:170px;">Questionários</th>
                    <th class="text-center" style="width:170px;">Respondidos</th>
                    <th class="text-center" style="width:150px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ordem = 1;

                foreach ($licoes as $L) {
                    $idpub  = (int)$L['idpub'];
                    $titulo = $L['titulo']." ". $idpub;

                    /**
                     * 2) CONTAR QUANTOS QUESTIONÁRIOS EXISTEM PARA ESTA PUBLICAÇÃO
                     *    a_curso_questionario (q)
                     *    DISTINCT por segurança
                     *    (considerando também o módulo para não misturar)
                     */
                    $sqlTotalQ = "
                SELECT COUNT(DISTINCT q.codigoquestionario) AS total
                FROM a_curso_questionario q
                WHERE q.idpublicacaocq = :idpub AND q.visivelcq = '1'
                  
            ";
                    $stT = $con->prepare($sqlTotalQ);
                    $stT->bindParam(':idpub',    $idpub,    PDO::PARAM_INT);
                    $stT->execute();
                    $totalQuestoes = (int)$stT->fetchColumn();
                    
                    /**
                     * 3) VERIFICAR RESPOSTAS DO ALUNO PARA OS QUESTIONÁRIOS DESSA PUBLICAÇÃO
                     *    a_curso_questionario_resposta (r)
                     *    Conferimos se há registros com r.idalunoqr para os ids de questionário da publicação
                     */
                    $sqlResp = "
                SELECT COUNT(DISTINCT r.idquestionarioqr) AS responded
                FROM a_curso_questionario_resposta r
                WHERE r.idalunoqr = :idaluno
                  AND r.idquestionarioqr IN (
                        SELECT q.codigoquestionario
                        FROM a_curso_questionario q
                        WHERE q.idpublicacaocq = :idpub
                          AND q.idmodulocq     = :idmodulo
                  )
            ";
                    $stR = $con->prepare($sqlResp);
                    $stR->bindParam(':idaluno',  $idaluno,  PDO::PARAM_INT);
                    $stR->bindParam(':idpub',    $idpub,    PDO::PARAM_INT);
                    $stR->bindParam(':idmodulo', $idmodulo, PDO::PARAM_INT);
                    $stR->execute();
                    $respondidas = (int)$stR->fetchColumn();

                    // STATUS
                    if ($totalQuestoes === 0) {
                        $status = '<span class="badge bg-secondary">Nenhuma atividade</span>';
                    } elseif ($respondidas >= $totalQuestoes) {
                        $status = '<span class="badge bg-success">OK</span>';
                    } else {
                        $status = '<span class="badge bg-warning text-dark">Pendente</span>';
                    }
                ?>
                    <tr>
                        <td><?= $ordem++; ?></td>
                        <td><?= htmlspecialchars($titulo); ?></td>
                        <td class="text-center"><?= $totalQuestoes; ?></td>
                        <td class="text-center"><?= $respondidas; ?></td>
                        <td class="text-center"><?= $status; ?></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
    echo ob_get_clean();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger m-0">Erro interno ao processar as atividades.</div>';
}
