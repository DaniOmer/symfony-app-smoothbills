<?php

// src/Service/PdfGeneratorService.php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGeneratorService
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generatePdf(string $template, array $data, string $outputPath): void
    {

        $html = $this->twig->render($template, $data);
        $options = new Options();
        // $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();
        file_put_contents($outputPath, $dompdf->output());
    }
}
