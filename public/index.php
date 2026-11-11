
<?php

require_once __DIR__ . '/../config/bootstrap.php';

use App\Middleware\BasicAuthMiddleware;

$response = BasicAuthMiddleware::handle();

if ($response !== null) {
    $response->send();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management Dashboard</title>
</head>
<body>
    <header>
        <h1>Product Management Dashboard</h1>
    </header>

    <main>
        <h2>Products</h2>
        <p>Welcome to your product management dashboard!</p>
    </main>
</body>
</html>
