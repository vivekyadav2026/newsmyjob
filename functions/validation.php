<?php
/**
 * Input Validation Functions
 */

declare(strict_types=1);

/**
 * Validate required field
 */
function validateRequired(mixed $value, string $fieldName): ?string
{
    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
        return "$fieldName is required.";
    }
    return null;
}

/**
 * Validate email
 */
function validateEmail(string $email): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email address.';
    }
    return null;
}

/**
 * Validate password strength
 */
function validatePassword(string $password): ?string
{
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    return null;
}

/**
 * Validate URL
 */
function validateUrl(string $url): ?string
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return 'Invalid URL.';
    }
    return null;
}

/**
 * Validate integer
 */
function validateInt(mixed $value, string $fieldName): ?string
{
    if (!is_numeric($value) || (int) $value != $value) {
        return "$fieldName must be a valid integer.";
    }
    return null;
}

/**
 * Validate slug format
 */
function validateSlug(string $slug): ?string
{
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        return 'Invalid slug format. Use lowercase letters, numbers, and hyphens only.';
    }
    return null;
}

/**
 * Collect validation errors
 */
function validate(array $rules, array $data): array
{
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;

        foreach ($fieldRules as $rule) {
            $error = match ($rule) {
                'required' => validateRequired($value, ucfirst(str_replace('_', ' ', $field))),
                'email'    => is_string($value) ? validateEmail($value) : 'Invalid email.',
                'password' => is_string($value) ? validatePassword($value) : 'Invalid password.',
                'url'      => is_string($value) ? validateUrl($value) : 'Invalid URL.',
                'slug'     => is_string($value) ? validateSlug($value) : 'Invalid slug.',
                default    => null,
            };

            if ($error) {
                $errors[$field] = $error;
                break;
            }
        }
    }

    return $errors;
}
