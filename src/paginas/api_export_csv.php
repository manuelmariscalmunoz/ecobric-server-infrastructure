<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    die("Acceso denegado");
}

$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Ingresos
$query_ingresos = "
    SELECT p.id as Pedido_ID, u.nombre as Cliente, p.monto_total as Ingreso_EUR, p.creado_en as Fecha
    FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.estado = 'PAGADO' AND MONTH(p.creado_en) = :mes AND YEAR(p.creado_en) = :anio
";
$stmt_ingresos = $pdo->prepare($query_ingresos);
$stmt_ingresos->execute([':mes' => $mes, ':anio' => $anio]);
$ingresos = $stmt_ingresos->fetchAll();

// Gastos
$query_gastos = "
    SELECT mi.id as Movimiento_ID, pr.nombre as Producto, mi.cantidad as Unidades, 
           (SELECT precio_suministro FROM producto_proveedor pp WHERE pp.producto_id = mi.producto_id LIMIT 1) as Costo_Unitario_EUR,
           (SELECT prov.nombre_empresa FROM proveedores prov JOIN producto_proveedor pp ON prov.id = pp.proveedor_id WHERE pp.producto_id = mi.producto_id LIMIT 1) as Nombre_Proveedor,
           mi.fecha_movimiento as Fecha_Compra
    FROM movimientos_inventario mi JOIN productos pr ON mi.producto_id = pr.id
    WHERE mi.tipo_movimiento = 'ENTRADA' AND mi.notas != 'Inventario inicial'
    AND MONTH(mi.fecha_movimiento) = :mes AND YEAR(mi.fecha_movimiento) = :anio
";
$stmt_gastos = $pdo->prepare($query_gastos);
$stmt_gastos->execute([':mes' => $mes, ':anio' => $anio]);
$gastos_raw = $stmt_gastos->fetchAll();

$gastos = [];
foreach ($gastos_raw as $g) {
    $precio = floatval($g['Costo_Unitario_EUR'] ?? 0.0);
    $proveedor = $g['Nombre_Proveedor'] ? $g['Nombre_Proveedor'] : 'No Asignado';
    $gastos[] = [
        'Ref_Movimiento' => "MOV-" . $g['Movimiento_ID'],
        'Material_Reabastecido' => $g['Producto'],
        'Proveedor' => $proveedor,
        'Unidades' => $g['Unidades'],
        'Costo_Unitario_EUR' => round($precio, 2),
        'Total_Gasto_EUR' => round($g['Unidades'] * $precio, 2),
        'Fecha_Compra' => $g['Fecha_Compra']
    ];
}

$tot_ing = 0.0;
foreach ($ingresos as $ing) {
    $tot_ing += floatval($ing['Ingreso_EUR']);
}

$tot_gas = 0.0;
foreach ($gastos as $gas) {
    $tot_gas += floatval($gas['Total_Gasto_EUR']);
}

$balance = $tot_ing - $tot_gas;

$filename = "Reporte_Financiero_{$mes}_{$anio}_" . date('YmdHis') . ".xls";

// Cabeceras para forzar descarga como Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Estilos y estructura HTML para Excel
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta charset="UTF-8">';
echo '<style>
    table { font-family: Arial, sans-serif; border-collapse: collapse; }
    td, th { padding: 5px; border: 1px solid #DDDDDD; }
    .th-blue { background-color: #1565C0; color: #FFFFFF; font-weight: bold; text-align: center; }
    .th-green { background-color: #2E7D32; color: #FFFFFF; font-weight: bold; text-align: left; }
    .th-red { background-color: #D32F2F; color: #FFFFFF; font-weight: bold; text-align: left; }
    .title-green { color: #2E7D32; font-weight: bold; font-size: 14px; border:none; }
    .title-red { color: #D32F2F; font-weight: bold; font-size: 14px; border:none; }
    .num { mso-number-format:"\#\,\#\#0\.00\ \€"; text-align: right; }
    .num-neg { mso-number-format:"\#\,\#\#0\.00\ \€"; text-align: right; color: #D32F2F; font-weight: bold; }
    .num-pos { mso-number-format:"\#\,\#\#0\.00\ \€"; text-align: right; color: #2E7D32; font-weight: bold; }
    .date-cell { text-align: center; }
</style>';
echo '</head><body><table>';

// RESUMEN CONTABLE
echo '<tr>';
echo '<th class="th-blue">Periodo Fiscal</th>';
echo '<th class="th-blue">Total Ingresos (EUR)</th>';
echo '<th class="th-blue">Total Gastos (EUR)</th>';
echo '<th class="th-blue">Balance Neto (EUR)</th>';
echo '</tr>';

$bal_class = $balance >= 0 ? "num-pos" : "num-neg";
echo "<tr>";
echo "<td style='text-align:center;'>{$mes}/{$anio}</td>";
echo "<td class='num'>{$tot_ing}</td>";
echo "<td class='num'>{$tot_gas}</td>";
echo "<td class='{$bal_class}'>{$balance}</td>";
echo "</tr>";

echo '<tr><td colspan="7" style="border:none;">&nbsp;</td></tr>';

// INGRESOS
echo "<tr><td colspan='7' class='title-green'>&gt;&gt;&gt; DETALLE DE INGRESOS (VENTAS)</td></tr>";
echo '<tr>';
echo '<th class="th-green">Pedido_ID</th>';
echo '<th class="th-green">Cliente</th>';
echo '<th class="th-green">Ingreso_EUR</th>';
echo '<th class="th-green">Fecha</th>';
echo '</tr>';

foreach ($ingresos as $ing) {
    $fecha = date('d/m/Y H:i', strtotime($ing['Fecha']));
    echo "<tr>";
    echo "<td>{$ing['Pedido_ID']}</td>";
    echo "<td>{$ing['Cliente']}</td>";
    echo "<td class='num'>{$ing['Ingreso_EUR']}</td>";
    echo "<td class='date-cell'>{$fecha}</td>";
    echo "</tr>";
}

echo '<tr><td colspan="7" style="border:none;">&nbsp;</td></tr>';

// GASTOS
echo "<tr><td colspan='7' class='title-red'>&gt;&gt;&gt; DETALLE DE GASTOS (COMPRAS A PROVEEDOR)</td></tr>";
echo '<tr>';
echo '<th class="th-red">Ref_Movimiento</th>';
echo '<th class="th-red">Material_Reabastecido</th>';
echo '<th class="th-red">Proveedor</th>';
echo '<th class="th-red">Unidades</th>';
echo '<th class="th-red">Costo_Unitario_EUR</th>';
echo '<th class="th-red">Total_Gasto_EUR</th>';
echo '<th class="th-red">Fecha_Compra</th>';
echo '</tr>';

foreach ($gastos as $gas) {
    $fecha = date('d/m/Y H:i', strtotime($gas['Fecha_Compra']));
    echo "<tr>";
    echo "<td>{$gas['Ref_Movimiento']}</td>";
    echo "<td>{$gas['Material_Reabastecido']}</td>";
    echo "<td>{$gas['Proveedor']}</td>";
    echo "<td style='text-align:center;'>{$gas['Unidades']}</td>";
    echo "<td class='num'>{$gas['Costo_Unitario_EUR']}</td>";
    echo "<td class='num'>{$gas['Total_Gasto_EUR']}</td>";
    echo "<td class='date-cell'>{$fecha}</td>";
    echo "</tr>";
}

echo '</table></body></html>';
exit;
?>
