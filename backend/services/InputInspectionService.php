<?php

namespace Auth\Service;

use Exception;

class InputInspectionService
{
    /**
     * Inspect and sanitize incoming input.
     * - Validates Content-Type vs body usage
     * - Enforces max body size
     * - Detects common XSS/SQLi patterns (heuristic)
     * - Sanitizes strings recursively (trim, strip tags for dangerous content)
     *
     * @param array|null $input Already decoded input (may be null)
     * @param array $context ['method' => string, 'headers' => array, 'raw' => string, 'max_body_bytes' => int]
     * @return array Sanitized input (array)
     * @throws Exception if the request is clearly malicious or violates constraints
     */
    public static function inspectInput($input, array $context = []): array
    {
        $method = $context['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $headers = $context['headers'] ?? [];
        $raw = $context['raw'] ?? '';
        $max = (int)($context['max_body_bytes'] ?? 1024 * 1024); // 1MB default

        // Enforce max body size
        $contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : strlen($raw);
        if ($contentLength > $max) {
            throw new Exception('Payload trop volumineux', 413);
        }

        // If body is present for write methods, ensure content type is acceptable
        $hasBody = strlen($raw) > 0;
        if ($hasBody && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $contentType = self::getHeader($headers, 'Content-Type');
            if ($contentType && !preg_match('#^(application/json|multipart/form-data|application/x-www-form-urlencoded)#i', $contentType)) {
                throw new Exception('Type de contenu non supporté', 415);
            }
        }

        // If JSON-like body but $input is null, attempt safe decode with error insight (do not throw here)
        if ($hasBody && $input === null) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $input = $decoded;
            }
        }

        // Normalize to array
        if (!is_array($input)) {
            $input = [];
        }

        // Detect obvious threats in raw and fields
        $issues = self::detectThreats($input, $raw);
        if (!empty($issues['critical'])) {
            // Logically, the caller can catch and convert to JSON response
            throw new Exception('Entrées dangereuses détectées', 400);
        }

        // Sanitize
        $sanitized = self::sanitizeArray($input);

        // Optionally attach metadata (not returned, but could be useful later)
        return $sanitized;
    }

    private static function getHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return is_array($v) ? implode(',', $v) : $v;
            }
        }
        // Fallback to $_SERVER for environments without getallheaders
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if (isset($_SERVER[$serverKey])) {
            return $_SERVER[$serverKey];
        }
        return null;
    }

    /**
     * Recursively sanitize arrays by trimming strings and removing obvious script tags.
     */
    public static function sanitizeArray($value, int $depth = 0)
    {
        if ($depth > 50) { // avoid excessive nesting
            return null;
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                // Normalize keys to strings without control chars
                $key = is_string($k) ? preg_replace('/[\x00-\x1F\x7F]/', '', $k) : $k;
                $out[$key] = self::sanitizeArray($v, $depth + 1);
            }
            return $out;
        }
        if (is_string($value)) {
            $v = trim($value);
            // Remove NULL bytes and control characters
            $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
            // Basic script tag and event handler stripping
            $v = preg_replace('#<\s*script[^>]*>.*?<\s*/\s*script\s*>#is', '', $v);
            $v = preg_replace('/on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $v);
            return $v;
        }
        // Leave numbers, bools, null as-is
        return $value;
    }

    /**
     * Heuristic detection of potentially dangerous content.
     * Returns ['critical' => [...], 'warnings' => [...]]
     */
    public static function detectThreats(array $input, string $raw): array
    {
        $critical = [];
        $warnings = [];

        // Raw body quick checks
        if (preg_match('#<\s*script\b#i', $raw)) {
            $warnings[] = 'script_tag_in_body';
        }
        if (preg_match('/(UNION\s+SELECT|DROP\s+TABLE|INSERT\s+INTO|UPDATE\s+.*SET|--|;\s*--)/i', $raw)) {
            $warnings[] = 'sql_keywords_in_body';
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($input));
        foreach ($iterator as $key => $value) {
            if (!is_string($value)) continue;
            if (strlen($value) > 100000) { // 100KB field is suspicious
                $warnings[] = 'field_too_large:' . $key;
            }
            if (preg_match('#<\s*script\b#i', $value)) {
                $critical[] = 'script_tag_in_field:' . $key;
            }
            if (preg_match('/(UNION\s+SELECT|DROP\s+TABLE|INSERT\s+INTO|UPDATE\s+.*SET|--|;\s*--)/i', $value)) {
                $warnings[] = 'sql_keywords_in_field:' . $key;
            }
            if (preg_match('/javascript\s*:\s*/i', $value)) {
                $critical[] = 'javascript_uri:' . $key;
            }
        }

        return [
            'critical' => array_values(array_unique($critical)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }
}
