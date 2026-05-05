<!-- Modal Exportar Excel Premium -->
<style>
    .excel-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        align-items: center;
        justify-content: center;
    }

    .excel-modal-content {
        background-color: #fff;
        padding: 2.5rem;
        border-radius: 12px;
        width: 450px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .excel-close {
        float: right;
        font-size: 1.5rem;
        cursor: pointer;
        color: #aaa;
        transition: color 0.2s;
    }

    .excel-close:hover {
        color: #333;
    }
</style>

<div id="excelExportModal" class="excel-modal">
    <div class="excel-modal-content">
        <span class="excel-close" onclick="closeExcelModal()">&times;</span>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-file-csv" style="font-size: 3rem; color: #207245; margin-bottom: 1rem;"></i>
            <h2 style="color:var(--primary-dark); margin:0;">Reporte Financiero</h2>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.5rem;">Selecciona el periodo fiscal a
                exportar CSV</p>
        </div>

        <form id="excelExportForm"
            action="<?php echo strpos($_SERVER['SCRIPT_NAME'], '/paginas/') !== false ? '' : 'paginas/'; ?>api_export_csv.php"
            method="GET">
            <div class="admin-form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: 600;">Mes</label>
                <select name="mes" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-size:1rem;">
                    <?php
                    $meses = ["1" => "Enero", "2" => "Febrero", "3" => "Marzo", "4" => "Abril", "5" => "Mayo", "6" => "Junio", "7" => "Julio", "8" => "Agosto", "9" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"];
                    $mes_actual = date('n');
                    foreach ($meses as $num => $nombre):
                        ?>
                        <option value="<?php echo str_pad($num, 2, '0', STR_PAD_LEFT); ?>" <?php echo $num == $mes_actual ? 'selected' : ''; ?>>
                            <?php echo $nombre; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 600;">Año</label>
                <select name="anio" required
                    style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-size:1rem;">
                    <?php
                    $anio_actual = date('Y');
                    for ($y = 2025; $y <= $anio_actual + 1; $y++):
                        ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $anio_actual ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; font-size:1.1rem; padding: 0.8rem; background-color: #207245; border-color: #207245;">
                <i class="fa-solid fa-download"></i> Descargar Reporte CSV
            </button>
        </form>
    </div>
</div>

<script>
    const excelModal = document.getElementById('excelExportModal');

    function openExcelModal(e) {
        if (e) e.preventDefault();
        excelModal.style.display = 'flex';
    }

    function closeExcelModal() {
        excelModal.style.display = 'none';
    }

    // Attach to all elements with class 'btn-export-excel-trigger' or specific IDs
    document.addEventListener("DOMContentLoaded", function () {
        // Enlazar a botones de la sidebar de los distintos paneles
        const btn1 = document.getElementById('btn-export-excel');
        const btn2 = document.getElementById('btn-export-excel-modal');
        // Buscar por selector genérico también por si actualizamos el menú de todos
        const sidebarLinks = document.querySelectorAll('.admin-nav a');

        sidebarLinks.forEach(link => {
            if (link.innerHTML.includes('Reporte Excel')) {
                link.addEventListener('click', openExcelModal);
            }
        });

        if (btn1) btn1.addEventListener('click', openExcelModal);
        if (btn2) btn2.addEventListener('click', openExcelModal);

        // Cerrar al enviar el formulario (y mostrar un pequeño delay para que baje)
        document.getElementById('excelExportForm').addEventListener('submit', function () {
            setTimeout(closeExcelModal, 500);
        });
    });

    window.addEventListener('click', function (event) {
        if (event.target == excelModal) {
            closeExcelModal();
        }
    });
</script>