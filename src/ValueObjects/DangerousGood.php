<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

class DangerousGood
{
    public function __construct(
        private string $contentId,
        private string $customDescription = '',
        private string $unCode = '',
        private int $dryIceTotalNetWeight = 0,
    ) {
    }


    public function getAsArray(): array
    {
        $result = [];

        $result['contentId'] = $this->contentId;

        if ($this->customDescription) {
            $result['customDescription'] = $this->customDescription;
        }

        if ($this->unCode) {
            $result['unCode'] = $this->unCode;
        }

        if ($this->dryIceTotalNetWeight) {
            $result['dryIceTotalNetWeight'] = $this->dryIceTotalNetWeight;
        }

        return $result;
    }
}
