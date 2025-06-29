<?php

namespace App\Exports;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

abstract class Export
{
    protected $data;
    protected $searchParams;
    protected $title;

    public function __construct($data, $searchParams = [], $title = 'Export')
    {
        $this->data = $data;
        $this->searchParams = $searchParams;
        $this->title = $title;
    }

    /**
     * Return collection of data to export
     */
    abstract public function getData();

    /**
     * Return headings for the export
     */
    abstract public function getHeadings(): array;

    /**
     * Export to Excel file using Spatie SimpleExcel and return download response
     */
    public function downloadExcel($filename = null)
    {
        $filename = $filename ?: $this->title . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $data = $this->getData();
        $headings = $this->getHeadings();
        
        // Create Excel file using SimpleExcel
        $writer = SimpleExcelWriter::streamDownload($filename);
        $writer->addHeader($headings);
        
        foreach ($data as $row) {
            $mappedRow = $this->mapRow($row);
            $writer->addRow($mappedRow);
        }
        
        return $writer->toBrowser();
    }

    /**
     * Export to CSV file and return download response
     */
    public function downloadCsv($filename = null): Response
    {
        $filename = $filename ?: $this->title . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $data = $this->getData();
        $headings = $this->getHeadings();
        
        // Create CSV content
        $csvContent = $this->generateCsvContent($headings, $data);
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Save Excel file to storage using SimpleExcel
     */
    public function saveExcel($path, $filename = null): string
    {
        $filename = $filename ?: $this->title . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        $fullPath = $path . '/' . $filename;
        
        $data = $this->getData();
        $headings = $this->getHeadings();
        
        // Create Excel file
        $writer = SimpleExcelWriter::create(storage_path('app/' . $fullPath));
        $writer->addHeader($headings);
        
        foreach ($data as $row) {
            $mappedRow = $this->mapRow($row);
            $writer->addRow($mappedRow);
        }
        
        return $fullPath;
    }

    /**
     * Save CSV file to storage
     */
    public function saveCsv($path, $filename = null): string
    {
        $filename = $filename ?: $this->title . '_' . date('Y-m-d_H-i-s') . '.csv';
        $fullPath = $path . '/' . $filename;
        
        $data = $this->getData();
        $headings = $this->getHeadings();
        
        // Create CSV content
        $csvContent = $this->generateCsvContent($headings, $data);
        
        Storage::put($fullPath, $csvContent);
        
        return $fullPath;
    }

    /**
     * Generate CSV content from headings and data
     */
    protected function generateCsvContent(array $headings, $data): string
    {
        $output = fopen('php://temp', 'w');
        
        // Write headings
        fputcsv($output, $headings);
        
        // Write data rows
        foreach ($data as $row) {
            $mappedRow = $this->mapRow($row);
            fputcsv($output, $mappedRow);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Map data for each row - can be overridden by child classes
     */
    protected function mapRow($row): array
    {
        if (is_array($row)) {
            return array_values($row);
        }
        
        if (is_object($row)) {
            return array_values((array) $row);
        }
        
        return [$row];
    }
}