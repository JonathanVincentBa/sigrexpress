<?php

namespace App\Services;

use Generator;
use RuntimeException;

class LegacySqlDumpReader
{
    /**
     * @return Generator<array<string, string|null>>
     */
    public function rows(string $path, string $expectedTable): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("No se pudo abrir el archivo de importación {$expectedTable}.");
        }

        $header = '';
        $columns = [];
        $insideInsert = false;
        $state = $this->emptyParserState();

        try {
            while (($chunk = fgets($handle, 65536)) !== false) {
                if (! $insideInsert) {
                    if ($header === '') {
                        if (preg_match('/^\s*INSERT INTO\b/i', $chunk) !== 1) {
                            continue;
                        }

                        $header = $chunk;
                    } else {
                        $header .= $chunk;
                    }

                    if (preg_match('/\bVALUES\b/i', $header) !== 1) {
                        if (strlen($header) > 65536) {
                            throw new RuntimeException("La cabecera INSERT de {$expectedTable} es demasiado extensa.");
                        }

                        continue;
                    }

                    if (preg_match('/^\s*INSERT INTO\s+`([^`]+)`\s*\((.*?)\)\s*VALUES\b(.*)$/is', $header, $matches) !== 1) {
                        throw new RuntimeException("No se pudo interpretar una sentencia INSERT de {$expectedTable}.");
                    }

                    $header = '';

                    if ($matches[1] !== $expectedTable) {
                        continue;
                    }

                    preg_match_all('/`([^`]+)`/', $matches[2], $columnMatches);
                    $columns = $columnMatches[1];

                    if ($columns === []) {
                        throw new RuntimeException("La sentencia INSERT de {$expectedTable} no declara columnas.");
                    }

                    $insideInsert = true;
                    $chunk = $matches[3];
                    $state = $this->emptyParserState();
                }

                foreach ($this->parseTuples($chunk, $state) as $values) {
                    if (count($values) !== count($columns)) {
                        throw new RuntimeException("Una fila del archivo {$expectedTable} no coincide con sus columnas.");
                    }

                    $row = [];

                    foreach ($columns as $index => $column) {
                        $row[$column] = $values[$index];
                    }

                    yield $row;
                }

                if ($state['statement_ended']) {
                    $insideInsert = false;
                    $state = $this->emptyParserState();
                }
            }
        } finally {
            fclose($handle);
        }

        if ($insideInsert || $header !== '') {
            throw new RuntimeException("El archivo {$expectedTable} termina con una sentencia SQL incompleta.");
        }
    }

    /**
     * @param array{
     *     row: list<string|null>,
     *     value: string,
     *     inside_row: bool,
     *     inside_string: bool,
     *     escape_next: bool,
     *     pending_quote: bool,
     *     quoted_value: bool,
     *     statement_ended: bool
     * } $state
     * @return Generator<list<string|null>>
     */
    private function parseTuples(string $chunk, array &$state): Generator
    {
        $length = strlen($chunk);

        for ($index = 0; $index < $length; $index++) {
            $character = $chunk[$index];

            if ($state['pending_quote']) {
                if ($character === "'") {
                    $state['value'] .= "'";
                    $state['pending_quote'] = false;

                    continue;
                }

                $state['inside_string'] = false;
                $state['pending_quote'] = false;
            }

            if ($state['escape_next']) {
                $state['value'] .= match ($character) {
                    '0' => "\0",
                    'b' => "\x08",
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    'Z' => "\x1a",
                    default => $character,
                };
                $state['escape_next'] = false;

                continue;
            }

            if ($state['inside_string'] && $character === '\\') {
                $state['escape_next'] = true;

                continue;
            }

            if ($character === "'") {
                if ($state['inside_string'] && ($chunk[$index + 1] ?? null) === "'") {
                    $state['value'] .= "'";
                    $index++;

                    continue;
                }

                if ($state['inside_string'] && $index === $length - 1) {
                    $state['pending_quote'] = true;

                    continue;
                }

                $state['inside_string'] = ! $state['inside_string'];
                $state['quoted_value'] = true;

                continue;
            }

            if ($state['inside_string']) {
                $state['value'] .= $character;

                continue;
            }

            if (! $state['inside_row']) {
                if ($character === '(') {
                    $state['inside_row'] = true;
                    $state['row'] = [];
                    $state['value'] = '';
                    $state['quoted_value'] = false;
                } elseif ($character === ';') {
                    $state['statement_ended'] = true;
                }

                continue;
            }

            if ($character === ',') {
                $state['row'][] = $this->parseValue($state['value'], $state['quoted_value']);
                $state['value'] = '';
                $state['quoted_value'] = false;

                continue;
            }

            if ($character === ')') {
                $state['row'][] = $this->parseValue($state['value'], $state['quoted_value']);
                yield $state['row'];
                $state['row'] = [];
                $state['value'] = '';
                $state['inside_row'] = false;
                $state['quoted_value'] = false;

                continue;
            }

            $state['value'] .= $character;
        }

        if ($state['statement_ended'] && ($state['inside_string'] || $state['inside_row'] || $state['escape_next'] || $state['pending_quote'])) {
            throw new RuntimeException('El archivo SQL contiene una fila incompleta o una cadena sin cerrar.');
        }
    }

    private function parseValue(string $value, bool $quoted): ?string
    {
        $value = trim($value);

        if (! $quoted && strtoupper($value) === 'NULL') {
            return null;
        }

        return $value;
    }

    /**
     * @return array{
     *     row: list<string|null>,
     *     value: string,
     *     inside_row: bool,
     *     inside_string: bool,
     *     escape_next: bool,
     *     pending_quote: bool,
     *     quoted_value: bool,
     *     statement_ended: bool
     * }
     */
    private function emptyParserState(): array
    {
        return [
            'row' => [],
            'value' => '',
            'inside_row' => false,
            'inside_string' => false,
            'escape_next' => false,
            'pending_quote' => false,
            'quoted_value' => false,
            'statement_ended' => false,
        ];
    }
}
