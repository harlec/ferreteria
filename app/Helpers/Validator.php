<?php
namespace App\Helpers;

class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Validar datos contra reglas
     */
    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $ruleString) {
            $rulesArray = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return $this->errors;
    }

    /**
     * Aplicar una regla de validación
     */
    private function applyRule(string $field, string $rule): void
    {
        $params = [];

        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $value = $this->data[$field] ?? null;

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'El campo es requerido');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'El email no es válido');
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'El campo debe ser numérico');
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'El campo debe ser un número entero');
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$params[0]) {
                    $this->addError($field, "El campo debe tener al menos {$params[0]} caracteres");
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$params[0]) {
                    $this->addError($field, "El campo no debe exceder {$params[0]} caracteres");
                }
                break;

            case 'minValue':
                if (!empty($value) && (float)$value < (float)$params[0]) {
                    $this->addError($field, "El valor mínimo es {$params[0]}");
                }
                break;

            case 'maxValue':
                if (!empty($value) && (float)$value > (float)$params[0]) {
                    $this->addError($field, "El valor máximo es {$params[0]}");
                }
                break;

            case 'between':
                if (!empty($value)) {
                    $len = strlen($value);
                    if ($len < (int)$params[0] || $len > (int)$params[1]) {
                        $this->addError($field, "El campo debe tener entre {$params[0]} y {$params[1]} caracteres");
                    }
                }
                break;

            case 'regex':
                if (!empty($value) && !preg_match($params[0], $value)) {
                    $this->addError($field, 'El formato del campo no es válido');
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'La fecha no es válida');
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    $this->addError($field, 'El valor seleccionado no es válido');
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'La confirmación no coincide');
                }
                break;

            case 'unique':
                // unique:tabla,columna
                if (!empty($value) && count($params) >= 2) {
                    $exists = \Sdba::table($params[0])
                        ->where($params[1], $value)
                        ->get_one();
                    if ($exists) {
                        $this->addError($field, 'El valor ya existe');
                    }
                }
                break;

            case 'exists':
                // exists:tabla,columna
                if (!empty($value) && count($params) >= 2) {
                    $exists = \Sdba::table($params[0])
                        ->where($params[1], $value)
                        ->get_one();
                    if (!$exists) {
                        $this->addError($field, 'El valor no existe');
                    }
                }
                break;

            case 'dni':
                if (!empty($value) && !preg_match('/^\d{8}$/', $value)) {
                    $this->addError($field, 'El DNI debe tener 8 dígitos');
                }
                break;

            case 'ruc':
                if (!empty($value) && !preg_match('/^\d{11}$/', $value)) {
                    $this->addError($field, 'El RUC debe tener 11 dígitos');
                }
                break;

            case 'phone':
                if (!empty($value) && !preg_match('/^\d{9}$/', $value)) {
                    $this->addError($field, 'El teléfono debe tener 9 dígitos');
                }
                break;
        }
    }

    /**
     * Agregar error
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Verificar si hay errores
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener primer error de un campo
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitizar string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar para SQL (escapar comillas)
     */
    public static function escape(string $value): string
    {
        return addslashes(trim($value));
    }
}
