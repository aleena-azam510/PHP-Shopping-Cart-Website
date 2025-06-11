<?php
session_start();

require_once 'config.php';

// Create users table if not exists
$createUsersTableSQL = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

$mysqli->query($createUsersTableSQL);

// Initialize error and success messages
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $errors[] = "Please fill in both email and password.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        } else {
            // Fetch user from DB
            $stmt = $mysqli->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                // Verify password (hashed)
                if (password_verify($password, $user['password'])) {
                    // Login success
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $email,
                        'name' => $user['name']
                    ];
                    $stmt->close();
                    header("Location: index.php");
                    exit;
                } else {
                    $errors[] = "Incorrect password.";
                }
            } else {
                $errors[] = "User not found.";
            }
            $stmt->close();
        }
    } elseif ($action === 'signup') {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (!$name || !$email || !$password || !$password_confirm) {
            $errors[] = "Please fill in all signup fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        } elseif ($password !== $password_confirm) {
            $errors[] = "Passwords do not match.";
        } else {
            // Check if email already exists
            $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = "Email is already registered.";
                $stmt->close();
            } else {
                $stmt->close();

                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $mysqli->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $name, $email, $password_hash);
                if ($stmt->execute()) {
                    $success = "Signup successful! Please login below.";
                } else {
                    $errors[] = "Database error: Could not register user.";
                }
                $stmt->close();
            }
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Login & Signup - Shopping Cart</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');
  :root {
    --color-bg: #fff;
    --color-text: #6b7280;
    --color-primary: #111827;
    --color-primary-hover: #374151;
    --color-error: #dc2626;
    --color-success: #16a34a;
    --shadow: rgba(0,0,0,0.05);
    --border-radius: 0.75rem;
    --spacing: 1rem;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; font-family: 'Poppins', sans-serif; background: var(--color-bg);
    color: var(--color-text); line-height: 1.6; padding: 2rem 1rem; min-height: 100vh;
    display: flex; justify-content: center; align-items: center;
  }
  .container {
    max-width: 900px; width: 100%; display: flex; gap: 2rem;
    background: var(--color-bg); box-shadow: 0 4px 20px var(--shadow);
    border-radius: var(--border-radius); padding: 2rem;
  }
  form {
    flex: 1; display: flex; flex-direction: column;
  }
  h2 {
    font-weight: 700; font-size: 2rem; color: var(--color-primary);
    margin-bottom: 1rem;
  }
  label {
    font-weight: 600; margin-bottom: 0.3rem; color: var(--color-primary);
  }
  input[type=text], input[type=email], input[type=password] {
    padding: 0.5rem 0.75rem; margin-bottom: var(--spacing);
    border: 1px solid #d1d5db; border-radius: var(--border-radius);
    font-size: 1rem; transition: border-color 0.3s ease;
  }
  input[type=text]:focus, input[type=email]:focus, input[type=password]:focus {
    outline: none; border-color: var(--color-primary);
    box-shadow: 0 0 5px var(--color-primary);
  }
  button {
    background-color: var(--color-primary); color: white; border: none;
    padding: 0.75rem 1rem; font-weight: 700; border-radius: var(--border-radius);
    cursor: pointer; transition: background-color 0.3s ease; margin-top: 0.5rem;
  }
  button:hover, button:focus {
    background-color: var(--color-primary-hover);
  }
  .messages {
    padding: 1rem; margin-bottom: 1rem; border-radius: var(--border-radius);
  }
  .error {
    background-color: #fee2e2; color: var(--color-error);
    border: 1px solid var(--color-error);
  }
  .success {
    background-color: #dcfce7; color: var(--color-success);
    border: 1px solid var(--color-success);
  }
  @media (max-width: 860px) {
    .container {
      flex-direction: column; padding: 1rem;
    }
  }
</style>
</head>
<body>
  <div class="container" role="main">
    <form method="post" aria-label="Login form" novalidate>
      <h2>Login</h2>
      <?php if ($errors && ($_POST['action'] ?? '') === 'login'): ?>
        <div class="messages error" role="alert" tabindex="-1">
          <?php foreach ($errors as $e) { echo htmlspecialchars($e) . "<br>"; } ?>
        </div>
      <?php endif; ?>
      <label for="login-email">Email</label>
      <input id="login-email" name="email" type="email" required autofocus autocomplete="username" />
      <label for="login-password">Password</label>
      <input id="login-password" name="password" type="password" required autocomplete="current-password" />
      <input type="hidden" name="action" value="login" />
      <button type="submit">Login</button>
    </form>

    <form method="post" aria-label="Signup form" novalidate>
      <h2>Sign Up</h2>
      <?php if ($errors && ($_POST['action'] ?? '') === 'signup'): ?>
        <div class="messages error" role="alert" tabindex="-1">
          <?php foreach ($errors as $e) { echo htmlspecialchars($e) . "<br>"; } ?>
        </div>
      <?php elseif ($success && ($_POST['action'] ?? '') === 'signup'): ?>
        <div class="messages success" role="alert" tabindex="-1"><?=htmlspecialchars($success)?></div>
      <?php endif; ?>
      <label for="signup-name">Full Name</label>
      <input id="signup-name" name="name" type="text" required autocomplete="name" />
      <label for="signup-email">Email</label>
      <input id="signup-email" name="email" type="email" required autocomplete="email" />
      <label for="signup-password">Password</label>
      <input id="signup-password" name="password" type="password" required autocomplete="new-password" />
      <label for="signup-password-confirm">Confirm Password</label>
      <input id="signup-password-confirm" name="password_confirm" type="password" required autocomplete="new-password" />
      <input type="hidden" name="action" value="signup" />
      <button type="submit">Sign Up</button>
    </form>
  </div>
</body>
</html>
