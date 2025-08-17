<?php

require_once __DIR__ . '/Piston/PistonExecutor.php';
require_once __DIR__ . '/Piston/PistonRequest.php';
require_once __DIR__ . '/Piston/PistonResponse.php';

use Piston\PistonExecutor;
use Piston\PistonRequest;

class CodeExecutionController {
    private PistonExecutor $executor;

    public function __construct() {
        $this->executor = new PistonExecutor();
    }

    public function handleExecution(array $input): array {
        try {
            // Validation
            if (empty($input['language']) || empty($input['code'])) {
                throw new InvalidArgumentException('Language and code are required');
            }

            $request = new PistonRequest(
                $input['language'],
                $input['code']
            );

            $response = $this->executor->execute($request);

            return [
                'success' => true,
                'result' => $response->toArray()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}