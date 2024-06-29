<?php

// src/Service/PdfGeneratorService.php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class PdfGeneratorService
{
    private $twig;
    private $factory;
    private $wrapper;

    public function __construct(Environment $twig, DompdfFactoryInterface $factory, DompdfWrapperInterface $wrapper)
    {
        $this->twig = $twig;
        $this->factory = $factory;
        $this->wrapper = $wrapper;
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

    public function showPdf(string $twigtemplate): string
    {
        $dompdf = $this->factory->create();

        $dompdf->loadHtml($twigtemplate);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function downloadPdf(string $twigtemplate, string $filename): StreamedResponse
    {
        $response = $this->wrapper->getStreamResponse($twigtemplate, $filename);

        return $response;
    }

    public function getPdfBinaryContent(string $html)
    {
        return $this->wrapper->getPdf($html);
    }
}
