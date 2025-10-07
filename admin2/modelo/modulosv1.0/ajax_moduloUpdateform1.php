<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.'];

// Verifica se os dados esperados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Coleta e validação básica
        $idModulo = encrypt(htmlspecialchars($_POST['idModulo']), 'd');
        $idModulo        = isset($idModulo) ? (int)$idModulo : 0;
        $modulo          = trim($_POST['modulo'] ?? '');
        $descricao       = trim($_POST['descricao'] ?? '');
        $valorm          = floatval($_POST['valorm'] ?? 0);
        $valorh          = floatval($_POST['valorh'] ?? 0);
        $nraulasm        = intval($_POST['nraulasm'] ?? 0);
        $ordemm          = intval($_POST['ordemm'] ?? 0);
        $bgcolor         = $_POST['bgcolor'] ?? '#ffffff';
        $visivelm        = isset($_POST['visivelm']) ? 1 : 0;
        $visivelhome     = isset($_POST['visivelhome']) ? 1 : 0;

        // Verificação mínima obrigatória
        if ($idModulo <= 0 || empty($modulo)) {
            throw new Exception('Dados inválidos para atualização.');
        }

        // Preparar e executar atualização
        $sql = "UPDATE new_sistema_modulos_PJA SET 
                    modulo = :modulo,
                    descricao = :descricao,
                    valorm = :valorm,
                    valorh = :valorh,
                    nraulasm = :nraulasm,
                    ordemm = :ordemm,
                    bgcolor = :bgcolor,
                    visivelm = :visivelm,
                    visivelhome = :visivelhome
                WHERE codigomodulos = :idModulo";

        $stmt = config::connect()->prepare($sql);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':valorm', $valorm);
        $stmt->bindParam(':valorh', $valorh);
        $stmt->bindParam(':nraulasm', $nraulasm, PDO::PARAM_INT);
        $stmt->bindParam(':ordemm', $ordemm, PDO::PARAM_INT);
        $stmt->bindParam(':bgcolor', $bgcolor);
        $stmt->bindParam(':visivelm', $visivelm, PDO::PARAM_INT);
        $stmt->bindParam(':visivelhome', $visivelhome, PDO::PARAM_INT);
        $stmt->bindParam(':idModulo', $idModulo, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $response = [
                'sucesso' => true,
                'mensagem' => 'Módulo atualizado com sucesso.'
            ];
        } else {
            $response['mensagem'] = 'Erro ao executar atualização.';
        }
    } catch (Exception $e) {
        $response['mensagem'] = $e->getMessage();
    }
} else {
    $response['mensagem'] = 'Requisição inválida.';
}

// Retorno JSON
echo json_encode($response);
