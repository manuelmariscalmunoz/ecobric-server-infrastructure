<?php
include '../includes/header.php';
?>

<div class="page-header" style="background-color: var(--primary-dark); padding: 4rem 0; color: white;">
    <div class="container text-center" style="text-align: center;">
        <h1 style="color: white; font-size: 3rem;"><i class="fa-solid fa-layer-group"></i> Simulador de Muro Ecológico
        </h1>
        <p style="font-size: 1.2rem; max-width: 600px; margin: 0 auto;">Diseña en tiempo real tu muro multicapa,
            descubre su rendimiento térmico y genera tu Certificado de Huella Verde.</p>
    </div>
</div>

<section class="section-padding" style="background-color: var(--bg-light); min-height: 80vh;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;" class="cart-grid">

            <!-- Controles del Simulador -->
            <div>
                <div
                    style="background: white; padding: 2rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                    <h3
                        style="margin-bottom: 1.5rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
                        Configura tus Capas</h3>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Capa 1: Bloque
                            Principal</label>
                        <select id="sim-bloque" onchange="updateSimulator()"
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="btc">Bloque de Tierra Comprimida (Altamente Ecológico)</option>
                            <option value="madera">Entramado de Madera Certificada</option>
                            <option value="hormigon">Ladrillo/Hormigón Tradicional (Alto Impacto)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Capa 2: Aislamiento
                            Interior</label>
                        <select id="sim-aislante" onchange="updateSimulator()"
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="corcho">Panel de Corcho Natural Expandido</option>
                            <option value="canamo">Manta Térmica de Cáñamo</option>
                            <option value="poliuretano">Espuma de Poliuretano Tradicional</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Capa 3: Revestimiento /
                            Acabado</label>
                        <select id="sim-revoco" onchange="updateSimulator()"
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <option value="arcilla">Revoco Fino de Arcilla Blanca</option>
                            <option value="cal">Mortero Tradicional de Cal Hidráulica</option>
                            <option value="cemento">Enlucido de Cemento Convencional</option>
                        </select>
                    </div>

                    <button onclick="generarCertificado()" class="btn btn-primary"
                        style="width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                        <i class="fa-solid fa-award"></i> Generar Certificado Huella Verde
                    </button>

                    <div id="certificado-resultado"
                        style="display: none; margin-top: 2rem; padding: 1.5rem; background: #e8f5e9; border: 2px dashed var(--primary-color); border-radius: 8px; text-align: center;">
                        <i class="fa-solid fa-leaf"
                            style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                        <h4 style="color: var(--primary-dark);">Certificado Expedido</h4>
                        <p style="font-weight: bold; font-size: 1.2rem; color: var(--accent-color);">Ahorras <span
                                id="cert-kg">0</span> Kg de CO2 por m²</p>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 0.5rem;">Acabamos de
                            adjuntarlo a tu sesión. Continúa hacia el catálogo para comprar los materiales recomendados.
                        </p>
                        <a href="catalogo.php" class="btn btn-outline" style="margin-top: 1rem;">Ir al Catálogo</a>
                    </div>
                </div>
            </div>

            <!-- Vista Visual -->
            <div>
                <div style="position: sticky; top: 120px;">
                    <h3 style="margin-bottom: 1rem; text-align: center;">Sección del Muro (Vista Superior)</h3>

                    <!-- Contenedor del Muro 3D/CSS -->
                    <div
                        style="display: flex; height: 300px; border-radius: 8px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 4px solid var(--text-dark); position: relative;">
                        <!-- Capa Exterior (Izquierda) -->
                        <div
                            style="width: 20px; background: #9e9e9e; height: 100%; display: flex; align-items: center; justify-content: center; color: white; writing-mode: vertical-rl; font-size: 0.8rem;">
                            Exterior</div>

                        <!-- Bloque -->
                        <div id="layer-bloque"
                            style="flex: 4; height: 100%; transition: all 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                            BTC</div>

                        <!-- Aislante -->
                        <div id="layer-aislante"
                            style="flex: 2; height: 100%; transition: all 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                            Corcho</div>

                        <!-- Revoco -->
                        <div id="layer-revoco"
                            style="flex: 1; height: 100%; transition: all 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                            Arcilla</div>

                        <!-- Capa Interior (Derecha) -->
                        <div
                            style="width: 20px; background: #f5f5f5; height: 100%; display: flex; align-items: center; justify-content: center; color: #333; writing-mode: vertical-rl; font-size: 0.8rem;">
                            Interior</div>
                    </div>

                    <!-- Stats visuales -->
                    <div
                        style="display: flex; justify-content: space-around; margin-top: 2rem; background: white; padding: 1rem; border-radius: var(--border-radius); box-shadow: var(--shadow-sm);">
                        <div style="text-align: center;">
                            <i class="fa-solid fa-temperature-arrow-down"
                                style="font-size: 2rem; color: #1976d2; margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: bold; font-size: 1.2rem;" id="stat-aislamiento">Excelente</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Aislamiento</div>
                        </div>
                        <div style="text-align: center;">
                            <i class="fa-solid fa-wind"
                                style="font-size: 2rem; color: #00bcd4; margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: bold; font-size: 1.2rem;" id="stat-transpirable">Alta</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Transpirabilidad</div>
                        </div>
                        <div style="text-align: center;">
                            <i class="fa-solid fa-leaf"
                                style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem;"></i>
                            <div style="font-weight: bold; font-size: 1.2rem; color: var(--primary-dark);"
                                id="stat-eco">A+</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Eco-Score</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const ecoData = {
        bloque: {
            btc: { color: '#8d6e63', bg: 'url("https://www.transparenttextures.com/patterns/sandpaper.png")', txt: 'BTC (Tierra)' },
            madera: { color: '#d7ccc8', bg: 'url("https://www.transparenttextures.com/patterns/wood-pattern.png")', txt: 'Madera' },
            hormigon: { color: '#9e9e9e', bg: 'url("https://www.transparenttextures.com/patterns/concrete-wall.png")', txt: 'Hormigón' }
        },
        aislante: {
            corcho: { color: '#a1887f', bg: 'url("https://www.transparenttextures.com/patterns/cork-wallet.png")', txt: 'Corcho' },
            canamo: { color: '#dcedc8', bg: 'none', txt: 'Cáñamo' },
            poliuretano: { color: '#fff59d', bg: 'none', txt: 'Poliuretano' }
        },
        revoco: {
            arcilla: { color: '#eefeeb', bg: 'none', txt: 'Arcilla Blanca' },
            cal: { color: '#f5f5f5', bg: 'url("https://www.transparenttextures.com/patterns/stucco.png")', txt: 'Cal' },
            cemento: { color: '#bdbdbd', bg: 'none', txt: 'Cemento' }
        }
    };

    function updateSimulator() {
        const valB = document.getElementById('sim-bloque').value;
        const valA = document.getElementById('sim-aislante').value;
        const valR = document.getElementById('sim-revoco').value;

        document.getElementById('certificado-resultado').style.display = 'none';

        const layerB = document.getElementById('layer-bloque');
        layerB.style.backgroundColor = ecoData.bloque[valB].color;
        layerB.style.backgroundImage = ecoData.bloque[valB].bg;
        layerB.innerText = ecoData.bloque[valB].txt;

        const layerA = document.getElementById('layer-aislante');
        layerA.style.backgroundColor = ecoData.aislante[valA].color;
        layerA.style.backgroundImage = ecoData.aislante[valA].bg;
        layerA.innerText = ecoData.aislante[valA].txt;

        const layerR = document.getElementById('layer-revoco');
        layerR.style.backgroundColor = ecoData.revoco[valR].color;
        layerR.style.backgroundImage = ecoData.revoco[valR].bg;
        layerR.innerText = ecoData.revoco[valR].txt;
        layerR.style.color = (valR === 'arcilla' || valR === 'cal') ? '#333' : 'white';
        layerR.style.textShadow = (valR === 'arcilla' || valR === 'cal') ? 'none' : '1px 1px 2px rgba(0,0,0,0.8)';

        let ecoScore = 0;
        let aisla = 0;
        let transp = 0;

        if (valB === 'btc') { ecoScore += 5; transp += 3; aisla += 2; }
        else if (valB === 'madera') { ecoScore += 4; transp += 2; aisla += 3; }
        else { ecoScore -= 3; aisla += 1; transp -= 2; }

        if (valA === 'corcho') { ecoScore += 4; aisla += 4; transp += 2; }
        else if (valA === 'canamo') { ecoScore += 3; aisla += 3; transp += 3; }
        else { ecoScore -= 3; aisla += 4; transp -= 3; }

        if (valR === 'arcilla') { ecoScore += 2; transp += 4; }
        else if (valR === 'cal') { ecoScore += 1; transp += 3; }
        else { ecoScore -= 2; transp -= 2; }

        const textAisla = aisla >= 7 ? 'Excelente' : (aisla >= 4 ? 'Bueno' : 'Pobre');
        document.getElementById('stat-aislamiento').innerText = textAisla;
        document.getElementById('stat-aislamiento').style.color = aisla >= 7 ? '#1976d2' : '#757575';

        const textTrans = transp >= 7 ? 'Alta' : (transp >= 3 ? 'Media' : 'Bloqueada');
        document.getElementById('stat-transpirable').innerText = textTrans;
        document.getElementById('stat-transpirable').style.color = transp >= 7 ? '#00bcd4' : '#d32f2f';

        let ecoLetter = 'A+'; let ecoColor = 'var(--primary-dark)';
        if (ecoScore < 10) { ecoLetter = 'A'; ecoColor = 'var(--primary-color)'; }
        if (ecoScore < 6) { ecoLetter = 'B'; ecoColor = '#8bc34a'; }
        if (ecoScore < 2) { ecoLetter = 'C'; ecoColor = '#ffc107'; }
        if (ecoScore < -2) { ecoLetter = 'D'; ecoColor = '#ff9800'; }
        if (ecoScore < -5) { ecoLetter = 'Z (Tóxico)'; ecoColor = '#d32f2f'; }

        document.getElementById('stat-eco').innerText = ecoLetter;
        document.getElementById('stat-eco').style.color = ecoColor;

        window.currentKg = ecoScore * 12.5;
    }

    function generarCertificado() {
        if (!window.currentKg) window.currentKg = 11 * 12.5;

        if (window.currentKg <= 0) {
            alert("Tu muro actual no tiene un impacto ecológico positivo. Intenta cambiar los materiales a opciones naturales como BTC o Corcho.");
            return;
        }

        const certDiv = document.getElementById('certificado-resultado');
        certDiv.style.display = 'block';
        document.getElementById('cert-kg').innerText = window.currentKg.toFixed(1);
    }

    // Initialize
    updateSimulator();
</script>

<?php include '../includes/footer.php'; ?>