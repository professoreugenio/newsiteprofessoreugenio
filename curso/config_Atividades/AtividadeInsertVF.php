<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';


$addtime = 60 * 60 * 6;
$duracao = time() + $addtime;

$expnav = [];
if (isset($_COOKIE['nav'])) {
    $navDescriptografado = encrypt($_COOKIE['nav'], 'd');
    if (is_string($navDescriptografado) && !empty($navDescriptografado)) {
        $expnav = explode("&", $navDescriptografado);
    } else {
        error_log("Falha ao descriptografar o cookie 'nav'.");
    }
} else {
    error_log("Cookie 'nav' não encontrado.");
}

$idquest = $expnav[5];
$idpublicacao = $expnav[4];
$iduser = null;
if (isset($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    $expUser = explode("&", $decUser);
    $iduser = $expUser[0];
} else {
    error_log("Cookie 'startusuario' não encontrado.");
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respostas'])) {
    // Obter as respostas enviadas
    $respostas = $_POST['respostas'];
    if (!empty($respostas)) {
        foreach ($respostas as $idafirmacao => $resposta) {

            $idquestvf = intval($idafirmacao);
            $resposta = trim($resposta);

            $queryInsert = $con->prepare("INSERT INTO a_curso_questionario_resposta (
idalunoqr,
idquestionarioqr,
idquestionariovf,
idaulaqr,
respostaqr,
cont,
dataqr,
horaqr
)VALUES (
:iduser,
:idquest,
:idquestvf,
:idpublicacao,
:resposta,
:cont,
:dataqr,
:horaqr
)");
            $data = date('Y-m-d');
            $hora = date('H:i:s');

            $queryInsert->bindParam(":iduser", $iduser, PDO::PARAM_INT);
            $queryInsert->bindParam(":idquest", $idquest, PDO::PARAM_INT);
            $queryInsert->bindParam(":idquestvf", $idquestvf, PDO::PARAM_INT);
            $queryInsert->bindParam(":idpublicacao", $idpublicacao, PDO::PARAM_INT);
            $queryInsert->bindParam(":resposta", $resposta, PDO::PARAM_STR);
            $queryInsert->bindValue(":cont", 1, PDO::PARAM_INT);
            $queryInsert->bindParam(":dataqr", $data, PDO::PARAM_STR);
            $queryInsert->bindParam(":horaqr", $hora, PDO::PARAM_STR);
            $queryInsert->execute();

            if ($queryInsert->rowCount() >= 1) {
                // echo '1';
                /** EXIBIE O PRÓXIMO */
                // Exemplo: ordem atual. Substitua com valor dinâmico.
                $visivel = 1;    // Supondo visível como 1


                $queryQ = $con->prepare("SELECT COUNT(*) as total FROM a_curso_questionario WHERE idpublicacaocq = :idpublic AND visivelcq='1'");
                $queryQ->bindParam(":idpublic", $idpublicacao);
                $queryQ->execute();
                $result = $queryQ->fetch(PDO::FETCH_ASSOC);
                $quantQuestoes = $result['total'];

                echo ('<div>ttl: ' . $quantQuestoes . ' Ordem: ' . $_POST['ordem'] . '</div>');


                if (!empty($_POST['ordem']) && $_POST['ordem'] < $quantQuestoes) {
                    $ordemAtual = $_POST['ordem'] + 1;
                    $queryQuestProximo = $con->prepare("SELECT * FROM a_curso_questionario 
    WHERE idpublicacaocq  = :idpublicacao 
    AND visivelcq = :visivel 
    AND ordemcq = :ordemAtual;
    ORDER BY ordemcq ASC 
    LIMIT 1");

                    $queryQuestProximo->bindParam(":idpublicacao", $idpublicacao);
                    $queryQuestProximo->bindParam(":visivel", $visivel);
                    $queryQuestProximo->bindParam(":ordemAtual", $ordemAtual);
                    $queryQuestProximo->execute();

                    $rwQuestProximo = $queryQuestProximo->fetch(PDO::FETCH_ASSOC);

                    if ($rwQuestProximo) {
                        $QuestProximo = encrypt($rwQuestProximo['codigoquestionario'], 'e');
                        $ordemProxima = $rwQuestProximo['ordemcq'];
                    } else {
                        $QuestProximo = null;
                        $ordemProxima = null;
                    }

                    $idProximo = $rwQuestProximo['codigoquestionario'];
                    /** FIMEXIBIE O PRÓXIMO */



                    $novaString = implode("&", [
                        $expnav[0],
                        $expnav[1],
                        $expnav[2],
                        $expnav[3],
                        $expnav[4],
                        $idProximo,
                        "0",
                        "0"
                    ]);

                    $navAtualizado = encrypt($novaString, 'e');
                    setcookie('nav', $navAtualizado, time() + $duracao, '/');
                }
            } else {
                echo '2';
            }
        }
    }
}
