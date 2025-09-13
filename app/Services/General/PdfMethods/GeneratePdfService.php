<?php

namespace App\Services\General\PdfMethods;
use Mpdf\Mpdf; 

class GeneratePdfService implements GeneratePdfServiceInterface{
    
    public function renderPdf($view,$data,$fileName){
        $pdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $pdf->autoScriptToLang = true;
        $pdf->autoLangToFont = true;
        // Load the view into the PDF
        $view = view($view, $data)->render();
        $pdf->WriteHTML($view);
        // Output the PDF
        return $pdf->Output($fileName, 'D');
    }
}
