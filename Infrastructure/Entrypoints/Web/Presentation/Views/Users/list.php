<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

    <h1>Lista de usuarios</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($users)): ?>
    <p>No hay usuarios registrados todavía.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user->getRole(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user->getStatus(), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a class="btn btn-sm" href="?route=users.show&id=<?= urlencode($user->getId()) ?>">Ver</a>
                    <a class="btn btn-sm btn-warning" href="?route=users.edit&id=<?= urlencode($user->getId()) ?>">Editar</a>
                    <form method="POST" action="?route=users.delete" style="display:inline"
                          onsubmit="return confirm('¿Eliminar este usuario?')">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($user->getId(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>