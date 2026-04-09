<?php require __DIR__ . '/layouts/header.php'; ?>
<?php require __DIR__ . '/layouts/menu.php'; ?>

    <h1>Menú principal</h1>
    <p>Desde aquí podrás acceder a las operaciones del sistema.</p>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

    <ul>
        <li><strong>C:</strong> Registrar usuario</li>
        <li><strong>R:</strong> Consultar usuario</li>
        <li><strong>U:</strong> Actualizar usuario</li>
        <li><strong>D:</strong> Eliminar usuario</li>
        <li><strong>L:</strong> Listar usuarios</li>
    </ul>

<?php require __DIR__ . '/layouts/footer.php'; ?>