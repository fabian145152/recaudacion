<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// === FILTROS ===
$mes_filtro = $_GET['mes'] ?? date('Y-m');
$chofer_filtro = isset($_GET['chofer']) ? (int)$_GET['chofer'] : 0;

// Obtener todos los choferes para el select (activos o que tengan recaudaciones históricas)
$choferes_lista = $con->query("
    SELECT id, nombre 
    FROM choferes 
    WHERE activo = 1 OR id IN (SELECT DISTINCT chofer_id FROM recauda)
    ORDER BY nombre ASC
")->fetch_all(MYSQLI_ASSOC);

// === OBTENER MOVIMIENTOS ===
$movimientos = [];
$nombre_chofer = "";
$total_general = [
    'abono' => 0,
    'voucher_bruto' => 0,
    'comision' => 0,
    'voucher_neto' => 0,
    'mp' => 0,
    'ft' => 0,
    'prestamo' => 0,
    'deposito' => 0
];

if ($chofer_filtro > 0) {
    // Buscar el nombre del chofer para el título
    $stmt_ch = $con->prepare("SELECT nombre FROM choferes WHERE id = ? LIMIT 1");
    $stmt_ch->bind_param("i", $chofer_filtro);
    $stmt_ch->execute();
    $res_ch = $stmt_ch->get_result()->fetch_assoc();
    if ($res_ch) $nombre_chofer = $res_ch['nombre'];
    $stmt_ch->close();

    // Traer recaudaciones filtrando SOLO por chofer_id
    $stmt = $con->prepare("
        SELECT r.*, v.movil AS vehiculo_movil
        FROM recauda r
        LEFT JOIN vehiculos v ON v.id = r.vehiculo_id
        WHERE r.chofer_id = ? AND r.fecha LIKE ?
        ORDER BY r.fecha ASC
    ");
    $mes_like = $mes_filtro . '-%';
    $stmt->bind_param("is", $chofer_filtro, $mes_like);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $movimientos[] = $row;

        $total_general['abono'] += floatval($row['abono_diario']);
        $total_general['voucher_bruto'] += floatval($row['voucher']);
        $total_general['comision'] += floatval($row['comision']);
        $total_general['voucher_neto'] += floatval($row['total']);
        $total_general['mp'] += floatval($row['mp']);
        $total_general['ft'] += floatval($row['ft']);

        $deposito_dia = floatval($row['total']) + floatval($row['mp']) + floatval($row['ft']);
        $total_general['deposito'] += $deposito_dia;
    }
    $stmt->close();
}
$con->close();

// Calcular balance final general
$balance_general = $total_general['abono'] - $total_general['deposito'];
$texto_balance = $balance_general > 0 ? "DEBE" : ($balance_general < 0 ? "A FAVOR" : "AL DÍA");
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reporte de Caja por Chofer</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .page-wrapper {
            max-width: 1500px;
            margin: 0 auto;
        }

        .filtros-card {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .form-select,
        .form-control {
            font-size: 0.9rem;
            border-radius: 8px;
            border: 1px solid #dfe6ef;
        }

        .btn-buscar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-buscar:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-excel {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-excel:hover {
            background: #218838;
            color: white;
            transform: translateY(-1px);
        }

        .planilla-titulo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #333;
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .planilla-subtitulo {
            font-size: 0.9rem;
            color: #666;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .tabla-planilla {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.8rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .tabla-planilla th {
            background: #f8f9fc;
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 5px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 0.7rem;
        }

        .tabla-planilla td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            text-align: right;
            vertical-align: middle;
        }

        .col-fecha {
            background: #ffeb9c;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .col-dia {
            background: #ffeb9c;
            font-weight: 700;
            text-align: center;
        }

        .celda-verde {
            background: #ccffcc;
            font-weight: 700;
            color: #006600;
        }

        .celda-roja {
            background: #ffcccc;
            font-weight: 700;
            color: #cc0000;
        }

        .celda-azul {
            background: #cce5ff;
            font-weight: 700;
            color: #004085;
        }

        .fila-total {
            background: #ffc000;
            font-weight: 800;
            font-size: 0.9rem;
        }

        .fila-total td {
            border-top: 2px solid #000;
        }

        .btn-eliminar {
            background-color: #dc3545;
            color: white !important;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-eliminar:hover {
            background-color: #a71d2a;
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="page-wrapper">
        <h1 style="text-align: center; font-size: 1.4rem; color: #333; margin-bottom: 20px;"><i class="fas fa-file-excel"></i> Reporte de Movimientos por Chofer</h1>

        <div class="filtros-card">
            <form method="GET" action="reporte_movil.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; margin: 0; width: 100%;">
                <div>
                    <label class="form-label">Mes / Año</label>
                    <input type="month" name="mes" class="form-control" value="<?php echo htmlspecialchars($mes_filtro); ?>" required>
                </div>
                <div>
                    <label class="form-label">Chofer</label>
                    <select name="chofer" class="form-select" style="min-width: 250px;" required>
                        <option value="">Seleccione un chofer...</option>
                        <?php foreach ($choferes_lista as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($chofer_filtro == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-buscar"><i class="fas fa-search"></i> Generar Reporte</button>

                <?php if ($chofer_filtro > 0 && !empty($movimientos)): ?>
                    <button type="button" onclick="exportarExcelPro()" class="btn-excel" style="margin-left: auto;">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($chofer_filtro > 0): ?>
            <div class="planilla-titulo"><?php echo htmlspecialchars($nombre_chofer); ?></div>
            <div class="planilla-subtitulo">MES: <?php echo strtoupper(date('F Y', strtotime($mes_filtro . '-01'))); ?></div>

            <?php if (empty($movimientos)): ?>
                <div class="alert alert-warning text-center">No hay movimientos registrados para este chofer en el mes seleccionado.</div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="tabla-planilla" id="tabla-exportar">
                        <thead>
                            <tr>
                                <th colspan="2">FECHA</th>
                                <th colspan="3">MÓVIL UTILIZADO</th>
                                <th>ABONO</th>
                                <th>VOUCHER</th>
                                <th>COMISIÓN</th>
                                <th>V. NETO</th>
                                <th>MP</th>
                                <th>FT</th>
                                <th>PRÉSTAMO</th>
                                <th>DEPÓSITO</th>
                                <th>ESTADO</th>
                                <th>OBSERVACIONES</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m):
                                $fecha_dt = new DateTime($m['fecha']);
                                $dia_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][$fecha_dt->format('w')];

                                $v_bruto = floatval($m['voucher']);
                                $comision = floatval($m['comision']);
                                $v_neto = floatval($m['total']);
                                $mp = floatval($m['mp']);
                                $ft = floatval($m['ft']);
                                $deposito = $v_neto + $mp + $ft;
                                $abono = floatval($m['abono_diario']);
                                $resultado = $abono - $deposito;

                                // El móvil utilizado ese día (si es que tuvo uno asignado en la recaudación)
                                $movil_utilizado = $m['vehiculo_movil'] ?? $m['movil'];

                                if ($resultado > 0) {
                                    $estado = "DEBE";
                                    $clase_estado = "celda-roja";
                                } elseif ($resultado < 0) {
                                    $estado = "A FAVOR";
                                    $clase_estado = "celda-verde";
                                } else {
                                    $estado = "AL DÍA";
                                    $clase_estado = "";
                                }
                            ?>
                                <tr>
                                    <td class="dato-fecha col-fecha" data-fecha="<?php echo $fecha_dt->format('d/m/Y'); ?>"><?php echo $fecha_dt->format('d/m/Y'); ?></td>
                                    <td class="dato-dia col-dia" data-dia="<?php echo $dia_semana; ?>"><?php echo $dia_semana; ?></td>

                                    <!-- Mostramos el móvil utilizado para tener registro, aunque la recaudación sea del chofer -->
                                    <td colspan="3" class="dato-movil" style="text-align: left; font-weight: 600;" data-movil="<?php echo htmlspecialchars($movil_utilizado); ?>">Móvil #<?php echo htmlspecialchars($movil_utilizado); ?></td>

                                    <td class="dato-abono" data-abono="<?php echo $abono; ?>">$<?php echo number_format($abono, 2, ',', '.'); ?></td>
                                    <td class="dato-voucher" data-voucher="<?php echo $v_bruto; ?>">$<?php echo number_format($v_bruto, 2, ',', '.'); ?></td>
                                    <td class="dato-comision" data-comision="<?php echo $comision; ?>">$<?php echo number_format($comision, 2, ',', '.'); ?></td>
                                    <td class="dato-vneto celda-verde" data-vneto="<?php echo $v_neto; ?>">$<?php echo number_format($v_neto, 2, ',', '.'); ?></td>
                                    <td class="dato-mp" data-mp="<?php echo $mp; ?>">$<?php echo number_format($mp, 2, ',', '.'); ?></td>
                                    <td class="dato-ft" data-ft="<?php echo $ft; ?>">$<?php echo number_format($ft, 2, ',', '.'); ?></td>
                                    <td>$0,00</td>
                                    <td class="dato-deposito celda-azul" data-deposito="<?php echo $deposito; ?>">$<?php echo number_format($deposito, 2, ',', '.'); ?></td>

                                    <td class="dato-estado <?php echo $clase_estado; ?>" data-estado="<?php echo $estado; ?> (<?php echo number_format(abs($resultado), 2, ',', '.'); ?>)" style="text-align: center;">
                                        <?php echo $estado; ?> ($<?php echo number_format(abs($resultado), 2, ',', '.'); ?>)
                                    </td>
                                    <td class="dato-obs" data-obs="<?php echo htmlspecialchars($m['observaciones']); ?>" style="text-align: left; font-size: 0.75rem;"><?php echo htmlspecialchars($m['observaciones']); ?></td>

                                    <td style="text-align: center; background-color: #fff5f5;">
                                        <a href="eliminar_recauda.php?id=<?php echo $m['id']; ?>&volver=reporte_movil.php?mes=<?php echo $mes_filtro; ?>&chofer=<?php echo $chofer_filtro; ?>"
                                            class="btn-eliminar"
                                            onclick="return confirm('¿Estás seguro de eliminar este registro? Se borrará la recaudación y la deuda de este día.');">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fila-total">
                                <td colspan="5" style="text-align: left;">TOTALES DEL MES</td>
                                <td>$<?php echo number_format($total_general['abono'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($total_general['voucher_bruto'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($total_general['comision'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($total_general['voucher_neto'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($total_general['mp'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($total_general['ft'], 2, ',', '.'); ?></td>
                                <td>$0,00</td>
                                <td>$<?php echo number_format($total_general['deposito'], 2, ',', '.'); ?></td>
                                <td colspan="2" style="text-align: center; font-size: 1rem; <?php echo $balance_general > 0 ? 'color:#721c24; background:#f8d7da;' : ($balance_general < 0 ? 'color:#155724; background:#d4edda;' : ''); ?>">
                                    <?php echo $texto_balance; ?>: $<?php echo number_format(abs($balance_general), 2, ',', '.'); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- LIBRERÍA EXCELJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        async function exportarExcelPro() {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Planilla_Chofer');

            worksheet.columns = [{
                    header: 'FECHA',
                    key: 'fecha',
                    width: 12
                },
                {
                    header: 'DIA',
                    key: 'dia',
                    width: 12
                },
                {
                    header: 'MÓVIL UTILIZADO',
                    key: 'movil',
                    width: 20
                },
                {
                    header: 'ABONO',
                    key: 'abono',
                    width: 12
                },
                {
                    header: 'VOUCHER',
                    key: 'voucher',
                    width: 12
                },
                {
                    header: 'COMISIÓN',
                    key: 'comision',
                    width: 12
                },
                {
                    header: 'V. NETO',
                    key: 'vneto',
                    width: 12
                },
                {
                    header: 'MP',
                    key: 'mp',
                    width: 12
                },
                {
                    header: 'FT',
                    key: 'ft',
                    width: 12
                },
                {
                    header: 'PRÉSTAMO',
                    key: 'prestamo',
                    width: 12
                },
                {
                    header: 'DEPÓSITO',
                    key: 'deposito',
                    width: 14
                },
                {
                    header: 'ESTADO',
                    key: 'estado',
                    width: 18
                },
                {
                    header: 'OBSERVACIONES',
                    key: 'obs',
                    width: 35
                }
            ];

            worksheet.getRow(1).font = {
                bold: true,
                color: {
                    argb: 'FFFFFFFF'
                }
            };
            worksheet.getRow(1).fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: {
                    argb: 'FF667EEA'
                }
            };
            worksheet.getRow(1).alignment = {
                vertical: 'middle',
                horizontal: 'center'
            };

            const filas = document.querySelectorAll('#tabla-exportar tbody tr');

            filas.forEach((tr) => {
                const fecha = tr.querySelector('.dato-fecha').getAttribute('data-fecha');
                const dia = tr.querySelector('.dato-dia').getAttribute('data-dia');
                const movil = tr.querySelector('.dato-movil').getAttribute('data-movil');
                const abono = parseFloat(tr.querySelector('.dato-abono').getAttribute('data-abono'));
                const voucher = parseFloat(tr.querySelector('.dato-voucher').getAttribute('data-voucher'));
                const comision = parseFloat(tr.querySelector('.dato-comision').getAttribute('data-comision'));
                const vneto = parseFloat(tr.querySelector('.dato-vneto').getAttribute('data-vneto'));
                const mp = parseFloat(tr.querySelector('.dato-mp').getAttribute('data-mp'));
                const ft = parseFloat(tr.querySelector('.dato-ft').getAttribute('data-ft'));
                const deposito = parseFloat(tr.querySelector('.dato-deposito').getAttribute('data-deposito'));
                const estadoTexto = tr.querySelector('.dato-estado').getAttribute('data-estado');
                const obs = tr.querySelector('.dato-obs').getAttribute('data-obs');

                const fila = worksheet.addRow({
                    fecha: fecha,
                    dia: dia,
                    movil: movil,
                    abono: abono,
                    voucher: voucher,
                    comision: comision,
                    vneto: vneto,
                    mp: mp,
                    ft: ft,
                    prestamo: 0,
                    deposito: deposito,
                    estado: estadoTexto,
                    obs: obs
                });

                ['abono', 'voucher', 'comision', 'vneto', 'mp', 'ft', 'prestamo', 'deposito'].forEach(col => {
                    fila.getCell(col).numFmt = '"$"#,##0.00';
                });

                if (estadoTexto.includes('DEBE')) {
                    fila.getCell('estado').fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FFFFC7CE'
                        }
                    };
                    fila.getCell('estado').font = {
                        color: {
                            argb: 'FF9C0006'
                        }
                    };
                } else if (estadoTexto.includes('A FAVOR')) {
                    fila.getCell('estado').fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FFC6EFCE'
                        }
                    };
                    fila.getCell('estado').font = {
                        color: {
                            argb: 'FF006100'
                        }
                    };
                }

                fila.getCell('fecha').fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFFFEB9C'
                    }
                };
                fila.getCell('dia').fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFFFEB9C'
                    }
                };
                fila.getCell('fecha').alignment = {
                    horizontal: 'center'
                };
                fila.getCell('dia').alignment = {
                    horizontal: 'center'
                };
                fila.getCell('estado').alignment = {
                    horizontal: 'center'
                };
            });

            const totalRow = worksheet.addRow({
                fecha: '',
                dia: '',
                movil: 'TOTALES DEL MES',
                abono: <?php echo $total_general['abono']; ?>,
                voucher: <?php echo $total_general['voucher_bruto']; ?>,
                comision: <?php echo $total_general['comision']; ?>,
                vneto: <?php echo $total_general['voucher_neto']; ?>,
                mp: <?php echo $total_general['mp']; ?>,
                ft: <?php echo $total_general['ft']; ?>,
                prestamo: 0,
                deposito: <?php echo $total_general['deposito']; ?>,
                estado: '<?php echo $texto_balance; ?>: $<?php echo number_format(abs($balance_general), 2, ',', '.'); ?>',
                obs: ''
            });

            totalRow.font = {
                bold: true,
                color: {
                    argb: 'FF333333'
                }
            };
            totalRow.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: {
                    argb: 'FFFFC000'
                }
            };

            ['abono', 'voucher', 'comision', 'vneto', 'mp', 'ft', 'prestamo', 'deposito'].forEach(col => {
                totalRow.getCell(col).numFmt = '"$"#,##0.00';
            });

            totalRow.getCell('estado').alignment = {
                horizontal: 'center'
            };

            if ('<?php echo $texto_balance; ?>' === 'DEBE') {
                totalRow.getCell('estado').fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFFFC7CE'
                    }
                };
                totalRow.getCell('estado').font = {
                    color: {
                        argb: 'FF9C0006'
                    },
                    bold: true
                };
            } else if ('<?php echo $texto_balance; ?>' === 'A FAVOR') {
                totalRow.getCell('estado').fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FFC6EFCE'
                    }
                };
                totalRow.getCell('estado').font = {
                    color: {
                        argb: 'FF006100'
                    },
                    bold: true
                };
            }

            const mes = "<?php echo $mes_filtro; ?>";
            const nombreChofer = "<?php echo addslashes($nombre_chofer); ?>".replace(/\s+/g, '_');

            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });
            saveAs(blob, `Planilla_Chofer_${nombreChofer}_${mes}.xlsx`);
        }
    </script>
</body>

</html>