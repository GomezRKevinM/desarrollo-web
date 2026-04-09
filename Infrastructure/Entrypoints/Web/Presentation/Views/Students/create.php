<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

    <h1>Registrar estudiante</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

    <form method="POST" action="?route=students.store">

        <div class="form-group">
            <label for="name">Nombre</label><br>
            <input type="text" id="name" name="name"
                   value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['name'])): ?>
                <div class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="lastName">Correo</label><br>
            <input type="text" id="lastName" name="lastName"
                   value="<?= htmlspecialchars($old['lastName'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['lastName'])): ?>
                <div class="field-error"><?= htmlspecialchars($errors['lastName'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Registrar estudiante</button>
    </form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>