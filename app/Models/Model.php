<?php
namespace App\Models;

use Sdba;

abstract class Model
{
    /**
     * Nombre de la tabla en la base de datos
     */
    protected static string $table = '';

    /**
     * Clave primaria de la tabla
     */
    protected static string $primaryKey = 'id';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected static array $fillable = [];

    /**
     * Reglas de validación
     */
    protected static array $rules = [];

    /**
     * Atributos del modelo
     */
    protected array $attributes = [];

    /**
     * Atributos originales (antes de cambios)
     */
    protected array $original = [];

    /**
     * Obtener instancia de SDBA para la tabla
     */
    public static function query(): Sdba
    {
        return Sdba::table(static::$table);
    }

    /**
     * Obtener todos los registros
     */
    public static function all(): array
    {
        $records = static::query()->get();
        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Obtener registros con límite y offset
     */
    public static function paginate(int $limit = 10, int $offset = 0): array
    {
        $records = static::query()->get($limit, $offset);
        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Buscar por ID
     */
    public static function find(int $id): ?static
    {
        $record = static::query()
            ->where(static::$primaryKey, $id)
            ->get_one();

        return $record ? static::hydrate($record) : null;
    }

    /**
     * Buscar por ID o lanzar excepción
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);

        if (!$model) {
            throw new \Exception("Registro no encontrado en " . static::$table . " con ID: {$id}");
        }

        return $model;
    }

    /**
     * Buscar con condición WHERE
     */
    public static function where(string $field, $value, string $operator = '='): array
    {
        $query = static::query();

        if ($operator === '=') {
            $query->where($field, $value);
        } else {
            $query->where($field . ' ' . $operator, $value);
        }

        $records = $query->get();
        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Buscar primer registro con condición
     */
    public static function whereFirst(string $field, $value): ?static
    {
        $record = static::query()
            ->where($field, $value)
            ->get_one();

        return $record ? static::hydrate($record) : null;
    }

    /**
     * Buscar con LIKE
     */
    public static function whereLike(string $field, string $value): array
    {
        $records = static::query()
            ->like($field, $value)
            ->get();

        return array_map(fn($record) => static::hydrate($record), $records);
    }

    /**
     * Crear nuevo registro
     */
    public static function create(array $data): ?static
    {
        $filtered = static::filterFillable($data);

        // Agregar campo de clave primaria vacío para auto-increment
        $filtered[static::$primaryKey] = '';

        static::query()->insert($filtered);
        $id = static::query()->insert_id();

        if ($id) {
            return static::find($id);
        }

        return null;
    }

    /**
     * Actualizar registro existente
     */
    public function update(array $data): bool
    {
        $filtered = static::filterFillable($data);
        $id = $this->getId();

        if (!$id) {
            return false;
        }

        static::query()
            ->where(static::$primaryKey, $id)
            ->update($filtered);

        // Actualizar atributos locales
        $this->fill($filtered);

        return true;
    }

    /**
     * Eliminar registro
     */
    public function delete(): bool
    {
        $id = $this->getId();

        if (!$id) {
            return false;
        }

        static::query()
            ->where(static::$primaryKey, $id)
            ->delete();

        return true;
    }

    /**
     * Eliminar por ID (estático)
     */
    public static function destroy(int $id): bool
    {
        static::query()
            ->where(static::$primaryKey, $id)
            ->delete();

        return true;
    }

    /**
     * Contar registros
     */
    public static function count(): int
    {
        return static::query()->total();
    }

    /**
     * Contar con condición
     */
    public static function countWhere(string $field, $value): int
    {
        return static::query()
            ->where($field, $value)
            ->total();
    }

    /**
     * Verificar si existe un registro
     */
    public static function exists(string $field, $value): bool
    {
        return static::whereFirst($field, $value) !== null;
    }

    /**
     * Obtener como lista clave-valor (para selects)
     */
    public static function getList(string $keyField, string $valueField): array
    {
        return static::query()->get_list($keyField, $valueField);
    }

    /**
     * Hidratar modelo desde array de BD
     */
    protected static function hydrate(array $record): static
    {
        $model = new static();
        $model->attributes = $record;
        $model->original = $record;
        return $model;
    }

    /**
     * Filtrar solo campos fillable
     */
    protected static function filterFillable(array $data): array
    {
        if (empty(static::$fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip(static::$fillable));
    }

    /**
     * Llenar atributos
     */
    public function fill(array $data): self
    {
        $filtered = static::filterFillable($data);
        $this->attributes = array_merge($this->attributes, $filtered);
        return $this;
    }

    /**
     * Obtener ID del registro
     */
    public function getId(): ?int
    {
        $id = $this->attributes[static::$primaryKey] ?? null;
        return $id ? (int)$id : null;
    }

    /**
     * Acceso mágico a atributos (getter)
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Acceso mágico a atributos (setter)
     */
    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Verificar si atributo existe
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convertir a JSON
     */
    public function toJson(): string
    {
        return json_encode($this->attributes, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener reglas de validación
     */
    public static function getRules(): array
    {
        return static::$rules;
    }

    /**
     * Obtener nombre de tabla
     */
    public static function getTable(): string
    {
        return static::$table;
    }

    /**
     * Obtener clave primaria
     */
    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * Verificar si el modelo tiene cambios
     */
    public function isDirty(): bool
    {
        return $this->attributes !== $this->original;
    }

    /**
     * Obtener cambios realizados
     */
    public function getChanges(): array
    {
        $changes = [];
        foreach ($this->attributes as $key => $value) {
            if (!isset($this->original[$key]) || $this->original[$key] !== $value) {
                $changes[$key] = $value;
            }
        }
        return $changes;
    }

    /**
     * Refrescar modelo desde BD
     */
    public function refresh(): self
    {
        $id = $this->getId();
        if ($id) {
            $record = static::query()
                ->where(static::$primaryKey, $id)
                ->get_one();

            if ($record) {
                $this->attributes = $record;
                $this->original = $record;
            }
        }
        return $this;
    }
}
