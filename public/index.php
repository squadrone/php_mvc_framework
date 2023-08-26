<?php

declare(strict_types=1);

use App\Framework\Application;

ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php';

$app = Application::getInstance();
$app->start();

dump($app);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
</head>
<body>

<h1>This is a Heading</h1>
<p>This is a paragraph.</p>
<form method="post" enctype="multipart/form-data">
    <input type="text" name="username" id="username" value="user" />
    <input type="password" name="password" id="password" value="pass" />
    <input type="file" name="file1">
    <input type="submit" name="submit" value="Gönder" />
</form>
</body>
</html>
