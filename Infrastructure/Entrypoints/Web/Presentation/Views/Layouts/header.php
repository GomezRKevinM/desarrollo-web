<?php declare(strict_types=1);

$route = $_GET['route'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'CRUD Usuarios', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="./assets/styles/home.css">

</head>
<body>