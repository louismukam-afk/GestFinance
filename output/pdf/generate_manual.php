<?php
require __DIR__ . '/../../vendor/autoload.php';
$options = new Dompdf\Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf\Dompdf($options);
$dompdf->loadHtml(file_get_contents(__DIR__ . '/Manuel_utilisateur_GestFinance.html'), 'UTF-8');
$dompdf->setPaper('A4');
$dompdf->render();
$canvas = $dompdf->getCanvas();
$canvas->page_text(490, 815, 'Page {PAGE_NUM} / {PAGE_COUNT}', null, 8, [0.3, 0.35, 0.4]);
file_put_contents(__DIR__ . '/Manuel_utilisateur_GestFinance.pdf', $dompdf->output());
