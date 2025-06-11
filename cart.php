<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Fetch all items from database
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_qty'])) {
        $item_id = (int)$_POST['item_id'];
        $qty = max(1, (int)$_POST['quantity']);
        if (isset($items[$item_id])) {
            $_SESSION['cart'][$item_id] = $qty;
        }
    } elseif (isset($_POST['remove_item'])) {
        $item_id = (int)$_POST['item_id'];
        if (isset($_SESSION['cart'][$item_id])) {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout'){
    session_destroy();
    header("Location: login.php");
    exit;
}

function format_price($price) {
    return '$' . number_format($price, 2);
}

function calculate_total($cart, $items) {
    $total = 0;
    foreach ($cart as $id => $qty) {
        if (isset($items[$id])) {
            $total += $items[$id]['price'] * $qty;
        }
    }
    return $total;
}
$total_price = calculate_total($_SESSION['cart'], $items);
?>
<!DOCTYPE html>
<html lang="en" >
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Shopping Cart - ShopEasy</title>
<style>
  /* Use the same CSS as before (cart page styling) */
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
    max-width: 900px; margin: 2rem auto 3rem auto;
    padding: 0 var(--spacing);
    background: var(--color-bg);
    box-shadow: 0 4px 20px var(--shadow);
    border-radius: var(--border-radius);
    padding: var(--spacing);
  }
  h1 {
    font-weight: 700; font-size: 2rem; color: var(--color-primary);
    margin-bottom: 1rem;
  }
  table {
    width: 100%; border-collapse: collapse;
  }
  th, td {
    padding: 0.75rem 0.5rem; text-align: left; vertical-align: middle;
  }
  th {
    border-bottom: 2px solid #e5e7eb;
    font-weight: 700;
    color: var(--color-primary);
  }
  td {
    border-bottom: 1px solid #e5e7eb;
  }
  .item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--border-radius);
  }
  .qty-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  input[type=number] {
    width: 60px; padding: 0.3rem 0.5rem;
    border: 1px solid #d1d5db; border-radius: var(--border-radius);
    font-size: 1rem;
  }
  button {
    background-color: var(--color-primary); color: white; border: none;
    padding: 0.4rem 0.75rem; font-weight: 700; border-radius: var(--border-radius);
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 0.9rem;
  }
  button:hover, button:focus {
    background-color: var(--color-primary-hover);
  }
  .actions {
    display: flex; justify-content: flex-end; margin-top: 1rem; gap: 1rem;
  }
  .link-button {
    background: none; border: none;
    color: var(--color-primary);
    cursor: pointer;
    text-decoration: underline;
    font-weight: 600;
    padding: 0;
    font-size: 1rem;
  }
  .link-button:hover, .link-button:focus {
    color: var(--color-primary-hover);
    outline: none;
  }
  .empty-message {
    text-align: center; color: var(--color-text);
    font-style: italic; font-size: 1.1rem; padding: var(--spacing) 0;
  }
  .continue-link {
    text-decoration:none;
    font-weight: 600;
    color: var(--color-primary);
  }
  .continue-link:hover, .continue-link:focus {
    color: var(--color-primary-hover);
  }
</style>
</head>
<body>
<header>
<nav aria-label="Main Navigation">
  <a href="index.php" class="logo">ShopEasy</a>
  <div>
    <a href="index.php" class="nav-links">Products</a>
    <a href="cart.php" class="nav-links" aria-current="page">Cart</a>
    <a href="index.php?action=logout" class="nav-links" aria-label="Logout">Logout</a>
  </div>
</nav>
</header>

<main>
<h1>Your Cart</h1>
<?php if (empty($_SESSION['cart'])): ?>
  <p class="empty-message">Your cart is empty. <a href="index.php" class="continue-link">Continue shopping →</a></p>
<?php else: ?>
<table aria-describedby="cart-summary">
  <thead>
    <tr>
      <th scope="col">Product</th>
      <th></th>
      <th scope="col">Price</th>
      <th scope="col">Quantity</th>
      <th scope="col">Subtotal</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($_SESSION['cart'] as $id => $qty): 
    $item = $items[$id] ?? null;
    if (!$item) continue;
    $subtotal = $item['price'] * $qty;
  ?>
  <tr>
    <td>
      <img src="<?=htmlspecialchars($item['image'])?>" alt="<?=htmlspecialchars($item['name'])?>" class="item-image" loading="lazy" />
    </td>
    <td><?=htmlspecialchars($item['name'])?></td>
    <td><?=format_price($item['price'])?></td>
    <td>
      <form method="post" class="qty-form" aria-label="Update quantity for <?=htmlspecialchars($item['name'])?>">
        <input type="hidden" name="item_id" value="<?=$id?>" />
        <input type="number" name="quantity" min="1" value="<?=$qty?>" aria-describedby="qty-desc-<?=$id?>" required />
        <button type="submit" name="update_qty" title="Update quantity">Update</button>
      </form>
    </td>
    <td><?=format_price($subtotal)?></td>
    <td>
      <form method="post" aria-label="Remove <?=htmlspecialchars($item['name'])?> from cart">
        <input type="hidden" name="item_id" value="<?=$id?>" />
        <button type="submit" name="remove_item" class="link-button">Remove</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="4" style="text-align:right; font-weight:700;">Total:</td>
      <td colspan="2" style="font-weight:700;"><?=format_price($total_price)?></td>
    </tr>
  </tfoot>
</table>
<div class="actions">
  <a href="index.php" class="continue-link" style="align-self:center;">Continue Shopping</a>
  <a href="checkout.php" style="background-color: var(--color-primary); color: white; border-radius: var(--border-radius); padding: 0.6rem 1rem; text-decoration:none; font-weight:700; transition: background-color 0.3s ease;">Checkout</a>
</div>
<?php endif; ?>
</main>

</body>
</html>
