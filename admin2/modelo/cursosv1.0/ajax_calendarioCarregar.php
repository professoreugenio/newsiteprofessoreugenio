<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

function gerarCalendarioHTML($datainicio, $datafim, $datasRegistradas)
{
    $html = '';

    $inicio = new DateTime($datainicio);
    $fim = new DateTime($datafim);
    $fim->modify('last day of this month');

    while ($inicio <= $fim) {
        $ano = $inicio->format('Y');
        $mes = $inicio->format('m');

        // Nome do mês em português
        $nomeMes = ucfirst(utf8_encode(strftime('%B', $inicio->getTimestamp())));

        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
        $primeiroDiaSemana = (new DateTime("$ano-$mes-01"))->format('w');

        $html .= "<div class='calendario-mes'>";
        $html .= "<h6 class='mb-2 text-center fw-bold'>$nomeMes de $ano</h6>";

        // Cabeçalho dos dias da semana
        $html .= "<div class='semana mb-2'>";
        $diasSemana = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];
        foreach ($diasSemana as $dia) {
            $html .= "<div class='text-center fw-bold'>$dia</div>";
        }
        $html .= "</div>";

        // Dias do mês
        $html .= "<div class='dias'>";
        for ($i = 0; $i < $primeiroDiaSemana; $i++) {
            $html .= "<div class='calendario-dia desabilitado'></div>";
        }

        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $dataFormatada = "$ano-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
            $ativo = in_array($dataFormatada, $datasRegistradas) ? 'ativo' : '';
            $html .= "<div class='calendario-dia $ativo text-center' data-data='$dataFormatada'>$dia</div>";
        }
        $html .= "</div>"; // .dias
        $html .= "</div>"; // .calendario-mes

        $inicio->modify('first day of next month');
    }

    return $html;
}

try {
    if (!isset($_POST['datainicio'], $_POST['datafim'], $_POST['idturma'])) {
        throw new Exception("Dados incompletos.");
    }

    $datainicio = $_POST['datainicio'];
    $datafim = $_POST['datafim'];
    $idturma = $_POST['idturma'];

    $stmt = config::connect()->prepare("
        SELECT dataaulactd 
        FROM new_sistema_cursos_turma_data 
        WHERE codigoturmactd = :idturma
    ");
    $stmt->bindParam(":idturma", $idturma);
    $stmt->execute();

    $datasRegistradas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo gerarCalendarioHTML($datainicio, $datafim, $datasRegistradas);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
}
