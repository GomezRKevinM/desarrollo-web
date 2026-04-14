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

    <div style="margin-top: 30px;">
        <h2>Usuarios</h2>
        <ul>
            <li><a class="link" href="?route=index">Listar usuarios</a></li>
            <li><a class="link" href="?route=create">Registrar usuario</a></li>
        </ul>
    </div>

    <div style="margin-top: 30px;">
        <h2>Estudiantes</h2>
        <ul>
            <li><a class="link" href="?route=students.index">Listar estudiantes</a></li>
            <li><a class="link" href="?route=students.create">Registrar estudiante</a></li>
        </ul>
    </div>

    <div style="margin-top: 30px;">
        <h2>Calificaciones</h2>
        <ul>
            <li><a class="link" href="?route=califications.index">Listar calificaciones</a></li>
            <li><a class="link" href="?route=califications.create">Registrar calificación</a></li>
        </ul>
    </div>

<?php require __DIR__ . '/layouts/footer.php'; ?>