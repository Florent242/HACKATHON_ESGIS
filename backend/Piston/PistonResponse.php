<?php

namespace Piston;

class PistonResponse {
    private ?string $output;
    private ?string $error;
    private ?int $exitCode;

    public function __construct(?string $output, ?string $error, ?int $exitCode) {
        $this->output = $output;
        $this->error = $error;
        $this->exitCode = $exitCode;
    }

    public static function fromApiResponse(array $apiResponse): self {
        $run = $apiResponse['run'] ?? [];
        return new self(
            $run['stdout'] ?? null,
            $run['stderr'] ?? null,
            $run['code'] ?? null
        );
    }

    // Getters
    public function getOutput(): ?string { return $this->output; }
    public function getError(): ?string { return $this->error; }
    public function getExitCode(): ?int { return $this->exitCode; }

    public function toArray(): array {
        return [
            'output' => $this->output,
            'error' => $this->error,
            'exitCode' => $this->exitCode
        ];
    }
}