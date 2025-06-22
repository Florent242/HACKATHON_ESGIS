<?php

namespace Piston;

class PistonExecutor {
    private const API_URL = 'https://emkc.org/api/v2/piston/execute';
    private const TIMEOUT = 5; // seconds

    public function execute(PistonRequest $request): PistonResponse {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($request->toArray()),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $error) {
            throw new \RuntimeException("Piston API error: HTTP $httpCode - $error");
        }

        $data = json_decode($response, true);
        return PistonResponse::fromApiResponse($data);
    }
}