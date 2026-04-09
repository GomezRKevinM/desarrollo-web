<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Common\DependencyInjection;
use App\Application\Services\Dto\Commands\LoginCommand;
use App\Domain\Enums\UserRoleEnum;
use App\Domain\Enums\UserStatusEnum;
use App\Domain\ValuesObjects\UserEmail;
use App\Domain\ValuesObjects\UserPassword;
use App\Infrastructure\Entrypoints\Web\Controllers\Config\WebRoutes;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateUserWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateUserWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\StudentResponse;
use App\Infrastructure\Entrypoints\Web\Presentation\Flash;
use App\Infrastructure\Entrypoints\Web\Presentation\View;

// ── Guardia de seguridad ──────────────────────────────────────────────────────
// El .htaccess redirige internamente cualquier URL fuera de /public/ hacia aquí.
// Detectamos ese caso y redirigimos al usuario a donde corresponde.
(function (): void {
    $requestPath = rtrim(
        (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH),
        '/'
    );
    $publicBase = rtrim(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');

    if ($requestPath !== $publicBase && !str_starts_with($requestPath, $publicBase . '/')) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $dest = isset($_SESSION['auth']['id']) ? 'home' : 'auth.login';
        header('Location: ' . $publicBase . '/index.php?route=' . $dest);
        exit;
    }
})();

// ── Bootstrap ─────────────────────────────────────────────────────────────────
Flash::start();

// ── Auth helpers ──────────────────────────────────────────────────────────────
function isLoggedIn(): bool
{
    return isset($_SESSION['auth']['id']);
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        Flash::setMessage('Debes iniciar sesión para acceder a esta sección.');
        View::redirect('auth.login');
    }
}

// ── Routing ───────────────────────────────────────────────────────────────────
$route  = isset($_GET['route']) ? trim((string) $_GET['route']) : 'home';
$routes = WebRoutes::routes();

if (!isset($routes[$route])) {
    http_response_code(404);
    View::render('home', buildHomeViewData('Ruta no encontrada.'));
    exit;
}

$definition = $routes[$route];
$httpMethod = strtoupper((string) $_SERVER['REQUEST_METHOD']);

if ($httpMethod !== $definition['method']) {
    http_response_code(405);
    View::render('home', buildHomeViewData('Método HTTP no permitido.'));
    exit;
}

// Rutas públicas: no requieren sesión activa
$publicActions = ['home', 'login', 'authenticate', 'logout', 'forgot', 'forgot.send', 'create', 'store'];

if (!in_array($definition['action'], $publicActions, true) && !isLoggedIn()) {
    Flash::setMessage('Debes iniciar sesión para acceder a esta sección.');
    View::redirect('auth.login');
}

// ── Dispatch ──────────────────────────────────────────────────────────────────
try {
    switch ($definition['action']) {

        // ── Home ──────────────────────────────────────────────────────────────
        case 'home':
            View::render('home', buildHomeViewData());
            break;

        // ── Crear usuario (formulario) ─────────────────────────────────────
        case 'create':
            View::render('users/create', buildCreateUserViewData());
            break;

        // ── Crear estudiante (formulario) ─────────────────────────────────────
        case 'students.create':
            View::render('students/create', buildCreateStudentViewData());
            break;

        // ── Crear usuario (submit) ─────────────────────────────────────────
        case 'store':
            $form           = getCreateUserFormData();
            $form['id']     = generateUuid4();
            $errors         = validateCreateUserForm($form);

            if (!empty($errors)) {
                Flash::setOld($form);
                Flash::setErrors($errors);
                Flash::setMessage('Corrige los errores del formulario.');
                View::redirect('users.create');
            }

            $request = new CreateUserWebRequest(
                id:       $form['id'],
                name:     $form['name'],
                email:    $form['email'],
                password: $form['password'],
                role:     $form['role'],
            );

            DependencyInjection::getUserController()->store($request);
            Flash::setSuccess('Usuario registrado correctamente.');
            View::redirect('users.index');
            break;

        // ── Crear estudiante (submit) ─────────────────────────────────────────
        case 'students.store':
            $form           = getCreateStudentFormData();
            $form['id']     = generateUuid4();
            $errors         = validateCreateStudentForm($form);

            if (!empty($errors)) {
                Flash::setOld($form);
                Flash::setErrors($errors);
                Flash::setMessage('Corrige los errores del formulario.');
                View::redirect('students.create');
            }

            $request = new CreateStudentWebRequest(
                id:       $form['id'],
                name:     $form['name'],
                lastName: $form['lastName'],
            );

            DependencyInjection::getStudentController()->store($request);
            Flash::setSuccess('Estudiante registrado correctamente.');
            View::redirect('students.index');
            break;

        // ── Listar estudiantes ────────────────────────────────────────────────
        case 'students.index':
            $students = DependencyInjection::getStudentController()->index();
            View::render('students/list', buildListStudentsViewData($students));
            break;

        // ── Ver estudiante ────────────────────────────────────────────────────
        case 'students.show':
            $id      = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $student = DependencyInjection::getStudentController()->show($id);
            View::render('students/show', [
                'pageTitle' => 'Detalle de estudiante',
                'student'   => $student,
                'message'   => Flash::message(),
            ]);
            break;

        // ── Editar estudiante (formulario) ────────────────────────────────────
        case 'students.edit':
            $id      = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $student = DependencyInjection::getStudentController()->show($id);
            View::render('students/edit', buildEditStudentViewData($student));
            break;

        // ── Editar estudiante (submit) ────────────────────────────────────────
        case 'students.update':
            $form   = getUpdateStudentFormData();
            $errors = validateUpdateStudentForm($form);

            if (!empty($errors)) {
                Flash::setOld($form);
                Flash::setErrors($errors);
                Flash::setMessage('Corrige los errores del formulario.');
                header('Location: ?route=students.edit&id=' . urlencode($form['id']));
                exit;
            }

            $request = new UpdateStudentWebRequest(
                id:       $form['id'],
                name:     $form['name'],
                lastName: $form['lastName'],
            );

            DependencyInjection::getStudentController()->update($request);
            Flash::setSuccess('Estudiante actualizado correctamente.');
            View::redirect('students.index');
            break;

        // ── Eliminar estudiante ───────────────────────────────────────────────
        case 'students.delete':
            $id = isset($_POST['id']) ? trim((string) $_POST['id']) : '';
            DependencyInjection::getStudentController()->delete($id);
            Flash::setSuccess('Estudiante eliminado correctamente.');
            View::redirect('students.index');
            break;

        // ── Listar usuarios ────────────────────────────────────────────────
        case 'index':
            $users = DependencyInjection::getUserController()->index();
            View::render('users/list', buildListUsersViewData($users));
            break;

        // ── Ver usuario ────────────────────────────────────────────────────
        case 'show':
            $id   = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $user = DependencyInjection::getUserController()->show($id);
            View::render('users/show', [
                'pageTitle' => 'Detalle de usuario',
                'user'      => $user,
                'message'   => Flash::message(),
            ]);
            break;

        // ── Editar usuario (formulario) ────────────────────────────────────
        case 'edit':
            $id   = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $user = DependencyInjection::getUserController()->show($id);
            View::render('users/edit', buildEditUserViewData($user));
            break;

        // ── Editar usuario (submit) ────────────────────────────────────────
        case 'update':
            $form   = getUpdateUserFormData();
            $errors = validateUpdateUserForm($form);

            if (!empty($errors)) {
                Flash::setOld($form);
                Flash::setErrors($errors);
                Flash::setMessage('Corrige los errores del formulario.');
                header('Location: ?route=users.edit&id=' . urlencode($form['id']));
                exit;
            }

            $request = new UpdateUserWebRequest(
                id:       $form['id'],
                name:     $form['name'],
                email:    $form['email'],
                password: $form['password'],
                role:     $form['role'],
                status:   $form['status'],
            );

            DependencyInjection::getUserController()->update($request);
            Flash::setSuccess('Usuario actualizado correctamente.');
            View::redirect('users.index');
            break;

        // ── Eliminar usuario ───────────────────────────────────────────────
        case 'delete':
            $id = isset($_POST['id']) ? trim((string) $_POST['id']) : '';
            DependencyInjection::getUserController()->delete($id);
            Flash::setSuccess('Usuario eliminado correctamente.');
            View::redirect('users.index');
            break;

        // ── Login (formulario) ─────────────────────────────────────────────
        case 'login':
            if (isLoggedIn()) {
                View::redirect('home');
            }
            View::render('auth/login', [
                'pageTitle' => 'Iniciar sesión',
                'message'   => Flash::message(),
                'errors'    => Flash::errors(),
                'old'       => Flash::old(),
            ]);
            break;

        // ── Login (submit) ─────────────────────────────────────────────────
        case 'authenticate':
            $email    = trim(strtolower((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');

            $authErrors = [];
            if ($email === '') {
                $authErrors['email'] = 'El correo es obligatorio.';
            }
            if ($password === '') {
                $authErrors['password'] = 'La contraseña es obligatoria.';
            }

            if (!empty($authErrors)) {
                Flash::setErrors($authErrors);
                Flash::setOld(['email' => $email]);
                View::redirect('auth.login');
            }

            $command = new LoginCommand($email, $password);
            $user    = DependencyInjection::getLoginUseCase()->execute($command);

            $_SESSION['auth'] = [
                'id'    => $user->id()->value(),
                'name'  => $user->name()->value(),
                'email' => $user->email()->value(),
                'role'  => $user->role(),
            ];

            Flash::setSuccess('Bienvenido/a, ' . $user->name()->value() . '.');
            View::redirect('home');
            break;

        // ── Logout ────────────────────────────────────────────────────────
        case 'logout':
            session_destroy();
            header('Location: ?route=auth.login');
            exit;

        // ── Recuperar contraseña (formulario) ─────────────────────────────
        case 'forgot':
            View::render('auth/forgot-password', [
                'pageTitle' => 'Recuperar contraseña',
                'message'   => Flash::message(),
                'success'   => Flash::success(),
                'errors'    => Flash::errors(),
                'old'       => Flash::old(),
            ]);
            break;

        // ── Recuperar contraseña (submit) ──────────────────────────────────
        case 'forgot.send':
            $forgotEmail = trim(strtolower((string) ($_POST['email'] ?? '')));

            if ($forgotEmail === '' || !filter_var($forgotEmail, FILTER_VALIDATE_EMAIL)) {
                Flash::setErrors(['email' => 'Introduce un correo electrónico válido.']);
                Flash::setOld(['email' => $forgotEmail]);
                View::redirect('auth.forgot');
            }

            $repository = DependencyInjection::getUserRepository();
            $foundUser  = $repository->getByEmail(new UserEmail($forgotEmail));

            // Siempre mensaje genérico → no revela si el email existe
            if ($foundUser !== null && $foundUser->status() === UserStatusEnum::ACTIVE) {
                $tempPassword = bin2hex(random_bytes(5));
                $newPassword  = UserPassword::fromPlainText($tempPassword);
                $updatedUser  = $foundUser->changePassword($newPassword);
                $repository->update($updatedUser);
                sendPasswordRecoveryEmail(
                    email:        $foundUser->email()->value(),
                    name:         $foundUser->name()->value(),
                    tempPassword: $tempPassword,
                );
            }

            Flash::setSuccess(
                'Si el correo está registrado y la cuenta está activa, ' .
                'recibirás un mensaje con tu contraseña temporal.'
            );
            View::redirect('auth.forgot');
            break;

        default:
            throw new \RuntimeException('Acción no soportada.');
    }

} catch (\Throwable $exception) {
    $msg = $exception->getMessage();
    Flash::setMessage($msg);

    switch ($route) {
        case 'users.store':
            Flash::setOld(getCreateUserFormData());
            View::redirect('users.create');
            break;
        case 'users.update':
            $updateId = trim((string) ($_POST['id'] ?? ''));
            Flash::setOld(getUpdateUserFormData());
            header('Location: ?route=users.edit&id=' . urlencode($updateId));
            exit;
        case 'auth.authenticate':
            Flash::setOld(['email' => trim(strtolower((string) ($_POST['email'] ?? '')))]);
            View::redirect('auth.login');
            break;
        case 'auth.forgot.send':
            Flash::setOld(['email' => trim((string) ($_POST['email'] ?? ''))]);
            View::redirect('auth.forgot');
            break;
        case 'users.show':
        case 'users.edit':
        case 'users.delete':
            View::redirect('users.index');
            break;
        case 'students.store':
            Flash::setOld(getCreateStudentFormData());
            View::redirect('students.create');
            break;
        case 'students.update':
            $updateId = trim((string) ($_POST['id'] ?? ''));
            Flash::setOld(getUpdateStudentFormData());
            header('Location: ?route=students.edit&id=' . urlencode($updateId));
            exit;
        case 'students.show':
        case 'students.edit':
        case 'students.delete':
            View::redirect('students.index');
            break;
        default:
            View::render('home', buildHomeViewData($msg));
            break;
    }
}

// ── Helpers de email ──────────────────────────────────────────────────────────
function sendPasswordRecoveryEmail(string $email, string $name, string $tempPassword): void
{
    $templateFile = __DIR__ . '/../crud-usuarios/Infrastructure/Entrypoints/Web/Presentation/Views/emails/forgot-password.php';

    ob_start();
    extract(['email' => $email, 'name' => $name, 'tempPassword' => $tempPassword], EXTR_SKIP);
    require $templateFile;
    $htmlBody = (string) ob_get_clean();

    $subject = '=?UTF-8?B?' . base64_encode('Recuperación de contraseña') . '?=';
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: CRUD Usuarios <no-reply@crud-usuarios.local>',
        'X-Mailer: PHP/' . PHP_VERSION,
    ]);

    mail($email, $subject, $htmlBody, $headers);
}

// ── Constructores de datos para vistas ────────────────────────────────────────
/** @return array<string, mixed> */
function buildHomeViewData(string $message = ''): array
{
    return [
        'pageTitle' => 'Menú principal',
        'message'   => $message,
        'success'   => Flash::success(),
    ];
}

/** @return array<string, mixed> */
function buildCreateUserViewData(): array
{
    return [
        'pageTitle'   => 'Registrar usuario',
        'roleOptions' => UserRoleEnum::values(),
        'message'     => Flash::message(),
        'success'     => Flash::success(),
        'errors'      => Flash::errors(),
        'old'         => Flash::old(),
    ];
}

/** @return array<string, mixed> */
function buildCreateStudentViewData(): array
{
    return [
        'pageTitle'   => 'Registrar estudiante',
        'message'     => Flash::message(),
        'success'     => Flash::success(),
        'errors'      => Flash::errors(),
        'old'         => Flash::old(),
    ];
}

/**
 * @param  UserResponse[]       $users
 * @return array<string, mixed>
 */
function buildListUsersViewData(array $users): array
{
    return [
        'pageTitle' => 'Lista de usuarios',
        'users'     => $users,
        'message'   => Flash::message(),
        'success'   => Flash::success(),
    ];
}

/**
 * @param  StudentResponse[] $students
 * @return array<string, mixed>
 */
function buildListStudentsViewData(array $students): array
{
    return [
        'pageTitle' => 'Lista de estudiantes',
        'students'     => $students,
        'message'   => Flash::message(),
        'success'   => Flash::success(),
    ];
}

/** @return array<string, mixed> */
function buildEditUserViewData(UserResponse $user): array
{
    return [
        'pageTitle'     => 'Editar usuario',
        'user'          => $user,
        'roleOptions'   => UserRoleEnum::values(),
        'statusOptions' => UserStatusEnum::values(),
        'message'       => Flash::message(),
        'errors'        => Flash::errors(),
        'old'           => Flash::old(),
    ];
}

/** @return array<string, mixed> */
function buildEditStudentViewData(StudentResponse $student): array
{
    return [
        'pageTitle'   => 'Editar estudiante',
        'student'     => $student,
        'message'     => Flash::message(),
        'errors'      => Flash::errors(),
        'old'         => Flash::old(),
    ];
}

// ── Lectores de formulario ────────────────────────────────────────────────────
/** @return array<string, string> */
function getCreateUserFormData(): array
{
    return [
        'name'     => isset($_POST['name'])     ? trim((string) $_POST['name'])     : '',
        'email'    => isset($_POST['email'])    ? trim((string) $_POST['email'])    : '',
        'password' => isset($_POST['password']) ? trim((string) $_POST['password']) : '',
        'role'     => isset($_POST['role'])     ? trim((string) $_POST['role'])     : '',
    ];
}

/** @return array<string, string> */
function getCreateStudentFormData(): array
{
    return [
        'name'     => isset($_POST['name'])     ? trim((string) $_POST['name'])     : '',
        'lastName' => isset($_POST['lastName']) ? trim((string) $_POST['lastName']) : '',
    ];
}

/** @return array<string, string> */
function getUpdateUserFormData(): array
{
    return [
        'id'       => isset($_POST['id'])       ? trim((string) $_POST['id'])       : '',
        'name'     => isset($_POST['name'])     ? trim((string) $_POST['name'])     : '',
        'email'    => isset($_POST['email'])    ? trim((string) $_POST['email'])    : '',
        'password' => isset($_POST['password']) ? (string) $_POST['password']       : '',
        'role'     => isset($_POST['role'])     ? trim((string) $_POST['role'])     : '',
        'status'   => isset($_POST['status'])   ? trim((string) $_POST['status'])   : '',
    ];
}

/** @return array<string, string> */
function getUpdateStudentFormData(): array
{
    return [
        'id'       => isset($_POST['id'])       ? trim((string) $_POST['id'])       : '',
        'name'     => isset($_POST['name'])     ? trim((string) $_POST['name'])     : '',
        'lastName' => isset($_POST['lastName']) ? trim((string) $_POST['lastName']) : '',
    ];
}

// ── Validadores ───────────────────────────────────────────────────────────────
/**
 * @param  array<string, string> $form
 * @return array<string, string>
 */
function validateCreateUserForm(array $form): array
{
    $errors = [];

    if ($form['name'] === '') {
        $errors['name'] = 'El nombre es obligatorio.';
    }
    if ($form['email'] === '') {
        $errors['email'] = 'El correo es obligatorio.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El correo no tiene un formato válido.';
    }
    if ($form['password'] === '') {
        $errors['password'] = 'La contraseña es obligatoria.';
    } elseif (strlen($form['password']) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($form['role'] === '') {
        $errors['role'] = 'El rol es obligatorio.';
    }

    return $errors;
}

/**
 * @param  array<string, string> $form
 * @return array<string, string>
 */
function validateCreateStudentForm(array $form): array
{
    $errors = [];

    if ($form['name'] === '') {
        $errors['name'] = 'El nombre es obligatorio.';
    }
    if ($form['lastName'] === '') {
        $errors['lastName'] = 'El apellido es obligatorio.';
    }

    return $errors;
}

/**
 * @param  array<string, string> $form
 * @return array<string, string>
 */
function validateUpdateUserForm(array $form): array
{
    $errors = [];

    if ($form['name'] === '') {
        $errors['name'] = 'El nombre es obligatorio.';
    }
    if ($form['email'] === '') {
        $errors['email'] = 'El correo es obligatorio.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El correo no tiene un formato válido.';
    }
    if ($form['password'] !== '' && strlen($form['password']) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres si deseas cambiarla.';
    }
    if ($form['role'] === '') {
        $errors['role'] = 'El rol es obligatorio.';
    }
    if ($form['status'] === '') {
        $errors['status'] = 'El estado es obligatorio.';
    }

    return $errors;
}

/**
 * @param  array<string, string> $form
 * @return array<string, string>
 */
function validateUpdateStudentForm(array $form): array
{
    $errors = [];

    if ($form['name'] === '') {
        $errors['name'] = 'El nombre es obligatorio.';
    }
    if ($form['lastName'] === '') {
        $errors['lastName'] = 'El apellido es obligatorio.';
    }

    return $errors;
}

// ── UUID v4 ───────────────────────────────────────────────────────────────────
function generateUuid4(): string
{
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}