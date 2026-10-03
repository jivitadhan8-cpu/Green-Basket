<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/includes/header.php';
$q = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$where = '1=1';
$params = [];
if ($q !== '') { $where .= ' AND p.name LIKE :q'; $params[':q'] = "%$q%"; }
if ($category !== '') { $where .= ' AND c.slug = :category'; $params[':category'] = $category; }
$products = getProducts($pdo, $where, $params, 40);
$categories = getCategories($pdo);
?>
<section class="container" style="padding:25px 0">
  <div class="section-title"><h2>Shop Grocery Products</h2></div>
  <form class="form-card" method="get" style="margin-bottom:16px;display:grid;grid-template-columns:2fr 1fr auto;gap:10px;">
    <input name="q" placeholder="Search products" value="<?= e($q) ?>">
    <select name="category"><option value="">All Categories</option><?php foreach($categories as $cat): ?><option value="<?= e($cat['slug']) ?>" <?= $category===$cat['slug']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select>
    <button class="btn">Apply</button>
  </form>
  <div class="grid product-grid">
    <?php foreach ($products as $p): ?>
      <div class="card"><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>"><div class="card-body"><span class="tag"><?= e($p['category_name']) ?></span><h4><?= e($p['name']) ?></h4><p class="price">Rs <?= e($p['price']) ?></p><div style="display:flex;gap:8px"><button class="btn btn-sm add-to-cart-btn" data-product="<?= e((string)$p['id']) ?>">Add</button><a class="btn btn-sm" href="/grocery/product.php?id=<?= (int)$p['id'] ?>">View</a></div></div></div>
    <?php endforeach; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
