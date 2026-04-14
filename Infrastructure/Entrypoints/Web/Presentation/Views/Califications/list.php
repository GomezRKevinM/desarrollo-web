<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

    <h1>Lista de calificaciones</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($califications)): ?>
    <p>No hay calificaciones registradas todavía.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Fecha</th><th>Estudiante</th><th>Asignatura</th><th>Docente</th><th>Nota</th><th>Porcentaje</th><th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($califications as $calification): ?>
            <tr>
                <td><?= htmlspecialchars($calification->getFecha(), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php
                    $studentName = 'Estudiante no encontrado';
                    foreach ($students as $student) {
                        if ($student->getId() === $calification->getStudentId()) {
                            $studentName = $student->getName() . ' ' . $student->getLastName();
                            break;
                        }
                    }
                    echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
                    ?>
                </td>
                <td><?= htmlspecialchars($calification->getAsignatura(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($calification->getDocente(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($calification->getNota(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(number_format($calification->getPorcentaje() * 100, 1) . '%', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a class="btn btn-sm" href="?route=califications.show&id=<?= urlencode($calification->getId()) ?>">Ver</a>
                    <a class="btn btn-sm btn-warning" href="?route=califications.edit&id=<?= urlencode($calification->getId()) ?>">Editar</a>
                    <form method="POST" action="?route=califications.delete" style="display:inline"
                          onsubmit="return confirm('¿Eliminar esta calificación?')">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($calification->getId(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
