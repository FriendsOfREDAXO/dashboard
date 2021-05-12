<?php

/**
 * @package yakamara\dashboard
 */
abstract class rex_dashboard_item_chart_line extends rex_dashboard_item_chart
{
    protected $chartType = 'line';

    public function getData()
    {
        $datasets = [];
        $colors = $this->colors;
        foreach ($this->getChartData() as $name => $data) {
            $chartData = [];
            $labels = [];
            foreach ($data as $label => $value) {
                if (is_array($value)) {
                    if (array_key_exists('label', $value) and array_key_exists('value', $value)) {
                        $label = $value['label'];
                        $value = $value['value'];
                    } else {
                        $label = array_shift($value);
                        $value = array_shift($value);
                    }
                }

                $labels[] = $label;
                $chartData[] = $value;
            }

            $color = array_shift($colors);
            if (is_array($color)) {
                if (isset($color[1])) {
                    $color = $color[1];
                }
                else {
                    $color = $color[0];
                }
            }

            $datasets[] = [
                'label' => $name ?: $this->name,
                'data' => $chartData,
                'borderColor' => $color ?? null,
            ];
        }

        return '<canvas id="dashboard-chart-' . $this->getId() . '"></canvas>
                    <script>
                    new Chart(document.getElementById("dashboard-chart-' . $this->getId() . '"), {
                        type: "' . $this->chartType . '",
                        data: ' . json_encode([
                            'labels' => $labels,
                            'datasets' => $datasets,
                        ]) . ',
                    });
                    </script>
                ';
    }
}
