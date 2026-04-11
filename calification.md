# CalificationModel — Entidad del Dominio de Calificaciones

**Proyecto:** Aplicación Web básica sobre PHP y MySQL aplicando arquitectura hexagonal y DDD  
**Tutor:** John Carlos Arrieta Arrieta  
**Documento:** CalificationModel  
**Fecha:** 2026-04-11

---

## ¿Qué es CalificationModel?

`CalificationModel` es la entidad principal del dominio de calificaciones. Representa una calificación académica asignada a un estudiante por un docente en una asignatura específica. Es el **Aggregate Root** del bounded context de calificaciones.

Esta entidad encapsula toda la información y reglas de negocio relacionadas con las calificaciones, asegurando que nunca pueda existir en un estado inválido.

### Reglas de negocio principales

- Una calificación siempre debe tener un ID único
- La fecha debe ser válida y estar en el formato correcto
- El docente, asignatura, carrera, universidad y periodo son obligatorios
- La actividad evaluada y porcentaje son requeridos
- El estudiante (studentId) debe existir y ser válido
- La nota debe ser un valor numérico válido
- Los porcentajes se expresan como números decimales (ej: 0.25 para 25%)

---

## Estructura de carpetas

```
Domain/
├── Models/
│   └── CalificationModel.php
├── ValuesObjects/
│   ├── CalificationId.php
│   ├── CalificationFecha.php
│   ├── CalificationDocente.php
│   ├── CalificationAsignatura.php
│   ├── CalificationCarrera.php
│   ├── CalificationUniversidad.php
│   ├── CalificationPeriodo.php
│   ├── CalificationActividadEvaluada.php
│   ├── CalificationPorcentaje.php
│   ├── CalificationNota.php
│   └── UserId.php (referencia al estudiante)
├── Exceptions/
│   ├── CalificationNotFoundException.php
│   └── [otras excepciones de validación]
└── Events/
    ├── CalificationCreatedDomainEvent.php
    ├── CalificationUpdatedDomainEvent.php
    └── CalificationDeletedDomainEvent.php
```

---

## Propiedades del CalificationModel

| Propiedad              | Tipo                          | Descripción |
|------------------------|-------------------------------|-------------|
| `$id`                  | `CalificationId`             | Identificador único de la calificación |
| `$fecha`               | `CalificationFecha`          | Fecha de la calificación |
| `$docente`             | `CalificationDocente`        | Nombre del docente que asigna la calificación |
| `$asignatura`          | `CalificationAsignatura`     | Nombre de la asignatura |
| `$carrera`             | `CalificationCarrera`        | Carrera académica |
| `$universidad`         | `CalificationUniversidad`    | Universidad |
| `$periodo`             | `CalificationPeriodo`        | Periodo académico |
| `$actividadEvaluada`   | `CalificationActividadEvaluada` | Actividad que se evaluó |
| `$porcentaje`          | `CalificationPorcentaje`     | Porcentaje de la calificación sobre la nota final |
| `$studentId`           | `UserId`                     | ID del estudiante (referencia a UserModel) |
| `$nota`                | `CalificationNota`           | Nota obtenida (valor numérico) |

---

## Constructor

```php
public function __construct(
    private CalificationId              $id,
    private CalificationFecha           $fecha,
    private CalificationDocente         $docente,
    private CalificationAsignatura      $asignatura,
    private CalificationCarrera         $carrera,
    private CalificationUniversidad     $universidad,
    private CalificationPeriodo         $periodo,
    private CalificationActividadEvaluada $actividadEvaluada,
    private CalificationPorcentaje      $porcentaje,
    private UserId                      $studentId,
    private CalificationNota            $nota,
) {}
```

**Características importantes:**
- Constructor privado: Solo se puede crear a través de métodos estáticos o factories
- Inyección de Value Objects: Garantiza validación automática
- Inmutabilidad: Una vez creada, la calificación no cambia (para mutaciones se crean nuevas instancias)

---

## Métodos públicos

### Getters (accesores)

```php
public function id(): CalificationId                       { return $this->id; }
public function fecha(): CalificationFecha                 { return $this->fecha; }
public function docente(): CalificationDocente             { return $this->docente; }
public function asignatura(): CalificationAsignatura       { return $this->asignatura; }
public function carrera(): CalificationCarrera             { return $this->carrera; }
public function universidad(): CalificationUniversidad     { return $this->universidad; }
public function periodo(): CalificationPeriodo             { return $this->periodo; }
public function actividadEvaluada(): CalificationActividadEvaluada { return $this->actividadEvaluada; }
public function porcentaje(): CalificationPorcentaje       { return $this->porcentaje; }
public function studentId(): UserId                        { return $this->studentId; }
public function nota(): CalificationNota                   { return $this->nota; }
```

Todos los getters retornan los Value Objects correspondientes, nunca valores primitivos.

### `toArray()` — Conversión a array

```php
public function toArray(): array
{
    return [
        'id'                => $this->id->value(),
        'fecha'             => $this->fecha->toDbFormat(),
        'docente'           => $this->docente->value(),
        'asignatura'        => $this->asignatura->value(),
        'carrera'           => $this->carrera->value(),
        'universidad'       => $this->universidad->value(),
        'periodo'           => $this->periodo->value(),
        'actividadEvaluada' => $this->actividadEvaluada->value(),
        'porcentaje'        => $this->porcentaje->value(),
        'studentId'         => $this->studentId->value(),
        'nota'              => $this->nota->value(),
    ];
}
```

**Propósito:** Convertir la entidad a un array simple para:
- Respuestas HTTP/JSON
- Persistencia en base de datos
- Logging y debugging

**Nota:** Los Value Objects tienen sus propios métodos de conversión (`value()`, `toDbFormat()`).

---

## Value Objects relacionados

### CalificationId
- **Tipo:** `string` (UUID v4)
- **Validación:** No puede estar vacío
- **Uso:** Identificador único global

### CalificationFecha
- **Tipo:** `DateTime`
- **Validación:** Formato Y-m-d H:i:s o Y-m-d
- **Métodos especiales:** `now()`, `toDbFormat()`, `equals()`

### CalificationPorcentaje
- **Tipo:** `float`
- **Validación:** Valor entre 0.0 y 1.0 (representa porcentaje decimal)
- **Ejemplo:** 0.25 para 25%

### CalificationNota
- **Tipo:** `float`
- **Validación:** Valor numérico positivo
- **Ejemplo:** 85.5, 100.0, 0.0

### Referencia a estudiante
- **Tipo:** `UserId` (del dominio de usuarios)
- **Propósito:** Relación con el estudiante calificado
- **Validación:** El ID debe existir en el sistema de usuarios

---

## Eventos de dominio

### CalificationCreatedDomainEvent
**Cuándo se lanza:** Al crear una nueva calificación
**Payload:**
```php
[
    'id' => string,
    'fecha' => string,
    'docente' => string,
    'asignatura' => string,
    'carrera' => string,
    'universidad' => string,
    'periodo' => string,
    'actividadEvaluada' => string,
    'porcentaje' => float,
    'studentId' => string,
    'nota' => float
]
```

### CalificationUpdatedDomainEvent
**Cuándo se lanza:** Al modificar una calificación existente
**Payload:** Mismos campos que Created

### CalificationDeletedDomainEvent
**Cuándo se lanza:** Al eliminar una calificación
**Payload:**
```php
['id' => string]
```

---

## Reglas de negocio implementadas

1. **Integridad referencial:** El `studentId` debe corresponder a un usuario existente
2. **Consistencia de porcentajes:** Los porcentajes deben sumar correctamente por asignatura/periodo
3. **Validación temporal:** La fecha no puede ser futura (salvo casos excepcionales)
4. **Permisos de docente:** Solo docentes autorizados pueden asignar calificaciones
5. **Histórico inmutable:** Las calificaciones no se pueden modificar una vez cerradas

---

## Casos de uso típicos

### Crear calificación
```php
$calification = new CalificationModel(
    new CalificationId($id),
    new CalificationFecha($fecha),
    new CalificationDocente($docente),
    new CalificationAsignatura($asignatura),
    new CalificationCarrera($carrera),
    new CalificationUniversidad($universidad),
    new CalificationPeriodo($periodo),
    new CalificationActividadEvaluada($actividad),
    new CalificationPorcentaje($porcentaje),
    new UserId($studentId),
    new CalificationNota($nota)
);
```

### Obtener datos para persistencia
```php
$data = $calification->toArray();
// Resultado: array con valores primitivos listos para BD
```

### Validación automática
```php
try {
    $fecha = new CalificationFecha('2026-04-11 10:30:00');
} catch (InvalidCalificationFechaException $e) {
    // Fecha inválida - se lanza excepción automáticamente
}
```

---

## Consideraciones de diseño

### Inmutabilidad
Siguiendo principios de DDD, `CalificationModel` es inmutable. Para "modificar" una calificación, se crea una nueva instancia con los valores actualizados.

### Value Objects como guardianes
Cada propiedad es un Value Object que valida su propio contenido. Esto garantiza que `CalificationModel` nunca pueda estar en estado inválido.

### Separación de responsabilidades
- `CalificationModel`: Lógica de negocio y estado
- Value Objects: Validación de tipos específicos
- Services: Orquestación de casos de uso
- Repositories: Persistencia

### Testing
La inmutabilidad y validación automática hacen que `CalificationModel` sea fácilmente testeable con pruebas unitarias.

---

## Relación con otras entidades

### Con UserModel (estudiantes)
- **Tipo de relación:** Muchos-a-uno
- **Campo:** `studentId` (UserId)
- **Regla:** El estudiante debe existir y estar activo

### Con otras calificaciones
- **Agrupación:** Por estudiante, asignatura y periodo
- **Validación:** Los porcentajes no deben superar 100% por asignatura

---

## Migración y evolución

### Versión actual
- Implementación completa con Value Objects
- Validación estricta de tipos
- Eventos de dominio
- Arquitectura hexagonal

### Futuras mejoras posibles
- Método `create()` estático para creación simplificada
- Métodos de negocio como `isApproved()`, `calculateFinalGrade()`
- Relaciones con entidades de cursos y docentes
- Histórico de cambios con eventos

---

*Esta documentación sigue las convenciones del proyecto y principios de Domain-Driven Design.*

---

## Capa de Application — Orquestación de Casos de Uso

La capa de Application coordina el flujo de información entre el exterior (controladores, APIs) y el dominio de calificaciones. Implementa el patrón **Hexagonal Architecture** y sigue los principios de **CQRS** (Command Query Responsibility Segregation).

### Regla absoluta

> La capa de Application no contiene lógica de negocio. Solo orquesta los casos de uso, delegando validaciones al Dominio y operaciones de persistencia a la Infraestructura.

---

## Estructura de carpetas

```
Application/
├── Ports/
│   ├── In/
│   │   ├── CreateCalificationUseCase.php
│   │   ├── UpdateCalificationUseCase.php
│   │   ├── DeleteCalificationUseCase.php
│   │   ├── GetAllCalificationsUseCase.php
│   │   └── GetByCalificationIdUseCase.php
│   └── Out/
│       ├── SaveCalificationPort.php
│       ├── UpdateCalificationPort.php
│       ├── DeleteCalificationPort.php
│       ├── GetAllCalificationsPort.php
│       └── GetCalificationByIdPort.php
├── Services/
│   ├── Dto/
│   │   ├── Commands/
│   │   │   ├── CreateCalificationCommand.php
│   │   │   ├── UpdateCalificationCommand.php
│   │   │   └── DeleteCalificationCommand.php
│   │   └── Queries/
│   │       ├── GetAllCalificationsQuery.php
│   │       └── GetCalificationByIdQuery.php
│   ├── CreateCalificationService.php
│   ├── UpdateCalificationService.php
│   ├── DeleteCalificationService.php
│   ├── GetAllCalificationsService.php
│   ├── GetCalificationByIdService.php
│   └── Mappers/
│       └── CalificationApplicationMapper.php
```

---

## Componentes

### 1. DTOs — Data Transfer Objects (CQRS Aplicado)

Se aplica estrictamente la segregación CQRS: Commands para escritura, Queries para lectura.

#### Commands (Escritura)

| Archivo                      | Propósito                                    | Campos principales |
|------------------------------|----------------------------------------------|-------------------|
| `CreateCalificationCommand`  | Transporta datos para crear calificación     | id, fecha, docente, asignatura, carrera, universidad, periodo, actividadEvaluada, porcentaje(float), studentId, nota(float) |
| `UpdateCalificationCommand`  | Transporta datos para actualizar calificación| id, fecha, docente, asignatura, carrera, universidad, periodo, actividadEvaluada, porcentaje(float), studentId, nota(float) |
| `DeleteCalificationCommand`  | Transporta ID de calificación a eliminar     | id |

**Características:**
- Constructores con validación básica (trim)
- Getters tipados
- Inmutables (readonly en PHP 8.1+)

#### Queries (Lectura)

| Archivo                      | Propósito                                    |
|------------------------------|----------------------------------------------|
| `GetAllCalificationsQuery`   | Representa intención de listar todas las calificaciones |
| `GetCalificationByIdQuery`   | Transporta ID de calificación a consultar   |

---

### 2. Puertos — Contratos del Hexágono

#### Puertos de Entrada (Ports/In)
Exponen las acciones del sistema hacia el exterior.

| Interfaz                     | Firma                                          | Propósito |
|------------------------------|------------------------------------------------|-----------|
| `CreateCalificationUseCase`  | `execute(CreateCalificationCommand): CalificationModel` | Crear nueva calificación |
| `UpdateCalificationUseCase`  | `execute(UpdateCalificationCommand): CalificationModel` | Actualizar calificación existente |
| `DeleteCalificationUseCase`  | `execute(DeleteCalificationCommand): void`     | Eliminar calificación |
| `GetAllCalificationsUseCase` | `execute(GetAllCalificationsQuery): CalificationModel[]` | Listar todas las calificaciones |
| `GetByCalificationIdUseCase` | `execute(GetCalificationByIdQuery): CalificationModel` | Obtener calificación por ID |

#### Puertos de Salida (Ports/Out)
Exigen a la infraestructura lo que la aplicación necesita.

| Interfaz                     | Firma                                          | Propósito |
|------------------------------|------------------------------------------------|-----------|
| `SaveCalificationPort`       | `save(CalificationModel): CalificationModel`   | Persistir nueva calificación |
| `UpdateCalificationPort`     | `update(CalificationModel): CalificationModel` | Actualizar calificación existente |
| `DeleteCalificationPort`     | `delete(CalificationId): void`                 | Eliminar calificación |
| `GetAllCalificationsPort`    | `getAll(): CalificationModel[]`                | Obtener todas las calificaciones |
| `GetCalificationByIdPort`    | `getById(CalificationId): ?CalificationModel`  | Obtener calificación por ID |

---

### 3. Servicios de Application — Implementación de Casos de Uso

Son las implementaciones concretas de los Use Cases. Reciben dependencias (Ports/Out) por inyección de dependencias.

| Servicio                     | Lógica de orquestación destacada |
|------------------------------|----------------------------------|
| `CreateCalificationService`  | Construye CalificationModel vía mapper, persiste y retorna |
| `UpdateCalificationService`  | Verifica existencia, construye modelo actualizado, persiste |
| `DeleteCalificationService`  | Verifica existencia antes de eliminar |
| `GetAllCalificationsService` | Delega directamente al puerto |
| `GetCalificationByIdService` | Busca por ID, lanza excepción si no existe |

**Patrón común en todos los servicios:**

```php
final class CreateCalificationService implements CreateCalificationUseCase
{
    public function __construct(private SaveCalificationPort $saveCalificationPort) {}

    public function execute(CreateCalificationCommand $command): CalificationModel
    {
        // 1. Construir modelo (Value Objects validan)
        $calification = CalificationApplicationMapper::fromCreateCommandToModel($command);
        
        // 2. Persistir y retornar
        return $this->saveCalificationPort->save($calification);
    }
}
```

---

### 4. CalificationApplicationMapper — Transformador de Datos

Responsable de convertir entre DTOs y modelos del dominio. Métodos estáticos, sin dependencias.

| Método                          | Conversión |
|---------------------------------|------------|
| `fromCreateCommandToModel`      | `CreateCalificationCommand` → `CalificationModel` |
| `fromUpdateCommandToModel`      | `UpdateCalificationCommand` → `CalificationModel` |
| `fromGetCalificationByIdQueryToCalificationId` | `GetCalificationByIdQuery` → `CalificationId` |
| `fromDeleteCommandToCalificationId` | `DeleteCalificationCommand` → `CalificationId` |
| `fromModelToArray`              | `CalificationModel` → `array` |
| `fromModelsToArray`             | `CalificationModel[]` → `array[]` |

**Ejemplo de transformación:**

```php
public static function fromCreateCommandToModel(CreateCalificationCommand $command): CalificationModel
{
    return new CalificationModel(
        new CalificationId($command->getId()),
        new CalificationFecha($command->getFecha()),
        new CalificationDocente($command->getDocente()),
        // ... otros Value Objects
        new CalificationPorcentaje($command->getPorcentaje()), // float
        new UserId($command->getStudentId()),
        new CalificationNota($command->getNota()) // float
    );
}
```

---

## Decisiones de Diseño Críticas

### 1. Tipos estrictos en Commands
- `porcentaje` y `nota` son `float` (no `string`) para representar valores numéricos correctamente
- Constructor valida formato básico, Value Objects hacen validación completa

### 2. Inyección de dependencias
- Servicios reciben puertos vía constructor
- Facilita testing con mocks y cambios de implementación

### 3. Separación CQRS estricta
- Commands: modifican estado
- Queries: solo lectura
- No hay objetos híbridos

### 4. Validación en capas
- Commands: validación básica (trim, tipos)
- Value Objects: validación de negocio
- Services: validación de existencia y reglas transversales

### 5. Mappers como clases estáticas
- Sin estado, solo transformación
- Fáciles de testear unitariamente
- Reutilizables en múltiples contextos

---

## Flujo de una Operación Típica

### Crear Calificación

```
Controller → CreateCalificationCommand → CreateCalificationUseCase.execute()
    ↓
CalificationApplicationMapper.fromCreateCommandToModel()
    ↓
new CalificationModel(...) ← Value Objects validan
    ↓
SaveCalificationPort.save() → Repository → Base de datos
    ↓
Retorna CalificationModel persistido
```

### Leer Calificación

```
Controller → GetCalificationByIdQuery → GetByCalificationIdUseCase.execute()
    ↓
CalificationApplicationMapper.fromGetCalificationByIdQueryToCalificationId()
    ↓
GetCalificationByIdPort.getById() → Repository → Base de datos
    ↓
Retorna CalificationModel o lanza CalificationNotFoundException
```

---

## Testing de la Capa Application

### Unit Tests
- **Services:** Verificar orquestación correcta, llamadas a puertos
- **Mappers:** Verificar transformaciones correctas
- **DTOs:** Verificar constructores y getters

### Integration Tests
- **Con repositorios reales:** Verificar flujo completo
- **Con bases de datos en memoria:** Para CI/CD

### Ejemplo de test para Service

```php
public function testCreateCalificationSuccess(): void
{
    // Arrange
    $command = new CreateCalificationCommand(/* ... */);
    $expectedModel = new CalificationModel(/* ... */);
    
    $portMock = $this->createMock(SaveCalificationPort::class);
    $portMock->expects($this->once())
             ->method('save')
             ->willReturn($expectedModel);
    
    $service = new CreateCalificationService($portMock);
    
    // Act
    $result = $service->execute($command);
    
    // Assert
    $this->assertEquals($expectedModel, $result);
}
```

---

## Relación con Otras Capas

### Con el Dominio
- **Entrada:** Recibe Value Objects ya validados
- **Salida:** Retorna entidades del dominio
- **Regla:** Nunca accede directamente a propiedades privadas del dominio

### Con la Infraestructura
- **Contratos:** Solo conoce interfaces (puertos)
- **Implementaciones:** Inyectadas, no crea instancias
- **Tecnologías:** Desconoce PDO, SQL, HTTP

### Con los Entrypoints
- **Entrada:** Recibe DTOs simples
- **Salida:** Retorna modelos del dominio
- **Transformación:** Mapper convierte entre formatos

---

## Consideraciones de Evolución

### Extensibilidad
- Nuevos casos de uso: agregar Use Case + Service + Ports
- Nuevos campos: actualizar Commands + Mapper + Model
- Nuevas validaciones: agregar en Value Objects

### Mantenibilidad
- Separación clara de responsabilidades
- Interfaces facilitan cambios de implementación
- Tests automatizados garantizan estabilidad

### Performance
- Lazy loading en queries complejas
- Paginación en GetAll
- Caching de datos frecuentemente accedidos

---

*Esta documentación sigue las convenciones del proyecto y principios de Clean Architecture.*
