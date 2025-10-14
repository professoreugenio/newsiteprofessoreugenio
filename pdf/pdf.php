<?php
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

$mpdf = new Mpdf();

$mpdf->SetHTMLHeader('<div style="text-align:center;">Cabeçalho do PDF</div><hr>');
$mpdf->SetHTMLFooter('<hr><div style="text-align:center;">Página {PAGENO}</div>');

$html = '
    <h1>Olá, Professor!</h1>
    <p>Este PDF contém texto, imagens e paginação.</p>
    <img src="imagens/logo.png" width="200" />
';

$mpdf->WriteHTML($html);
$mpdf->Output();
