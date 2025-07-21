<?php

namespace App\Services\Production;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class GanttChartService
{
    /**
     * Genera il codice SVG per un diagramma di Gantt a partire da un array di dati delle fasi.
     *
     * @param array $phasesData
     * @return string|null
     */
    public function generateForData(array $phasesData): ?string
    {
        if (empty($phasesData)) {
            return null;
        }

        // Converti le date stringa in oggetti Carbon se necessario
        $phasesData = array_map(function ($phase) {
            $phase['scheduled_start_time'] = Carbon::parse($phase['scheduled_start_time']);
            $phase['scheduled_end_time'] = Carbon::parse($phase['scheduled_end_time']);
            return $phase;
        }, $phasesData);
        
        $phasesCollection = collect($phasesData);

        $startDate = $phasesCollection->min('scheduled_start_time');
        $endDate = $phasesCollection->max('scheduled_end_time');

        if (!$startDate || !$endDate) return null;

        // --- NUOVA LOGICA PER GESTIRE SOLO I GIORNI LAVORATIVI ---
        $workdayMap = [];
        $currentDate = $startDate->copy();
        $workdayIndex = 0;
        while ($currentDate <= $endDate) {
            // Considera lavorativi i giorni da Lunedì (1) a Venerdì (5)
            if ($currentDate->isWeekday()) {
                $workdayMap[$currentDate->format('Y-m-d')] = $workdayIndex;
                $workdayIndex++;
            }
            $currentDate->addDay();
        }
        $totalWorkdays = count($workdayMap);
        if ($totalWorkdays === 0) return null;
        // --- FINE NUOVA LOGICA ---

        $ganttWidth = 1200;
        $rowHeight = 40;
        $headerHeight = 50;
        $padding = 20;
        
        $pixelsPerWorkday = $totalWorkdays > 0 ? $ganttWidth / $totalWorkdays : 0;
        $ganttHeight = ($phasesCollection->count() * $rowHeight) + $headerHeight + $padding;
        $svg = "<svg width=\"{$ganttWidth}\" height=\"{$ganttHeight}\" xmlns=\"http://www.w3.org/2000/svg\" style=\"font-family: sans-serif; background-color: #f9fafb;\">";

        // Header (mostra solo i giorni lavorativi)
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if ($currentDate->isWeekday()) {
                $x = $workdayMap[$currentDate->format('Y-m-d')] * $pixelsPerWorkday;
                $svg .= "<text x=\"{$x}\" y=\"30\" font-size=\"12\">{$currentDate->format('d/m')}</text>";
            }
            $currentDate->addDay();
        }

        // Rows
        $y = $headerHeight;
        foreach ($phasesCollection->sortBy('scheduled_start_time') as $index => $phase) {
            // Sfondo riga
            $rowColor = $index % 2 == 0 ? '#ffffff' : '#f9fafb';
            $svg .= "<rect x=\"0\" y=\"{$y}\" width=\"{$ganttWidth}\" height=\"{$rowHeight}\" fill=\"{$rowColor}\" />";

            $phaseStartDate = $phase['scheduled_start_time'];
            $phaseEndDate = $phase['scheduled_end_time'];

            // Calcola offset e durata basandosi sulla mappa dei giorni lavorativi
            $startWorkdayIndex = $workdayMap[$phaseStartDate->format('Y-m-d')] ?? null;
            if ($startWorkdayIndex === null) continue; // Salta fasi che iniziano in un weekend

            $durationWorkdays = 0;
            $checkDate = $phaseStartDate->copy();
            while($checkDate < $phaseEndDate) {
                if($checkDate->isWeekday()) {
                    $durationWorkdays++;
                }
                $checkDate->addDay();
            }

            $x = $startWorkdayIndex * $pixelsPerWorkday;

            // --- NUOVA LOGICA PER LA LARGHEZZA DELLA BARRA ---
            $durationWidth = max(1, $durationWorkdays * $pixelsPerWorkday);

            $labelText = ($phase['name'] ?? 'N/D') . " (" . ($phase['workstation_name'] ?? 'N/D') . ")";
            // Stima approssimativa della larghezza del testo in pixel (caratteri * 7px/carattere + padding)
            $textWidth = (strlen($labelText) * 7) + 10; 

            // La larghezza della barra è il massimo tra la durata e lo spazio per il testo
            $width = max($durationWidth, $textWidth);
            // --- FINE NUOVA LOGICA ---

            $color = $phase['color'] ?? '#4299e1';
            $textColor = $this->getContrastColor($color);
            $textStrokeColor = ($textColor === '#ffffff') ? 'black' : 'white';
            
            $barY = $y + ($rowHeight * 0.1);
            $barHeight = $rowHeight * 0.8;
            $textY = $y + ($rowHeight * 0.55);

            // Disegna la barra e il testo
            $svg .= "<rect x=\"{$x}\" y=\"{$barY}\" width=\"{$width}\" height=\"{$barHeight}\" fill=\"{$color}\" rx=\"3\" ry=\"3\"></rect>";
            // Bordo per il testo
            $svg .= "<text x=\"" . ($x + 5) . "\" y=\"{$textY}\" font-size=\"12\" font-weight=\"bold\" fill=\"{$textStrokeColor}\" stroke=\"{$textStrokeColor}\" stroke-width=\"2\" style=\"paint-order: stroke;\">" . htmlspecialchars($labelText) . "</text>";
            // Testo principale
            $svg .= "<text x=\"" . ($x + 5) . "\" y=\"{$textY}\" font-size=\"12\" font-weight=\"bold\" fill=\"{$textColor}\">" . htmlspecialchars($labelText) . "</text>";
            
            $y += $rowHeight;
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Determina se usare testo bianco o nero in base alla luminosità del colore di sfondo.
     *
     * @param string $hexColor
     * @return string
     */
    private function getContrastColor(string $hexColor): string
    {
        $hexColor = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
} 