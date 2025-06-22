<?php
namespace App\Config;

class Config
{
    public static function getPistonApiConfig(): array
    {
        return [
            'api_url' => 'https://emkc.org/api/v2/piston/execute',
            'supported_languages' => [
                'javascript' => '18.15.0',
                'python' => '3.10.0',
                'java' => '15.0.2',
                'cpp' => '11.3.0',
                'bash' => '5.2.15'
            ],
            'timeout' => 10
        ];
    }
}
?>