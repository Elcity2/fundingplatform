<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdfunding Platform</title>
    <link rel="stylesheet" href="finny.css">
</head>
<body>
    <div class="container">
        <h1>Crowdfunding Platform</h1>
    </div>
<?php
$host = 'localhost';
$dbname = 'assignments';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
session_start();
function showRegistrationForm() {
    echo '
    <h2>Register</h2>
    <form method="POST" action="?action=register">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="user_type">
            <option value="individual">Individual</option>
            <option value="organization">Organization</option>
        </select>
        <button type="submit">Register</button>
    </form>';
}
function showLoginForm() {
    echo '
    <h2>Login</h2>
    <form method="POST" action="?action=login">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>';
}
function showProjectForm() {
    echo '
    <h2>Create Project</h2>
    <form method="POST" action="?action=create_project">
        <input type="text" name="title" placeholder="Project Title" required>
        <textarea name="description" placeholder="Project Description" required></textarea>
        <input type="number" name="goal" placeholder="Funding Goal" required>
        <input type="date" name="deadline" required>
        <input type="text" name="category" placeholder="Category" required>
        <button type="submit">Create Project</button>
    </form>';
}
function showProjects($db) {
    echo '<h2>Active Projects</h2><ul>';
    $stmt = $db->prepare("SELECT * FROM projects WHERE status = 'active'");
    $stmt->execute();
    $projects = $stmt->fetchAll();

    foreach ($projects as $project) {
        echo "<li>
            <h3>" . htmlspecialchars($project['title']) . "</h3>
            <p>" . htmlspecialchars($project['description']) . "</p>
            <p>Goal: $" . $project['goal'] . " | Raised: $" . $project['current_funds'] . "</p>
            <p>Deadline: " . $project['deadline'] . "</p>
            <form method='POST' action='?action=donate'>
                <input type='hidden' name='project_id' value='" . $project['id'] . "'>
                <input type='number' name='amount' placeholder='Donation Amount' required>
                <button type='submit'>Donate</button>
            </form>
        </li>";
    }
    echo '</ul>';
}
if (isset($_GET['action']) && $_GET['action'] == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    $stmt = $db->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $password, $user_type])) {
        echo "Registration successful!";
    } else {
        echo "Error during registration.";
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        header("Location: ?action=dashboard");
    } else {
        echo "Invalid email or password.";
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'create_project' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $goal = $_POST['goal'];
    $deadline = $_POST['deadline'];
    $category = $_POST['category'];

    $stmt = $db->prepare("INSERT INTO projects (user_id, title, description, category, goal, deadline) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $title, $description, $category, $goal, $deadline])) {
        echo "Project created successfully!";
    } else {
        echo "Error creating project.";
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'donate' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $project_id = $_POST['project_id'];
    $amount = $_POST['amount'];

    $stmt = $db->prepare("INSERT INTO donations (project_id, user_id, amount) VALUES (?, ?, ?)");
    if ($stmt->execute([$project_id, $_SESSION['user_id'], $amount])) {
        $update = $db->prepare("UPDATE projects SET current_funds = current_funds + ? WHERE id = ?");
        $update->execute([$amount, $project_id]);
        echo "Donation successful!";
    } else {
        echo "Error during donation.";
    }
}
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['action']) && $_GET['action'] == 'register') {
        showRegistrationForm();
    } elseif (isset($_GET['action']) && $_GET['action'] == 'login') {
        showLoginForm();
    } else {
        echo '<a href="?action=register">Register</a> | <a href="?action=login">Login</a>';
    }
} else {
    echo '<a href="?action=create_project">Create Project</a> | <a href="?action=logout">Logout</a>';
    
    if (isset($_GET['action']) && $_GET['action'] == 'create_project') {
        showProjectForm();
    } else {
        showProjects($db);
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ?");
}
?>
</body>
</html>