<?php
$data = $args['data'];

// İstatistikleri hesapla
$totalCategories = count($data);
$totalProducts = 0;
$totalStock = 0;
$totalValue = 0;

foreach ($data as $category) {
    $totalProducts += count($category['product']);
    foreach ($category['product'] as $product) {
        $totalStock += $product['stock'];
        $totalValue += ($product['sale_price'] * $product['stock']);
    }
}

// Yardımcı fonksiyonlar
function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}

function formatNumber($number) {
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1, ',', '.') . 'M ₺';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 0, ',', '.') . 'K ₺';
    }
    return number_format($number, 0, ',', '.');
}

function getStockClass($stock) {
    if ($stock == 0) return 'zero';
    if ($stock < 10) return 'low';
    if ($stock < 50) return 'medium';
    return 'high';
}

function getStatusText($status) {
    switch ($status) {
        case 'active': return 'Aktif';
        case 'draft': return 'Taslak';
        case 'inactive': return 'Pasif';
        default: return ucfirst($status);
    }
}

function calculateProfitMargin($costPrice, $salePrice) {
    if ($costPrice == 0) return 0;
    return (($salePrice - $costPrice) / $costPrice) * 100;
}

function getCategoryIcon($categoryName) {
    $icons = [
        'Elektronik' => '📱',
        'Bilgisayar' => '💻',
        'Mobil' => '📱',
        'Ofis Malzemeleri' => '🏢',
        'Kırtasiye' => '✏️',
        'Yazılım' => '💿',
        'Bulut Hizmetleri' => '☁️',
        'Hizmetler' => '🔧'
    ];
    return $icons[$categoryName] ?? '📦';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Kataloğu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .categories-section {
            padding: 30px;
        }
        
        .category-group {
            margin-bottom: 40px;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .category-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-info h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        
        .category-code {
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .category-badge {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-count {
            background: rgba(255,255,255,0.3);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th {
            background: #f8f9fa;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .products-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 14px;
        }
        
        .products-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .product-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .product-code {
            font-family: 'Courier New', monospace;
            background: #e3f2fd;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #1565c0;
        }
        
        .price {
            font-weight: 600;
            color: #27ae60;
        }
        
        .cost-price {
            color: #e74c3c;
            font-size: 13px;
        }
        
        .stock {
            text-align: center;
            font-weight: 600;
        }
        
        .stock.high {
            color: #27ae60;
        }
        
        .stock.medium {
            color: #f39c12;
        }
        
        .stock.low {
            color: #e74c3c;
        }
        
        .stock.zero {
            color: #95a5a6;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .status.draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-products {
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            background: #f8f9fa;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .profit-margin {
            color: #16a085;
            font-weight: 600;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 TechCorp Solutions - Ürün Kataloğu</h1>
            <p>Organizasyon ID: 550e8400-e29b-41d4-a716-446655440001</p>
        </div>
        
        <div class="categories-section">
            <!-- Dinamik İstatistikler -->
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $totalCategories ?></div>
                    <div class="stat-label">Toplam Kategori</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $totalProducts ?></div>
                    <div class="stat-label">Toplam Ürün</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= formatNumber($totalStock) ?></div>
                    <div class="stat-label">Toplam Stok</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= formatNumber($totalValue) ?></div>
                    <div class="stat-label">Toplam Değer</div>
                </div>
            </div>
            
            <!-- Dinamik Kategoriler -->
            <?php foreach ($data as $category): ?>
                <div class="category-group">
                    <div class="category-header">
                        <div class="category-info">
                            <h2><?= getCategoryIcon($category['name']) ?> <?= htmlspecialchars($category['name']) ?></h2>
                        </div>
                        <div class="category-badge">
                            <span class="category-code"><?= htmlspecialchars($category['code']) ?></span>
                            <span class="product-count"><?= count($category['product']) ?> Ürün</span>
                        </div>
                    </div>
                    
                    <?php if (empty($category['product'])): ?>
                        <div class="no-products">
                            Bu kategoride henüz ürün bulunmuyor
                        </div>
                    <?php else: ?>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Ürün Kodu</th>
                                    <th>Ürün Adı</th>
                                    <th>Maliyet</th>
                                    <th>Satış Fiyatı</th>
                                    <th>Kar Marjı</th>
                                    <th>Stok</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category['product'] as $product): ?>
                                    <?php 
                                    $profitMargin = calculateProfitMargin($product['cost_price'], $product['sale_price']);
                                    $stockClass = getStockClass($product['stock']);
                                    $statusText = getStatusText($product['status']);
                                    ?>
                                    <tr>
                                        <td><span class="product-code"><?= htmlspecialchars($product['code']) ?></span></td>
                                        <td class="product-name"><?= htmlspecialchars($product['name']) ?></td>
                                        <td class="cost-price"><?= formatPrice($product['cost_price']) ?></td>
                                        <td class="price"><?= formatPrice($product['sale_price']) ?></td>
                                        <td class="profit-margin">+<?= number_format($profitMargin, 1) ?>%</td>
                                        <td class="stock <?= $stockClass ?>"><?= number_format($product['stock']) ?></td>
                                        <td><span class="status <?= $product['status'] ?>"><?= $statusText ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>