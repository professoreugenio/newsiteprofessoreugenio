
<?php

// 1. Buscar todos os e-mails liberados do curso
$stmt = config::connect()->prepare("
    SELECT DISTINCT c.email 
    FROM new_sistema_inscricao_PJA i
    INNER JOIN new_sistema_cadastro c ON i.codigousuario = c.codigocadastro
    WHERE i.codcurso_ip = :idCurso AND (c.emailbloqueio = 0 OR c.emailbloqueio IS NULL) AND c.email IS NOT NULL AND c.email <> ''
");
$stmt->bindParam(":idCurso", $idCurso);
$stmt->execute();

$emailsArr = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $emailsArr[] = trim($row['email']);
}
$emailsCCO = implode(',', $emailsArr);

// Promoção de curso
$assuntoPromo = "🚀 Oferta Imperdível: MASTER CLASS DE INFORMÁTICA – Estude o Ano Todo!";

$corpoPromo = "Olá!

Tenho uma novidade especial para você: agora o seu aprendizado não termina mais na sala de aula presencial! Apresento a *MASTER CLASS DE INFORMÁTICA*, uma plataforma online exclusiva para dar continuidade aos seus estudos, com conteúdos novos todas as semanas.

👉 Acesso a aulas online, vídeo-aulas inéditas, tutoriais e dicas para se manter sempre atualizado!

✅ Assinatura anual por apenas *R$ 39,90*  
✅ Assinatura vitalícia por *R$ 80,00* (pague uma vez e tenha acesso para sempre!)  
✅ Teste GRÁTIS por 7 dias – Experimente sem compromisso  
✅ Novas aulas e vídeos publicados semanalmente  
✅ O melhor custo-benefício para evoluir em informática

Não perca essa chance de continuar crescendo, mesmo após o curso presencial. Aproveite para manter o ritmo dos estudos e aprender sempre mais!

Qualquer dúvida, estou à disposição.

Abraços,  
Professor Eugênio";


// Produtos para compra
$assuntoProdutos = "🛒 Produtos Exclusivos para Alunos!";
$corpoProdutos = "Olá!\n\nConheça nossos produtos exclusivos para alunos, que vão turbinar ainda mais seus estudos. Acesse nossa plataforma ou fale comigo para receber a lista completa!\n\nAbraços,\nProfessor Eugênio";

// Saudação e motivação
$assuntoSaudacao = "💡 Uma Mensagem Especial do Professor Eugênio";
$corpoSaudacao = "Olá, aluno(a)!\n\nQuero te lembrar da importância de nunca desistir dos seus sonhos e de continuar firme nos estudos. Sempre que precisar, estarei por aqui para apoiar sua jornada.\n\nConte comigo!\n\nAbraços,\nProfessor Eugênio";

// Função para gerar mailto
function mailtoAll($emailsCCO, $assunto, $corpo)
{
    return 'mailto:?bcc=' . rawurlencode($emailsCCO)
        . '&subject=' . rawurlencode($assunto)
        . '&body=' . rawurlencode($corpo);
}
?>