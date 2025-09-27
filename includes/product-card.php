<div class="custom-product-card">
    <div class="product-image-container">
        <?php
        $isNew = (strtotime($product['created_at'] ?? 'now') >= strtotime('-14 days'));
        $lowStock = ($product['stock'] ?? 0) > 0 && ($product['stock'] ?? 0) <= 5;
        ?>
        <?php if ($isNew): ?><span class="product-badge badge-new">New</span><?php endif; ?>
        <?php if ($lowStock): ?><span class="product-badge badge-low" style="left:auto; right:10px;">Low Stock</span><?php endif; ?>

        <div class="price-badge"><?= formatPrice($product['price']) ?></div>

        <!-- Lazy loading image -->
        <a href="product.php?id=<?= $product['id'] ?>">
            <img data-src="admin/assets/uploads/<?= esc_html($product['image']) ?>"
                class="product-image loading-skeleton"
                alt="<?= esc_html($product['name']) ?>"
                loading="lazy">
        </a>

        <div class="product-overlay">
            <a href="add_to_cart.php?id=<?= $product['id'] ?>" class="quick-view-btn">
                <i class="bi bi-cart-plus"></i> Add to Cart
            </a>
        </div>
    </div>
    <div class="product-info">
        <a href="product.php?id=<?= $product['id'] ?>" class="product-name">
            <?= esc_html($product['name']) ?>
        </a>
        <div class="product-price">
            <?= formatPrice($product['price']) ?>
        </div>
        <?php if ($product['stock'] > 0): ?>
            <div class="button-container">
                <a href="product.php?id=<?= $product['id'] ?>" class="buy-button button">
                    <i class="bi bi-eye me-1"></i> View Details
                </a>
            </div>
        <?php else: ?>
            <button class="btn out-of-stock-custom" disabled>Out of Stock</button>
        <?php endif; ?>
    </div>
</div>