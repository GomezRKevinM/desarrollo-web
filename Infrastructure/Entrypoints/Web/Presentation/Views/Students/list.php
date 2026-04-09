<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

    <h1>Lista de estudiantes</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($students)): ?>
    <p>No hay estudiantes registrados todavía.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nombre</th><th>Apellidos</th><th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student->getName(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($student->getLastName(), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a class="btn btn-sm" href="?route=studentss.show&id=<?= urlencode($student->getId()) ?>">Ver</a>
                    <a class="btn btn-sm btn-warning" href="?route=students.edit&id=<?= urlencode($student->getId()) ?>">Editar</a>
                    <form method="POST" action="?route=students.delete" style="display:inline"
                          onsubmit="return confirm('¿Eliminar este estudiante?')">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($student->getId(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>