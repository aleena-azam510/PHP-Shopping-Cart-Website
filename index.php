<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Get items from database
$items = [];
$result = $mysqli->query("SELECT * FROM items");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[$row['id']] = $row;
    }
    $result->free();
} else {
    die("Database query error: " . $mysqli->error);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

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

// Logout
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
  /* Include your CSS styles here - same as previous index.php styles */
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
  body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--color-bg);
    color: var(--color-text); line-height:1.6; min-height:100vh;
    display:flex; flex-direction:column;
  }
  header { position: sticky; top: 0; background: var(--color-bg); z-index:10;
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
    min-height: 2.8em;
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
