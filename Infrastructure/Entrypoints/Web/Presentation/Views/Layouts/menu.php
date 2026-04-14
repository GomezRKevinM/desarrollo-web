<?php declare(strict_types=1); ?>
<?php $authUser = $_SESSION['auth'] ?? null; ?>

<nav>
    <a class="link" href="?route=home">Inicio</a>
    <?php if ($authUser): ?>
        <a class="link" href="?route=users.create">Registrar usuario</a>
        <a class="link" href="?route=users.index">Listar usuarios</a>
        <span style="margin: 0 10px; color:#555;">|</span>
        <span style="color:#333;">👤 <?= htmlspecialchars($authUser['name'], ENT_QUOTES, 'UTF-8') ?></span>
        &nbsp;
        <a class="link" href="?route=auth.logout">Cerrar sesión</a>
    <?php else: ?>
        <a class="link" href="?route=auth.login">Iniciar sesión</a>
        <a class="link" href="?route=auth.forgot">Recuperar contraseña</a>
    <?php endif; ?>
</nav>

<hr>