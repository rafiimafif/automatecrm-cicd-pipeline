<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AppendOnlyExportService
{
    /**
     * Append new records to existing Excel file without duplicates
     *
     * @param string $filePath Path to the Excel file
     * @param array $existingRecords Array of existing record identifiers (key => value pairs)
     * @param array $newRecords Array of new records to append
     * @param string $startColumn Starting column (e.g., 'A', 'AE')
     * @param array $headers Column headers for new data section
     * @return array ['success' => true|false, 'newCount' => int, 'message' => string]
     */
    public static function appendRecords(
        string $filePath,
        array $existingRecords,
        array $newRecords,
        string $startColumn = 'A',
        array $headers = []
    ): array {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        try {
            // Create file if it doesn't exist
            if (!file_exists($filePath)) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();

                // Add headers if provided
                if (!empty($headers)) {
                    $columnIndex = 0;
                    foreach ($headers as $header) {
                        $sheet->setCellValueByColumnAndRow($columnIndex + 1, 1, $header);
                        $columnIndex++;
                    }
                }

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($filePath);
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $writer);
            }

            // Load existing file
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet();

            // Get the starting column index
            $startColumnIndex = self::columnLetterToIndex($startColumn);

            // Find highest used row in this column section
            $highestRow = $sheet->getHighestRow();

            // Append new records
            $nextRow = $highestRow + 1;
            $newCount = 0;

            foreach ($newRecords as $record) {
                $columnIndex = $startColumnIndex;
                foreach ($record as $value) {
                    $sheet->setCellValueByColumnAndRow($columnIndex + 1, $nextRow, $value ?? '');
                    $columnIndex++;
                }
                $nextRow++;
                $newCount++;
            }

            // Save to temp file first
            $tempPath = storage_path('app/Dataset_temp_' . time() . '.xlsx');
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($tempPath);

            // Free memory
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer, $reader);

            // Atomically replace the production file
            if (file_exists($tempPath)) {
                copy($tempPath, $filePath);
                @unlink($tempPath);
            }

            return [
                'success' => true,
                'newCount' => $newCount,
                'message' => "{$newCount} record(s) appended successfully",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'newCount' => 0,
                'message' => 'Export failed: ' . class_basename($e) . ' — ' . substr($e->getMessage(), 0, 150),
            ];
        }
    }

    /**
     * Convert column letter to index (A=0, B=1, etc.)
     */
    private static function columnLetterToIndex(string $letter): int
    {
        $index = 0;
        for ($i = 0; $i < strlen($letter); $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
