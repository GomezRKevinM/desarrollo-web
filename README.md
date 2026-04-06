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

| Clase                          | Extiende                   | ¿Quién la lanza?        | Named Constructors                                           |
|--------------------------------|----------------------------|-------------------------|--------------------------------------------------------------|
| `InvalidUserIdException`       | `InvalidArgumentException` | `UserId`                | `becauseValueIsEmpty()`                                      |
| `InvalidUserNameException`     | `InvalidArgumentException` | `UserName`              | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)`     |
| `InvalidUserEmailException`    | `InvalidArgumentException` | `UserEmail`             | `becauseValueIsEmpty()`, `becauseFormatIsInvalid($email)`    |
| `InvalidUserPasswordException` | `InvalidArgumentException` | `UserPassword`          | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)`     |
| `InvalidUserRoleException`     | `InvalidArgumentException` | `UserRoleEnum`          | `becauseValueIsInvalid($value)`                              |
| `InvalidUserStatusException`   | `InvalidArgumentException` | `UserStatusEnum`        | `becauseValueIsInvalid($value)`                              |
| `UserAlreadyExistsException`   | `DomainException`          | Servicios de Aplicación | `becauseEmailAlreadyExists($email)`                          |
| `UserNotFoundException`        | `DomainException`          | Servicios de Aplicación | `becauseIdWasNotFound($id)`                                  |
| `InvalidCredentialsException`  | `RuntimeException`         | Servicio de login       | `becauseCredentialsAreInvalid()`, `becauseUserIsNotActive()` |

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
| Constante  | Valor        | Descripción               |
|------------|--------------|---------------------------|
| `ADMIN`    | `'ADMIN'`    | Administrador del sistema |
| `MEMBER`   | `'MEMBER'`   | Usuario estándar          |
| `REVIEWER` | `'REVIEWER'` | Usuario revisor           |

#### UserStatusEnum
| Constante  | Valor        | Descripción                                  |
|------------|--------------|----------------------------------------------|
| `ACTIVE`   | `'ACTIVE'`   | Usuario activo, puede operar                 |
| `INACTIVE` | `'INACTIVE'` | Desactivado por el administrador             |
| `PENDING`  | `'PENDING'`  | Recién creado, pendiente de activación       |
| `BLOCKED`  | `'BLOCKED'`  | Bloqueado por intentos fallidos u otra razón |

#### Responsabilidades de cada Enum

```php
UserRoleEnum::values();              // ['ADMIN', 'MEMBER', 'REVIEWER']
UserRoleEnum::isValid('ADMIN');      // true/false sin lanzar excepción
UserRoleEnum::ensureIsValid('XXX');  // lanza InvalidUserRoleException
```

---

### 3. Value Objects — Tipos con reglas de validación

Un Value Object es un objeto que representa un concepto del dominio con validación incorporada. **No puede existir en estado inválido** — si el dato es inválido, el constructor lanza una excepción antes de crear el objeto.

| Clase          | Reglas que valida                                                            |
|----------------|------------------------------------------------------------------------------|
| `UserId`       | No puede estar vacío                                                         |
| `UserName`     | No puede estar vacío, mínimo 3 caracteres                                    |
| `UserEmail`    | No puede estar vacío, debe ser email válido (RFC), se normaliza a minúsculas |
| `UserPassword` | No puede estar vacía, mínimo 8 caracteres                                    |

#### Métodos comunes de todo Value Object

```php
$vo->value();          // retorna el valor primitivo encapsulado
$vo->equals($other);   // compara por valor, no por referencia
(string) $vo;          // permite usar el VO donde PHP espera un string
```

#### Métodos especiales de UserPassword

| Método                              | Cuándo usarlo                                                   |
|-------------------------------------|-----------------------------------------------------------------|
| `new UserPassword($hash)`           | Constructor interno. Usado con hashes ya generados              |
| `UserPassword::fromPlainText($raw)` | Usuario ingresa su contraseña en formulario → hashea con bcrypt |
| `UserPassword::fromHash($hash)`     | Reconstruir usuario desde la base de datos. No re-hashea        |
| `verifyPlain($plain)`               | Compara texto plano con el hash almacenado. Retorna `bool`      |

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

| Propiedad   | Tipo                                     |
|-------------|------------------------------------------|
| `$id`       | `UserId`                                 |
| `$name`     | `UserName`                               |
| `$email`    | `UserEmail`                              |
| `$password` | `UserPassword`                           |
| `$role`     | `string` (validado por `UserRoleEnum`)   |
| `$status`   | `string` (validado por `UserStatusEnum`) |

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

| Método                      | Resultado                                                 |
|-----------------------------|-----------------------------------------------------------|
| `activate()`                | Nuevo `UserModel` con `status = ACTIVE`                   |
| `deactivate()`              | Nuevo `UserModel` con `status = INACTIVE`                 |
| `block()`                   | Nuevo `UserModel` con `status = BLOCKED`                  |
| `changeName($name)`         | Nuevo `UserModel` con el nombre actualizado               |
| `changeEmail($email)`       | Nuevo `UserModel` con el email actualizado                |
| `changePassword($password)` | Nuevo `UserModel` con la contraseña actualizada           |
| `changeRole($role)`         | Nuevo `UserModel` con el rol actualizado                  |
| `toArray()`                 | Array con los valores primitivos de todas las propiedades |

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

| Clase                    | Evento         | Recibe      | Datos en payload              |
|--------------------------|----------------|-------------|-------------------------------|
| `UserCreatedDomainEvent` | `user.created` | `UserModel` | id, name, email, role, status |
| `UserUpdatedDomainEvent` | `user.updated` | `UserModel` | id, name, email, role, status |
| `UserDeletedDomainEvent` | `user.deleted` | `UserId`    | id                            |

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


---

# Guia 03 - Applicacion

## ¿Qué es la Capa de Aplicación?

La capa de aplicación es el pegamento entre el mundo exterior (controladores, CLI, APIs) y el corazón del software (el Dominio). Sigue los principios de la Arquitectura Hexagonal y es responsable de **orquestar los casos de uso del sistema**.

Recibe datos de entrada, delega las validaciones y reglas de negocio al Dominio, interactúa con la infraestructura a través de contratos (puertos) y retorna resultados.

### Reglas absolutas de la Capa de Aplicación

> **No contiene lógica de negocio** ni conoce tecnologías concretas. Ningún servicio usa `echo`, `header()`, `PDO`, `$_POST`, `$_GET`, ni frameworks específicos. Solo coordina el flujo de información.

---

```text
app/Application/
├── Ports/
│   ├── In/
│   │   ├── CreateUserUseCase.php
│   │   ├── UpdateUserUseCase.php
│   │   ├── DeleteUserUseCase.php
│   │   ├── GetUserByIdUseCase.php
│   │   ├── GetAllUsersUseCase.php
│   │   └── LoginUseCase.php
│   └── Out/
│       ├── SaveUserPort.php
│       ├── UpdateUserPort.php
│       ├── DeleteUserPort.php
│       ├── GetUserByIdPort.php
│       ├── GetUserByEmailPort.php
│       └── GetAllUsersPort.php
└── Services/
    ├── Dto/
    │   ├── Commands/
    │   │   ├── CreateUserCommand.php
    │   │   ├── UpdateUserCommand.php
    │   │   ├── DeleteUserCommand.php
    │   │   └── LoginCommand.php
    │   └── Queries/
    │       ├── GetUserByIdQuery.php
    │       └── GetAllUsersQuery.php
    ├── UseCases/
    │   ├── CreateUserService.php
    │   ├── UpdateUserService.php
    │   ├── DeleteUserService.php
    │   ├── GetUserByIdService.php
    │   ├── GetAllUsersService.php
    │   └── LoginService.php
    └── UserApplicationMapper.php
```

## Componentes

1. DTOs — Data Transfer Objects (CQRS Aplicado)

    Se aplica el principio de segregación CQRS (Command Query Responsibility Segregation). Los DTOs se dividen estrictamente en operaciones que modifican el estado (Commands) y operaciones que solo leen (Queries). Son objetos anémicos: solo tienen constructor y getters, sin lógica.
    
    * **Commands (Escritura)**
    
    | Archivo           | Propósito                                    |
    |-------------------|----------------------------------------------|
    | CreateUserCommand | Transporta datos para crear un usuario.      |
    | UpdateUserCommand | Transporta datos para actualizar un usuario. |
    | DeleteUserCommand | Transporta el ID del usuario a eliminar.     |
    | LoginCommand      | Transporta credenciales de autenticación     |

    * **Queries (Lectura)**

    | Archivo          | Propósito                                                         |
    |------------------|-------------------------------------------------------------------|
    | GetUserByIdQuery | Transporta el ID del usuario a consultar.                         |
    | GetAllUsersQuery | Representa la intención de listar todos los usuarios (sin datos). |
        
2. Puertos — Contratos del Hexágono

    Los puertos son interfaces que definen cómo la aplicación se comunica con el exterior y viceversa.
    - **Regla de oro:** Los puertos de salida usan tipos del dominio en sus firmas (`UserId`, `UserEmail`, `UserModel`), nunca tipos primitivos sueltos o arrays. Esto garantiza que cualquier dato que viaje a la infraestructura ya fue validado por el dominio.

    **Puertos de Salida (Ports/Out)**
    Exigen a la infraestructura lo que la aplicación necesita para funcionar (ej. persistencia).

    | Interfaz           | Método                       | Retorno      |
    |--------------------|------------------------------|--------------|
    | SaveUserPort       | save(UserModel $user)        | UserModel    |
    | UpdateUserPort     | update(UserModel $user)      | UserModel    |
    | DeleteUserPort     | delete(UserId $id)           | void         |
    | GetUserByIdPort    | getById(UserId $id)          | ?UserModel   |
    | GetUserByEmailPort | getByEmail(UserEmail $email) | ?UserModel   |
    | GetAllUsersPort    | getAll()                     | UserModel[]  |

   **Puertos de Entrada (Ports/In)**
   Exponen las acciones del sistema hacia el exterior (Controladores). El entrypoint solo conoce estas interfaces, nunca las implementaciones concretas.
   
    | Interfaz             | Firma                                          |
    |----------------------|------------------------------------------------|
    | `CreateUserUseCase`  | execute(CreateUserCommand $command): UserModel |
    | `UpdateUserUseCase`  | execute(UpdateUserCommand $command): UserModel |  
    | `DeleteUserUseCase`  | execute(DeleteUserCommand $command): void      |
    | `GetUserByIdUseCase` | execute(GetUserByIdQuery $query): UserModel    |
    | `GetAllUsersUseCase` | execute(GetAllUsersQuery $query): UserModel[]  |
    | `LoginUseCase`       | execute(LoginCommand $command): UserModel      |

3. Mapper — Transformador de datos

   `UserApplicationMapper` es responsable de transformar DTOs (Commands/Queries) en objetos del Dominio y viceversa.
   Al extraer esta lógica, los servicios quedan limpios y el mapper se vuelve testeable de forma completamente independiente. Sus métodos son estáticos.

   | Método                       | Entrada → Salida                  |
   |------------------------------|-----------------------------------|
   | fromCreateCommandToModel     | `CreateUserCommand` → `UserModel` | 
   | fromUpdateCommandToModel     | `UpdateUserCommand` → `UserModel` | 
   | fromGetUserByIdQueryToUserId | `GetUserByIdQuery` → `UserId`     |
   | fromDeleteCommandToUserId    | `DeleteUserCommand` → `UserId`    |
   | fromModelToArray             | `UserModel` → `array`             |
   | fromModelsToArray            | `UserModel[]` → `array[]`         |
    
4. Servicios de Aplicación — Los Casos de Uso

   Son las implementaciones concretas de los _Ports/In_. Reciben sus dependencias (los _Ports/Out_) a través del constructor (Inyección de Dependencias) y nunca instancian infraestructura directamente.
   
    | Servicio           | Lógica destacada de orquestación                                                                            |
    |--------------------|-------------------------------------------------------------------------------------------------------------|
    | CreateUserService  | Verifica duplicidad del email a través del puerto antes de guardar.                                         |
    | UpdateUserService  | Verifica existencia del usuario + unicidad del nuevo email + conserva el hash si la contraseña viene vacía. |
    | DeleteUserService  | Verifica existencia del usuario antes de eliminar.                                                          |
    | GetUserByIdService | Lanza UserNotFoundException si el usuario no existe.                                                        |
    | GetAllUsersService | "Delega directamente al puerto, sin lógica adicional."                                                      |
    | LoginService       | Valida credenciales y estado de cuenta (ACTIVE) en un único bloque.                                         |

## Decisiones de Diseño Críticas

1. **Contraseña en edición**: UpdateUserService detecta si el campo contraseña en el Command viene vacío. De ser así, consulta el usuario actual y reutiliza el hash existente. Esto evita forzar al usuario a reingresar su contraseña en cada edición de perfil.
2. **Seguridad en login**: LoginService unifica los errores de "usuario no encontrado" y "contraseña incorrecta" lanzando siempre una única excepción genérica: InvalidCredentialsException. Esto es una medida de seguridad vital para evitar ataques de enumeración (no revela a un atacante si un email está registrado o no).
3. **Separación de responsabilidades (Servicio vs Mapper)**: El servicio orquesta la acción (llama a puertos, verifica reglas), mientras que el mapper solo transforma la forma de los datos. Esta estricta separación hace que ambas clases cumplan el principio de Responsabilidad Única (SRP).

---

# Guia 04 - Infraestructura: Adaptadores de Salida (Persistence)

## ¿Qué es la Capa de Infraestructura?

La capa de infraestructura contiene las **implementaciones concretas** de los puertos de salida definidos en la capa de aplicación. Es la "tecnología" que se coloca detrás de las interfaces: aquí vive el SQL, el PDO, la conexión a MySQL.

Un adaptador implementa uno o más puertos de salida, traduce entre el lenguaje del dominio (Value Objects, `UserModel`) y el lenguaje de la tecnología (filas SQL, arrays asociativos), y aísla esa tecnología del resto del sistema. Si mañana se reemplaza MySQL por PostgreSQL, solo se toca esta capa.

### Regla absoluta

> Los adaptadores no contienen lógica de negocio. No verifican duplicados, no lanzan `UserNotFoundException`, no toman decisiones. Solo persisten y recuperan datos.

---

## Estructura de carpetas

```
app/Infrastructure/
└── Adapters/
    └── Persistence/
        └── MySQL/
            ├── Config/
            │   └── Connection.php
            ├── Dto/
            │   └── UserPersistenceDto.php
            ├── Entity/
            │   └── UserEntity.php
            ├── Mapper/
            │   └── UserPersistenceMapper.php
            └── Repository/
                └── UserRepositoryMySQL.php
```

| Carpeta       | Propósito                                                           |
|---------------|---------------------------------------------------------------------|
| `Config/`     | Configuración de conexión y fábrica del objeto PDO                  |
| `Dto/`        | Objetos planos con los datos del usuario listos para enviarse a SQL |
| `Entity/`     | Representación directa de una fila de la tabla `users`              |
| `Mapper/`     | Conversión entre `UserModel`, `UserEntity` y `UserPersistenceDto`   |
| `Repository/` | Implementación concreta de los 6 puertos de salida usando PDO       |

---

## Componentes

### 1. Connection — Fábrica de la conexión PDO

`Connection` encapsula los parámetros de conexión a MySQL y fabrica objetos `PDO`. Se inyecta en el repositorio en lugar de hardcodear la conexión.

**Parámetros del constructor**

| Parámetro   | Tipo     | Descripción                                    |
|-------------|----------|------------------------------------------------|
| `$host`     | `string` | Dirección del servidor MySQL (ej. `127.0.0.1`) |
| `$port`     | `int`    | Puerto (por defecto `3306`)                    |
| `$database` | `string` | Nombre de la base de datos                     |
| `$username` | `string` | Usuario de MySQL                               |
| `$password` | `string` | Contraseña                                     |
| `$charset`  | `string` | Codificación (por defecto `utf8mb4`)           |

**Opciones PDO configuradas en `createPdo()`**

| Opción                     | Propósito                                                               |
|----------------------------|-------------------------------------------------------------------------|
| `ERRMODE_EXCEPTION`        | Los errores SQL lanzan `PDOException` en lugar de retornar `false`      |
| `FETCH_ASSOC`              | Las filas se retornan como arrays asociativos por defecto                |
| `EMULATE_PREPARES: false`  | Usa prepared statements reales del servidor (protege contra SQL injection) |

**Esquema de base de datos requerido**

```sql
CREATE DATABASE IF NOT EXISTS crud_usuarios;
USE crud_usuarios;

CREATE TABLE IF NOT EXISTS users (
    id         VARCHAR(36)  NOT NULL,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       VARCHAR(30)  NOT NULL,
    status     VARCHAR(30)  NOT NULL,
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email)
);
```

> El `id` es `VARCHAR(36)` y no un entero autoincremental porque en DDD el dominio es responsable de generar el ID, no la base de datos.

---

### 2. UserPersistenceDto — Datos planos para SQL

DTO de infraestructura que transporta los campos del usuario como strings simples, listos para ser enviados como parámetros a sentencias SQL (`INSERT`, `UPDATE`).

**¿Por qué no enviar el `UserModel` directamente a SQL?**

`UserModel` contiene Value Objects, no strings. PDO trabaja con strings e integers. El DTO hace el puente. Además mantiene una separación limpia: si la tabla añade columnas técnicas como `created_at` o `updated_at`, esos son detalles de infraestructura que `UserModel` no necesita conocer.

| Propiedad   | Tipo     | Nota                    |
|-------------|----------|-------------------------|
| `$id`       | `string` |                         |
| `$name`     | `string` |                         |
| `$email`    | `string` |                         |
| `$password` | `string` | Hash bcrypt ya generado |
| `$role`     | `string` |                         |
| `$status`   | `string` |                         |

Solo getters. Una vez construido, no cambia.

---

### 3. UserEntity — La fila de la base de datos como objeto

`UserEntity` representa una fila de la tabla `users` tal como llega de un `SELECT`. Incluye los campos propios de la BD que el dominio no tiene.

**Diferencia con `UserPersistenceDto`**

| `UserPersistenceDto`          | `UserEntity`                             |
|-------------------------------|------------------------------------------|
| Para enviar a SQL (escritura) | Para recibir de SQL (lectura)            |
| Sin `created_at`/`updated_at` | Con `created_at`/`updated_at` (nullable) |

---

### 4. UserPersistenceMapper — El traductor de infraestructura

Convierte entre las distintas representaciones del usuario que existen en la capa de infraestructura.

**Diagrama de transformaciones**

```
UserModel
    ↓ fromModelToDto()
UserPersistenceDto  →  SQL INSERT/UPDATE (vía repository)

SQL SELECT resultado (array)
    ↓ fromRowToEntity()
UserEntity
    ↓ fromEntityToModel()
UserModel

Atajo directo:
SQL SELECT resultado (array)
    ↓ fromRowToModel()
UserModel
```

**Métodos**

| Método               | De                      | A                    | Cuándo se usa                      |
|----------------------|-------------------------|----------------------|------------------------------------|
| `fromModelToDto`     | `UserModel`             | `UserPersistenceDto` | Antes de INSERT o UPDATE           |
| `fromDtoToEntity`    | `UserPersistenceDto`    | `UserEntity`         | Auxiliar, por si se necesita       |
| `fromRowToEntity`    | `array` (fila SQL)      | `UserEntity`         | Al recibir filas de SELECT         |
| `fromEntityToModel`  | `UserEntity`            | `UserModel`          | Para reconstruir el dominio        |
| `fromRowToModel`     | `array` (fila SQL)      | `UserModel`          | Atajo Row → Entity → Model         |
| `fromRowsToModels`   | `array[]` (múltiples filas) | `UserModel[]`    | Para consultas que retornan lista  |

> **Punto crítico:** `fromEntityToModel()` usa `UserPassword::fromHash()`, **nunca** `fromPlainText()`. La BD ya contiene el hash bcrypt. Re-hashearlo produciría una cadena distinta e impediría que `verifyPlain()` funcione correctamente en el login.

---

### 5. UserRepositoryMySQL — El adaptador que implementa todo

`UserRepositoryMySQL` es el adaptador concreto que implementa los **6 puertos de salida** en una sola clase usando PDO y MySQL.

**¿Por qué una sola clase implementa 6 interfaces?**

Porque todos los puertos están relacionados con el mismo agregado (`User`) y las queries se reutilizan internamente (ej. `getById` es llamado por `save` y `update` para retornar el estado final persistido).

**Constructor**

```php
public function __construct(PDO $pdo, UserPersistenceMapper $mapper)
```

Ambas dependencias se inyectan. El repositorio no crea su propia conexión.

**Lógica de cada método**

| Método          | Operación SQL                          | Detalle clave                                                            |
|-----------------|----------------------------------------|--------------------------------------------------------------------------|
| `save()`        | `INSERT INTO users`                    | Llama a `getById()` al final para retornar el estado real con timestamps |
| `update()`      | `UPDATE users SET ... WHERE id`        | Ídem — retorna el estado actualizado desde la BD                         |
| `getById()`     | `SELECT ... WHERE id = :id LIMIT 1`   | Retorna `null` si no se encuentra, nunca lanza excepción                 |
| `getByEmail()`  | `SELECT ... WHERE email = :email LIMIT 1` | Igual que `getById()` pero por email                                 |
| `getAll()`      | `SELECT ... ORDER BY name ASC`         | Usa `$pdo->query()` (sin parámetros)                                     |
| `delete()`      | `DELETE FROM users WHERE id = :id`     | Retorna `void`                                                           |

> **Seguridad:** Todos los SQLs con datos del usuario usan **prepared statements con parámetros nombrados** (`:id`, `:name`, etc.). Nunca se concatenan strings de usuario en el SQL.

---

## Orden de construcción

```
1. Connection.php             → sin dependencias del proyecto
2. UserPersistenceDto.php     → sin dependencias del proyecto
3. UserEntity.php             → sin dependencias del proyecto
4. UserPersistenceMapper.php  → depende de Dto, Entity, y los Value Objects del dominio
5. UserRepositoryMySQL.php    → depende de los 6 puertos + Mapper + Domain
```

---

## Checklist de verificación

- [ ] El DSN de `Connection` incluye `charset=utf8mb4`
- [ ] PDO está configurado con `ERRMODE_EXCEPTION` y `FETCH_ASSOC`
- [ ] Todos los SQLs con datos del usuario usan parámetros nombrados (`:parametro`), nunca concatenación de strings
- [ ] `save()` y `update()` llaman a `getById()` al final para retornar el estado real de la BD
- [ ] `getById()` y `getByEmail()` retornan `null` cuando no encuentran, no lanzan excepción
- [ ] `getAll()` ordena por nombre
- [ ] `UserRepositoryMySQL` no tiene lógica de negocio (no verifica duplicados, no lanza `UserNotFoundException`)
- [ ] `UserPersistenceMapper::fromEntityToModel()` usa `UserPassword::fromHash()` para no re-hashear el bcrypt almacenado