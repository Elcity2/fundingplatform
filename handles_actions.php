<?php
if (isset($_GET['action']) && $_GET['action'] == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    $stmt = $db->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $password, $user_type])) {
        echo '<div class="alert alert-success">Registration successful! You can now <a href="?action=login">log in</a>.</div>';
    } else {
        echo '<div class="alert alert-danger">Error during registration. Please try again.</div>';
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
        echo '<div class="alert alert-success">Login successful! Redirecting to the dashboard...</div>';
        header("Refresh: 2; url=elphine.php");
    } else {
        echo '<div class="alert alert-danger">Invalid email or password. Please try again.</div>';
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'create_project' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $goal = $_POST['goal'];
    $deadline = $_POST['deadline'];
    $category = $_POST['category'];

    $stmt = $db->prepare("INSERT INTO projects (user_id, title, description, category, goal, deadline, current_funds, status) VALUES (?, ?, ?, ?, ?, ?, 0, 'active')");
    if ($stmt->execute([$_SESSION['user_id'], $title, $description, $category, $goal, $deadline])) {
        echo '<div class="alert alert-success">Project created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error creating project. Please try again.</div>';
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'donate' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $project_id = $_POST['project_id'];
    $amount = $_POST['amount'];

    $stmt = $db->prepare("INSERT INTO donations (project_id, user_id, amount) VALUES (?, ?, ?)");
    if ($stmt->execute([$project_id, $_SESSION['user_id'], $amount])) {
        $update = $db->prepare("UPDATE projects SET current_funds = current_funds + ? WHERE id = ?");
        $update->execute([$amount, $project_id]);
        echo '<div class="alert alert-success">Donation successful! Thank you for your support.</div>';
    } else {
        echo '<div class="alert alert-danger">Error during donation. Please try again.</div>';
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: elphine.php");
}
?>