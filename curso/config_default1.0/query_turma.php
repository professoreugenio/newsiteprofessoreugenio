<?php
$queryTurma = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codigoturma = :idsubcat");
$queryTurma->bindParam(":idsubcat", $idTurma);
$queryTurma->execute();
$rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);
if ($rwTurma) {
    $nomeTurma     = $rwTurma['nometurma'] ?  $rwTurma['nometurma'] : 'nan';
    $chaveTurma    = $rwTurma['chave'] ?? '';
    $idCurso       = $rwTurma['codcursost'] ?? '';
    $idTurma       = $rwTurma['codigoturma'] ?? '';
    $comercialTurma = $rwTurma['comercialt'] ?? '';
    $datainicio    = $rwTurma['datainiciost'] ?? '';
    $datafim       = $rwTurma['datafimst'] ?? '';
    $tipocurso     = $rwTurma['tipocurso'] ?? '';
    $horainicio    = $rwTurma['horainiciost'] ?? '';
    $horafim       = $rwTurma['horafimst'] ?? '';
    $cargahoraria  = $rwTurma['cargahorariasct'] ?? '';
    $aulas         = $rwTurma['aulasst'] ?? '';
    $lkwhats       = $rwTurma['linkwhatsapp'] ?? '';
    $descricao     = $rwTurma['texto'] ?? '';
} else {
    // Opcional: log, redirecionamento, mensagem de erro ou valor padrão
    $nomeTurma = "";
    $chaveTurma = $idCurso = $comercialTurma = $datainicio = $datafim = $descricao = "";
    $tipocurso = $horainicio = $horafim = $cargahoraria = $aulas = $lkwhats = "";
}
// Exemplo de valor vindo do banco ou formulário
$whatsapp = $rwTurma['linkwhatsapp'] ?? '';
// Define a saudação personalizada (pode ser URL-encoded para suportar acentos)
$mensagem = urlencode("Olá! Gostaria de falar com o professor sobre a turma.");
// Lógica de verificação
if (strpos($whatsapp, 'whatsapp.com') !== false) {
    // É um link de grupo
    $lkwhats = $whatsapp;
    $rotuloWats = " Entrar no grupo " . $nomeTurma;
} else {
    // É número de telefone, montar o link com mensagem
    // Remove possíveis caracteres não numéricos
    $numero = preg_replace('/\D/', '', $whatsapp);
    // Se não vier número, usa um número padrão opcional
    if (empty($numero)) {
        $numero = '5585996537577'; // fallback
    }
    $lkwhats = "https://wa.me/{$numero}?text={$mensagem}";
    $rotuloWats = "Fale com o professor";
}
