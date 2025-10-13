<?php

$idUsuario  = $_GET['idUsuario'] ?? $_GET['id'] ?? '';
$idUsuario = encrypt($idUsuario, $action = 'd');
$stmt = $con->prepare("
    SELECT codigocadastro AS idAluno, nome, celular, pastasc, imagem50, email, senha
    FROM new_sistema_cadastro
    WHERE codigocadastro = :idusuario
    LIMIT 1
");
$stmt->bindParam(':idusuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!empty($row)) {

    $decsenha =  $dec = encrypt($row['senha'], $action = 'd');
    $expSenha = explode("&", $decsenha);
    $senha = $expSenha[1] ?? 'não registrado';
    $email = htmlspecialchars($row['email']);
    $idAluno     = $row['idAluno'] ?? '';
    $celular     = preg_replace('/[^0-9]/', '', $row['celular'] ?? '');
    $nomeArr = explode(' ', trim($row['nome']));
    $nomeAluno = htmlspecialchars($nomeArr[0] . ' ' . ($nomeArr[1] ?? ''));
    $temWhats    = strlen($celular) >= 10;

    $ultimaTurma = $ultimaTurma ?? $cursoPrincipal;

    $nome1 = htmlspecialchars($nomeArr[0]);

    $msgSenha = "*{$nome1}*, caso não se recorde de sua senha de acesso \n
        segue seus dados de acesso ao portal *professoreugenio.com* tanto por celular quanto por computador:
        E-*mail*:{$email}
        *Senha*:{$senha}
        Página de login:\nn https://professoreugenio.com/login_aluno.php?ts=" . time();

    $msgSaudacao = "*-------------*\n*{$saudacao} {$nomeAluno}*, aqui é o professor Eugênio! Tudo bem?\n";

    $msgNovidades = "*-------------*\nVenho trazer algumas novidades para você sobre o curso online do Professor!*";

    $msgAcolimento = "*{$nomeAluno}*, \nSeja bem vindo(a) \nAo curso de {$ultimaTurma}!";

    $msgAcessoGratuito = "📢 Você recebeu acesso *GRATUITO por 5 dias* ao *Curso de *Informática MASTER CLASS* do Professor Eugênio!\n💻 Aulas novas toda semana, para você assistir de qualquer lugar e a qualquer hora.\n
✅ Este é o momento de se manter atualizado, evoluir na sua formação e não deixar o aprendizado esfriar (como acontece no curso presencial, que já acabou).\n👉 Aproveite esta experiência *TOTALMENTE GRATUITA* e sinta como é ter suporte direto via WhatsApp e tira-dúvidas com o professor.\n✨ Novidade especial que você vai gostar:\nSe quiser continuar após este período gratuito, você pode escolher:\n
🔹 *Assinatura Anual:* R$ 39,90 (paga só uma vez)
🔹 *Assinatura Vitalícia:* R$ 85,00 (acesso para sempre!)\n
🚀 Não perca essa oportunidade de se manter sempre preparado e atualizado no mercado!\n\n
📲 Conte comigo no WhatsApp para suporte direto.\n\n";

    $msgOfertaPowerBI = "*{$nomeAluno}*,\n\nSe você tem interesse em continuar seu aprendizado em *Power BI* curso online com foco em dashboards e inteligência artificial, essa é sua oportunidade!\n\n💡 *Acesso Vitalício* com todo conteúdo liberado, suporte, materiais para download e atualizações gratuitas.\n\nAulas semanais abaixo:\n👉 https://professoreugenio.com/pagina_vendas.php?nav=blV1Z1R1QXpuQjgxblBwMmZjYVRxWlFFc09oMGh0SWM1SFRPaGx3RVlmMD0=&ts=1757616725\n\nFico à disposição para tirar qualquer dúvida!\n\n*Professor Eugênio*";



    $msgRedes = "{$saudacao} *{$nomeAluno}*, tudo bem?
Venho aqui pedir para me acompanhar nas redes sociais e ficar por dentro das novidades, dicas e conteúdos gratuitos!
📺 YouTube:
https://www.youtube.com/@professoreugenio
📸 Instagram:
https://instagram.com/professoreugenio
🎵 TikTok:
https://www.tiktok.com/@professoreugeniomci
Conte comigo no seu aprendizado!
Abraço,
Professor Eugênio
        ";

    $linkAcessoWhats = 'https://wa.me/55' . $celular . '?text=' . rawurlencode("Olá $nomeAluno, Precisa de ajuda?");
    $emailPromo = 'mailto:' . $row['email'] . '?subject=Promoção de Cursos&body=Olá ' . $nomeAluno . ', Novidades!\n' . $msgAcessoGratuito;
    $emailMotiv = 'mailto:' . $row['email'] . '?subject=Mensagem Motivacional&body=Continue firme, ' . $nomeAluno . '! Você está indo muito bem.';
}
?>


<?php

// function linkWhats($cel, $msg)
// {
//     $numero = preg_replace('/\D/', '', $cel);
//     if ($numero && substr($numero, 0, 2) !== '55') $numero = '55' . $numero;
//     return $numero ? 'https://wa.me/' . $numero . '?text=' . urlencode($msg) : false;
// }

?>

<?php

// function linkWhats(string $cel, string $msg): string
// {
//     // remove caracteres que não sejam dígitos do celular
//     $cel = preg_replace('/\D/', '', $cel);
//     // codifica corretamente a mensagem em UTF-8
//     $msg = rawurlencode($msg);
//     return "https://wa.me/{$cel}?text={$msg}";
// }



function linkWhats(string $cel, string $msg): string
{
    // força encoding UTF-8 antes de codificar
    if (!mb_check_encoding($msg, 'UTF-8')) {
        $msg = mb_convert_encoding($msg, 'UTF-8', 'auto');
    }

    // remove caracteres que não sejam dígitos do celular
    $cel = preg_replace('/\D/', '', $cel);

    // codifica corretamente a mensagem
    $msg = rawurlencode($msg);

    return "https://wa.me/{$cel}?text={$msg}";
}

?>


<div class="d-flex align-items-center gap-2">
    <div>
       
        <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuBtn<?= $idAluno ?>" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-send"></i> Enviar Mensagem
            </button>



            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuBtn<?= $idAluno ?>">
                <?php if ($temWhats): ?>

                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgSaudacao) ?>">
                            <i class="bi bi-whatsapp text-success"></i> WhatsApp Saudação
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgNovidades) ?>">
                            <i class="bi bi-whatsapp text-success"></i> Novidades
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgAcessoGratuito) ?>">
                            <i class="bi bi-whatsapp text-success"></i> WhatsApp Acesso Gratuito
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgAcolimento) ?>">
                            <i class="bi bi-whatsapp text-success"></i> WhatsApp Acolhimento
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgSenha) ?>">
                            <i class="bi bi-key"></i> WhatsApp Recuperar Senha
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgRedes) ?>">
                            <i class="bi bi-instagram"></i> WhatsApp Siga nas Redes
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= linkWhats('55' . $row['celular'], $msgOfertaPowerBI) ?>">
                            <i class="bi bi-instagram"></i> WhatsApp Oferta Power BI
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" target="_blank" href="<?= $linkAcessoWhats ?>">
                            <i class="bi bi-clock-history text-warning"></i> Último Acesso / Motivação
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                <?php endif; ?>
                <li>
                    <a class="dropdown-item" href="<?= $emailPromo ?>">
                        <i class="bi bi-envelope-paper"></i> E-mail Promoção
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= $emailMotiv ?>">
                        <i class="bi bi-emoji-smile"></i> E-mail Motivacional
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div>
        <span class="fw-semibold">
            <button class="btn btn-outline-success btn-sm abrirPagamentoBtn ms-2"
                data-idusuario="<?= $idAluno ?>"
                data-idturma="<?= htmlspecialchars($_GET['idturma'] ?? '') ?>"
                data-nomealuno="<?= htmlspecialchars($nomeAluno) ?>">
                <i class="bi bi-currency-dollar"></i> Pagamento
            </button>
        </span>
    </div>
</div>

<div class="row">


    <div class="col-md-4 text-end mb-3">

    </div>
</div>