<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Define 15 items with images
$items = [
    1 => ['name' => 'Wireless Headphones', 'price' => 99.99, 'image' => 'https://images.unsplash.com/photo-1511367461989-f85a21fda167?auto=format&fit=crop&w=400&q=80'],
    2 => ['name' => 'Smartwatch', 'price' => 149.99, 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=400&q=80'],
    3 => ['name' => 'Bluetooth Speaker', 'price' => 79.99, 'image' => 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?auto=format&fit=crop&w=400&q=80'],
    4 => ['name' => 'E-reader', 'price' => 129.99, 'image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80'],
    5 => ['name' => 'Portable Charger', 'price' => 39.99, 'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=400&q=80'],
    6 => ['name' => 'Camera Lens', 'price' => 199.99, 'image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=400&q=80'],
    7 => ['name' => 'Mechanical Keyboard', 'price' => 89.99, 'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80'],
    8 => ['name' => 'Gaming Mouse', 'price' => 49.99, 'image' => 'images/image-2.jpg'],
    9 => ['name' => 'VR Headset', 'price' => 299.99, 'image' => 'https://images.unsplash.com/photo-1549924231-f129b911e442?auto=format&fit=crop&w=400&q=80'],
    10 => ['name' => 'Smart Home Hub', 'price' => 99.99, 'image' => 'https://images.unsplash.com/photo-1568688223145-c0dbf769e11e?auto=format&fit=crop&w=400&q=80'],
    11 => ['name' => 'Drone', 'price' => 399.99, 'image' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=400&q=80'],
    12 => ['name' => 'Fitness Tracker', 'price' => 59.99, 'image' => 'images/image-2.jpg'],
    13 => ['name' => 'Laptop Stand', 'price' => 29.99, 'image' => 'images/image-1.jpg'],
    14 => ['name' => 'Wireless Charger', 'price' => 24.99, 'image' => 'images/image-4.jpg'],
    15 => ['name' => 'Noise Cancelling Earbuds', 'price' => 129.99, 'image' => 'images/image-5.jpg'],
];

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $item_id = (int)$_POST['item_id'];
    if (isset($items[$item_id])) {
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]++;
        } else {
            $_SESSION['cart'][$item_id] = 1;
        }
    }
    header("Location: index.php");
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout'){
    session_destroy();
    header("Location: login.php");
    exit;
}

function format_price($price) {
    return '$' . number_format($price, 2);
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Items - Shopping Cart</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');
  :root {
    --color-bg: #fff;
    --color-text: #6b7280;
    --color-primary: #111827;
    --color-primary-hover: #374151;
    --shadow: rgba(0,0,0,0.05);
    --border-radius: 0.75rem;
    --spacing: 1rem;
  }
  * { box-sizing: border-box;}
  body {
    margin: 0; font-family: 'Poppins', sans-serif; background: var(--color-bg);
    color: var(--color-text); line-height:1.6; min-height:100vh;
    display:flex; flex-direction:column;
  }
  header {
    position: sticky; top: 0; background: var(--color-bg); z-index:10;
    box-shadow: 0 2px 6px var(--shadow);
  }
  nav {
    max-width: 1200px; margin: 0 auto; padding: 1rem var(--spacing);
    display: flex; justify-content: space-between; align-items:center;
  }
  .logo {
    font-weight: 800; font-size: 1.5rem; color: var(--color-primary);
    text-decoration: none;
  }
  .nav-links a {
    margin-left: 1.5rem; text-decoration:none; color: var(--color-primary);
    font-weight: 600;
    transition: color 0.3s ease;
  }
  .nav-links a:hover, .nav-links a:focus {
    color: var(--color-primary-hover);
  }
  main {
    max-width: 1200px; margin: 2rem auto 3rem auto;
    padding: 0 var(--spacing);
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 2rem;
  }
  @media (max-width: 1100px) {
    main {
      grid-template-columns: repeat(4, 1fr);
    }
  }
  @media (max-width: 860px) {
    main {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  @media (max-width: 600px) {
    main {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 400px) {
    main {
      grid-template-columns: 1fr;
    }
  }
  .card {
    background: var(--color-bg);
    box-shadow: 0 4px 20px var(--shadow);
    border-radius: var(--border-radius);
    padding: var(--spacing);
    display: flex; flex-direction: column; justify-content: space-between;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .card:hover, .card:focus-within {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px var(--shadow);
  }
  .item-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing);
  }
  .item-name {
    font-weight: 700; font-size: 1.1rem; color: var(--color-primary);
    margin-bottom: 0.3rem;
    min-height: 2.8em; /* reserve space for 2 lines */
  }
  .item-price {
    font-weight: 700; font-size: 1rem; color: var(--color-primary);
    margin-bottom: 1rem;
  }
  button {
    background-color: var(--color-primary); color: white; border: none;
    padding: 0.5rem; font-weight: 700; border-radius: var(--border-radius);
    cursor: pointer; transition: background-color 0.3s ease;
    font-size: 1rem;
  }
  button:hover, button:focus {
    background-color: var(--color-primary-hover);
  }
  .cart-link {
    font-weight: 700; font-size: 1rem; color: var(--color-primary);
    text-decoration: none;
    margin-left: var(--spacing);
    border: 2px solid var(--color-primary);
    padding: 0.3rem 0.75rem;
    border-radius: var(--border-radius);
    transition: background-color 0.3s ease, color 0.3s ease;
  }
  .cart-link:hover, .cart-link:focus {
    background-color: var(--color-primary);
    color: white;
  }
</style>
</head>
<body>
<header>
<nav aria-label="Main Navigation">
  <a href="index.php" class="logo">ShopEasy</a>
  <div>
    <a href="cart.php" class="cart-link" aria-label="View Cart">Cart (<?=array_sum($_SESSION['cart'] ?? [])?>)</a>
    <a href="index.php?action=logout" class="cart-link" aria-label="Logout">Logout</a>
  </div>
</nav>
</header>

<main>
<?php foreach ($items as $id => $item): ?>
  <article class="card" role="region" aria-labelledby="item-<?=$id?>">
    <img src="<?=htmlspecialchars($item['image'])?>" alt="<?=htmlspecialchars($item['name'])?>" class="item-image" loading="lazy" />
    <h3 id="item-<?=$id?>" class="item-name"><?=htmlspecialchars($item['name'])?></h3>
    <p class="item-price"><?=format_price($item['price'])?></p>
    <form method="post" aria-label="Add <?=htmlspecialchars($item['name'])?> to cart">
      <input type="hidden" name="item_id" value="<?=$id?>" />
      <button type="submit" name="add_to_cart">Add to Cart</button>
    </form>
  </article>
<?php endforeach; ?>
</main>
</body>
</html>

