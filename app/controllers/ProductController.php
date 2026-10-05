<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Controller: ProductController
 * 
 * Automatically generated via CLI.
 */
class ProductController extends Controller {
    public function __construct()
    {
        parent::__construct();
        $this->call->library('api');
    }

    public function index()
    {
        $this->api->require_method('GET');
        $this->api->require_jwt();

        $stmt = $this->db->raw('SELECT * FROM products ORDER BY id DESC');
        $this->api->respond(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function show($id)
    {
        $this->api->require_method('GET');
        $this->api->require_jwt();

        $stmt = $this->db->raw('SELECT * FROM products WHERE id = ?', [$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) $this->api->respond_error('Product not found', 404);
        $this->api->respond(['data' => $product]);
    }

    public function store()
    {
        $this->api->require_method('POST');
        $this->api->require_jwt();
        $in = $this->api->body();

        $this->validate_product($in);

        $this->db->raw(
            'INSERT INTO products (product_name, description, price, quantity) VALUES (?, ?, ?, ?)',
            [$in['product_name'], $in['description'] ?? '', $in['price'], (int)($in['quantity'] ?? 0)]
        );

        $this->api->respond(['message' => 'Product created'], 201);
    }

    public function update($id)
    {
        $this->api->require_method('PUT');
        $this->api->require_jwt();
        $in = $this->api->body();

        $this->validate_product($in);

        $this->db->raw(
            'UPDATE products SET product_name = ?, description = ?, price = ?, quantity = ? WHERE id = ?',
            [$in['product_name'], $in['description'] ?? '', $in['price'], (int)($in['quantity'] ?? 0), $id]
        );

        $this->api->respond(['message' => 'Product updated']);
    }

    public function destroy($id)
    {
        $this->api->require_method('DELETE');
        $this->api->require_jwt();

        $this->db->raw('DELETE FROM products WHERE id = ?', [$id]);
        $this->api->respond(['message' => 'Product deleted']);
    }

    private function validate_product($in)
    {
        if (empty($in['product_name']) || !isset($in['price']) || !is_numeric($in['price'])) {
            $this->api->respond_error('product_name and a numeric price are required', 422);
        }
    }

}