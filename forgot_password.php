<?php
// Forgot password page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .forgot-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        .forgot-container h2 {
            margin-top: 0;
            text-align: center;
        }

        .forgot-container p {
            color: #666;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .forgot-container input[type="email"] {
            width: 100%;
            padding: 0.5rem;
            margin: 0.5rem 0 1rem 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .forgot-container button {
            width: 100%;
            padding: 0.7rem;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .forgot-container button:hover {
            background-color: #0056b3;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            display: none;
            text-align: center;
        }

        .message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <h2>Reset Password</h2>
        <p>Enter your email address and we'll send you instructions to reset your password.</p>
        
        <div class="message" id="successMessage">
            Instructions have been sent to your email!
        </div>

        <form action="#" method="post" onsubmit="handleSubmit(event)">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Send Reset Link</button>
        </form>

        <div class="back-link">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <script>
        function handleSubmit(event) {
            event.preventDefault();
            document.getElementById('successMessage').classList.add('show');
            document.querySelector('form').reset();
        }
    </script>
</body>
</html>