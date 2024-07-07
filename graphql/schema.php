<?php
error_log('Executing GraphQL schema');

require '../vendor/autoload.php';
require '../db.php';

use Scandiweb\DatabaseConnection;
use Doctrine\DBAL\DriverManager;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\GraphQL;

// Create an instance of the DatabaseConnection class and get the connection
$db = new DatabaseConnection();
$conn = $db->getConnection();

// Define your GraphQL types
$categoryType = new ObjectType([
    'name' => 'Category',
    'fields' => [
        'id' => Type::int(),
        'name' => Type::string(),
        'typename' => Type::string(),
    ],
]);

$priceType = new ObjectType([
    'name' => 'Price',
    'fields' => [
        'amount' => Type::float(),
        'currency' => new ObjectType([
            'name' => 'Currency',
            'fields' => [
                'label' => Type::string(),
                'symbol' => Type::string(),
            ],
        ]),
    ],
]);

$productType = new ObjectType([
    'name' => 'Product',
    'fields' => [
        'id' => Type::string(),
        'name' => Type::string(),
        'inStock' => Type::boolean(),
        'description' => Type::string(),
        'category' => Type::string(),
        'brand' => Type::string(),
        'typename' => Type::string(),
        'gallery' => Type::listOf(Type::string()),
        'prices' => Type::listOf($priceType),
        'attributes' => Type::listOf(new ObjectType([
            'name' => 'AttributeSet',
            'fields' => [
                'id' => Type::nonNull(Type::string()),
                'name' => Type::nonNull(Type::string()),
                'type' => Type::nonNull(Type::string()),
                'items' => Type::listOf(new ObjectType([
                    'name' => 'Attribute',
                    'fields' => [
                        'displayValue' => Type::nonNull(Type::string()),
                        'value' => Type::nonNull(Type::string()),
                        'id' => Type::nonNull(Type::string()),
                    ],
                ])),
            ],
        ])),
    ],
]);

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'categories' => [
            'type' => Type::listOf($categoryType),
            'resolve' => function () use ($conn) {
                return $conn->fetchAllAssociative('SELECT * FROM categories');
            }
        ],
        'products' => [
            'type' => Type::listOf($productType),
            'resolve' => function () use ($conn) {
                try {
                    $products = $conn->fetchAllAssociative('SELECT * FROM products');
                    foreach ($products as &$product) {
                        $product['gallery'] = json_decode($product['gallery'], true);
                        $product['prices'] = json_decode($product['prices'], true);
                    }
                    error_log('Fetched products: ' . print_r($products, true));
                    return $products;
                } catch (\Exception $e) {
                    error_log('Error fetching products: ' . $e->getMessage());
                    return null;
                }
            }
        ],
        'product' => [
            'type' => $productType,
            'args' => [
                'id' => Type::nonNull(Type::string())
            ],
            'resolve' => function ($root, $args) use ($conn) {
                try {
                    $product = $conn->fetchAssociative('SELECT * FROM products WHERE id = ?', [$args['id']]);
                    if (!$product) {
                        return null;
                    }
                    $product['gallery'] = json_decode($product['gallery'], true);
                    $product['prices'] = json_decode($product['prices'], true);
                    return $product;
                } catch (\Exception $e) {
                    error_log('Error fetching product: ' . $e->getMessage());
                    return null;
                }
            }
        ],
    ],
]);

$mutationType = new ObjectType([
    'name' => 'Mutation',
    'fields' => [
        'createOrder' => [
            'type' => Type::string(),
            'args' => [
                'productId' => Type::nonNull(Type::string()),
                'quantity' => Type::nonNull(Type::int()),
                'customerName' => Type::nonNull(Type::string()),
                'address' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($root, $args) use ($conn) {
                $conn->insert('orders', [
                    'product_id' => $args['productId'],
                    'quantity' => $args['quantity'],
                    'customer_name' => $args['customerName'],
                    'address' => $args['address'],
                ]);
                return 'Order created successfully';
            }
        ],
    ],
]);

$schema = new Schema([
    'query' => $queryType,
    'mutation' => $mutationType,
]);

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];

try {
    $result = GraphQL::executeQuery($schema, $query);
    $output = $result->toArray();
    error_log('GraphQL Output: ' . print_r($output, true));
    header('Content-Type: application/json');
    echo json_encode($output);
} catch (\Exception $e) {
    error_log('GraphQL Execution Error: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['errors' => [['message' => $e->getMessage()]]]);
}
