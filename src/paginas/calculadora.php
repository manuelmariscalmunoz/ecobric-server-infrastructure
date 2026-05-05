<?php
require_once '../config/db.php';

// Obtener productos calculables
$stmt = $pdo->query("SELECT * FROM productos WHERE es_calculable_volumen = 1");
$productosCalculables = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="page-header"
    style="background-color: var(--primary-light); padding: 4rem 0; color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: 0.5rem;"><i class="fa-solid fa-calculator"></i> Calculadora de
            Materiales</h1>
        <p>Calcula exactamente lo que necesitas o elige un proyecto preconfigurado.</p>
    </div>
</div>

<section class="section-padding">
    <div class="container">

        <!-- Calculadora de Volumen (Única Pestaña) -->
        <div id="tab-volumen" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
            <!-- Formulario -->
            <div
                style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                <h3 style="margin-bottom: 1.5rem;">Dimensiones de tu obra</h3>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div>
                        <label style="font-weight: 500; display: block; margin-bottom: 0.5rem;">Selecciona el material a
                            calcular</label>
                        <select id="calc-material"
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="">-- Elige un material --</option>
                            <?php foreach ($productosCalculables as $prod): ?>
                                <option value="<?php echo $prod['id']; ?>"
                                    data-rendimiento="<?php echo $prod['rendimiento_por_m3']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                    data-precio="<?php echo $prod['precio']; ?>">
                                    <?php echo htmlspecialchars($prod['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div>
                            <label>Alto (m)</label>
                            <input type="number" id="calc-alto" step="0.01" min="0" placeholder="Ej: 2.50"
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        <div>
                            <label>Ancho (m)</label>
                            <input type="number" id="calc-ancho" step="0.01" min="0" placeholder="Ej: 4.00"
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                        <div>
                            <label>Prof. (m)</label>
                            <input type="number" id="calc-prof" step="0.01" min="0" placeholder="Ej: 0.15"
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                        </div>
                    </div>

                    <button onclick="calcularVolumen()" class="btn btn-accent"
                        style="width: 100%; margin-top: 1rem;">Calcular Necesidad</button>
                </div>
            </div>

            <!-- Resultados -->
            <div
                style="background: var(--bg-light); padding: 2rem; border-radius: var(--border-radius); border: 2px dashed var(--primary-color); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <h3 style="color: var(--primary-dark); margin-bottom: 1rem;">Resultado Estimado</h3>

                <div id="resultado-vacio">
                    <i class="fa-solid fa-ruler-combined"
                        style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p style="color: var(--text-muted);">Introduce las dimensiones para ver el cálculo.</p>
                </div>

                <div id="resultado-lleno" style="display: none; width: 100%;">
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">Volumen Total: <strong
                            id="res-volumen">0</strong> m³</div>
                    <div style="font-size: 1.1rem; margin-bottom: 1.5rem; color: var(--text-muted);">Material: <span
                            id="res-material-nombre">Ninguno</span></div>

                    <div
                        style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary-color);"
                            id="res-cantidad">0</div>
                        <div style="font-weight: 500;">Unidades (Aprox.)</div>
                    </div>

                    <div style="font-size: 1.3rem; font-weight: bold; margin-bottom: 1.5rem;">Presupuesto: <span
                            id="res-precio" style="color: var(--accent-color);">0,00</span> €</div>

                    <button class="btn btn-primary" onclick="addCalculadoAlCarrito()" style="width: 100%;"><i
                            class="fa-solid fa-cart-plus"></i> Añadir esta cantidad al carrito</button>
                </div>
            </div>
        </div>

        <script>
            // Lógica de cálculo
            function calcularVolumen() {
                const selector = document.getElementById('calc-material');
                const opcion = selector.options[selector.selectedIndex];

                if (!opcion.value) {
                    alert("Por favor, selecciona un material primero.");
                    return;
                }

                const alto = parseFloat(document.getElementById('calc-alto').value) || 0;
                const ancho = parseFloat(document.getElementById('calc-ancho').value) || 0;
                const prof = parseFloat(document.getElementById('calc-prof').value) || 0;

                if (alto <= 0 || ancho <= 0 || prof <= 0) {
                    alert("Por favor, introduce medidas válidas mayores a 0.");
                    return;
                }

                const volumen = alto * ancho * prof;
                const rendimiento = parseFloat(opcion.getAttribute('data-rendimiento'));
                const precioUnitario = parseFloat(opcion.getAttribute('data-precio'));

                // Cálculo de unidades necesarias (redondeando hacia arriba porque no compras medio bloque)
                const unidadesNecesarias = Math.ceil(volumen * rendimiento);
                const precioTotal = unidadesNecesarias * precioUnitario;

                // UI Update
                document.getElementById('resultado-vacio').style.display = 'none';
                document.getElementById('resultado-lleno').style.display = 'block';

                document.getElementById('res-volumen').innerText = volumen.toFixed(3);
                document.getElementById('res-material-nombre').innerText = opcion.getAttribute('data-nombre');
                document.getElementById('res-cantidad').innerText = unidadesNecesarias;
                document.getElementById('res-precio').innerText = precioTotal.toFixed(2).replace('.', ',');
            }

            // Llamada de verdad al Carrito en lugar de simulación
            function addCalculadoAlCarrito() {
                const selector = document.getElementById('calc-material');
                const opcion = selector.options[selector.selectedIndex];

                if (!opcion || !opcion.value) {
                    alert("Calcula primero el material antes de añadirlo.");
                    return;
                }

                const cantidadTotal = parseInt(document.getElementById('res-cantidad').innerText);
                if (cantidadTotal <= 0 || isNaN(cantidadTotal)) {
                    alert("Fallo al obtener la cantidad. Refresca e inténtalo de nuevo.");
                    return;
                }

                const productId = opcion.value; // Que es el ID

                fetch('../paginas/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${productId}&qty=${cantidadTotal}`
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            let badge = document.querySelector('.cart-count');
                            if (badge) {
                                badge.textContent = data.total_items;
                            } else {
                                const cartIcon = document.querySelector('.cart-icon');
                                if (cartIcon) cartIcon.innerHTML += `<span class="cart-count">${data.total_items}</span>`;
                            }
                            alert(`¡Éxito! Se añadieron ${cantidadTotal} uds del material a tu carrito.`);
                        }
                    })
                    .catch(err => console.error(err));
            }
        </script>

        <?php include '../includes/footer.php'; ?>