<?php
// models/Category.php
class Category {
    private $id;
    private $name;
    private $typename;

    public function __construct($id, $name, $typename) {
        $this->id = $id;
        $this->name = $name;
        $this->typename = $typename;
    }

    // getters and setters
}

// models/Product.php
class Product {
    private $id;
    private $name;
    private $inStock;
    private $description;
    private $category;
    private $brand;
    private $typename;
    private $attributes;
    private $prices;
    private $gallery;

    public function __construct($id, $name, $inStock, $description, $category, $brand, $typename, $attributes = [], $prices = [], $gallery = []) {
        $this->id = $id;
        $this->name = $name;
        $this->inStock = $inStock;
        $this->description = $description;
        $this->category = $category;
        $this->brand = $brand;
        $this->typename = $typename;
        $this->attributes = $attributes;
        $this->prices = $prices;
        $this->gallery = $gallery;
    }

    // getters and setters
}

// models/Attribute.php
class Attribute {
    private $id;
    private $productId;
    private $name;
    private $type;
    private $items;

    public function __construct($id, $productId, $name, $type, $items = []) {
        $this->id = $id;
        $this->productId = $productId;
        $this->name = $name;
        $this->type = $type;
        $this->items = $items;
    }

    // getters and setters
}

// models/AttributeItem.php
class AttributeItem {
    private $id;
    private $attributeId;
    private $displayValue;
    private $value;

    public function __construct($id, $attributeId, $displayValue, $value) {
        $this->id = $id;
        $this->attributeId = $attributeId;
        $this->displayValue = $displayValue;
        $this->value = $value;
    }

    // getters and setters
}

// models/Price.php
class Price {
    private $id;
    private $productId;
    private $amount;
    private $currencyLabel;
    private $currencySymbol;

    public function __construct($id, $productId, $amount, $currencyLabel, $currencySymbol) {
        $this->id = $id;
        $this->productId = $productId;
        $this->amount = $amount;
        $this->currencyLabel = $currencyLabel;
        $this->currencySymbol = $currencySymbol;
    }

    // getters and setters
}

// models/Gallery.php
class Gallery {
    private $id;
    private $productId;
    private $url;

    public function __construct($id, $productId, $url) {
        $this->id = $id;
        $this->productId = $productId;
        $this->url = $url;
    }

    // getters and setters
}
?>
