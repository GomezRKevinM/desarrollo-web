# Estudiantes (Students)

**Proyecto:** Aplicación Web básica sobre PHP y MySQL aplicando arquitectura hexagonal y DDD
**Tutor:** John Carlos Arrieta Arrieta
**Documento:** Clases Adicionales — Estudiantes (Students)

---

## Introducción

La entidad **Student** (Estudiante) representa un estudiante en el sistema. A diferencia de `User`, que es un usuario, un `Student` es un registro académico que puede estar vinculado a una `Calification`.

Un estudiante tiene:
- **Identidad única:** `StudentId` (UUID)
- **Información personal:** nombre y apellido
- **Metadata:** timestamps de creación y actualización

---

## Estructura de carpetas

```
app/Domain/
├── Exceptions/
│   ├── InvalidStudentIdException.php
│   ├── InvalidStudentNameException.php
│   ├── InvalidStudentLastNameException.php
│   └── StudentAlreadyExistsException.php
│   └── StudentNotFoundException.php
├── ValuesObjects/
│   ├── StudentId.php
│   ├── StudentName.php
│   └── StudentLastName.php
├── Models/
│   └── StudentModel.php
└── Events/
    ├── StudentCreatedDomainEvent.php
    ├── StudentUpdatedDomainEvent.php
    └── StudentDeletedDomainEvent.php

app/Application/
├── Ports/
│   └── In/
│       ├── CreateStudentUseCase.php
│       ├── UpdateStudentUseCase.php
│       ├── DeleteStudentUseCase.php
│       ├── GetStudentByIdUseCase.php
│       └── GetAllStudentsUseCase.php
└── Services/
    ├── Dto/
    │   ├── Commands/
    │   │   ├── CreateStudentCommand.php
    │   │   ├── UpdateStudentCommand.php
    │   │   └── DeleteStudentCommand.php
    │   └── Queries/
    │       ├── GetStudentByIdQuery.php
    │       └── GetAllStudentsQuery.php
    ├── CreateStudentService.php
    ├── UpdateStudentService.php
    ├── DeleteStudentService.php
    ├── GetStudentByIdService.php
    ├── GetAllStudentsService.php
    └── Mappers/
        └── StudentApplicationMapper.php

app/Infrastructure/
├── Adapters/
│   └── Persistence/
│       └── MySQL/
│           ├── Dto/
│           │   └── StudentPersistenceDto.php
│           ├── Entity/
│           │   └── StudentEntity.php
│           ├── Mapper/
│           │   └── StudentPersistenceMapper.php
│           └── Repository/
│               └── StudentRepositoryMySQL.php
└── Entrypoints/
    └── Web/
        ├── Controllers/
        │   ├── Dto/
        │   │   ├── CreateStudentWebRequest.php
        │   │   ├── UpdateStudentWebRequest.php
        │   │   └── StudentResponse.php
        │   ├── Mapper/
        │   │   └── StudentWebMapper.php
        │   └── StudentController.php
        └── Presentation/
            └── Views/
                └── students/
                    ├── create.php
                    ├── list.php
                    ├── show.php
                    └── edit.php
```

---

## 1. Excepciones del Dominio

Las excepciones de dominio representan situaciones inválidas del negocio específicas de estudiantes.

| Clase                            | Extiende                   | ¿Quién la lanza?       | Named Constructors                                      |
|----------------------------------|----------------------------|------------------------|--------------------------------------------------------|
| `InvalidStudentIdException`      | `InvalidArgumentException` | `StudentId`            | `becauseValueIsEmpty()`                                |
| `InvalidStudentNameException`    | `InvalidArgumentException` | `StudentName`          | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)` |
| `InvalidStudentLastNameException`| `InvalidArgumentException` | `StudentLastName`      | `becauseValueIsEmpty()`, `becauseLengthIsTooShort($min)` |
| `StudentAlreadyExistsException`  | `DomainException`          | Servicios de Aplicación| `becauseIdAlreadyExists($id)`                          |
| `StudentNotFoundException`       | `DomainException`          | Servicios de Aplicación| `becauseIdWasNotFound($id)`                            |

### Patrón Named Constructors

```php
// ❌ El mensaje se puede olvidar o repetir distinto
throw new InvalidStudentNameException('El nombre está vacío');

// ✅ El nombre del método documenta el motivo exacto
throw InvalidStudentNameException::becauseValueIsEmpty();
```

### `Domain/Exceptions/InvalidStudentIdException.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

class InvalidStudentIdException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El ID del estudiante no puede estar vacío.');
    }
}
```

### `Domain/Exceptions/InvalidStudentNameException.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

class InvalidStudentNameException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre del estudiante no puede estar vacío.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self(
            sprintf('El nombre del estudiante debe tener al menos %d caracteres.', $min)
        );
    }
}
```

### `Domain/Exceptions/InvalidStudentLastNameException.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

class InvalidStudentLastNameException extends InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El apellido del estudiante no puede estar vacío.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self(
            sprintf('El apellido del estudiante debe tener al menos %d caracteres.', $min)
        );
    }
}
```

### `Domain/Exceptions/StudentAlreadyExistsException.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

class StudentAlreadyExistsException extends DomainException
{
    public static function becauseIdAlreadyExists(string $id): self
    {
        return new self(
            sprintf('Ya existe un estudiante con el ID "%s".', $id)
        );
    }
}
```

### `Domain/Exceptions/StudentNotFoundException.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

class StudentNotFoundException extends DomainException
{
    public static function becauseIdWasNotFound(string $id): self
    {
        return new self(
            sprintf('No se encontró estudiante con el ID "%s".', $id)
        );
    }
}
```

---

## 2. Value Objects

Los Value Objects son objetos que representan conceptos del dominio con validación incorporada. **No pueden existir en estado inválido**.

| Clase             | Reglas que valida                                      |
|-------------------|--------------------------------------------------------|
| `StudentId`       | No puede estar vacío                                   |
| `StudentName`     | No puede estar vacío, mínimo 2 caracteres              |
| `StudentLastName` | No puede estar vacío, mínimo 2 caracteres              |

### Métodos comunes de todo Value Object

```php
$vo->value();          // retorna el valor primitivo encapsulado
$vo->equals($other);   // compara por valor, no por referencia
(string) $vo;          // permite usar el VO donde PHP espera un string
```

### `Domain/ValuesObjects/StudentId.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidStudentIdException;

final class StudentId
{
    public function __construct(
        private readonly string $value,
    ) {
        if ($value === '') {
            throw InvalidStudentIdException::becauseValueIsEmpty();
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StudentId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### `Domain/ValuesObjects/StudentName.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidStudentNameException;

final class StudentName
{
    private const MIN_LENGTH = 2;

    public function __construct(
        private readonly string $value,
    ) {
        if ($value === '') {
            throw InvalidStudentNameException::becauseValueIsEmpty();
        }

        if (strlen($value) < self::MIN_LENGTH) {
            throw InvalidStudentNameException::becauseLengthIsTooShort(self::MIN_LENGTH);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StudentName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

### `Domain/ValuesObjects/StudentLastName.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidStudentLastNameException;

final class StudentLastName
{
    private const MIN_LENGTH = 2;

    public function __construct(
        private readonly string $value,
    ) {
        if ($value === '') {
            throw InvalidStudentLastNameException::becauseValueIsEmpty();
        }

        if (strlen($value) < self::MIN_LENGTH) {
            throw InvalidStudentLastNameException::becauseLengthIsTooShort(self::MIN_LENGTH);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StudentLastName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

---

## 3. StudentModel — La Entidad del Dominio

`StudentModel` es la entidad principal (Aggregate Root) de estudiantes. Representa un estudiante con toda su información y las reglas que lo gobiernan.

### Estado interno

| Propiedad    | Tipo                |
|--------------|---------------------|
| `$id`        | `StudentId`         |
| `$name`      | `StudentName`       |
| `$lastName`  | `StudentLastName`   |

### Constructor vs `create()`

```php
// Reconstruir un estudiante desde la BD (ya tiene estado asignado)
new StudentModel($id, $name, $lastName);

// Crear un estudiante nuevo
StudentModel::create($id, $name, $lastName);
```

### Mutaciones inmutables

Ningún método modifica `$this`. Todos retornan un nuevo objeto:

```php
$student = StudentModel::create(...);              // estado inicial
$renamedStudent = $student->changeName($newName); // nuevo objeto con nombre actualizado
// $student sigue siendo con el nombre original
```

| Método                | Resultado                                              |
|-----------------------|--------------------------------------------------------|
| `changeName()`        | Nuevo `StudentModel` con el nombre actualizado         |
| `changeLastName()`    | Nuevo `StudentModel` con el apellido actualizado       |
| `toArray()`           | Array con los valores primitivos de todas las propiedades |

### `Domain/Models/StudentModel.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Models;

use App\crud_usuarios\Domain\ValuesObjects\StudentId;
use App\crud_usuarios\Domain\ValuesObjects\StudentName;
use App\crud_usuarios\Domain\ValuesObjects\StudentLastName;

final class StudentModel
{
    public function __construct(
        private readonly StudentId $id,
        private readonly StudentName $name,
        private readonly StudentLastName $lastName,
    ) {}

    /**
     * Factory method para crear un nuevo estudiante.
     */
    public static function create(
        StudentId $id,
        StudentName $name,
        StudentLastName $lastName,
    ): self {
        return new self($id, $name, $lastName);
    }

    public function id(): StudentId
    {
        return $this->id;
    }

    public function name(): StudentName
    {
        return $this->name;
    }

    public function lastName(): StudentLastName
    {
        return $this->lastName;
    }

    /**
     * Retorna un nuevo StudentModel con el nombre actualizado.
     */
    public function changeName(StudentName $newName): self
    {
        return new self($this->id, $newName, $this->lastName);
    }

    /**
     * Retorna un nuevo StudentModel con el apellido actualizado.
     */
    public function changeLastName(StudentLastName $newLastName): self
    {
        return new self($this->id, $this->name, $newLastName);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id->value(),
            'name'     => $this->name->value(),
            'lastName' => $this->lastName->value(),
        ];
    }
}
```

---

## 4. Domain Events — Hechos que Ocurrieron

Los eventos de dominio representan hechos del pasado en el negocio. Se nombran siempre en **pasado**.

#### Clase base abstracta `DomainEvent`

```php
$event->eventName();   // nombre en formato entidad.acción (ej: 'student.created')
$event->occurredOn();  // timestamp de cuándo ocurrió
$event->payload();     // abstracto → cada evento implementa sus propios datos
```

#### Eventos de estudiantes

| Clase                        | Evento              | Recibe        | Datos en payload         |
|------------------------------|---------------------|---------------|--------------------------|
| `StudentCreatedDomainEvent`  | `student.created`   | `StudentModel`| id, name, lastName       |
| `StudentUpdatedDomainEvent`  | `student.updated`   | `StudentModel`| id, name, lastName       |
| `StudentDeletedDomainEvent`  | `student.deleted`   | `StudentId`   | id                       |

### `Domain/Events/StudentCreatedDomainEvent.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Events;

use App\crud_usuarios\Domain\Models\StudentModel;

final class StudentCreatedDomainEvent extends DomainEvent
{
    public function __construct(
        private readonly StudentModel $student,
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'student.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->student->toArray();
    }

    public function student(): StudentModel
    {
        return $this->student;
    }
}
```

### `Domain/Events/StudentUpdatedDomainEvent.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Events;

use App\crud_usuarios\Domain\Models\StudentModel;

final class StudentUpdatedDomainEvent extends DomainEvent
{
    public function __construct(
        private readonly StudentModel $student,
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'student.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->student->toArray();
    }

    public function student(): StudentModel
    {
        return $this->student;
    }
}
```

### `Domain/Events/StudentDeletedDomainEvent.php`

```php
<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Events;

use App\crud_usuarios\Domain\ValuesObjects\StudentId;

final class StudentDeletedDomainEvent extends DomainEvent
{
    public function __construct(
        private readonly StudentId $id,
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'student.deleted';
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return ['id' => $this->id->value()];
    }

    public function id(): StudentId
    {
        return $this->id;
    }
}
```

---

## 5. Capa de Aplicación

### Puertos de Salida (Ports/Out)

Exigen a la infraestructura lo que la aplicación necesita para funcionar.

| Interfaz             | Método                           | Retorno         |
|----------------------|----------------------------------|-----------------|
| SaveStudentPort      | save(StudentModel $student)      | StudentModel    |
| UpdateStudentPort    | update(StudentModel $student)    | StudentModel    |
| DeleteStudentPort    | delete(StudentId $id)            | void            |
| GetStudentByIdPort   | getById(StudentId $id)           | ?StudentModel   |
| GetAllStudentsPort   | getAll()                         | StudentModel[]  |

### Puertos de Entrada (Ports/In)

Exponen las acciones del sistema hacia el exterior.

| Interfaz                | Firma                                              |
|-------------------------|--------------------------------------------------|
| `CreateStudentUseCase`  | execute(CreateStudentCommand $command): StudentModel |
| `UpdateStudentUseCase`  | execute(UpdateStudentCommand $command): StudentModel |
| `DeleteStudentUseCase`  | execute(DeleteStudentCommand $command): void        |
| `GetStudentByIdUseCase` | execute(GetStudentByIdQuery $query): StudentModel    |
| `GetAllStudentsUseCase` | execute(GetAllStudentsQuery $query): StudentModel[]  |

### DTOs — Data Transfer Objects

#### Commands (Escritura)

```php
// CreateStudentCommand
new CreateStudentCommand($id, $name, $lastName);

// UpdateStudentCommand
new UpdateStudentCommand($id, $name, $lastName);

// DeleteStudentCommand
new DeleteStudentCommand($id);
```

#### Queries (Lectura)

```php
// GetStudentByIdQuery
new GetStudentByIdQuery($id);

// GetAllStudentsQuery
new GetAllStudentsQuery();
```

### Mapper — Transformador de datos

`StudentApplicationMapper` transforma DTOs en objetos del Dominio y viceversa.

| Método                            | Entrada → Salida                  |
|-----------------------------------|-----------------------------------|
| fromCreateCommandToModel          | `CreateStudentCommand` → `StudentModel` |
| fromUpdateCommandToModel          | `UpdateStudentCommand` → `StudentModel` |
| fromGetStudentByIdQueryToStudentId| `GetStudentByIdQuery` → `StudentId`     |
| fromDeleteCommandToStudentId      | `DeleteStudentCommand` → `StudentId`    |
| fromModelToArray                  | `StudentModel` → `array`          |
| fromModelsToArray                 | `StudentModel[]` → `array[]`      |

### Servicios de Aplicación — Los Casos de Uso

| Servicio              | Lógica destacada                                                 |
|-----------------------|------------------------------------------------------------------|
| CreateStudentService  | Verifica duplicidad del ID antes de guardar.                     |
| UpdateStudentService  | Verifica existencia del estudiante antes de actualizar.           |
| DeleteStudentService  | Verifica existencia del estudiante antes de eliminar.             |
| GetStudentByIdService | Lanza StudentNotFoundException si no existe.                      |
| GetAllStudentsService | Delega directamente al puerto, sin lógica adicional.              |

---

## 6. Infraestructura: Persistencia MySQL

### Esquema de base de datos requerido

```sql
CREATE TABLE IF NOT EXISTS students (
    id         VARCHAR(36)  NOT NULL,
    name       VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NOT NULL,
    PRIMARY KEY (id)
);
```

### Componentes

#### StudentPersistenceDto
DTO que transporta los campos del estudiante como strings simples, listos para SQL.

| Propiedad   | Tipo     |
|-------------|----------|
| `$id`       | `string` |
| `$name`     | `string` |
| `$lastName` | `string` |

#### StudentEntity
Representa una fila de la tabla `students` tal como llega de un `SELECT`.

#### StudentPersistenceMapper
Convierte entre `StudentModel`, `StudentEntity` y `StudentPersistenceDto`.

**Métodos**

| Método               | De                      | A               | Cuándo se usa           |
|----------------------|-------------------------|-----------------|-------------------------|
| `fromModelToDto`     | `StudentModel`          | `StudentPersistenceDto` | Antes de INSERT/UPDATE |
| `fromRowToEntity`    | `array` (fila SQL)      | `StudentEntity` | Al recibir SELECT       |
| `fromEntityToModel`  | `StudentEntity`         | `StudentModel`  | Para reconstruir        |
| `fromRowToModel`     | `array` (fila SQL)      | `StudentModel`  | Atajo directo           |
| `fromRowsToModels`   | `array[]` (múltiples)   | `StudentModel[]`| Para listados           |

#### StudentRepositoryMySQL
Implementa los 5 puertos de salida usando PDO y MySQL.

**Métodos**

| Método          | Operación SQL                      |
|-----------------|-------------------------------------|
| `save()`        | `INSERT INTO students`             |
| `update()`      | `UPDATE students SET ... WHERE id` |
| `getById()`     | `SELECT ... WHERE id = :id`        |
| `getAll()`      | `SELECT ...`                       |
| `delete()`      | `DELETE FROM students WHERE id`    |

---

## 7. Entrypoints Web

### DTOs Web

#### CreateStudentWebRequest
```php
new CreateStudentWebRequest($id, $name, $lastName);
```

#### UpdateStudentWebRequest
```php
new UpdateStudentWebRequest($id, $name, $lastName);
```

#### StudentResponse
```php
new StudentResponse($id, $name, $lastName);
```

### Mapper Web

`StudentWebMapper` transforma DTOs web en Commands/Queries de aplicación.

| Método                           | De                      | A                  |
|----------------------------------|-------------------------|-------------------|
| fromCreateRequestToCommand       | `CreateStudentWebRequest` | `CreateStudentCommand` |
| fromUpdateRequestToCommand       | `UpdateStudentWebRequest` | `UpdateStudentCommand` |
| fromIdToGetByIdQuery             | `string` (ID)           | `GetStudentByIdQuery`  |
| fromIdToDeleteCommand            | `string` (ID)           | `DeleteStudentCommand` |
| fromModelToResponse              | `StudentModel`          | `StudentResponse`  |
| fromModelsToResponses            | `StudentModel[]`        | `StudentResponse[]`|

### Controlador

`StudentController` orquesta los casos de uso de estudiantes.

**Métodos**

```php
index(): StudentResponse[]         // Listar todos
show(string $id): StudentResponse  // Obtener por ID
store(CreateStudentWebRequest)     // Crear nuevo
update(UpdateStudentWebRequest)    // Actualizar
delete(string $id): void           // Eliminar
```

### Rutas Web

| Ruta                  | Método | Acción |
|-----------------------|--------|--------|
| `students.create`     | GET    | create |
| `students.store`      | POST   | store  |
| `students.index`      | GET    | index  |
| `students.show`       | GET    | show   |
| `students.edit`       | GET    | edit   |
| `students.update`     | POST   | update |
| `students.delete`     | POST   | delete |

### Vistas

#### `Views/students/create.php`
Formulario para registrar un nuevo estudiante.

Campos:
- Nombre (requerido, mínimo 2 caracteres)
- Apellido (requerido, mínimo 2 caracteres)

#### `Views/students/list.php`
Tabla con listado de todos los estudiantes.

Acciones: Ver, Editar, Eliminar

#### `Views/students/show.php`
Vista detallada de un estudiante.

#### `Views/students/edit.php`
Formulario para editar un estudiante existente.

---

## 8. Orden de Construcción

Los archivos se construyen en este orden exacto. Cada uno depende solo de los anteriores:

```
1. Excepciones         → sin dependencias
2. Value Objects       → dependen de sus Exceptions
3. DomainEvent         → clase base abstracta
4. StudentModel        → depende de ValueObjects
5. Eventos             → dependen de DomainEvent + StudentModel
6. Puertos Salida      → interfaces (sin dependencias internas)
7. DTOs Aplicación     → sin dependencias
8. Mapper Aplicación   → depende de DTOs + Domain
9. Servicios           → dependen de Puertos + Mapper + Domain
10. Persistence Layer  → DTOs, Entities, Mappers
11. Repository         → depende de Persistence Layer + Domain
12. Web DTOs           → sin dependencias
13. Web Mapper         → depende de Web DTOs + Application DTOs
14. Controller         → depende de Puertos + Web Mapper
15. Routes             → definición de rutas
16. Views              → templates HTML
```

---

## 9. Flujos Comunes de Casos de Uso

### Crear Estudiante

```
WebRequest (name, lastName)
    ↓ (StudentWebMapper)
CreateStudentCommand
    ↓ (CreateStudentService)
StudentModel::create() [valida name, lastName]
    ↓ (SaveStudentPort)
INSERT into BD
    ↓ (StudentRepositoryMySQL)
SELECT (para obtener estado persistido)
    ↓
StudentModel [retornado]
    ↓ (StudentWebMapper)
StudentResponse → Vista/JSON
```

### Actualizar Estudiante

```
WebRequest (id, name, lastName)
    ↓ (StudentWebMapper)
UpdateStudentCommand
    ↓ (UpdateStudentService)
GetStudentByIdPort [verifica existencia]
StudentModel::changeName()/changeLastName()
    ↓ (UpdateStudentPort)
UPDATE en BD
    ↓
SELECT (para obtener estado persistido)
    ↓
StudentModel [retornado]
    ↓ (StudentWebMapper)
StudentResponse → Vista/JSON
```

### Eliminar Estudiante

```
WebRequest (id)
    ↓ (StudentWebMapper)
DeleteStudentCommand
    ↓ (DeleteStudentService)
GetStudentByIdPort [verifica existencia]
    ↓ (DeleteStudentPort)
DELETE en BD
    ↓
void [sin retorno]
```

### Listar Estudiantes

```
WebRequest
    ↓ (StudentWebMapper)
GetAllStudentsQuery
    ↓ (GetAllStudentsService)
GetAllStudentsPort
    ↓ (StudentRepositoryMySQL)
SELECT * FROM students ORDER BY name
    ↓
StudentModel[]
    ↓ (StudentWebMapper)
StudentResponse[] → Vista/JSON
```

---

## 10. Validaciones

### En el Dominio (Value Objects)

- **StudentId:** No puede estar vacío
- **StudentName:** No puede estar vacío, mínimo 2 caracteres
- **StudentLastName:** No puede estar vacío, mínimo 2 caracteres

### En la Aplicación (Servicios)

- **CreateStudentService:** Verifica que no exista estudiante con el mismo ID
- **UpdateStudentService:** Verifica existencia del estudiante
- **DeleteStudentService:** Verifica existencia del estudiante

### En el Entrypoint (Controlador Web)

- Campo name requerido
- Campo lastName requerido
- Validación de longitud mínima

---

## 11. Manejo de Errores

Las excepciones fluyen desde el dominio hacia arriba:

```
DomainException (padre)
    ├── InvalidStudentIdException
    ├── InvalidStudentNameException
    ├── InvalidStudentLastNameException
    ├── StudentAlreadyExistsException
    └── StudentNotFoundException

InvalidArgumentException
    ├── InvalidStudentIdException
    ├── InvalidStudentNameException
    └── InvalidStudentLastNameException
```

En `index.php`, todas las excepciones se capturan y se muestran mensajes amigables al usuario:

```php
try {
    // Operación
} catch (\Throwable $exception) {
    $msg = $exception->getMessage();
    Flash::setMessage($msg);
    // Redirigir o renderizar según la acción
}
```

---

## 12. Convenciones

### Namespaces

```php
App\crud_usuarios\Domain\Models\StudentModel
App\crud_usuarios\Domain\ValuesObjects\StudentId
App\crud_usuarios\Application\Ports\In\CreateStudentUseCase
App\crud_usuarios\Application\Services\CreateStudentService
App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Repository\StudentRepositoryMySQL
App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\StudentController
```

### Declaración de tipos

Todos los archivos del dominio y aplicación usan `declare(strict_types=1)` al inicio.

### Property promotion y readonly

```php
public function __construct(
    private readonly StudentId $id,
    private readonly StudentName $name,
) {}
```

### Métodos estáticos en Mappers

```php
StudentApplicationMapper::fromCreateCommandToModel($command);
StudentWebMapper::fromCreateRequestToCommand($request);
StudentPersistenceMapper::fromModelToDto($model);
```

---

## 13. Integración con DependencyInjection

En `Common/DependencyInjection.php`, se agregan los métodos factory para estudiantes:

```php
public static function getCreateStudentUseCase(): CreateStudentUseCase
{
    $repo = self::getStudentRepository();
    return new CreateStudentService($repo, $repo);
}

public static function getUpdateStudentUseCase(): UpdateStudentUseCase
{
    $repo = self::getStudentRepository();
    return new UpdateStudentService($repo, $repo, $repo);
}

public static function getStudentController(): StudentController
{
    return new StudentController(
        createStudentUseCase:  self::getCreateStudentUseCase(),
        updateStudentUseCase:  self::getUpdateStudentUseCase(),
        getStudentByIdUseCase: self::getGetStudentByIdUseCase(),
        getAllStudentsUseCase: self::getGetAllStudentsUseCase(),
        deleteStudentUseCase:  self::getDeleteStudentUseCase(),
        mapper:                new StudentWebMapper(),
    );
}
```



