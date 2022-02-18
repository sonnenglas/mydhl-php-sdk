<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\Traits;

trait GetRawResponse
{
    private function getRawResponse(): array
    {
        return $this->response;
    }
}
