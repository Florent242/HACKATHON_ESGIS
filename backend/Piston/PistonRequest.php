<?php

namespace Piston;

class PistonRequest {
    private string $language;
    private string $code;
    private string $version;

    public function __construct(string $language, string $code, string $version = '*') {
        $this->language = $language;
        $this->code = $code;
        $this->version = $version;
    }

    public function toArray(): array {
        return [
            'language' => $this->language,
            'version' => $this->version,
            'files' => [['content' => $this->code]]
        ];
    }

    // Getters
    public function getLanguage(): string { return $this->language; }
    public function getCode(): string { return $this->code; }
}