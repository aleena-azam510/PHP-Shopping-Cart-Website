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

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
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

$purchase_complete = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_purchase'])) {
    $purchase_complete = true;
    $_SESSION['cart'] = [];
}

if (isset($_GET['action']) && $_GET['action'] === 'logout'){
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" >
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Checkout - ShopEasy</title>
<style>
  /* Include same styling as previous checkout.php */
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');
  :root {
    --color-bg: #fff;
    --color-text: #6b7280;
    --color-primary: #111827;
    --color-primary-hover: #374151;
    --shadow: rgba(0,0,0,0.05);
    --success-color: #16a34a;
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
  .total-row td {
    font-weight: 700;
  }
  button.confirm-btn {
    margin-top: 1rem; padding: 0.75rem 1.25rem;
    background-color: var(--color-primary); color: white; border: none;
    font-weight: 700; border-radius: var(--border-radius);
    cursor: pointer; transition: background-color 0.3s ease;
    font-size: 1.1rem;
  }
  button.confirm-btn:hover, button.confirm-btn:focus {
    background-color: var(--color-primary-hover);
  }
  .success-message {
    color: var(--success-color);
    font-weight: 700;
    font-size: 1.25rem;
    margin-top: 1rem;
  }
  .back-link {
    display: inline-block;
    margin-top: 1rem;
    color: var(--color-primary);
    font-weight: 600;
    text-decoration:none;
  }
  .back-link:hover, .back-link:focus {
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
    <a href="cart.php" class="nav-links">Cart</a>
    <a href="index.php?action=logout" class="nav-links" aria-label="Logout">Logout</a>
  </div>
</nav>
</header>

<main>
<h1>Checkout</h1>

<?php if ($purchase_complete): ?>
  <p class="success-message" role="alert" tabindex="-1">
    Thank you for your purchase, <?=htmlspecialchars($_SESSION['user']['name'])?>! Your order has been processed.
  </p>
  <a href="index.php" class="back-link" aria-label="Back to products">← Continue Shopping</a>
<?php else: ?>

<table aria-describedby="order-total">
  <thead>
    <tr>
      <th scope="col">Product</th>
      <th></th>
      <th scope="col">Unit Price</th>
      <th scope="col">Quantity</th>
      <th scope="col">Subtotal</th>
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
      <td><?=$qty?></td>
      <td><?=format_price($subtotal)?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr class="total-row">
      <td colspan="4" style="text-align:right;">Total:</td>
      <td id="order-total"><?=format_price($total_price)?></td>
    </tr>
  </tfoot>
</table>

<form method="post" aria-label="Confirm purchase form">
  <button type="submit" name="confirm_purchase" class="confirm-btn">Confirm Purchase</button>
</form>

<a href="cart.php" class="back-link" aria-label="Back to cart">← Back to Cart</a>

<?php endif; ?>
</main>
</body>
</html>
