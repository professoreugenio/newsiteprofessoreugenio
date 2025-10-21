<?php
$logoUrl = ('https://professoreugenio.com/img/logosite.png');
$ano = date('Y');
$prazoTexto = $prazoTexto ?? '';
$preheader   = 'inscrição realizado com sucesso! Agora próxima etapa selecionar um plano.';
$linkAcesso = $linkAcesso ?? '';
$linkVisualizarWeb = $linkVisualizarWeb ?? '';
$whatsapp = $whatsapp ?? '';
$nomepara = $nomepara ?? '';

$telefone = $telefone ?? $celular;
$temHoario = !empty($horario) && $horario !== '00:00:00';
$linhaHorario = $temHoario
  ? ('<tr>
        <td style="padding:6px 0; color:#6b7280;">Horário do Curso: </td>
        <td style="padding:6px 0;"><strong>' . $horario . '</strong></td>
      </tr>')
  : '';

$textHeader = $textoHeader ?? 'Nesta primeira fase a coleta de dados e reserva de sua inscrição. Confirme seus dados abaixo';
$Senha = $senha ?? '';
if (!empty($Senha)) {
  $Senha = '<tr>
        <td style="padding:6px 0; color:#6b7280;">Senha de Acesso: </td>
        <td style="padding:6px 0;"><strong>' . $senha . '</strong></td>
      </tr>';
}
$Termos = $termos ?? '';
if (!empty($Termos)) {
  $Termos = '<tr>
            <td style="padding:8px 24px 24px 24px;">
            
            <p style="margin:10px 0 0 0; font-family:Arial, sans-serif; font-size:14px; color:#6b7280;">' . $termos . '</p>
            </td>
          </tr>';
}
$Body = ('
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>' . $assunto . '</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* E-mails usam majoritariamente estilos inline; CSS aqui é mínimo */
@media (max-width: 620px){
  .container{ width:100% !important; }
  .stack{ display:block !important; width:100% !important; }
  .p-24{ padding:16px !important; }
}
</style>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4;">
  <!-- Preheader oculto -->
  <div style="display:none; font-size:1px; color:#f4f4f4; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
    ' . $preheader . '
  </div>

  <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="background-color:#f4f4f4;">
    <tr>
      <td align="center" style="padding:20px;">
        <table role="presentation" class="container" cellspacing="0" cellpadding="0" border="0" width="600" style="width:600px; max-width:600px; background:#ffffff; border-radius:10px; overflow:hidden;">
          <!-- Header / Marca -->
          <tr>
            <td style="background:#eef8f9; padding:20px 24px; text-align:left;">
              <a href="https://professoreugenio.com" style="text-decoration:none;">
                <img src="' . $logoUrl . '" alt="Professor Eugênio" height="36" style="display:block; border:0; outline:none; text-decoration:none;">
              </a>
            </td>
          </tr>

          <!-- Topo com título -->
          <tr>
            <td class="p-24" style="padding:24px;">
             <!-- <p style="margin:0 0 8px 0; font-family:Arial, sans-serif; font-size:14px; color:#6b7280;">
                <a href="' . $linkVisualizarWeb . '" style="color:#6b7280; text-decoration:underline;">Ver no navegador</a>
              </p> -->
              <h1 style="margin:8px 0 0 0; font-family:Arial, sans-serif; font-size:22px; line-height:1.3; color:#00BB9C;">
                Inscrição recebida com sucesso!
              </h1>
              <p style="margin:8px 0 0 0; font-family:Arial, sans-serif; font-size:16px; color:#111827;">
                Olá <strong>' . $nome . '</strong>, obrigado por se inscrever no curso:  <strong>' . $nmCurso . '</strong>.
              </p>
              
            </td>
          </tr>

          <!-- Barra de etapas -->
          <tr>
            <td style="padding:0 24px 8px 24px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                  <!-- Etapa 1 ativa -->
                  <td align="center" class="stack" style="padding:12px 6px;">
                    <div style="font-family:Arial, sans-serif; font-size:13px; color:#111827;">
                      <div style="display:inline-block; width:32px; height:32px; background:#00BB9C; color:#ffffff; border-radius:50%; line-height:32px; text-align:center; font-weight:bold;">1</div>
                      <div style="margin-top:6px;">Inscrição</div>
                    </div>
                  </td>
                  <!-- Etapa 2 -->
                  <td align="center" class="stack" style="padding:12px 6px;">
                    <div style="font-family:Arial, sans-serif; font-size:13px; color:#6b7280;">
                      <div style="display:inline-block; width:32px; height:32px; background:#e5e7eb; color:#6b7280; border-radius:50%; line-height:32px; text-align:center; font-weight:bold;">2</div>
                      <div style="margin-top:6px;">Plano</div>
                    </div>
                  </td>
                  <!-- Etapa 3 -->
                  <td align="center" class="stack" style="padding:12px 6px;">
                    <div style="font-family:Arial, sans-serif; font-size:13px; color:#6b7280;">
                      <div style="display:inline-block; width:32px; height:32px; background:#e5e7eb; color:#6b7280; border-radius:50%; line-height:32px; text-align:center; font-weight:bold;">3</div>
                      <div style="margin-top:6px;">Pagamento</div>
                    </div>
                  </td>
                  <!-- Etapa 4 -->
                  <td align="center" class="stack" style="padding:12px 6px;">
                    <div style="font-family:Arial, sans-serif; font-size:13px; color:#6b7280;">
                      <div style="display:inline-block; width:32px; height:32px; background:#e5e7eb; color:#6b7280; border-radius:50%; line-height:32px; text-align:center; font-weight:bold;">4</div>
                      <div style="margin-top:6px;">Senha</div>
                    </div>
                  </td>
                </tr>
              </table>
              <div style="height:4px; background:#e5e7eb; border-radius:999px; position:relative; overflow:hidden;">
                <div style="width:25%; height:100%; background:#00BB9C;"></div>
              </div>
            </td>
          </tr>

          <!-- Bloco dados enviados -->
          <tr>
            <td style="padding:16px 24px 0 24px;">

            <p style="margin:8px 0 0 0; font-family:Arial, sans-serif; font-size:15px; color:#111827;">
               '.$textHeader.'
                
              </p>
              <h2 style="margin:0 0 8px 0; font-family:Arial, sans-serif; font-size:18px; color:#FF9C00;">Seus dados enviados</h2>
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="font-family:Arial, sans-serif; font-size:15px; color:#111827;">
                <tr>
                  <td style="padding:6px 0; width:140px; color:#6b7280;">Nome</td>
                  <td style="padding:6px 0;"><strong>' . ($nome) . '</strong></td>
                </tr>
                <tr>
                  <td style="padding:6px 0; color:#6b7280;">E-mail</td>
                  <td style="padding:6px 0;"><strong>' . $emailpara . '</strong></td>
                </tr>
                ' . $Senha . '
                <tr>
                  <td style="padding:6px 0; color:#6b7280;">Celular</td>
                  <td style="padding:6px 0;"><strong>' . $telefone . '</strong></td>
                </tr>
                <tr>
                  <td style="padding:6px 0; color:#6b7280;">Curso</td>
                  <td style="padding:6px 0;"><strong>' . $nmCurso . '</strong></td>
                </tr>
                ' . $linhaHorario . '
              </table>
              <p style="margin:10px 0 0 0; font-family:Arial, sans-serif; font-size:14px; color:#6b7280;">
                ' . $prazoTexto . '
              </p>
            </td>
          </tr>

          <!-- CTA principal: continuar inscrição -->
          <!-- <tr>
            <td align="center" style="padding:20px 24px 8px 24px;">
                <a href="' . $linkContinuar . '" target="_blank"
                    style="display:inline-block; background:#FF9C00; color:#ffffff; text-decoration:none; font-family:Arial, sans-serif; font-size:16px; font-weight:bold; padding:14px 28px; border-radius:6px;">
                    Continuar minha inscrição
                </a>
              
            </td>
          </tr> -->

          <!-- CTA secundário: revisar dados -->
          ' . $Termos . '

          <!-- Avisos / Suporte -->
          <tr>
            <td style="padding:8px 24px 24px 24px;">
              <p style="margin:8px 0 0 0; font-family:Arial, sans-serif; font-size:14px; color:#111827;">
                Dúvidas? Fale com o suporte:
                <a href="professoreugeniomls@gmail.com" style="color:#0d6efd; text-decoration:none;">contato@professoreugenio.com</a>
                • <a href="https://professoreugenio.com" style="color:#0d6efd; text-decoration:none;">professoreugenio.com</a>
              </p>
              <!-- WhatsApp (opcional nesta etapa) -->
              <!-- <p style="margin:6px 0 0 0; font-family:Arial, sans-serif; font-size:14px; color:#111827;">
                Entre no grupo da turma: <a href="' . $whatsapp . '" style="color:#0d6efd; text-decoration:none;">Abrir WhatsApp</a>
              </p> -->
              <p style="margin:12px 0 0 0; font-family:Arial, sans-serif; font-size:12px; color:#6b7280;">
                Se você não realizou esta solicitação, ignore este e-mail. Este é um e-mail transacional; não é necessário descadastrar.
              </p>
            </td>
          </tr>

          <!-- Rodapé -->
          <tr>
            <td style="background:#f9fafb; padding:16px 24px; text-align:center;">
              <p style="margin:0; font-family:Arial, sans-serif; font-size:12px; color:#6b7280;">
                © ' . $ano . ' Professor Eugênio — Todos os direitos reservados.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
');
