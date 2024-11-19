<?php
session_start();
$host = 'localhost';
$dbname = 'platform_db';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function showRegistrationForm() {
    echo '
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="?action=register">
            <input type="text" name="name" placeholder="Name" required class="form-control mb-3">
            <input type="email" name="email" placeholder="Email" required class="form-control mb-3">
            <input type="password" name="password" placeholder="Password" required class="form-control mb-3">
            <select name="user_type" class="form-select mb-3">
                <option value="individual">Individual</option>
                <option value="organization">Organization</option>
            </select>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <a href="elphine.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>';
}

function showLoginForm() {
    echo '
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="?action=login">
            <input type="email" name="email" placeholder="Email" required class="form-control mb-3">
            <input type="password" name="password" placeholder="Password" required class="form-control mb-3">
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <a href="elphine.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>';
}

function showProjectForm() {
    echo '
    <div class="container">
        <h2>Create Project</h2>
        <form method="POST" action="?action=create_project">
            <input type="text" name="title" placeholder="Project Title" required class="form-control mb-3">
            <textarea name="description" placeholder="Project Description" required class="form-control mb-3"></textarea>
            <input type="number" name="goal" placeholder="Funding Goal" required class="form-control mb-3">
            <input type="date" name="deadline" required class="form-control mb-3">
            <input type="text" name="category" placeholder="Category" required class="form-control mb-3">
            <button type="submit" class="btn btn-primary">Create Project</button>
        </form>
        <a href="elphine.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>';
}

function showProjects($db) {
    echo '<div class="container">
            <h2>Active Projects</h2>
            <div class="row">';
    $stmt = $db->prepare("SELECT * FROM projects WHERE status = 'active'");
    $stmt->execute();
    $projects = $stmt->fetchAll();

    foreach ($projects as $project) {
        echo '<div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($project['title']) . '</h5>
                        <p class="card-text">' . htmlspecialchars($project['description']) . '</p>
                        <p><strong>Goal:</strong> $' . $project['goal'] . ' | <strong>Raised:</strong> $' . $project['current_funds'] . '</p>
                        <p><strong>Deadline:</strong> ' . $project['deadline'] . '</p>
                        <form method="POST" action="?action=donate">
                            <input type="hidden" name="project_id" value="' . $project['id'] . '">
                            <input type="number" name="amount" placeholder="Donation Amount" required class="form-control mb-3">
                            <button type="submit" class="btn btn-secondary">Donate</button>
                        </form>
                    </div>
                </div>
            </div>';
    }
    echo '</div></div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdfunding Platform</title>
    <link rel="stylesheet" href="finny.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>Welcome to the Crowdfunding Platform</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="?action=register">Register</a>
                <a href="?action=login">Login</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?action=create_project">Create Project</a>
                    <a href="?action=logout">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php
    if (!isset($_SESSION['user_id'])) {
        if (isset($_GET['action']) && $_GET['action'] == 'register') {
            showRegistrationForm();
        } elseif (isset($_GET['action']) && $_GET['action'] == 'login') {
            showLoginForm();
        } else {
            echo '<div class="container">
                    <p>Connect with people and bring your ideas to life. Support projects or create your own!</p>
                    <a href="?action=register" class="btn btn-primary">Get Started</a>
                  </div>';
            showProjects($db);
        }
    } else {
        if (isset($_GET['action']) && $_GET['action'] == 'create_project') {
            showProjectForm();
        } elseif (isset($_GET['action']) && $_GET['action'] == 'logout') {
            session_destroy();
            header("Location: elphine.php");
        } else {
            showProjects($db);
        }
    }

    include 'handles_actions.php';
    ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?= date("Y") ?> Crowdfunding Platform. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>