<?php

namespace Codechap\Aiwrapper\Interfaces;

interface AIServiceInterface
{
    public function query(string $prompt, ?string $systemMessage = null): string;
} 