<?php

namespace App\Service;

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
}
