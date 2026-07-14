<?php
namespace Utils;

use Fpdf\Fpdf;

class ReportPDF extends Fpdf
{
    private $titleLabel;
    private $rangeLabel;

    public function __construct($titleLabel, $rangeLabel)
    {
        parent::__construct('L', 'mm', 'A4');
        $this->titleLabel = $titleLabel;
        $this->rangeLabel = $rangeLabel;
    }

    public function Header()
    {
        // Title banner
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(20, 108, 148); // --color-primary #146c94 (20, 108, 148)
        $this->Cell(0, 8, 'DOREMI APP - DORMITORY SYSTEM', 0, 1, 'L');
        
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(71, 85, 105); // Slate 600
        $this->Cell(0, 6, strtoupper($this->titleLabel), 0, 1, 'L');
        
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 116, 139); // Slate 500
        $this->Cell(0, 5, 'Periode: ' . $this->rangeLabel, 0, 1, 'L');
        
        $this->Ln(3);
        
        // Draw dividing line
        $this->SetDrawColor(20, 108, 148);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 287, $this->GetY());
        $this->Ln(5);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(148, 163, 184); // Slate 400
        
        // Center: Page number
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'C');
        
        // Right: Download time
        $this->Cell(0, 10, 'Diunduh pada: ' . date('d/m/Y H:i'), 0, 0, 'R');
    }
}

/**
 * Generate and download a PDF report table.
 *
 * @param string $filename File name for download
 * @param string $title Report Title
 * @param string $rangeLabel Text showing date range
 * @param array $headers Header columns text
 * @param array $widths Numeric widths in mm for each column
 * @param array $rows 2D array containing row values
 */
function generateReportPdf($filename, $title, $rangeLabel, $headers, $widths, $rows)
{
    $pdf = new ReportPDF($title, $rangeLabel);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Table Header styling
    $pdf->SetFillColor(20, 108, 148); // Primary (#146c94)
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(226, 232, 240); // borders
    $pdf->SetLineWidth(0.2);
    $pdf->SetFont('Arial', 'B', 9);
    
    for ($i = 0; $i < count($headers); $i++) {
        $pdf->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
    }
    $pdf->Ln();
    
    // Table Body styling
    $pdf->SetTextColor(51, 65, 85); // dark grey
    $pdf->SetFont('Arial', '', 8.5);
    
    $fill = false;
    foreach ($rows as $row) {
        $pdf->SetFillColor(248, 250, 252); // Alternating light gray
        
        for ($i = 0; $i < count($row); $i++) {
            $text = (string)$row[$i];
            
            // Handle charset encoding to Windows-1252 for FPDF standard fonts
            if (function_exists('iconv')) {
                $text = iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
            }
            
            // Alignments
            $align = 'L';
            $headerText = $headers[$i];
            if ($headerText === 'No' || $headerText === 'Kamar' || $headerText === 'Status' || $headerText === 'NIM' || $headerText === 'Durasi' || $headerText === 'Tipe') {
                $align = 'C';
            }
            
            // Truncate text if it overflows the column width to keep clean rows
            $colWidth = $widths[$i];
            $textWidth = $pdf->GetStringWidth($text);
            if ($textWidth > $colWidth - 2) {
                while ($pdf->GetStringWidth($text . '...') > $colWidth - 2 && strlen($text) > 0) {
                    $text = substr($text, 0, -1);
                }
                $text .= '...';
            }
            
            $pdf->Cell($colWidth, 7, $text, 1, 0, $align, $fill);
        }
        $pdf->Ln();
        $fill = !$fill;
    }
    
    $pdf->Output('D', $filename);
}
