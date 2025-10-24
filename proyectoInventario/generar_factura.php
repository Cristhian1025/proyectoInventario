<?php
/**
 * generar_factura.php
 *
 * Genera un documento PDF con los detalles de una venta (factura) usando la librería FPDF.
 * Incluye información del vendedor, productos vendidos y el total de la venta.
 * 
 * Flujo general:
 * 1. Se valida que el ID de la venta sea correcto.
 * 2. Se obtienen los datos de la venta desde la base de datos.
 * 3. Se construye un PDF con formato de factura utilizando la clase personalizada PDF_Invoice.
 */

require('fpdf.php');           // Librería FPDF para generar archivos PDF
require('db.php');                  // Conexión a la base de datos
require('queries/venta_querie.php');// Funciones para obtener los datos de venta

// Validar que se haya recibido un ID de venta válido por GET
if (!isset($_GET['id_venta']) || !is_numeric($_GET['id_venta'])) {
    die("ID de venta no válido.");
}

$idVenta = (int)$_GET['id_venta']; // Conversión segura a entero

// Obtener los datos de la venta (incluye información general e ítems vendidos)
$data = getVentaDetailsById($conn, $idVenta);

if (!$data) {
    die("No se encontraron datos para la venta con ID: " . $idVenta);
}

/**
 * Clase PDF_Invoice
 * Extiende la clase FPDF para crear una factura personalizada con encabezado, pie y tabla de productos.
 */
class PDF_Invoice extends FPDF
{
    /**
     * Cabecera de la factura.
     * Incluye el título principal (FACTURA) y opcionalmente un logo.
     */
    function Header()
    {
        // Logo (comentado, se puede habilitar si existe la imagen)
        // $this->Image('imagenes/logo1.png',10,6,30);
        $this->SetFont('Arial','B',18);
        $this->Cell(80);
        $this->Cell(30,10,utf8_decode('FACTURA'),0,0,'C');
        $this->Ln(20);
    }

    /**
     * Pie de página de la factura.
     * Muestra un mensaje de agradecimiento y el número de página.
     */
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Gracias por su compra'),0,0,'C');
        $this->SetX(-35);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo(),0,0,'C');
    }

    /**
     * Muestra los datos principales de la factura (empresa, fecha, vendedor, etc.).
     *
     * @param array $venta Datos generales de la venta.
     */
    function InvoiceDetails($venta)
    {
        // Información de la empresa emisora
        $this->SetFont('Arial','B',12);
        $this->Cell(0, 7, utf8_decode('Mi Empresa S.A.S'), 0, 1);
        $this->SetFont('','',10);
        $this->Cell(0, 6, utf8_decode('Dirección: Calle Falsa 123'), 0, 1);
        $this->Cell(0, 6, utf8_decode('Teléfono: 123-4567'), 0, 1);
        $this->Ln(10);

        // Información de la factura
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
        $this->Ln(10);
    }

    /**
     * Genera la tabla con los productos de la venta.
     *
     * @param array $header Encabezados de las columnas (Producto, Cantidad, etc.)
     * @param array $items Lista de productos vendidos.
     */
    function ItemsTable($header, $items)
    {
        // Configurar encabezado de la tabla
        $this->SetFillColor(230,230,230);
        $this->SetFont('Arial','B',10);
        $w = array(90, 30, 35, 35); // Anchos de columnas

        // Dibujar encabezados
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();

        // Dibujar filas de productos
        $this->SetFont('Arial','',10);
        $fill = false;
        foreach($items as $item)
        {
            $this->Cell($w[0],6,utf8_decode($item['nombreProducto']),'LR',0,'L',$fill);
            $this->Cell($w[1],6,$item['cantidadVendida'],'LR',0,'C',$fill);
            $this->Cell($w[2],6,'$'.number_format($item['precioUnitario'], 2),'LR',0,'R',$fill);
            $this->Cell($w[3],6,'$'.number_format($item['importe'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill; // Alterna el color de las filas
        }

        // Línea final inferior de la tabla
        $this->Cell(array_sum($w),0,'','T');
    }

    /**
     * Muestra el total general de la venta al final del documento.
     *
     * @param array $venta Datos generales de la venta (incluye totalVenta).
     */
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

// Crear el documento PDF
$pdf = new PDF_Invoice();
$pdf->AliasNbPages();
$pdf->AddPage();

// Agregar los detalles de la factura
$pdf->InvoiceDetails($data['venta']);

// Crear tabla de ítems vendidos
$header = array('Producto', 'Cantidad', 'Precio Unit.', 'Importe');
$pdf->ItemsTable($header, $data['items']);

// Mostrar totales
$pdf->Totals($data['venta']);

// Descargar el archivo PDF con el nombre "Factura_ID.pdf"
$pdf->Output('D', 'Factura_'.$idVenta.'.pdf');
?>
