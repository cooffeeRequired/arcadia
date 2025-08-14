<?php

namespace Core\Traits;

use Core\Http\Request;
use Exception;

trait Validation
{
    /**
     * Získá instanci Request
     *
     * @return Request
     */
    protected function getRequest_(): Request
    {
        return Request::getInstance();
    }

    /**
     * Validuje data podle pravidel
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return array
     * @throws Exception
     */
    protected function validateData(array $data, array $rules, array $messages = [], array $attributes = []): array
    {
        $errors = $this->validate($data, $rules, $messages, $attributes);

        if (!empty($errors)) {
            throw new Exception('Validation failed: ' . json_encode($errors));
        }

        return $data;
    }

    /**
     * Validuje data a vrací chyby
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return array
     */
    protected function validate(array $data, array $rules, array $messages = [], array $attributes = []): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

                $value = $data[$field] ?? null;
                $fieldName = $attributes[$field] ?? $field;

                if (!$this->validateField($value, $ruleName, $ruleParams, $fieldName)) {
                    $message = $messages[$field . '.' . $ruleName] ?? $this->getDefaultMessage($ruleName, $fieldName, $ruleParams);
                    $errors[$field][] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Validuje jedno pole
     *
     * @param mixed $value
     * @param string $rule
     * @param array $params
     * @param string $fieldName
     * @return bool
     */
    protected function validateField(mixed $value, string $rule, array $params = [], string $fieldName = ''): bool
    {
        return match ($rule) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, $params[0] ?? 0),
            'max' => $this->validateMax($value, $params[0] ?? 0),
            'numeric' => $this->validateNumeric($value),
            'string' => $this->validateString($value),
            'url' => $this->validateUrl($value),
            'alpha' => $this->validateAlpha($value),
            'alpha_num' => $this->validateAlphaNum($value),
            'alpha_dash' => $this->validateAlphaDash($value),
            'confirmed' => $this->validateConfirmed($value, $fieldName),
            'unique' => $this->validateUnique($value, $params[0] ?? '', $params[1] ?? 'id'),
            default => true,
        };
    }

    /**
     * Validuje required pole
     */
    protected function validateRequired(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * Validuje email
     */
    protected function validateEmail(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validuje minimální délku
     */
    protected function validateMin(mixed $value, int $min): bool
    {
        if (is_numeric($value)) {
            return $value >= $min;
        }
        return strlen($value) >= $min;
    }

    /**
     * Validuje maximální délku
     */
    protected function validateMax(mixed $value, int $max): bool
    {
        if (is_numeric($value)) {
            return $value <= $max;
        }
        return strlen($value) <= $max;
    }

    /**
     * Validuje numerickou hodnotu
     */
    protected function validateNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validuje string
     */
    protected function validateString(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * Validuje URL
     */
    protected function validateUrl(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validuje pouze písmena
     */
    protected function validateAlpha(mixed $value): bool
    {
        return preg_match('/^[a-zA-Z]+$/', $value);
    }

    /**
     * Validuje písmena a čísla
     */
    protected function validateAlphaNum(mixed $value): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }

    /**
     * Validuje písmena, čísla, pomlčky a podtržítka
     */
    protected function validateAlphaDash(mixed $value): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value);
    }

    /**
     * Validuje potvrzení pole
     */
    protected function validateConfirmed(mixed $value, string $fieldName): bool
    {
        $request = $this->getRequest_();
        $confirmationField = $fieldName . '_confirmation';
        return $request->input($confirmationField) === $value;
    }

    /**
     * Validuje unikátnost v databázi
     */
    protected function validateUnique(mixed $value, string $table, string $column = 'id'): bool
    {
        // Zde by byla implementace kontroly v databázi
        // Pro jednoduchost vracíme true
        return true;
    }

    /**
     * Získá výchozí chybovou zprávu
     */
    protected function getDefaultMessage(string $rule, string $fieldName, array $params = []): string
    {
        $messages = [
            'required' => "Pole {$fieldName} je povinné.",
            'email' => "Pole {$fieldName} musí být platná emailová adresa.",
            'min' => "Pole {$fieldName} musí mít minimálně {$params[0]} znaků.",
            'max' => "Pole {$fieldName} může mít maximálně {$params[0]} znaků.",
            'numeric' => "Pole {$fieldName} musí být číslo.",
            'string' => "Pole {$fieldName} musí být text.",
            'url' => "Pole {$fieldName} musí být platná URL adresa.",
            'alpha' => "Pole {$fieldName} může obsahovat pouze písmena.",
            'alpha_num' => "Pole {$fieldName} může obsahovat pouze písmena a čísla.",
            'alpha_dash' => "Pole {$fieldName} může obsahovat pouze písmena, čísla, pomlčky a podtržítka.",
            'confirmed' => "Pole {$fieldName} se neshoduje s potvrzením.",
            'unique' => "Pole {$fieldName} již existuje."
        ];

        return $messages[$rule] ?? "Pole {$fieldName} není platné.";
    }

    /**
     * Zkontroluje, zda data projdou validací
     */
    protected function passesValidation(array $data, array $rules, array $messages = [], array $attributes = []): bool
    {
        $errors = $this->validate($data, $rules, $messages, $attributes);
        return empty($errors);
    }

    /**
     * Zkontroluje, zda data neprojdou validací
     */
    protected function failsValidation(array $data, array $rules, array $messages = [], array $attributes = []): bool
    {
        return !$this->passesValidation($data, $rules, $messages, $attributes);
    }

    /**
     * Získá chyby validace
     */
    protected function getValidationErrors(array $data, array $rules, array $messages = [], array $attributes = []): array
    {
        return $this->validate($data, $rules, $messages, $attributes);
    }

    /**
     * Validuje aktuální request data
     */
    protected function validateRequest(array $rules, array $messages = [], array $attributes = []): array
    {
        $request = $this->getRequest_();
        $data = $request->isPost() ? $request->allInput() : $request->allQuery();

        return $this->validate($data, $rules, $messages, $attributes);
    }

    /**
     * Získá chybu pro konkrétní pole z request
     */
    protected function getRequestValidationError(string $field): ?string
    {
        $request = $this->getRequest_();
        $session = $request->getSession();

        if ($session && $session->has('validation_errors')) {
            $errors = $session->get('validation_errors', []);
            return $errors[$field][0] ?? null;
        }

        return null;
    }

    /**
     * Zkontroluje, zda pole má chybu validace v request
     */
    protected function hasRequestValidationError(string $field): bool
    {
        return $this->getRequestValidationError($field) !== null;
    }

    /**
     * Uloží chyby validace do session
     */
    protected function flashValidationErrors(array $errors): void
    {
        $request = $this->getRequest_();
        $session = $request->getSession();

        if ($session) {
            $session->set('validation_errors', $errors);
        }
    }
}
