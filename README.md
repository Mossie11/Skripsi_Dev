# Local Login Page Demo

This workspace contains a simple static login page you can open locally in your browser.

## Files

- **login.php** – the login form with account, password and a "Forgot password?" link (PHP file).

## Running

No server is required; just open `login.php` in your web browser (served through a PHP-enabled server).

If you prefer to serve it via a simple HTTP server, you can use Python:

```powershell
# open a terminal in the project folder
python -m http.server 8000
# then visit http://localhost:8000 in your browser
```

Or, with Node.js installed:

```powershell
npx http-server . -p 8000
```

This is intended for local development and demonstration. You can extend the form by connecting it to your backend of choice.