<?php
/**
 * Printful → Magento product sync script
 * Usage: php8.2 printful_sync.php
 *
 * Requires: printful.env (copy from printful.env.example and fill in values)
 */

$envFile = __DIR__ . '/printful.env';
if (!file_exists($envFile)) {
    echo "Error: falta el archivo printful.env (cópialo de printful.env.example)\n";
    exit(1);
}

// Load env file
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    [$key, $val] = explode('=', $line, 2);
    define(trim($key), trim($val));
}

function printful_request($endpoint) {
    $ch = curl_init('https://api.printful.com' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . PRINTFUL_API_KEY],
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response['result'] ?? [];
}

function magento_token() {
    $ch = curl_init(MAGENTO_BASE_URL . '/rest/V1/integration/admin/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['username' => MAGENTO_ADMIN_USER, 'password' => MAGENTO_ADMIN_PASS]),
    ]);
    $token = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $token;
}

function magento_request($method, $endpoint, $token, $data = null) {
    $ch = curl_init(MAGENTO_BASE_URL . '/rest/V1' . $endpoint);
    $headers = ['Authorization: Bearer ' . $token, 'Content-Type: application/json'];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
    ]);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $response;
}

echo "Obteniendo token de Magento...\n";
$token = magento_token();
if (!is_string($token)) { echo "Error: no se pudo obtener token de Magento\n"; exit(1); }
echo "Token OK\n\n";

echo "Obteniendo productos de Printful...\n";
$products = printful_request('/store/products');
echo "Encontrados: " . count($products) . " productos\n\n";

foreach ($products as $p) {
    echo "Procesando: " . $p['name'] . "...\n";

    $detail = printful_request('/store/products/' . $p['id']);
    $variant = $detail['sync_variants'][0] ?? [];
    $price = $variant['retail_price'] ?? '29.99';
    $sku = $variant['sku'] ?? 'PRINTFUL-' . $p['id'];

    $existing = magento_request('GET', '/products/' . urlencode($sku), $token);
    if (isset($existing['id'])) {
        echo "  Ya existe en Magento (SKU: $sku), omitiendo\n";
        continue;
    }

    $product = [
        'product' => [
            'sku' => $sku,
            'name' => $p['name'],
            'price' => (float)$price,
            'status' => 1,
            'visibility' => 4,
            'type_id' => 'simple',
            'attribute_set_id' => 4,
            'weight' => 0.5,
            'custom_attributes' => [
                ['attribute_code' => 'description', 'value' => $p['name'] . ' - Print on demand by Printful'],
                ['attribute_code' => 'short_description', 'value' => $p['name']],
                ['attribute_code' => 'url_key', 'value' => strtolower(str_replace(' ', '-', $p['name'])) . '-' . $p['id']],
            ],
            'extension_attributes' => [
                'stock_item' => ['qty' => 999, 'is_in_stock' => true, 'manage_stock' => false]
            ]
        ]
    ];

    $result = magento_request('POST', '/products', $token, $product);
    if (isset($result['id'])) {
        echo "  ✓ Creado en Magento (ID: " . $result['id'] . ", Precio: $price)\n";
    } else {
        echo "  ✗ Error: " . json_encode($result) . "\n";
    }
}

echo "\nSync completado.\n";
