<?php
namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public static function create(array $options = []): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // Get custom config or use defaults
        $customConfig = config('pdf', []);
        
        // Default configuration if pdf config doesn't exist
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
            'default_font' => 'Arial',
            'default_font_size' => 12,
            'useOTL' => true,
            'useKashida' => true,
            'tempDir' => storage_path('app/temp'),
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];

        // If custom config exists, merge it
        if (!empty($customConfig)) {
            // Verify custom font directory exists if specified
            if (isset($customConfig['custom_font_dir']) && !file_exists($customConfig['custom_font_dir'])) {
                Log::warning('Custom font directory not found: ' . $customConfig['custom_font_dir']);
            } else if (isset($customConfig['custom_font_dir'])) {
                $config['fontDir'] = array_merge($fontDirs, [$customConfig['custom_font_dir']]);
            }

            // Merge custom font data if available
            if (isset($customConfig['custom_font_data']) && is_array($customConfig['custom_font_data'])) {
                $config['fontdata'] = $fontData + $customConfig['custom_font_data'];
            }

            // Use custom default font if specified and exists
            if (isset($customConfig['default_font'])) {
                if (isset($config['fontdata'][$customConfig['default_font']])) {
                    $config['default_font'] = $customConfig['default_font'];
                } else {
                    Log::warning("Configured default font '{$customConfig['default_font']}' not found in font data");
                }
            }
        }

        // Ensure temp directory exists
        $tempDir = $config['tempDir'];
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return new Mpdf(array_merge($config, $options));
    }

    public static function renderAndStream(string $view, $data, string $fileName = '', string $dest = ''): \Illuminate\Http\Response
    {
        $mPdf = self::create();
        
        try {
            $pdfContent = view($view, $data)->render();
            
            // Validate content
            if (empty(trim($pdfContent))) {
                throw new \Exception('Rendered PDF content is empty');
            }

            // Write in chunks if content is large
            $mPdf->WriteHTML($pdfContent);

        } catch (\Throwable $e) {
            throw new \Exception('Failed to generate PDF: ' . $e->getMessage(), 500, $e);
        }

        $fileName = $fileName ?: 'document_' . date('Y_m_d') . '.pdf';

        try {
            return Response::make(
                $mPdf->Output($fileName, $dest ?: \Mpdf\Output\Destination::STRING_RETURN), 
                200, 
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                ]
            );
        } catch (\Throwable $e) {
            throw new \Exception('Failed to output PDF: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Generate POS receipt PDF
     * 
     * @param array $receiptData Receipt data array
     * @param string $dest Destination: 'I' = Inline, 'D' = Download, 'F' = File, 'S' = String
     * @return mixed
     */
    public static function generatePOSReceipt(array $receiptData, string $dest = 'S')
    {
        try {
            $filename = "receipt_" . ($receiptData['receipt_number'] ?? 'unknown') . "_" . date('Y-m-d') . ".pdf";
            
            // For 'S' destination, return string content
            if ($dest === 'S') {
                $mPdf = self::create();
                // dd($receiptData, $dest);
                $pdfContent = view('pos.receipts.standard', ['data' => $receiptData])->render();
                
                if (empty(trim($pdfContent))) {
                    throw new \Exception('Rendered PDF content is empty');
                }
                
                $mPdf->WriteHTML($pdfContent);
                return $mPdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
            }
            
            // For other destinations, use renderAndStream
            return self::renderAndStream(
                'pos.receipts.standard',
                ['data' => $receiptData],
                $filename,
                $dest
            );
            
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
