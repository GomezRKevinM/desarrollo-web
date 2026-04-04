**Proyecto:** Aplicación Web básica sobre PHP y MySQL aplicando arquitectura hexagonal y DDD  
**Tutor:** John Carlos Arrieta Arrieta  
**Documento:** 02 — Capa de Dominio

---

# Guia 02 - Dominio

## ¿Qué es el Dominio?

El dominio es el conocimiento del negocio expresado en código. Es la capa más interna de la arquitectura hexagonal y responde a la pregunta: **¿qué reglas son verdaderas sin importar la tecnología que use?**

Ejemplos de reglas de negocio:
- Un usuario siempre debe tener un email válido
- Un usuario siempre debe tener un nombre con al menos 3 caracteres
- Un usuario solo puede tener ciertos roles
- Un usuario recién creado siempre nace en estado `PENDING`

Estas reglas no cambian si pasas de MySQL a MongoDB, de web a API REST, de PHP a Java.

### Regla absoluta

> Ningún archivo del dominio importa algo de fuera de `app/Domain/`. No conoce PDO, `$_POST`, `$_SESSION`, MySQL, HTML, ni ningún framework.

---

## Estructura de carpetas

```
app/Domain/
├── Exceptions/
│   ├── InvalidUserIdException.php
│   ├── InvalidUserNameException.php
│   ├── InvalidUserEmailException.php
│   ├── InvalidUserPasswordException.php
│   ├── InvalidUserRoleException.php
│   ├── InvalidUserStatusException.php
│   ├── InvalidCredentialsException.php
│   ├── UserAlreadyExistsException.php
│   └── UserNotFoundException.php
├── Enums/
│   ├── UserRoleEnum.php
│   └── UserStatusEnum.php
├── ValuesObjects/
│   ├── UserId.php
│   ├── UserName.php
│   ├── UserEmail.php
│   └── UserPassword.php
├── Models/
│   └── UserModel.php
└── Events/
    ├── DomainEvent.php
    ├── UserCreatedDomainEvent.php
    ├── UserUpdatedDomainEvent.php
    └── UserDeletedDomainEvent.php
```

---

## Componentes

### 1. Exceptions — Errores del negocio

Las excepciones de dominio representan situaciones inválidas del negocio, no errores técnicos.

| Clase | Extiende | ¿Quién la lanza? | Named Constructors |
|---|---|---|---|
| `InvalidUserIdException` | `InvalidArgumentException` | `UserId` | `becauseValueIsEmpty()` |
| `InvalidUserNameException` | `InvalidArgumentException` | `UserName` | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)` |
| `InvalidUserEmailException` | `InvalidArgumentException` | `UserEmail` | `becauseValueIsEmpty()`, `becauseFormatIsInvalid($email)` |
| `InvalidUserPasswordException` | `InvalidArgumentException` | `UserPassword` | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)` |
| `InvalidUserRoleException` | `InvalidArgumentException` | `UserRoleEnum` | `becauseValueIsInvalid($value)` |
| `InvalidUserStatusException` | `InvalidArgumentException` | `UserStatusEnum` | `becauseValueIsInvalid($value)` |
| `UserAlreadyExistsException` | `DomainException` | Servicios de Aplicación | `becauseEmailAlreadyExists($email)` |
| `UserNotFoundException` | `DomainException` | Servicios de Aplicación | `becauseIdWasNotFound($id)` |
| `InvalidCredentialsException` | `RuntimeException` | Servicio de login | `becauseCredentialsAreInvalid()`, `becauseUserIsNotActive()` |

#### Patrón Named Constructors

En lugar de `new MiExcepcion('mensaje')` se usan métodos estáticos descriptivos:

```php
// ❌ El mensaje se puede olvidar o repetir distinto
throw new InvalidUserEmailException('El email está vacío');

// ✅ El nombre del método documenta el motivo exacto
throw InvalidUserEmailException::becauseValueIsEmpty();
```

---

### 2. Enums — Valores fijos del negocio

Los Enums representan conjuntos cerrados de valores que el negocio permite.

#### UserRoleEnum
| Constante | Valor | Descripción |
|---|---|---|
| `ADMIN` | `'ADMIN'` | Administrador del sistema |
| `MEMBER` | `'MEMBER'` | Usuario estándar |
| `REVIEWER` | `'REVIEWER'` | Usuario revisor |

#### UserStatusEnum
| Constante | Valor | Descripción |
|---|---|---|
| `ACTIVE` | `'ACTIVE'` | Usuario activo, puede operar |
| `INACTIVE` | `'INACTIVE'` | Desactivado por el administrador |
| `PENDING` | `'PENDING'` | Recién creado, pendiente de activación |
| `BLOCKED` | `'BLOCKED'` | Bloqueado por intentos fallidos u otra razón |

#### Responsabilidades de cada Enum

```php
UserRoleEnum::values();              // ['ADMIN', 'MEMBER', 'REVIEWER']
UserRoleEnum::isValid('ADMIN');      // true/false sin lanzar excepción
UserRoleEnum::ensureIsValid('XXX');  // lanza InvalidUserRoleException
```

---

### 3. Value Objects — Tipos con reglas de validación

Un Value Object es un objeto que representa un concepto del dominio con validación incorporada. **No puede existir en estado inválido** — si el dato es inválido, el constructor lanza una excepción antes de crear el objeto.

| Clase | Reglas que valida |
|---|---|
| `UserId` | No puede estar vacío |
| `UserName` | No puede estar vacío, mínimo 3 caracteres |
| `UserEmail` | No puede estar vacío, debe ser email válido (RFC), se normaliza a minúsculas |
| `UserPassword` | No puede estar vacía, mínimo 8 caracteres |

#### Métodos comunes de todo Value Object

```php
$vo->value();          // retorna el valor primitivo encapsulado
$vo->equals($other);   // compara por valor, no por referencia
(string) $vo;          // permite usar el VO donde PHP espera un string
```

#### Métodos especiales de UserPassword

| Método | Cuándo usarlo |
|---|---|
| `new UserPassword($hash)` | Constructor interno. Usado con hashes ya generados |
| `UserPassword::fromPlainText($raw)` | Usuario ingresa su contraseña en formulario → hashea con bcrypt |
| `UserPassword::fromHash($hash)` | Reconstruir usuario desde la base de datos. No re-hashea |
| `verifyPlain($plain)` | Compara texto plano con el hash almacenado. Retorna `bool` |

#### Inmutabilidad

Los Value Objects son inmutables: una vez creados, no cambian. Si necesitas un email diferente, creas un nuevo `UserEmail`.

```php
$a = new UserEmail('test@test.com');
$b = new UserEmail('test@test.com');

$a === $b;        // false → son objetos distintos en memoria
$a->equals($b);   // true  → mismo valor interno
```

---

### 4. UserModel — La entidad del dominio

`UserModel` es la entidad principal (Aggregate Root). Representa un usuario del negocio con toda su información y las reglas que lo gobiernan.

#### Estado interno

| Propiedad | Tipo |
|---|---|
| `$id` | `UserId` |
| `$name` | `UserName` |
| `$email` | `UserEmail` |
| `$password` | `UserPassword` |
| `$role` | `string` (validado por `UserRoleEnum`) |
| `$status` | `string` (validado por `UserStatusEnum`) |

#### Constructor vs `create()`

```php
// Reconstruir un usuario desde la BD (ya tiene estado asignado)
new UserModel($id, $name, $email, $password, $role, $status);

// Crear un usuario nuevo → siempre asigna PENDING como estado inicial
UserModel::create($id, $name, $email, $password, $role);
```

#### Mutaciones inmutables

Ningún método modifica `$this`. Todos retornan un nuevo objeto:

```php
$user = UserModel::create(...);       // status = PENDING
$userActivo = $user->activate();      // status = ACTIVE
// $user sigue siendo PENDING
```

| Método | Resultado |
|---|---|
| `activate()` | Nuevo `UserModel` con `status = ACTIVE` |
| `deactivate()` | Nuevo `UserModel` con `status = INACTIVE` |
| `block()` | Nuevo `UserModel` con `status = BLOCKED` |
| `changeName($name)` | Nuevo `UserModel` con el nombre actualizado |
| `changeEmail($email)` | Nuevo `UserModel` con el email actualizado |
| `changePassword($password)` | Nuevo `UserModel` con la contraseña actualizada |
| `changeRole($role)` | Nuevo `UserModel` con el rol actualizado |
| `toArray()` | Array con los valores primitivos de todas las propiedades |

---

### 5. Domain Events — Hechos que ocurrieron

Un evento de dominio representa un hecho del pasado en el negocio. Se nombran siempre en **pasado**.

#### Clase base abstracta `DomainEvent`

```php
$event->eventName();   // nombre en formato entidad.acción (ej: 'user.created')
$event->occurredOn();  // timestamp de cuándo ocurrió
$event->payload();     // abstracto → cada evento implementa sus propios datos
```

#### Eventos del proyecto

| Clase | Evento | Recibe | Datos en payload |
|---|---|---|---|
| `UserCreatedDomainEvent` | `user.created` | `UserModel` | id, name, email, role, status |
| `UserUpdatedDomainEvent` | `user.updated` | `UserModel` | id, name, email, role, status |
| `UserDeletedDomainEvent` | `user.deleted` | `UserId` | id |

> `UserDeletedDomainEvent` solo recibe `UserId` porque cuando un usuario se elimina el modelo ya no existe — solo importa su ID.

---

## Orden de construcción

Los archivos se construyen en este orden exacto. Cada uno depende solo de los anteriores:

```
1. Exceptions     → sin dependencias
2. Enums          → dependen de sus Exceptions
3. Value Objects  → dependen de sus Exceptions
4. DomainEvent    → clase base abstracta, sin dependencias
5. UserModel      → depende de ValueObjects + Enums
6. Eventos        → dependen de DomainEvent + UserModel
```

---

## Convenciones del proyecto

### Namespaces y carga de archivos

Como el proyecto no tiene autoloader configurado, cada archivo usa `require_once` junto al `use` correspondiente. El orden dentro de cada archivo es siempre:

```php
<?php
declare(strict_types=1);  // 1. tipado estricto
namespace App\Domain\...;  // 2. namespace
use App\Domain\...;        // 3. imports
require_once __DIR__...;   // 4. carga de archivos
class ...                  // 5. definición de la clase
```

### `declare(strict_types=1)`

Activado en todos los archivos del dominio. Impide que PHP realice conversiones de tipo silenciosas:

```php
// Con strict_types=1 activo en el archivo que llama:
suma("3", "2"); // ❌ TypeError — no acepta strings aunque parezcan números
```
