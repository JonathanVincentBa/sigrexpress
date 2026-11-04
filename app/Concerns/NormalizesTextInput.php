<?php

namespace App\Concerns;

trait NormalizesTextInput
{
    /**
     * @param  array<string|int, mixed>  $values
     * @return array<string|int, mixed>
     */
    protected function normalizeTextValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->normalizeTextValues($value);

                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            $field = (string) $key;

            if (str_contains($field, 'password')) {
                continue;
            }

            $value = trim($value);

            if ($field === 'email' || str_ends_with($field, '_email')) {
                $values[$key] = mb_strtolower($value);

                continue;
            }

            if ($field === 'guard_name') {
                $values[$key] = $value;

                continue;
            }

            if (str_starts_with($field, 'id_') || str_ends_with($field, '_id') || $field === 'id') {
                $values[$key] = $value;

                continue;
            }

            $values[$key] = mb_strtoupper($value);
        }

        return $values;
    }
}
