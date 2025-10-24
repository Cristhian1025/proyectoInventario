<?php
/**
 * exportar_informe.php
 *
 * Genera y exporta informes de ventas en formato PDF utilizando la librería FPDF.
 * Si la librería no está instalada, crea una clase base vacía (stub) para evitar errores de ejecución.
 * El script obtiene los datos desde la base de datos (usando queries/informe_queries.php)
 * y los presenta en una tabla dentro de un PDF.
 */

// Intentar cargar la librería FPDF si existe en la carpeta fpdf/
if (!class_exists('FPDF')) {
    $fpdfPath = __DIR__ . '/fpdf/fpdf.php';
    if (file_exists($fpdfPath)) {
        // Se incluye la librería si el archivo existe
        require_once $fpdfPath;
    } else {
        // En caso de no encontrar FPDF, se lanza una advertencia
        trigger_error('FPDF no encontrado en ' . $fpdfPath . ' — algunos exports PDF no funcionarán hasta instalar la librería.', E_USER_WARNING);
    }
}

// Si la clase FPDF aún no existe, se define un stub básico
if (!class_exists('FPDF')) {
    /**
     * Clase FPDF ficticia para evitar errores cuando la librería real no está disponible.
     * Solo contiene métodos vacíos necesarios para que el código no falle.
     */
    class FPDF {
        public function __construct() {}
        public function AliasNbPages() {}
        public function AddPage($orientation='P', $size='A4') {}
        public function SetFont($family='', $style='', $size=0) {}
        public function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false) {}
        public function Ln($h=0) {}
        public function SetY($y) {}
        public function PageNo() { return 1; }
        public function SetFillColor($r,$g,$b) {}
        public function SetTextColor($v) {}
        public function SetDrawColor($r,$g,$b) {}
        public function SetLineWidth($width) {}
        public function Output($dest='I', $name='doc.pdf') {}
    }
}

// Conexión a la base de datos y carga de las consultas del informe
require('db.php');
require('queries/informe_queries.php');

/**
 * Clase PDF personalizada que extiende FPDF
 * Define la estructura del encabezado, pie de página y tabla de datos del informe.
 */
class PDF extends FPDF
{
    /**
     * Cabecera del documento PDF.
     * Muestra el título centrado en la parte superior de la página.
     */
    function Header()
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Informe de Ventas',0,1,'C');
        $this->Ln(10);
    }

    /**
     * Pie de página del documento PDF.
     * Muestra la numeración de páginas en la parte inferior.
     */
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    /**
     * Genera una tabla con los datos del informe.
     *
     * @param array $header Encabezados de la tabla.
     * @param array $data Datos a mostrar (cada fila representa una venta).
     */
    function CreateTable($header, $data)
    {
        // Configuración inicial de colores, líneas y fuente
        $this->SetFillColor(2, 11, 105);  // Color de fondo del encabezado
        $this->SetTextColor(255);         // Texto blanco
        $this->SetDrawColor(128,0,0);     // Color de los bordes
        $this->SetLineWidth(.3);
        $this->SetFont('','B');

        // Definición de los anchos de las columnas
        $w = array(40, 95, 55);

        // Dibujar los encabezados
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();

        // Restaurar colores y fuentes para las filas de datos
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');

        // Variable para alternar colores de fila
        $fill = false;
        $totalGeneral = 0;

        // Iterar sobre cada fila de datos del informe
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row['fechaVenta'],'LR',0,'L',$fill);
            $this->Cell($w[1],6,utf8_decode($row['nombrecompleto']),'LR',0,'L',$fill);
            $this->Cell($w[2],6,'$'.number_format($row['total'], 2),'LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
            $totalGeneral += $row['total'];
        }

        // Línea final de cierre de la tabla
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();

        // Mostrar total general al final de la tabla
        $this->SetFont('','B');
        $this->Cell($w[0] + $w[1], 7, 'Total General:', 'T', 0, 'R');
        $this->Cell($w[2], 7, '$'.number_format($totalGeneral, 2), 'T', 0, 'R');
    }
}

// Verificar que se hayan recibido los parámetros de fecha
if (isset($_GET['start']) && isset($_GET['end'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $vendedorId = isset($_GET['usuario']) && $_GET['usuario'] !== '' ? $_GET['usuario'] : null;
    
    // Obtener datos del informe desde la base de datos
    $reportData = getSalesReportByDateRange($conn, $start, $end, $vendedorId);

    // Crear el PDF e iniciar configuración básica
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4');
    $pdf->SetFont('Arial','',12);
    
    // Encabezados de las columnas del informe
    $header = array('Fecha', 'Vendedor', 'Total Vendido');
    
    // Verificar si existen datos
    if(!empty($reportData)) {
        $pdf->CreateTable($header, $reportData);
    } else {
        // Mostrar mensaje si no hay resultados
        $pdf->Cell(0,10,'No se encontraron resultados para el rango de fechas seleccionado.',0,1);
    }

    // Descargar el informe generado
    $pdf->Output('D', 'Informe_Ventas.pdf');

} else {
    // Si no se reciben parámetros, mostrar advertencia
    echo "Por favor, especifique un rango de fechas.";
}
?>
