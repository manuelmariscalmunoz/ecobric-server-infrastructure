<?php
// api/chatbot.php
header('Content-Type: application/json');

// Cargar las claves seguras
require_once '../config/api_keys.php';
$apiKey = GEMINI_API_KEY;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'message' => 'El mensaje está vacío']);
    exit;
}

if ($apiKey === 'TU_API_KEY_DE_GEMINI_AQUI' || empty($apiKey)) {
    echo json_encode([
        'success' => true,
        'reply' => '¡Hola! Para que funcione la IA real, necesitas registrarte en Google AI Studio, conseguir una API Key gratuita y pegarla en el archivo api/chatbot.php (línea 5). ¡A partir de ahí, estaré vivo! 🤖'
    ]);
    exit;
}

require_once '../config/db.php';

// Obtener contexto de productos desde la Base de Datos para pasárselo a la IA
$stmt = $pdo->query("SELECT nombre, precio, stock, es_calculable_volumen, descripcion FROM productos");
$productosDb = $stmt->fetchAll();

$catalogoContexto = "\n\n--- INVENTARIO Y CATÁLOGO ACTUAL DE ECOBRIC (Usa esta información para responder) ---\n";
foreach ($productosDb as $p) {
    $calc = $p['es_calculable_volumen'] ? 'SÍ (se puede presupuestar en la Calculadora de Volúmenes)' : 'NO';
    $catalogoContexto .= "Producto: {$p['nombre']} | Precio: {$p['precio']}€ | Stock: {$p['stock']} uds | ¿Calculable por volumen?: {$calc} | Detalles: {$p['descripcion']}\n";
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

$systemInstruction = "Eres EcoBot, un asistente virtual experto, amable y profesional para la tienda online 'Ecobric', especializada en materiales ecológicos y bioconstrucción. Tu objetivo es ayudar a los clientes con información de la empresa, presupuestos (basados en el catálogo real), envíos (25€ fijos provincia Madrid) y ubicación (Polígono Verde Industrial, Nave 4, Madrid). Si un cliente te pide un presupuesto, puedes calcular el precio multiplicando las unidades por su precio. Si preguntan por calculadora, diles los materiales marcados como 'calculables'. REGLA MUY IMPORTANTE: Responde SIEMPRE en TEXTO PLANO. NO uses formato Markdown (ni asteriscos para negritas ** **, ni viñetas, ni listas con guiones). Responde usando párrafos cortos, concisos y directos al grano." . $catalogoContexto;

$data = [
    'system_instruction' => [
        'parts' => [
            ['text' => $systemInstruction]
        ]
    ],
    'contents' => [
        [
            'parts' => [
                ['text' => $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 800
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $error]);
    exit;
}

$responseData = json_decode($response, true);

if ($httpCode === 200 && isset($responseData['candidates'][0]['content']['parts'])) {
    $aiReply = '';
    foreach ($responseData['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['text'])) {
            $aiReply .= $part['text'];
        }
    }

    // Por si la IA ignora nuestra instrucción y envia Markdown
    $aiReply = str_replace(['**', '*'], '', $aiReply);

    echo json_encode(['success' => true, 'reply' => trim($aiReply)]);
} else {
    // Si falla la API (límite superado, clave errónea...)
    $errorMsg = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Error desconocido de la API';

    // Capturar errores de cuota superada (Límite de API gratuita)
    if ($httpCode === 429 || stripos($errorMsg, 'Quota exceeded') !== false) {
        $errorMsg = "¡Uf, qué de trabajo! 🥵 He recibido demasiadas peticiones seguidas y necesito recuperar el aliento. Por favor, espera unos segundos e inténtalo de nuevo.";
    } else {
        $errorMsg = 'No pude procesar tu mensaje: ' . $errorMsg;
    }

    echo json_encode(['success' => false, 'message' => $errorMsg]);
}
?>