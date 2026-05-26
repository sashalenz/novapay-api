<?php

namespace Sashalenz\NovapayApi\Types;

use Spatie\LaravelData\Data;

class ApiError extends Data
{
    public function __construct(
        public string $status,   // e.g. "logic_error", "system_error"
        public string $title,    // Human-readable error description
        public ?string $stacktrace = null,
    ) {}
}
