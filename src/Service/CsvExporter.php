<?php

namespace App\Service;

use App\Entity\Quotation;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
{
    public function export(array $data, array $headers, string $fileName): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($data, $headers) {
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, $headers, ';');

            foreach ($data as $row) {
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$fileName.'.csv"');

        return $response;
    }

    public function exportEntities(array $entities, array $headers, callable $dataExtractor, string $fileName): StreamedResponse
    {
        $data = array_map($dataExtractor, $entities);
        return $this->export($data, $headers, $fileName);
    }

    public function exportQuotation(Quotation $quotation): string
    {
        $headersQuotation = ['Nom', 'Total HT', 'Total TTC', 'Status', 'Client', 'Envoyé le'];
        
        $totalPriceWithoutTax = 0;
        $totalPriceWithTax = 0;

        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $quantity = $quotationHasService->getQuantity();
            $priceWithoutTax = $quotationHasService->getPriceWithoutTax();
            $priceWithTax = $quotationHasService->getPriceWithTax();

            $totalPriceWithoutTax += $priceWithoutTax * $quantity;
            $totalPriceWithTax += $priceWithTax * $quantity;
        }

        $dataQuotation = [
            [
                $quotation->getUid(),
                $totalPriceWithoutTax,
                $totalPriceWithTax,
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getSendingDate()->format('Y-m-d H:i:s'),
            ]
        ];

        $headersDetails = ['Nom du service', 'Prix HT', 'Prix TTC', 'Quantité', 'Proposé par', 'Date'];

        $quotationDetails = [];
        foreach ($quotation->getQuotationHasServices() as $quotationHasService) {
            $quotationDetails[] = [
                $quotationHasService->getService()->getName(),
                $quotationHasService->getPriceWithoutTax(),
                $quotationHasService->getPriceWithTax(),
                $quotationHasService->getQuantity(),
                $quotationHasService->getService()->getCompany()->getDenomination(),
                $quotationHasService->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, $headersQuotation);
        foreach ($dataQuotation as $row) {
            fputcsv($output, $row);
        }

        fputcsv($output, []);
        
        fputcsv($output, $headersDetails);
        foreach ($quotationDetails as $detail) {
            fputcsv($output, $detail);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
