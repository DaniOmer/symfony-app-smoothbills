<?php

namespace App\Service;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartJsService
{
    private $chartBuilder;

    public function __construct(ChartBuilderInterface $chartBuilder)
    {
        $this->chartBuilder = $chartBuilder;
    }

    public function createChart($labelTitle, $labels, $data, $chartType)
    {
        switch ($chartType) {
            case 'TYPE_BAR':
                $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
                break;
            case 'TYPE_LINE':
                $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
                break;
            case 'TYPE_BUBBLE':
                $chart = $this->chartBuilder->createChart(Chart::TYPE_BUBBLE);
                break;
            case 'TYPE_PIE':
                $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);
                break;
            default:
                throw new \InvalidArgumentException("Type de graphique '$chartType' non pris en charge.");
        }

        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $labelTitle,
                    'backgroundColor' => 'rgb(72, 88, 208)',
                    'borderColor' => 'rgb(72, 88, 208)',
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => max($data) + 1000,
                ],
            ],
        ]);

        return $chart;
    }

}