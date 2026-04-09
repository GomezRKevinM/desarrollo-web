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

# Guía 05 — Capa de Infraestructura: Entrypoints Web

**Proyecto:** Aplicación Web básica sobre PHP y MySQL aplicando arquitectura hexagonal y DDD  
**Tutor:** John Carlos Arrieta Arrieta  
**Documento:** 05 — Entrypoints Web, Common y el Punto de Entrada

---

## Estructura de carpetas construida

```
/ (raíz del proyecto)
├── composer.json
├── .htaccess
├── Common/
│   └── DependencyInjection.php
├── public/
│   └── index.php
└── crud-usuarios/
    └── Infrastructure/
        └── Entrypoints/
            └── Web/
                ├── Controllers/
                │   ├── Config/
                │   │   └── WebRoutes.php
                │   ├── Dto/
                │   │   ├── CreateUserWebRequest.php
                │   │   ├── UpdateUserWebRequest.php
                │   │   ├── LoginWebRequest.php
                │   │   └── UserResponse.php
                │   ├── Mapper/
                │   │   └── UserWebMapper.php
                │   └── UserController.php
                └── Presentation/
                    ├── Flash.php
                    ├── View.php
                    └── Views/
                        ├── layouts/
                        │   ├── header.php
                        │   ├── menu.php
                        │   └── footer.php
                        ├── home.php
                        ├── users/
                        │   ├── create.php
                        │   ├── list.php
                        │   ├── show.php
                        │   └── edit.php
                        └── auth/
                            ├── login.php
                            └── forgot-password.php
```

---

## `Common/DependencyInjection.php`

```php
<?php

declare(strict_types=1);

namespace App\Common;

use App\crud_usuarios\Application\Ports\In\CreateUserUseCase;
use App\crud_usuarios\Application\Ports\In\DeleteUserUseCase;
use App\crud_usuarios\Application\Ports\In\GetAllUsersUseCase;
use App\crud_usuarios\Application\Ports\In\GetByUserIdUseCase;
use App\crud_usuarios\Application\Ports\In\LoginUseCase;
use App\crud_usuarios\Application\Ports\In\UpdateUserUseCase;
use App\crud_usuarios\Application\Services\CreateUSerService;
use App\crud_usuarios\Application\Services\DeleteUserService;
use App\crud_usuarios\Application\Services\GetAllUsersService;
use App\crud_usuarios\Application\Services\GetUserByIdService;
use App\crud_usuarios\Application\Services\LoginService;
use App\crud_usuarios\Application\Services\UpdateUserService;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Config\Connection;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Mapper\UserPersistenceMapper;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Repository\UserRepositoryMySQL;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Mapper\UserWebMapper;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\UserController;

final class DependencyInjection
{
    private static function getConnection(): Connection
    {
        return new Connection(
            host: '127.0.0.1',
            port: '3306',
            database: 'crud_usuarios',
            username: 'root',
            password: '',
        );
    }

    public static function getUserRepository(): UserRepositoryMySQL
    {
        return new UserRepositoryMySQL(
            pdo:    self::getConnection()->createPDO(),
            mapper: new UserPersistenceMapper(),
        );
    }

    public static function getCreateUserUseCase(): CreateUserUseCase
    {
        $repo = self::getUserRepository();
        return new CreateUSerService($repo, $repo);
    }

    public static function getUpdateUserUseCase(): UpdateUserUseCase
    {
        $repo = self::getUserRepository();
        return new UpdateUserService($repo, $repo, $repo);
    }

    public static function getDeleteUserUseCase(): DeleteUserUseCase
    {
        $repo = self::getUserRepository();
        return new DeleteUserService($repo, $repo);
    }

    public static function getGetUserByIdUseCase(): GetByUserIdUseCase
    {
        return new GetUserByIdService(self::getUserRepository());
    }

    public static function getGetAllUsersUseCase(): GetAllUsersUseCase
    {
        return new GetAllUsersService(self::getUserRepository());
    }

    public static function getLoginUseCase(): LoginUseCase
    {
        return new LoginService(self::getUserRepository());
    }

    public static function getUserController(): UserController
    {
        return new UserController(
            createUserUseCase:  self::getCreateUserUseCase(),
            updateUserUseCase:  self::getUpdateUserUseCase(),
            getUserByIdUseCase: self::getGetUserByIdUseCase(),
            getAllUsersUseCase:  self::getGetAllUsersUseCase(),
            deleteUserUseCase:  self::getDeleteUserUseCase(),
            mapper:             new UserWebMapper(),
        );
    }
}
```

---

## DTOs Web

### `Controllers/Dto/CreateUserWebRequest.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto;

final class CreateUserWebRequest
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
        private readonly string $role,
    ) {}

    public function getId(): string       { return $this->id; }
    public function getName(): string     { return $this->name; }
    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string     { return $this->role; }
}
```

### `Controllers/Dto/UpdateUserWebRequest.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto;

final class UpdateUserWebRequest
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $password,
        private readonly string $role,
        private readonly string $status,
    ) {}

    public function getId(): string       { return $this->id; }
    public function getName(): string     { return $this->name; }
    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string     { return $this->role; }
    public function getStatus(): string   { return $this->status; }
}
```

### `Controllers/Dto/LoginWebRequest.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto;

final class LoginWebRequest
{
    public function __construct(
        private readonly string $email,
        private readonly string $password,
    ) {}

    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }
}
```

### `Controllers/Dto/UserResponse.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto;

final class UserResponse
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $email,
        private readonly string $role,
        private readonly string $status,
    ) {}

    public function getId(): string     { return $this->id; }
    public function getName(): string   { return $this->name; }
    public function getEmail(): string  { return $this->email; }
    public function getRole(): string   { return $this->role; }
    public function getStatus(): string { return $this->status; }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'role'   => $this->role,
            'status' => $this->status,
        ];
    }
}
```

---

## `Controllers/Mapper/UserWebMapper.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Mapper;

use App\crud_usuarios\Application\Services\Dto\Commands\CreateUserCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\DeleteUserCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\LoginCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\UpdateUserCommand;
use App\crud_usuarios\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\LoginWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;

final class UserWebMapper
{
    public function fromCreateRequestToCommand(CreateUserWebRequest $request): CreateUserCommand
    {
        return new CreateUserCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            email:    $request->getEmail(),
            password: $request->getPassword(),
            role:     $request->getRole(),
        );
    }

    public function fromUpdateRequestToCommand(UpdateUserWebRequest $request): UpdateUserCommand
    {
        return new UpdateUserCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            email:    $request->getEmail(),
            password: $request->getPassword(),
            role:     $request->getRole(),
            status:   $request->getStatus(),
        );
    }

    public function fromLoginRequestToCommand(LoginWebRequest $request): LoginCommand
    {
        return new LoginCommand(
            email:    $request->getEmail(),
            password: $request->getPassword(),
        );
    }

    public function fromIdToGetByIdQuery(string $id): GetUserByIdQuery
    {
        return new GetUserByIdQuery($id);
    }

    public function fromIdToDeleteCommand(string $id): DeleteUserCommand
    {
        return new DeleteUserCommand($id);
    }

    public function fromModelToResponse(UserModel $user): UserResponse
    {
        return new UserResponse(
            id:     $user->id()->value(),
            name:   $user->name()->value(),
            email:  $user->email()->value(),
            role:   $user->role(),
            status: $user->status(),
        );
    }

    /**
     * @param  UserModel[]   $users
     * @return UserResponse[]
     */
    public function fromModelsToResponses(array $users): array
    {
        return array_map(
            fn(UserModel $user): UserResponse => $this->fromModelToResponse($user),
            $users,
        );
    }
}
```

---

## `Controllers/Config/WebRoutes.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Config;

final class WebRoutes
{
    /**
     * @return array<string, array{method: string, action: string}>
     */
    public static function routes(): array
    {
        return [
            'home'              => ['method' => 'GET',  'action' => 'home'],
            'users.create'      => ['method' => 'GET',  'action' => 'create'],
            'users.store'       => ['method' => 'POST', 'action' => 'store'],
            'users.index'       => ['method' => 'GET',  'action' => 'index'],
            'users.show'        => ['method' => 'GET',  'action' => 'show'],
            'users.edit'        => ['method' => 'GET',  'action' => 'edit'],
            'users.update'      => ['method' => 'POST', 'action' => 'update'],
            'users.delete'      => ['method' => 'POST', 'action' => 'delete'],
            'auth.login'        => ['method' => 'GET',  'action' => 'login'],
            'auth.authenticate' => ['method' => 'POST', 'action' => 'authenticate'],
            'auth.logout'       => ['method' => 'GET',  'action' => 'logout'],
            'auth.forgot'       => ['method' => 'GET',  'action' => 'forgot'],
            'auth.forgot.send'  => ['method' => 'POST', 'action' => 'forgot.send'],
        ];
    }
}
```

---

## `Controllers/UserController.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers;

use App\crud_usuarios\Application\Ports\In\CreateUserUseCase;
use App\crud_usuarios\Application\Ports\In\DeleteUserUseCase;
use App\crud_usuarios\Application\Ports\In\GetAllUsersUseCase;
use App\crud_usuarios\Application\Ports\In\GetByUserIdUseCase;
use App\crud_usuarios\Application\Ports\In\UpdateUserUseCase;
use App\crud_usuarios\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Mapper\UserWebMapper;

final class UserController
{
    public function __construct(
        private readonly CreateUserUseCase  $createUserUseCase,
        private readonly UpdateUserUseCase  $updateUserUseCase,
        private readonly GetByUserIdUseCase $getUserByIdUseCase,
        private readonly GetAllUsersUseCase $getAllUsersUseCase,
        private readonly DeleteUserUseCase  $deleteUserUseCase,
        private readonly UserWebMapper      $mapper,
    ) {}

    /** @return UserResponse[] */
    public function index(): array
    {
        $users = $this->getAllUsersUseCase->execute(new GetAllUsersQuery());
        return $this->mapper->fromModelsToResponses($users);
    }

    public function show(string $id): UserResponse
    {
        $query = $this->mapper->fromIdToGetByIdQuery($id);
        $user  = $this->getUserByIdUseCase->execute($query);
        return $this->mapper->fromModelToResponse($user);
    }

    public function store(CreateUserWebRequest $request): UserResponse
    {
        $command = $this->mapper->fromCreateRequestToCommand($request);
        $user    = $this->createUserUseCase->execute($command);
        return $this->mapper->fromModelToResponse($user);
    }

    public function update(UpdateUserWebRequest $request): UserResponse
    {
        $command = $this->mapper->fromUpdateRequestToCommand($request);
        $user    = $this->updateUserUseCase->execute($command);
        return $this->mapper->fromModelToResponse($user);
    }

    public function delete(string $id): void
    {
        $command = $this->mapper->fromIdToDeleteCommand($id);
        $this->deleteUserUseCase->execute($command);
    }
}
```

---

## `Presentation/Flash.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Presentation;

final class Flash
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        if (!isset($_SESSION['_flash'][$key])) {
            return $default;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /** @param array<string, string> $data */
    public static function setOld(array $data): void
    {
        self::set('old', $data);
    }

    /** @return array<string, string> */
    public static function old(): array
    {
        $data = self::get('old', []);
        return is_array($data) ? $data : [];
    }

    /** @param array<string, string> $errors */
    public static function setErrors(array $errors): void
    {
        self::set('errors', $errors);
    }

    /** @return array<string, string> */
    public static function errors(): array
    {
        $errors = self::get('errors', []);
        return is_array($errors) ? $errors : [];
    }

    public static function setMessage(string $message): void
    {
        self::set('message', $message);
    }

    public static function message(): string
    {
        $value = self::get('message', '');
        return is_string($value) ? $value : '';
    }

    public static function setSuccess(string $message): void
    {
        self::set('success', $message);
    }

    public static function success(): string
    {
        $value = self::get('success', '');
        return is_string($value) ? $value : '';
    }
}
```

---

## `Presentation/View.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Presentation;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = []): void
    {
        $file = __DIR__ . '/Views/' . $template . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException(
                sprintf('Vista no encontrada: "%s" en %s', $template, $file)
            );
        }

        extract($data, EXTR_SKIP);
        require $file;
    }

    public static function redirect(string $route): never
    {
        header('Location: ?route=' . urlencode($route));
        exit;
    }
}
```

---

## Vistas

### `Views/layouts/header.php`

```php
<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CRUD Usuarios', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        nav a { margin-right: 12px; text-decoration: none; }
        .alert-error   { margin: 12px 0; padding: 10px; border: 1px solid #d33; background: #fdeaea; }
        .alert-success { margin: 12px 0; padding: 10px; border: 1px solid #2d8a34; background: #eaf8ec; }
        .field-error   { color: #c00; font-size: 0.9rem; margin-top: 3px; }
        .form-group    { margin-bottom: 14px; }
        label          { display: inline-block; margin-bottom: 4px; }
        input, select  { min-width: 280px; padding: 6px; }
        table          { border-collapse: collapse; }
        table th, table td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        table.detail-table th { background: #f5f5f5; width: 140px; }
        .btn         { display: inline-block; padding: 5px 12px; text-decoration: none;
                       cursor: pointer; border: none; border-radius: 3px; font-size: 0.9rem;
                       background: #e0e0e0; color: #333; }
        .btn-primary { background: #0066cc; color: #fff; }
        .btn-primary:hover { background: #0052a3; }
        .btn-warning { background: #e68a00; color: #fff; }
        .btn-warning:hover { background: #cc7a00; }
        .btn-danger  { background: #cc2200; color: #fff; }
        .btn-danger:hover  { background: #aa1a00; }
        .btn-sm      { padding: 3px 8px; font-size: 0.8rem; }
        .auth-box    { max-width: 420px; margin: 40px auto; padding: 28px;
                       border: 1px solid #ddd; border-radius: 6px; background: #fafafa; }
    </style>
</head>
<body>
```

### `Views/layouts/menu.php`

```php
<?php declare(strict_types=1); ?>
<?php $authUser = $_SESSION['auth'] ?? null; ?>
<nav>
    <a href="?route=home">Inicio</a>
    <?php if ($authUser !== null): ?>
        <a href="?route=users.create">Registrar usuario</a>
        <a href="?route=users.index">Listar usuarios</a>
        <span style="margin: 0 10px; color:#555;">|</span>
        <span style="color:#333;">👤 <?= htmlspecialchars($authUser['name'], ENT_QUOTES, 'UTF-8') ?></span>
        &nbsp;
        <a href="?route=auth.logout">Cerrar sesión</a>
    <?php else: ?>
        <a href="?route=auth.login">Iniciar sesión</a>
        <a href="?route=auth.forgot">Recuperar contraseña</a>
    <?php endif; ?>
</nav>
<hr>
```

### `Views/layouts/footer.php`

```php
<?php declare(strict_types=1); ?>
</body>
</html>
```

### `Views/home.php`

```php
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

<?php require __DIR__ . '/layouts/footer.php'; ?>
```

### `Views/users/create.php`

```php
<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

<h1>Registrar usuario</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST" action="?route=users.store">

    <div class="form-group">
        <label for="name">Nombre</label><br>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['name'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email">Correo</label><br>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Contraseña</label><br>
        <input type="password" id="password" name="password" autocomplete="new-password">
        <?php if (!empty($errors['password'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="role">Rol</label><br>
        <select id="role" name="role">
            <?php foreach ($roleOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($old['role'] ?? '') === $opt) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['role'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Registrar usuario</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### `Views/users/list.php`

```php
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
                        <a class="btn btn-sm"
                           href="?route=users.show&id=<?= urlencode($user->getId()) ?>">Ver</a>
                        <a class="btn btn-sm btn-warning"
                           href="?route=users.edit&id=<?= urlencode($user->getId()) ?>">Editar</a>
                        <form method="POST" action="?route=users.delete" style="display:inline"
                              onsubmit="return confirm('¿Eliminar este usuario?')">
                            <input type="hidden" name="id"
                                   value="<?= htmlspecialchars($user->getId(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### `Views/users/show.php`

```php
<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

<h1>Detalle de usuario</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<table class="detail-table">
    <tr><th>ID</th>     <td><?= htmlspecialchars($user->getId(),     ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><th>Nombre</th> <td><?= htmlspecialchars($user->getName(),   ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><th>Correo</th> <td><?= htmlspecialchars($user->getEmail(),  ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><th>Rol</th>    <td><?= htmlspecialchars($user->getRole(),   ENT_QUOTES, 'UTF-8') ?></td></tr>
    <tr><th>Estado</th> <td><?= htmlspecialchars($user->getStatus(), ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<p style="margin-top: 16px;">
    <a class="btn btn-warning"
       href="?route=users.edit&amp;id=<?= urlencode($user->getId()) ?>">Editar</a>
    &nbsp;
    <a class="btn" href="?route=users.index">Volver al listado</a>
</p>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### `Views/users/edit.php`

```php
<?php require __DIR__ . '/../layouts/header.php'; ?>
<?php require __DIR__ . '/../layouts/menu.php'; ?>

<h1>Editar usuario</h1>

<?php if (!empty($message)): ?>
    <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="POST" action="?route=users.update">
    <input type="hidden" name="id"
           value="<?= htmlspecialchars($old['id'] ?? $user->getId(), ENT_QUOTES, 'UTF-8') ?>">

    <div class="form-group">
        <label for="name">Nombre</label><br>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($old['name'] ?? $user->getName(), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['name'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email">Correo</label><br>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($old['email'] ?? $user->getEmail(), ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($errors['email'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">
            Contraseña <small>(déjala en blanco para no cambiarla)</small>
        </label><br>
        <input type="password" id="password" name="password"
               value="" autocomplete="new-password">
        <?php if (!empty($errors['password'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="role">Rol</label><br>
        <select id="role" name="role">
            <?php foreach ($roleOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($old['role'] ?? $user->getRole()) === $opt) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['role'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['role'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="status">Estado</label><br>
        <select id="status" name="status">
            <?php foreach ($statusOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>"
                    <?= (($old['status'] ?? $user->getStatus()) === $opt) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errors['status'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['status'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    &nbsp;
    <a class="btn" href="?route=users.index">Cancelar</a>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### `Views/auth/login.php`

```php
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-box">
    <h1>Iniciar sesión</h1>

    <?php if (!empty($message)): ?>
        <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" action="?route=auth.authenticate">

        <div class="form-group">
            <label for="email">Correo electrónico</label><br>
            <input type="email" id="email" name="email" autofocus
                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['email'])): ?>
                <div class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label><br>
            <input type="password" id="password" name="password"
                   autocomplete="current-password">
            <?php if (!empty($errors['password'])): ?>
                <div class="field-error"><?= htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <p style="margin-top: 16px;">
        <a href="?route=auth.forgot">¿Olvidaste tu contraseña?</a>
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### `Views/auth/forgot-password.php`

```php
<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-box">
    <h1>Recuperar contraseña</h1>

    <?php if (!empty($message)): ?>
        <div class="alert-error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <p>Introduce el correo con el que te registraste y te enviaremos una contraseña temporal.</p>

    <form method="POST" action="?route=auth.forgot.send">
        <div class="form-group">
            <label for="email">Correo electrónico</label><br>
            <input type="email" id="email" name="email" autofocus
                   value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php if (!empty($errors['email'])): ?>
                <div class="field-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Enviar contraseña temporal</button>
    </form>

    <p style="margin-top: 16px;">
        <a href="?route=auth.login">Volver al inicio de sesión</a>
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

---

## `public/index.php`

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Common\DependencyInjection;
use App\crud_usuarios\Application\Services\Dto\Commands\LoginCommand;
use App\crud_usuarios\Domain\Enums\UserRoleEnum;
use App\crud_usuarios\Domain\Enums\UserStatusEnum;
use App\crud_usuarios\Domain\ValuesObjects\UserEmail;
use App\crud_usuarios\Domain\ValuesObjects\UserPassword;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Config\WebRoutes;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Presentation\Flash;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Presentation\View;

// ── Guardia de seguridad ──────────────────────────────────────────────────────
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

$publicActions = ['home', 'login', 'authenticate', 'logout', 'forgot', 'forgot.send', 'create', 'store'];

if (!in_array($definition['action'], $publicActions, true) && !isLoggedIn()) {
    Flash::setMessage('Debes iniciar sesión para acceder a esta sección.');
    View::redirect('auth.login');
}

// ── Dispatch ──────────────────────────────────────────────────────────────────
try {
    switch ($definition['action']) {

        case 'home':
            View::render('home', buildHomeViewData());
            break;

        case 'create':
            View::render('users/create', buildCreateUserViewData());
            break;

        case 'store':
            $form       = getCreateUserFormData();
            $form['id'] = generateUuid4();
            $errors     = validateCreateUserForm($form);

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

        case 'index':
            $users = DependencyInjection::getUserController()->index();
            View::render('users/list', buildListUsersViewData($users));
            break;

        case 'show':
            $id   = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $user = DependencyInjection::getUserController()->show($id);
            View::render('users/show', [
                'pageTitle' => 'Detalle de usuario',
                'user'      => $user,
                'message'   => Flash::message(),
            ]);
            break;

        case 'edit':
            $id   = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
            $user = DependencyInjection::getUserController()->show($id);
            View::render('users/edit', buildEditUserViewData($user));
            break;

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

        case 'delete':
            $id = isset($_POST['id']) ? trim((string) $_POST['id']) : '';
            DependencyInjection::getUserController()->delete($id);
            Flash::setSuccess('Usuario eliminado correctamente.');
            View::redirect('users.index');
            break;

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

        case 'logout':
            session_destroy();
            header('Location: ?route=auth.login');
            exit;

        case 'forgot':
            View::render('auth/forgot-password', [
                'pageTitle' => 'Recuperar contraseña',
                'message'   => Flash::message(),
                'success'   => Flash::success(),
                'errors'    => Flash::errors(),
                'old'       => Flash::old(),
            ]);
            break;

        case 'forgot.send':
            $forgotEmail = trim(strtolower((string) ($_POST['email'] ?? '')));

            if ($forgotEmail === '' || !filter_var($forgotEmail, FILTER_VALIDATE_EMAIL)) {
                Flash::setErrors(['email' => 'Introduce un correo electrónico válido.']);
                Flash::setOld(['email' => $forgotEmail]);
                View::redirect('auth.forgot');
            }

            $repository = DependencyInjection::getUserRepository();
            $foundUser  = $repository->getByEmail(new UserEmail($forgotEmail));

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
        default:
            View::render('home', buildHomeViewData($msg));
            break;
    }
}

// ── Helper de email ───────────────────────────────────────────────────────────
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

// ── UUID v4 ───────────────────────────────────────────────────────────────────
function generateUuid4(): string
{
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
```

---

## `.htaccess` (raíz del proyecto)

```apache
Options -Indexes
RewriteEngine On

# Las peticiones a /public/ pasan directamente
RewriteRule ^public/ - [L]

# Los archivos estáticos se sirven directamente
RewriteRule \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ - [L,NC]

# Todo lo demás → public/index.php
RewriteRule ^ public/index.php [L]
```

---

## Decisiones de diseño clave

### Sin ClassLoader — solo Composer

La guía original usa un `ClassLoader` manual con `spl_autoload_register`. Como el proyecto ya tiene Composer configurado con PSR-4, el ClassLoader es innecesario y redundante. El único `require_once` que existe en todo el proyecto es `vendor/autoload.php` en `index.php`.

### `Common` y `public` en la raíz

`Common/DependencyInjection.php` y `public/index.php` son **transversales a todos los módulos**. Ubicarlos dentro de `crud-usuarios/` los atarían incorrectamente a ese módulo. Al estar en la raíz, cuando se añada el módulo `calificaciones/`, la `DependencyInjection` simplemente agrega sus fábricas sin mover nada.

### `namespace App\Common`

Al estar `Common/` en la raíz y el PSR-4 mapear `App\` a `""`, el namespace correcto es `App\Common` — no `App\crud_usuarios\Common`.

### `View::redirect()` retorna `never`

PHP 8.1 introdujo el tipo de retorno `never` para métodos que siempre terminan la ejecución (`exit`, `throw`). Usarlo en `redirect()` permite que los analizadores estáticos (PHPStan, Psalm) detecten código inalcanzable después de un redirect.

### Constructor promotion + `readonly` en todos los DTOs

Los DTOs y el controlador usan `private readonly` en constructor promotion. Esto garantiza inmutabilidad sin boilerplate — una vez creado el objeto, ningún campo puede cambiar.

### `getUserRepository()` es `public`

El método `getUserRepository()` en `DependencyInjection` es `public` porque `index.php` lo necesita directamente en el caso `forgot.send` para llamar a `update()` con la nueva contraseña temporal — una operación que no pasa por el `UserController`.
