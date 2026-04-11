<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

    <h1>Detalle de calificación</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

    <table class="detail-table">
        <tr><th>ID</th>     <td><?= htmlspecialchars($calification->getId(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Fecha</th> <td><?= htmlspecialchars($calification->getFecha(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Docente</th> <td><?= htmlspecialchars($calification->getDocente(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Asignatura</th> <td><?= htmlspecialchars($calification->getAsignatura(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Carrera</th> <td><?= htmlspecialchars($calification->getCarrera(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Universidad</th> <td><?= htmlspecialchars($calification->getUniversidad(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Periodo</th> <td><?= htmlspecialchars($calification->getPeriodo(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Actividad Evaluada</th> <td><?= htmlspecialchars($calification->getActividadEvaluada(), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Porcentaje</th> <td><?= htmlspecialchars(number_format($calification->getPorcentaje() * 100, 1) . '%', ENT_QUOTES, 'UTF-8') ?></td></tr>
        <tr><th>Estudiante</th>
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
        </tr>
        <tr><th>Nota</th> <td><?= htmlspecialchars($calification->getNota(), ENT_QUOTES, 'UTF-8') ?></td></tr>
    </table>

    <p style="margin-top: 16px;">
        <a class="btn btn-warning" href="?route=califications.edit&amp;id=<?= urlencode($calification->getId()) ?>">Editar</a>
        &nbsp;
        <a class="btn" href="?route=califications.index">Volver al listado</a>
    </p>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
