<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('fpdf17/fpdf.php');

class PDF extends FPDF
{
// En-tête
function Header()
{
    // Logo
    $this->Image('images/logo.jpg', 80);
  
    // Saut de ligne
    $this->Ln(20);
    
    }


}

// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'REMBOURSEMENT DE FRAIS ENGAGES',1,1,'C');
$pdf->Output();

?>
