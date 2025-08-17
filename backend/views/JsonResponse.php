<?php
namespace App\Views;

class JsonResponse
{
    public static function send(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::send([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }
}
?>