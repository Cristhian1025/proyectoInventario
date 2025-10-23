<?php
require('fpdf/fpdf.php');
require('db.php');
require('queries/venta_querie.php');

if (!isset($_GET['id_venta']) || !is_numeric($_GET['id_venta'])) {
    die("ID de venta no válido.");
}

$idVenta = (int)$_GET['id_venta'];
$data = getVentaDetailsById($conn, $idVenta);

if (!$data) {
    die("No se encontraron datos para la venta con ID: " . $idVenta);
}

class PDF_Invoice extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo (opcional)
        // $this->Image('imagenes/logo1.png',10,6,30);
        $this->SetFont('Arial','B',18);
        $this->Cell(80);
        $this->Cell(30,10,utf8_decode('FACTURA'),0,0,'C');
        $this->Ln(20);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Gracias por su compra'),0,0,'C');
        $this->SetX(-35);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo(),0,0,'C');
    }

    // Detalles de la factura
    function InvoiceDetails($venta)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0, 7, utf8_decode('Mi Empresa S.A.S'), 0, 1);
        $this->SetFont('','',10);
        $this->Cell(0, 6, utf8_decode('Dirección: Calle Falsa 123'), 0, 1);
        $this->Cell(0, 6, utf8_decode('Teléfono: 123-4567'), 0, 1);
        $this->Ln(10);

        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Factura Nro:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, $venta['idVenta'], 0, 1);

        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Fecha:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, date("d/m/Y", strtotime($venta['fechaVenta'])), 0, 1);

        $this->SetFont('Arial','B',11);
        $this->Cell(40, 7, 'Vendedor:', 0, 0);
        $this->SetFont('','',11);
        $this->Cell(100, 7, utf8_decode($venta['vendedor']), 0, 1);

        if (!empty($venta['nombreCliente'])) {
            $this->SetFont('Arial','B',11);
            $this->Cell(40, 7, 'Cliente:', 0, 0);
            $this->SetFont('','',11);
            $this->Cell(100, 7, utf8_decode($venta['nombreCliente']), 0, 1);
        }

        if (!empty($venta['cedulaNit'])) {
            $this->SetFont('Arial','B',11);
            $this->Cell(40, 7, 'Cedula/NIT:', 0, 0);
            $this->SetFont('','',11);
            $this->Cell(100, 7, utf8_decode($venta['cedulaNit']), 0, 1);
        }
        $this->Ln(10);
    }

    // Tabla de productos
    function ItemsTable($header, $items)
    {
        $this->SetFillColor(230,230,230);
        $this->SetFont('Arial','B',10);
        $w = array(90, 30, 35, 35);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();

        $this->SetFont('Arial','',10);
        $fill = false;
        foreach($items as $item)
        {
            $this->Cell($w[0],6,utf8_decode($item['nombreProducto']),'LR',0,'L',$fill);
            $this->Cell($w[1],6,$item['cantidadVendida'],'LR',0,'C',$fill);
            $this->Cell($w[2],6,'$'.number_format($item['precioUnitario'], 2),'LR',0,'R',$fill);
            $this->Cell($w[3],6,'$'.number_format($item['importe'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w),0,'','T');
    }

    // Totales
    function Totals($venta)
    {
        $this->Ln(5);
        $this->SetFont('Arial','B',11);
        $this->Cell(120);
        $this->Cell(35, 7, 'TOTAL', 1, 0, 'C');
        $this->SetFont('','',11);
        $this->Cell(35, 7, '$'.number_format($venta['totalVenta'], 2), 1, 1, 'R');
    }
}

$pdf = new PDF_Invoice();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->InvoiceDetails($data['venta']);

$header = array('Producto', 'Cantidad', 'Precio Unit.', 'Importe');
$pdf->ItemsTable($header, $data['items']);

$pdf->Totals($data['venta']);

$pdf->Output('D', 'Factura_'.$idVenta.'.pdf');
?>