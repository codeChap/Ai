<?php

namespace Codechap\Aiwrapper\Interfaces;

use Codechap\Aiwrapper\Curl;

interface AIServiceInterface
{
    public function query(string|array $prompts): self;
}
