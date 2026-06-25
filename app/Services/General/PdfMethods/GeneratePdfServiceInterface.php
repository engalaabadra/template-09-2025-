<?php

namespace App\Services\General\PdfMethods;

interface GeneratePdfServiceInterface{
    public function renderPdf($view,$data,$fileName);
}
