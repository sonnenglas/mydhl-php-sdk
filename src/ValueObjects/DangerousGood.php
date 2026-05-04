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

    /**
     * @return array<string, string|int>
     */
    public function getAsArray(): array
    {
        $result = ['contentId' => $this->contentId];

        if ($this->customDescription !== '') {
            $result['customDescription'] = $this->customDescription;
        }

        if ($this->unCode !== '') {
            $result['unCode'] = $this->unCode;
        }

        if ($this->dryIceTotalNetWeight !== 0) {
            $result['dryIceTotalNetWeight'] = $this->dryIceTotalNetWeight;
        }

        return $result;
    }
}
