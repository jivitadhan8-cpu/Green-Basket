<?php
require_once __DIR__ . '/../includes/db.php';

$categories = [
    ['Fruits', 'fruits', 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?auto=format&fit=crop&w=700&q=80', 'Farm fresh fruits'],
    ['Vegetables', 'vegetables', 'https://images.unsplash.com/photo-1518843875459-f738682238a6?auto=format&fit=crop&w=700&q=80', 'Daily picked vegetables'],
    ['Dairy', 'dairy', 'https://images.unsplash.com/photo-1628088062854-d1870b4553da?auto=format&fit=crop&w=700&q=80', 'Milk, cheese and more'],
    ['Bakery', 'bakery', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=700&q=80', 'Freshly baked items'],
    ['Beverages', 'beverages', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=700&q=80', 'Juices, drinks and healthy beverages'],
    ['Snacks', 'snacks', 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?auto=format&fit=crop&w=700&q=80', 'Ready-to-eat snacks and namkeen'],
    ['Staples', 'staples', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=700&q=80', 'Rice, flour, pulses and essentials'],
    ['Personal Care', 'personal-care', 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=700&q=80', 'Daily hygiene and care products']
];

$insertCategory = $pdo->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
$findCategory = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
$categoryIds = [];

foreach ($categories as $c) {
    $findCategory->execute([$c[1]]);
    $existing = $findCategory->fetch();
    if (!$existing) {
        $insertCategory->execute([$c[0], $c[1], $c[2], $c[3]]);
        $categoryIds[$c[1]] = (int)$pdo->lastInsertId();
    } else {
        $categoryIds[$c[1]] = (int)$existing['id'];
    }
}

$products = [
    ['fruits','Alphonso Mangoes (1 Kg)','alphonso-mangoes','Sweet premium mangoes from Ratnagiri.',299,349,120,'FRU-003','https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=700&q=80',4.8,1,1,1],
    ['fruits','Seedless Grapes (500g)','seedless-grapes','Crisp and juicy green seedless grapes.',99,129,180,'FRU-004','https://images.unsplash.com/photo-1537640538966-79f369143f8f?auto=format&fit=crop&w=700&q=80',4.4,0,1,0],
    ['fruits','Pomegranate (1 Kg)','pomegranate-1kg','Ruby red antioxidant-rich pomegranates.',189,219,90,'FRU-005','https://images.unsplash.com/photo-1541344999734-5fe3bd36b0a7?auto=format&fit=crop&w=700&q=80',4.5,1,0,0],
    ['fruits','Papaya (1 Pc)','papaya-1pc','Ripe papaya rich in vitamins.',69,79,130,'FRU-006','https://images.unsplash.com/photo-1517282009859-f000ec3b26fe?auto=format&fit=crop&w=700&q=80',4.2,0,0,0],
    ['fruits','Kiwi (Pack of 3)','kiwi-pack-3','Imported kiwis with tangy flavor.',129,149,70,'FRU-007','https://images.unsplash.com/photo-1618897996318-5a901fa6ca71?auto=format&fit=crop&w=700&q=80',4.6,0,1,1],

    ['vegetables','Red Onions (1 Kg)','red-onions-1kg','Farm fresh onions for daily cooking.',42,52,250,'VEG-003','https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?auto=format&fit=crop&w=700&q=80',4.1,0,0,0],
    ['vegetables','Potatoes (1 Kg)','potatoes-1kg','Premium potatoes ideal for all recipes.',38,48,300,'VEG-004','https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=700&q=80',4.3,0,1,1],
    ['vegetables','Cauliflower (1 Pc)','cauliflower-1pc','Fresh cauliflower cleaned and packed.',55,65,95,'VEG-005','https://images.unsplash.com/photo-1568584711271-4727245e3c14?auto=format&fit=crop&w=700&q=80',4.0,0,0,0],
    ['vegetables','Carrots (500g)','carrots-500g','Crunchy orange carrots for salads.',49,59,150,'VEG-006','https://images.unsplash.com/photo-1447175008436-054170c2e979?auto=format&fit=crop&w=700&q=80',4.2,0,1,0],
    ['vegetables','Capsicum Mix (500g)','capsicum-mix-500g','Assorted bell peppers for stir-fries.',99,119,80,'VEG-007','https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=700&q=80',4.4,1,1,0],

    ['dairy','Greek Yogurt (400g)','greek-yogurt-400g','High-protein creamy greek yogurt.',120,140,85,'DAI-002','https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=700&q=80',4.5,1,0,1],
    ['dairy','Salted Butter (500g)','salted-butter-500g','Rich and smooth table butter.',265,295,75,'DAI-003','https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=700&q=80',4.6,0,1,1],
    ['dairy','Cheddar Cheese Slices','cheddar-cheese-slices','Cheese slices for sandwiches and burgers.',145,169,110,'DAI-004','https://images.unsplash.com/photo-1552767059-ce182ead6c1b?auto=format&fit=crop&w=700&q=80',4.4,1,0,0],
    ['dairy','Paneer Fresh (200g)','paneer-fresh-200g','Soft fresh paneer for curries.',89,99,140,'DAI-005','https://images.unsplash.com/photo-1631452180519-c014fe946bc7?auto=format&fit=crop&w=700&q=80',4.3,0,1,0],
    ['dairy','Eggs (Pack of 12)','eggs-pack-12','Protein-rich farm eggs.',92,110,220,'DAI-006','https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?auto=format&fit=crop&w=700&q=80',4.7,1,1,1],

    ['bakery','Whole Wheat Bread','whole-wheat-bread','Soft whole wheat loaf with fiber.',52,60,90,'BAK-002','https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=700&q=80',4.2,0,0,0],
    ['bakery','Butter Croissant (Pack of 4)','butter-croissant-pack-4','Flaky buttery croissants.',140,165,60,'BAK-003','https://images.unsplash.com/photo-1555507036-ab794f4afe5a?auto=format&fit=crop&w=700&q=80',4.6,1,1,0],
    ['bakery','Blueberry Muffins (4 Pc)','blueberry-muffins-4pc','Freshly baked muffins with blueberry filling.',180,210,55,'BAK-004','https://images.unsplash.com/photo-1607958996333-41aef7caefaa?auto=format&fit=crop&w=700&q=80',4.5,1,0,1],
    ['bakery','Burger Buns (6 Pc)','burger-buns-6pc','Soft buns for homemade burgers.',75,90,130,'BAK-005','https://images.unsplash.com/photo-1598373182133-52452f7691ef?auto=format&fit=crop&w=700&q=80',4.1,0,1,0],
    ['bakery','Garlic Bread Sticks','garlic-bread-sticks','Toasted garlic bread sticks.',110,130,88,'BAK-006','https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?auto=format&fit=crop&w=700&q=80',4.4,0,0,0],

    ['beverages','Orange Juice (1L)','orange-juice-1l','100% fruit orange juice.',125,145,170,'BEV-001','https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=700&q=80',4.2,0,1,0],
    ['beverages','Cold Coffee Bottle','cold-coffee-bottle','Ready-to-drink cold coffee.',95,110,160,'BEV-002','https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=700&q=80',4.3,0,1,1],
    ['beverages','Green Tea Bags (50)','green-tea-bags-50','Antioxidant green tea bags.',180,210,90,'BEV-003','https://images.unsplash.com/photo-1597318181409-cf64d0b5d8a2?auto=format&fit=crop&w=700&q=80',4.5,1,0,0],
    ['beverages','Sparkling Water (750ml)','sparkling-water-750ml','Refreshing zero sugar sparkling water.',69,79,200,'BEV-004','https://images.unsplash.com/photo-1616118132534-381148898bb4?auto=format&fit=crop&w=700&q=80',4.1,0,0,0],
    ['beverages','Coconut Water (1L)','coconut-water-1l','Natural coconut water hydration drink.',110,130,145,'BEV-005','https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=700&q=80',4.4,1,1,0],

    ['snacks','Classic Potato Chips','classic-potato-chips','Crispy salted potato chips.',35,40,300,'SNK-001','https://images.unsplash.com/photo-1566478989037-eec170784d0b?auto=format&fit=crop&w=700&q=80',4.0,0,1,0],
    ['snacks','Roasted Almonds (200g)','roasted-almonds-200g','Premium roasted almonds snack pack.',245,280,100,'SNK-002','https://images.unsplash.com/photo-1508747703725-719777637510?auto=format&fit=crop&w=700&q=80',4.7,1,1,1],
    ['snacks','Masala Peanuts (250g)','masala-peanuts-250g','Spicy crunchy masala peanuts.',85,99,180,'SNK-003','https://images.unsplash.com/photo-1587132137056-bfbf0166836e?auto=format&fit=crop&w=700&q=80',4.2,0,0,0],
    ['snacks','Nachos Cheese (180g)','nachos-cheese-180g','Crunchy cheese flavored nachos.',120,135,120,'SNK-004','https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?auto=format&fit=crop&w=700&q=80',4.3,0,1,0],
    ['snacks','Granola Bars (6 Pc)','granola-bars-6pc','Healthy oats and nuts snack bars.',150,170,95,'SNK-005','https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?auto=format&fit=crop&w=700&q=80',4.5,1,0,1],

    ['staples','Basmati Rice (5 Kg)','basmati-rice-5kg','Aromatic premium basmati rice.',620,699,85,'STP-001','https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=700&q=80',4.6,1,1,1],
    ['staples','Whole Wheat Atta (5 Kg)','whole-wheat-atta-5kg','Stone-ground whole wheat flour.',260,299,130,'STP-002','https://images.unsplash.com/photo-1627489937988-2e32c7f33a6c?auto=format&fit=crop&w=700&q=80',4.4,0,1,0],
    ['staples','Toor Dal (1 Kg)','toor-dal-1kg','Unpolished toor dal rich in protein.',165,190,140,'STP-003','https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=700&q=80',4.3,0,0,0],
    ['staples','Moong Dal (1 Kg)','moong-dal-1kg','Light and healthy moong dal.',152,175,150,'STP-004','https://images.unsplash.com/photo-1593113598332-cd288d649433?auto=format&fit=crop&w=700&q=80',4.4,0,0,0],
    ['staples','Sunflower Oil (1L)','sunflower-oil-1l','Refined sunflower cooking oil.',149,169,200,'STP-005','https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=700&q=80',4.2,0,1,0],

    ['personal-care','Shampoo Herbal (340ml)','shampoo-herbal-340ml','Mild herbal shampoo for daily use.',215,249,75,'PRC-001','https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=700&q=80',4.3,0,0,0],
    ['personal-care','Body Wash (500ml)','body-wash-500ml','Refreshing body wash with aloe.',299,349,65,'PRC-002','https://images.unsplash.com/photo-1601612628452-9e99ced43524?auto=format&fit=crop&w=700&q=80',4.4,0,1,0],
    ['personal-care','Toothpaste Mint (200g)','toothpaste-mint-200g','Fluoride mint toothpaste.',110,130,180,'PRC-003','https://images.unsplash.com/photo-1559599101-f09722fb4948?auto=format&fit=crop&w=700&q=80',4.2,0,0,0],
    ['personal-care','Hand Wash (750ml)','hand-wash-750ml','Antibacterial hand wash refill.',165,185,140,'PRC-004','https://images.unsplash.com/photo-1583947581924-860bda6a26df?auto=format&fit=crop&w=700&q=80',4.3,0,1,0],
    ['personal-care','Face Tissues (Box Pack)','face-tissues-box-pack','Soft 2-ply tissue box.',75,90,210,'PRC-005','https://images.unsplash.com/photo-1583947215259-38e31be8751f?auto=format&fit=crop&w=700&q=80',4.1,0,0,0]
];

$checkProduct = $pdo->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
$insertProduct = $pdo->prepare('INSERT INTO products (category_id, name, slug, description, price, old_price, stock, sku, image, rating, is_featured, is_trending, is_best_seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$added = 0;

foreach ($products as $p) {
    $checkProduct->execute([$p[2]]);
    if ($checkProduct->fetch()) {
        continue;
    }
    $insertProduct->execute([
        $categoryIds[$p[0]], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11], $p[12]
    ]);
    $added++;
}

$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo "Seed completed. Added: {$added}. Total products now: {$totalProducts}" . PHP_EOL;
?>
