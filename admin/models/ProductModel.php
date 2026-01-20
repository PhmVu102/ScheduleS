<?php
class ProductModel extends AdminModel
{
    public function getProducts($search = '', $category = '', $status = '', $page = 1, $limit = 15)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, 
                       c.name AS category_name,
                       b.name AS brand_name,
                       (
                           SELECT MIN(price) 
                           FROM product_variants 
                           WHERE product_id = p.id
                       ) AS min_price,
                       (
                           SELECT SUM(stock) 
                           FROM product_variants 
                           WHERE product_id = p.id
                       ) AS total_stock
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE 1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search OR p.slug LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($category)) {
            $sql .= " AND p.category_id = :category";
            $params[':category'] = $category;
        }

        if ($status !== '') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countProducts($search = '', $category = '', $status = '')
    {
        $sql = "SELECT COUNT(*) FROM products p WHERE 1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE :search OR p.slug LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($category)) {
            $sql .= " AND p.category_id = :category";
            $params[':category'] = $category;
        }

        if ($status !== '') {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getCategories()
    {
        $stmt = $this->conn->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ================================
    // THÊM MỚI TỪ ĐÂY TRỞ XUỐNG
    // ================================

    public function getBrands()
    {
        $stmt = $this->conn->query("SELECT id, name FROM brands ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getPhoneDetailsByProductId($productId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM phone_details WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getVariantsByProductId($productId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProduct($data)
    {
        $sql = "INSERT INTO products 
                (name, slug, category_id, brand_id, type, thumbnail, description, status) 
                VALUES (?, ?, ?, ?, 'phone', ?, ?, 1)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['category_id'] ?? null,
            $data['brand_id'] ?? null,
            $data['thumbnail'] ?? null,
            $data['description'] ?? ''
        ]);

        return $this->conn->lastInsertId();
    }

    public function updateProduct($id, $data)
    {
        $sql = "UPDATE products SET 
                name = ?, 
                slug = ?, 
                category_id = ?, 
                brand_id = ?, 
                thumbnail = ?, 
                description = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['category_id'] ?? null,
            $data['brand_id'] ?? null,
            $data['thumbnail'] ?? null,
            $data['description'] ?? '',
            $id
        ]);
    }

    public function addPhoneDetails($productId, $data)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO phone_details 
            (product_id, chipset, ram, rom, screen, camera, battery) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $productId,
            $data['chipset'] ?? '',
            $data['ram'] ?? '',
            $data['rom'] ?? '',
            $data['screen'] ?? '',
            $data['camera'] ?? '',
            $data['battery'] ?? ''
        ]);
    }

    public function updatePhoneDetails($productId, $data)
    {
        $stmt = $this->conn->prepare("
            UPDATE phone_details SET 
                chipset = ?, ram = ?, rom = ?, screen = ?, camera = ?, battery = ?
            WHERE product_id = ?
        ");
        return $stmt->execute([
            $data['chipset'] ?? '',
            $data['ram'] ?? '',
            $data['rom'] ?? '',
            $data['screen'] ?? '',
            $data['camera'] ?? '',
            $data['battery'] ?? '',
            $productId
        ]);
    }

    public function deleteVariantsByProductId($productId)
    {
        $stmt = $this->conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        return $stmt->execute([$productId]);
    }

    public function addVariant($data)
    {
        $sku = $data['sku'] ?? ('SKU' . $data['product_id'] . strtoupper(substr(md5($data['ram'] . $data['rom'] . $data['color']), 0, 8)));

        $stmt = $this->conn->prepare("
            INSERT INTO product_variants 
            (product_id, sku, ram, rom, color, price, price_sale, stock, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['product_id'],
            $sku,
            $data['ram'] ?? '',
            $data['rom'] ?? '',
            $data['color'] ?? '',
            $data['price'],
            $data['price_sale'] ?? 0,
            $data['stock'] ?? 0,
            $data['image'] ?? null
        ]);
    }

    public function getProductDetail($id)
    {
        $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name,
                    pd.chipset, pd.ram, pd.rom, pd.screen, pd.camera, pd.battery
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN phone_details pd ON p.id = pd.product_id
                WHERE p.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) return null;

        // Lấy variants
        $variants = $this->getVariantsByProductId($id);

        // Gắn specs và variants vào product
        $product['specs'] = [
            'chipset' => $product['chipset'] ?? '',
            'ram'     => $product['ram'] ?? '',
            'rom'     => $product['rom'] ?? '',
            'screen'  => $product['screen'] ?? '',
            'camera'  => $product['camera'] ?? '',
            'battery' => $product['battery'] ?? ''
        ];

        $product['variants'] = $variants;

        // Xóa các field thừa khỏi mảng chính (nếu muốn gọn)
        unset($product['chipset'], $product['ram'], $product['rom'], $product['screen'], $product['camera'], $product['battery']);

        return $product;
    }
}