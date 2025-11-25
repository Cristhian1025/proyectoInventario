<?php
require('fpdf/fpdf.php');
require('db.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
require('queries/informe_queries.php');
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Informe de Ventas',0,1,'C');
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabla de datos
    function CreateTable($header, $data)
    {
        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(2, 11, 105);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        // Cabecera
        $w = array(40, 95, 55);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        $totalGeneral = 0;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row['fechaVenta'],'LR',0,'L',$fill);
            $this->Cell($w[1],6,utf8_decode($row['nombrecompleto']),'LR',0,'L',$fill);
            $this->Cell($w[2],6,'$'.number_format($row['total'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
            $totalGeneral += $row['total'];
        }
        // Línea de cierre
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();
        // Total
        $this->SetFont('','B');
        $this->Cell($w[0] + $w[1], 7, 'Total General:', 'T', 0, 'R');
        $this->Cell($w[2], 7, '$'.number_format($totalGeneral, 2), 'T', 0, 'R');
    }
}

if (isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;
    
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4');
    $pdf->SetFont('Arial','',12);
    
    $header = array('Fecha', 'Vendedor', 'Total Vendido');
    
    if(!empty($reportData)) {
        $pdf->CreateTable($header, $reportData);
    } else {
        $pdf->Cell(0,10,'No se encontraron resultados para el rango de fechas seleccionado.',0,1);
    }
    $pdf->Output('D', 'Informe_Ventas.pdf');
} else {
    echo "Por favor, especifique un rango de fechas.";
}
?>
