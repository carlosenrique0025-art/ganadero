<?php

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

function generarArchivoReporte(
    PDO $conexion,
    int $rancho_id,
    string $tipo_reporte,
    string $titulo,
    ?string $fecha_inicio,
    ?string $fecha_fin,
    string $formato,
    string $observaciones = ''
): array {

    $columnas = [];
    $filas = [];

    /*
    |--------------------------------------------------------------------------
    | GANADO
    |--------------------------------------------------------------------------
    */

    if ($tipo_reporte === 'GANADO') {

        $sql = "
            SELECT
                g.id,
                g.arete,
                g.nombre,
                g.sexo,
                r.nombre AS raza,
                g.fecha_nacimiento,
                g.peso_actual,
                g.color,
                g.procedencia,
                g.fecha_ingreso,
                CASE
                    WHEN g.estado = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                g.observaciones
            FROM ganado g
            LEFT JOIN razas r
                ON r.id = g.raza_id
            WHERE g.rancho_id = :rancho_id
        ";

        $params = [
            ':rancho_id' => $rancho_id
        ];

        if ($fecha_inicio !== null) {
            $sql .= " AND g.fecha_ingreso >= :fecha_inicio ";
            $params[':fecha_inicio'] = $fecha_inicio;
        }

        if ($fecha_fin !== null) {
            $sql .= " AND g.fecha_ingreso <= :fecha_fin ";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $sql .= " ORDER BY g.id DESC ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $columnas = [
            'ID',
            'Arete',
            'Nombre',
            'Sexo',
            'Raza',
            'Fecha nacimiento',
            'Peso actual',
            'Color',
            'Procedencia',
            'Fecha ingreso',
            'Estado',
            'Observaciones'
        ];

        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /*
    /*
|--------------------------------------------------------------------------
| MOVIMIENTOS
|--------------------------------------------------------------------------
*/

elseif ($tipo_reporte === 'MOVIMIENTOS') {

    $sql = "
        SELECT
            m.id,
            g.arete,
            g.nombre AS ganado,
            m.tipo_movimiento,
            m.fecha_movimiento,
            m.peso,
            m.precio,
            CONCAT_WS(
                ' ',
                s.nombre,
                s.apellido_paterno,
                s.apellido_materno
            ) AS socio,
            m.origen,
            m.destino,
            m.motivo,
            m.observaciones
        FROM movimientos_ganado m
        INNER JOIN ganado g
            ON g.id = m.ganado_id
        LEFT JOIN socios s
            ON s.id = m.socio_id
            AND s.rancho_id = m.rancho_id
        WHERE m.rancho_id = :rancho_id
    ";

    $params = [
        ':rancho_id' => $rancho_id
    ];

    if ($fecha_inicio !== null) {
        $sql .= " AND m.fecha_movimiento >= :fecha_inicio ";
        $params[':fecha_inicio'] = $fecha_inicio;
    }

    if ($fecha_fin !== null) {
        $sql .= " AND m.fecha_movimiento <= :fecha_fin ";
        $params[':fecha_fin'] = $fecha_fin;
    }

    $sql .= "
        ORDER BY m.fecha_movimiento DESC, m.id DESC
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    $columnas = [
        'ID',
        'Arete',
        'Ganado',
        'Tipo movimiento',
        'Fecha',
        'Peso',
        'Precio',
        'Socio',
        'Origen',
        'Destino',
        'Motivo',
        'Observaciones'
    ];

    $filas = $stmt->fetchAll(PDO::FETCH_NUM);
}


    /*
    |--------------------------------------------------------------------------
    | SOCIOS
    |--------------------------------------------------------------------------
    */

    elseif ($tipo_reporte === 'SOCIOS') {

        $sql = "
            SELECT
                id,
                nombre,
                apellido_paterno,
                apellido_materno,
                telefono,
                email,
                direccion,
                rfc,
                curp,
                tipo,
                CASE
                    WHEN estado = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                fecha_registro
            FROM socios
            WHERE rancho_id = :rancho_id
        ";

        $params = [
            ':rancho_id' => $rancho_id
        ];

        if ($fecha_inicio !== null) {
            $sql .= " AND DATE(fecha_registro) >= :fecha_inicio ";
            $params[':fecha_inicio'] = $fecha_inicio;
        }

        if ($fecha_fin !== null) {
            $sql .= " AND DATE(fecha_registro) <= :fecha_fin ";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $sql .= " ORDER BY id DESC ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $columnas = [
            'ID',
            'Nombre',
            'Apellido paterno',
            'Apellido materno',
            'Teléfono',
            'Email',
            'Dirección',
            'RFC',
            'CURP',
            'Tipo',
            'Estado',
            'Fecha registro'
        ];

        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES
    |--------------------------------------------------------------------------
    */

    elseif ($tipo_reporte === 'PROVEEDORES') {

        $sql = "
            SELECT
                id,
                nombre,
                empresa,
                rfc,
                telefono,
                email,
                direccion,
                ciudad,
                estado_direccion,
                productos_servicios,
                CASE
                    WHEN estado = 1 THEN 'Activo'
                    ELSE 'Inactivo'
                END AS estado,
                fecha_registro
            FROM proveedores
            WHERE rancho_id = :rancho_id
        ";

        $params = [
            ':rancho_id' => $rancho_id
        ];

        if ($fecha_inicio !== null) {
            $sql .= " AND DATE(fecha_registro) >= :fecha_inicio ";
            $params[':fecha_inicio'] = $fecha_inicio;
        }

        if ($fecha_fin !== null) {
            $sql .= " AND DATE(fecha_registro) <= :fecha_fin ";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $sql .= " ORDER BY id DESC ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $columnas = [
            'ID',
            'Nombre',
            'Empresa',
            'RFC',
            'Teléfono',
            'Email',
            'Dirección',
            'Ciudad',
            'Estado dirección',
            'Productos / servicios',
            'Estado',
            'Fecha registro'
        ];

        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /*
    |--------------------------------------------------------------------------
    | GUIAS
    |--------------------------------------------------------------------------
    */

    elseif ($tipo_reporte === 'GUIAS') {

        $sql = "
            SELECT
                g.id,
                g.numero_guia,
                g.tipo,
                g.fecha_emision,
                g.fecha_vencimiento,
                g.origen,
                g.destino,
                g.transportista,
                g.placas,
                g.cantidad_animales,
                s.nombre AS socio,
                g.estado,
                g.observaciones
            FROM guias g
            LEFT JOIN socios s
                ON s.id = g.socio_id
            WHERE g.rancho_id = :rancho_id
        ";

        $params = [
            ':rancho_id' => $rancho_id
        ];

        if ($fecha_inicio !== null) {
            $sql .= " AND g.fecha_emision >= :fecha_inicio ";
            $params[':fecha_inicio'] = $fecha_inicio;
        }

        if ($fecha_fin !== null) {
            $sql .= " AND g.fecha_emision <= :fecha_fin ";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $sql .= "
            ORDER BY g.fecha_emision DESC, g.id DESC
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $columnas = [
            'ID',
            'Número guía',
            'Tipo',
            'Fecha emisión',
            'Fecha vencimiento',
            'Origen',
            'Destino',
            'Transportista',
            'Placas',
            'Cantidad animales',
            'Socio',
            'Estado',
            'Observaciones'
        ];

        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /*
    |--------------------------------------------------------------------------
    | FACTURAS
    |--------------------------------------------------------------------------
    */

    elseif ($tipo_reporte === 'FACTURAS') {

        $sql = "
            SELECT
                f.id,
                f.folio,
                f.tipo,
                f.fecha,
                f.concepto,
                f.subtotal,
                f.iva,
                f.total,
                f.metodo_pago,
                f.estado_pago,
                s.nombre AS socio,
                p.nombre AS proveedor,
                f.observaciones
            FROM facturas f
            LEFT JOIN socios s
                ON s.id = f.socio_id
            LEFT JOIN proveedores p
                ON p.id = f.proveedor_id
            WHERE f.rancho_id = :rancho_id
        ";

        $params = [
            ':rancho_id' => $rancho_id
        ];

        if ($fecha_inicio !== null) {
            $sql .= " AND f.fecha >= :fecha_inicio ";
            $params[':fecha_inicio'] = $fecha_inicio;
        }

        if ($fecha_fin !== null) {
            $sql .= " AND f.fecha <= :fecha_fin ";
            $params[':fecha_fin'] = $fecha_fin;
        }

        $sql .= "
            ORDER BY f.fecha DESC, f.id DESC
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $columnas = [
            'ID',
            'Folio',
            'Tipo',
            'Fecha',
            'Concepto',
            'Subtotal',
            'IVA',
            'Total',
            'Método pago',
            'Estado pago',
            'Socio',
            'Proveedor',
            'Observaciones'
        ];

        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAL / OTRO
    |--------------------------------------------------------------------------
    */

    elseif (
        $tipo_reporte === 'GENERAL' ||
        $tipo_reporte === 'OTRO'
    ) {

        $columnas = [
            'Concepto',
            'Cantidad'
        ];

        $consultas = [
            'Ganado' => "
                SELECT COUNT(*)
                FROM ganado
                WHERE rancho_id = :rancho_id
            ",
            'Movimientos' => "
                SELECT COUNT(*)
                FROM movimientos_ganado
                WHERE rancho_id = :rancho_id
            ",
            'Socios' => "
                SELECT COUNT(*)
                FROM socios
                WHERE rancho_id = :rancho_id
            ",
            'Guías' => "
                SELECT COUNT(*)
                FROM guias
                WHERE rancho_id = :rancho_id
            ",
            'Facturas' => "
                SELECT COUNT(*)
                FROM facturas
                WHERE rancho_id = :rancho_id
            ",
            'Proveedores' => "
                SELECT COUNT(*)
                FROM proveedores
                WHERE rancho_id = :rancho_id
            "
        ];

        if ($tipo_reporte === 'GENERAL') {
            $consultas['Ganado activo'] = "
                SELECT COUNT(*)
                FROM ganado
                WHERE rancho_id = :rancho_id
                AND estado = 1
            ";
        }

        foreach ($consultas as $nombre => $consulta) {

            $stmt = $conexion->prepare($consulta);

            $stmt->execute([
                ':rancho_id' => $rancho_id
            ]);

            $filas[] = [
                $nombre,
                $stmt->fetchColumn()
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CARPETA
    |--------------------------------------------------------------------------
    */

    $carpeta = __DIR__ . DIRECTORY_SEPARATOR . 'archivos';

    if (!is_dir($carpeta)) {

        if (!mkdir($carpeta, 0775, true)) {
            throw new Exception(
                'No se pudo crear la carpeta de archivos.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRE
    |--------------------------------------------------------------------------
    */

    $nombreSeguro = preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $titulo
    );

    $nombreSeguro = trim(
        $nombreSeguro,
        '_'
    );

    if ($nombreSeguro === '') {
        $nombreSeguro = 'reporte';
    }

    $fechaArchivo = date('Ymd_His');

    $extensiones = [
        'PDF' => 'pdf',
        'EXCEL' => 'xlsx',
        'WORD' => 'docx',
        'CSV' => 'csv'
    ];

    $extension = $extensiones[$formato];

    $nombreArchivo =
        $nombreSeguro .
        '_' .
        $fechaArchivo .
        '.' .
        $extension;

    $rutaArchivo =
        $carpeta .
        DIRECTORY_SEPARATOR .
        $nombreArchivo;

    /*
    |--------------------------------------------------------------------------
    | CSV
    |--------------------------------------------------------------------------
    */

    if ($formato === 'CSV') {

        $archivo = fopen(
            $rutaArchivo,
            'w'
        );

        if (!$archivo) {
            throw new Exception(
                'No se pudo crear el archivo CSV.'
            );
        }

        fwrite(
            $archivo,
            "\xEF\xBB\xBF"
        );

        fputcsv(
            $archivo,
            $columnas,
            ','
        );

        foreach ($filas as $fila) {

            fputcsv(
                $archivo,
                $fila,
                ','
            );
        }

        fclose($archivo);
    }

    /*
    |--------------------------------------------------------------------------
    | EXCEL
    |--------------------------------------------------------------------------
    */

    elseif ($formato === 'EXCEL') {

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Reporte');

        foreach (
            $columnas as $indice => $columna
        ) {

            $sheet->setCellValueByColumnAndRow(
                $indice + 1,
                1,
                $columna
            );
        }

        $filaExcel = 2;

        foreach ($filas as $fila) {

            foreach (
                $fila as $indice => $valor
            ) {

                $sheet->setCellValueByColumnAndRow(
                    $indice + 1,
                    $filaExcel,
                    $valor
                );
            }

            $filaExcel++;
        }

        foreach (
            range(1, count($columnas)) as $col
        ) {

            $sheet
                ->getColumnDimensionByColumn($col)
                ->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($rutaArchivo);
    }

    /*
    |--------------------------------------------------------------------------
    | WORD
    |--------------------------------------------------------------------------
    */

    elseif ($formato === 'WORD') {

        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        $section->addTitle(
            $titulo,
            1
        );

        $section->addText(
            'Tipo: ' . $tipo_reporte
        );

        $section->addText(
            'Fecha de generación: ' .
            date('d/m/Y H:i')
        );

        if (
            $fecha_inicio !== null ||
            $fecha_fin !== null
        ) {

            $section->addText(
                'Periodo: ' .
                ($fecha_inicio ?: 'Inicio') .
                ' - ' .
                ($fecha_fin ?: 'Actual')
            );
        }

        if ($observaciones !== '') {

            $section->addText(
                'Observaciones: ' .
                $observaciones
            );
        }

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80
        ]);

        $table->addRow();

        foreach ($columnas as $columna) {

            $table
                ->addCell(2000)
                ->addText(
                    (string)$columna
                );
        }

        foreach ($filas as $fila) {

            $table->addRow();

            foreach ($fila as $valor) {

                $table
                    ->addCell(2000)
                    ->addText(
                        (string)($valor ?? '')
                    );
            }
        }

        $writer = IOFactory::createWriter(
            $phpWord,
            'Word2007'
        );

        $writer->save($rutaArchivo);
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    elseif ($formato === 'PDF') {

        $options = new Options();

        $options->set(
            'isRemoteEnabled',
            true
        );

        $dompdf = new Dompdf($options);

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';

        $html .= '
            <style>
                body {
                    font-family: DejaVu Sans, sans-serif;
                    font-size: 8px;
                }

                h1 {
                    color: #1f2937;
                }

                .info {
                    margin-bottom: 15px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                th {
                    background: #198754;
                    color: white;
                    padding: 5px;
                    border: 1px solid #777;
                }

                td {
                    padding: 4px;
                    border: 1px solid #999;
                }

                tr:nth-child(even) {
                    background: #f2f2f2;
                }
            </style>
        ';

        $html .= '</head>';
        $html .= '<body>';

        $html .= '<h1>' .
            htmlspecialchars($titulo) .
            '</h1>';

        $html .= '<div class="info">';

        $html .= '<strong>Tipo:</strong> ' .
            htmlspecialchars($tipo_reporte);

        $html .= '<br>';

        $html .= '<strong>Fecha generación:</strong> ' .
            date('d/m/Y H:i');

        $html .= '<br>';

        if (
            $fecha_inicio !== null ||
            $fecha_fin !== null
        ) {

            $html .= '<strong>Periodo:</strong> ' .
                htmlspecialchars(
                    $fecha_inicio ?: 'Inicio'
                ) .
                ' - ' .
                htmlspecialchars(
                    $fecha_fin ?: 'Actual'
                );

            $html .= '<br>';
        }

        if ($observaciones !== '') {

            $html .= '<strong>Observaciones:</strong> ' .
                htmlspecialchars(
                    $observaciones
                );
        }

        $html .= '</div>';

        $html .= '<table>';

        $html .= '<thead>';
        $html .= '<tr>';

        foreach ($columnas as $columna) {

            $html .= '<th>' .
                htmlspecialchars(
                    (string)$columna
                ) .
                '</th>';
        }

        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';

        foreach ($filas as $fila) {

            $html .= '<tr>';

            foreach ($fila as $valor) {

                $html .= '<td>' .
                    htmlspecialchars(
                        (string)($valor ?? '')
                    ) .
                    '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';

        $dompdf->loadHtml($html);

        $dompdf->setPaper(
            'A4',
            'landscape'
        );

        $dompdf->render();

        file_put_contents(
            $rutaArchivo,
            $dompdf->output()
        );
    }

    if (!file_exists($rutaArchivo)) {

        throw new Exception(
            'No fue posible generar el archivo.'
        );
    }

    return [
        'nombreArchivo' => $nombreArchivo,
        'rutaArchivo' => $rutaArchivo,
        'archivoBD' => 'archivos/' . $nombreArchivo
    ];
}
