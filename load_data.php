<?php
require 'vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

// Load the .env file from the root directory
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set up the database connection
$conn = DriverManager::getConnection([
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
    'host' => $_ENV['DB_HOST'],
    'driver' => 'pdo_mysql',
]);

// Load the JSON data
$data = json_decode(file_get_contents(__DIR__ . '/Data.json'), true);

if ($data === null) {
    throw new \Exception('Failed to decode JSON data.');
}

// Insert categories
foreach ($data['data']['categories'] as $category) {
    $conn->insert('categories', [
        'name' => $category['name'],
        'typename' => $category['__typename']
    ]);
}

// Insert products
foreach ($data['data']['products'] as $product) {
    $product_data = [
        'id' => $product['id'],
        'name' => $product['name'],
        'in_stock' => $product['inStock'],
        'description' => $product['description'],
        'category' => $product['category'],
        'brand' => $product['brand'],
        'typename' => $product['__typename'],
    ];
    $conn->insert('products', $product_data);

    foreach ($product['attributes'] as $attribute) {
        $conn->insert('attributes', [
            'product_id' => $product['id'],
            'name' => $attribute['name'],
            'type' => $attribute['type'],
        ]);

        $attribute_id = $conn->lastInsertId();
        foreach ($attribute['items'] as $item) {
            $conn->insert('attribute_items', [
                'attribute_id' => $attribute_id,
                'display_value' => $item['displayValue'],
                'value' => $item['value'],
            ]);
        }
    }

    foreach ($product['prices'] as $price) {
        $conn->insert('prices', [
            'product_id' => $product['id'],
            'amount' => $price['amount'],
            'currency_label' => $price['currency']['label'],
            'currency_symbol' => $price['currency']['symbol'],
        ]);
    }

    foreach ($product['gallery'] as $url) {
        $conn->insert('gallery', [
            'product_id' => $product['id'],
            'url' => $url,
        ]);
    }
}

echo "Data loaded successfully.";
?>
