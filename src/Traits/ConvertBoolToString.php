<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Traits;

trait ConvertBoolToString
{

    private function convertBoolToString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
