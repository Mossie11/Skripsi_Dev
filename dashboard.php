<?php
// Dashboard page shown after successful login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #000;
            color: white;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #222;
            color: #ddd; /* header text & icon color */
            padding: 1rem 1rem 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar h1 {
            margin: 0;
            flex: 1;
        }

        .hamburger {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            background: none;
            border: none;
            margin: 10px 20px;
        }

        .hamburger span {
            width: 28px;
            height: 4px;
            background-color: #ddd; 
            border-radius: 2px;
            transition: all 0.3s ease;
            display: block;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(7px, 7px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 100px; /* lowered further */
            width: 260px;
            height: calc(100vh - 100px);
            background-color: #222;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 99;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
            padding-top: 1.5rem;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
            transition: background-color 0.3s, color 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #333;
            color: white;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 98;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
        }

        .close-btn:hover {
            color: white;
        }

        .overlay.active {
            display: block;
        }

        .navbar a.logout {
            color: white;
            text-decoration: none;
            background-color: #007bff;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            margin-left: 1rem;
        }

        .navbar a.logout:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #1a1a1a;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .welcome-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: white;
        }

        .card {
            background-color: #2a2a2a;
            border: 1px solid #444;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            color: white;
        }

        .card h2 {
            margin-top: 0;
            color: white;
        }

        .card p {
            color: #ccc;
        }

        .card a {
            color: #007bff;
            text-decoration: none;
        }

        .card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Dashboard</h1>
        <a href="login.php" class="logout">Logout</a>
    </div>

    <button class="hamburger" id="hamburgerBtn" style="margin: 10px 20px;">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar" id="sidebar">
        <button class="close-btn" id="closeBtn">×</button>
        <ul class="sidebar-menu">
            <li><a href="#dashboard">Dashboard</a></li>
            <li><a href="#profile">Profile</a></li>
            <li><a href="#settings">Settings</a></li>
            <li><a href="#messages">Messages</a></li>
            <li><a href="#help">Help & Support</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>
    </aside>

    <div class="container">
        <div class="welcome-message">
            Welcome! You have successfully logged in.
        </div>

        <div class="card">
            <h2>Account Information</h2>
            <p>Your account is active and ready to use.</p>
        </div>
    </div>

    <script>
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        hamburgerBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            hamburgerBtn.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            hamburgerBtn.classList.remove('active');
        });

        // Close sidebar when clicking a menu item
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                hamburgerBtn.classList.remove('active');
            });
        });

        // close button handler
        document.getElementById('closeBtn').addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            hamburgerBtn.classList.remove('active');
        });
    </script>
</body>
</html>