<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 560px;
            margin: 40px auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 32px;
        }

        h2 {
            color: #333;
            margin-top: 0;
        }

        .password-box {
            font-size: 1.4em;
            font-weight: bold;
            background: #f0f7ff;
            border: 1px solid #99c8ff;
            border-radius: 4px;
            padding: 12px 20px;
            display: inline-block;
            letter-spacing: 2px;
            margin: 12px 0;
        }

        .footer-note {
            font-size: 0.8em;
            color: #888;
            margin-top: 24px;
            border-top: 1px solid #eee;
            padding-top: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Hola, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h2>

    <p>
        Recibimos una solicitud de recuperación de contraseña para la cuenta registrada
        con el correo <strong><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></strong>.
    </p>

    <p>Tu nueva contraseña temporal es:</p>

    <div class="password-box"><?= htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8') ?></div>

    <p>
        Por seguridad, <strong>te recomendamos cambiar esta contraseña</strong>
        inmediatamente después de iniciar sesión.
    </p>

    <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>

    <div class="footer-note">
        Este es un correo automático generado por el sistema, por favor no respondas a este mensaje.
    </div>
</div>
</body>
</html>