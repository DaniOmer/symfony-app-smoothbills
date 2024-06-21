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
        $headers = ['Nom', 'Status', 'Client', 'Envoyé le'];
        $data = [
            [
                $quotation->getUid(),
                $quotation->getQuotationStatus()->getName(),
                $quotation->getCustomer()->getName(),
                $quotation->getSendingDate()->format('Y-m-d H:i:s')
            ]
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
