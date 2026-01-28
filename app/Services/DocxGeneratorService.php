<?php

namespace App\Services;

use App\Models\TripReport;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

class DocxGeneratorService
{
    protected PhpWord $phpWord;

    public function __construct()
    {
        $this->phpWord = new PhpWord();
        $this->setupStyles();
    }

    protected function setupStyles(): void
    {
        $this->phpWord->addFontStyle('titleStyle', ['bold' => true, 'size' => 14]);
        $this->phpWord->addFontStyle('subtitleStyle', ['bold' => true, 'size' => 12]);
        $this->phpWord->addFontStyle('normalStyle', ['size' => 11]);
        $this->phpWord->addFontStyle('boldStyle', ['bold' => true, 'size' => 11]);

        $this->phpWord->addParagraphStyle('centerStyle', ['alignment' => Jc::CENTER]);
        $this->phpWord->addParagraphStyle('rightStyle', ['alignment' => Jc::END]);
    }

    /**
     * Generate DOCX laporan perjalanan dinas
     */
    public function generateTripReport(TripReport $report): string
    {
        $section = $this->phpWord->addSection();

        // Header
        $section->addText('LAPORAN PERJALANAN DINAS', 'titleStyle', 'centerStyle');
        $section->addText($report->spd->spd_number, 'normalStyle', 'centerStyle');
        $section->addTextBreak(2);

        // Info Pegawai
        $this->addInfoRow($section, 'Nama', $report->employee->name);
        $this->addInfoRow($section, 'NIP', $report->employee->nip);
        $this->addInfoRow($section, 'Pangkat/Golongan', $report->employee->rank);
        $this->addInfoRow($section, 'Jabatan', $report->employee->position);
        $this->addInfoRow($section, 'Unit Kerja', $report->employee->unit->name);
        $section->addTextBreak();

        // Info Perjalanan
        $this->addInfoRow($section, 'Tujuan', $report->spd->destination);
        $this->addInfoRow($section, 'Tanggal Berangkat', $report->actual_departure_date->format('d F Y'));
        $this->addInfoRow($section, 'Tanggal Kembali', $report->actual_return_date->format('d F Y'));
        $this->addInfoRow($section, 'Lama Perjalanan', $report->actual_duration . ' hari');
        $section->addTextBreak();

        // Isi Perjalanan
        $section->addText('I. ISI PERJALANAN', 'subtitleStyle');
        $section->addTextBreak();

        foreach ($report->activities as $activity) {
            $section->addText(
                $activity->date->format('d F Y') . ' (' . $activity->time . ')',
                'boldStyle'
            );
            $section->addText('Lokasi: ' . $activity->location, 'normalStyle');
            $section->addText($activity->description, 'normalStyle');
            $section->addTextBreak();
        }

        // Output Perjalanan
        $section->addText('II. OUTPUT PERJALANAN', 'subtitleStyle');
        $section->addTextBreak();

        $listNum = 1;
        foreach ($report->outputs as $output) {
            $section->addText($listNum . '. ' . $output->description, 'normalStyle');
            $listNum++;
        }
        $section->addTextBreak(2);

        // Tanda Tangan
        $section->addText(
            ($report->employee->unit->location ?? 'Jakarta') . ', ' . now()->format('d F Y'),
            'normalStyle',
            'rightStyle'
        );
        $section->addTextBreak(4);
        $section->addText($report->employee->name, 'boldStyle', 'rightStyle');
        $section->addText('NIP. ' . $report->employee->nip, 'normalStyle', 'rightStyle');

        // Save to temp file
        $filename = 'Laporan_' . str_replace(['/', '\\'], '-', $report->spd->spd_number) . '.docx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
        $writer->save($tempPath);

        return $tempPath;
    }

    protected function addInfoRow($section, string $label, string $value): void
    {
        $textRun = $section->addTextRun();
        $textRun->addText($label, 'boldStyle');
        $textRun->addText(': ' . $value, 'normalStyle');
    }

    /**
     * Generate from custom template
     */
    public function generateFromTemplate(string $templatePath, array $placeholders): string
    {
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        foreach ($placeholders as $key => $value) {
            $templateProcessor->setValue($key, $value);
        }

        $filename = 'Generated_' . time() . '.docx';
        $outputPath = storage_path('app/temp/' . $filename);

        $templateProcessor->saveAs($outputPath);

        return $outputPath;
    }
}
