<?php

namespace codechap\ai\Helpers;

class JsonExtractor
{
    /**
     * Attempts to extract and decode the outermost JSON structure found in the input string.
     *
     * @param string $string
     * @return array|null The decoded JSON structure, or null if extraction/decoding fails.
     */
    public static function extract(string $string) : ?array
    {
        $string = trim($string);
        $result = self::findOutermostJson($string);
        if ($result !== null) {
            try {
                return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Finds the outermost JSON structure inside the given string.
     *
     * @param string $str
     * @return string|null The JSON substring if successfully found, or null otherwise.
     */
    private static function findOutermostJson(string $str): ?string
    {
        $stack = [];
        $start = -1;
        $end = -1;
        $inString = false;
        $escapeNext = false;
        $length = strlen($str);

        for ($i = 0; $i < $length; $i++) {
            $char = $str[$i];

            // Handle string boundaries and escapes
            if ($inString) {
                if ($escapeNext) {
                    $escapeNext = false;
                } elseif ($char === '\\') {
                    $escapeNext = true;
                } elseif ($char === '"') {
                    $inString = false;
                }
                continue;
            } elseif ($char === '"') {
                $inString = true;
                continue;
            }

            // Process working with brackets outside of strings
            if ($char === '{' || $char === '[') {
                if (empty($stack)) {
                    $start = $i;
                }
                $stack[] = $char;
            } elseif ($char === '}' || $char === ']') {
                if (empty($stack)) {
                    continue; // Skip unmatched closing
                }
                $last = array_pop($stack);
                if (($char === '}' && $last !== '{') || ($char === ']' && $last !== '[')) {
                    return null; // Mismatched brackets encountered
                }
                if (empty($stack)) {
                    $end = $i;
                    break;
                }
            }
        }

        return ($start !== -1 && $end !== -1 && empty($stack))
            ? substr($str, $start, $end - $start + 1)
            : null;
    }
}
