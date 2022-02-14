<?php

declare(strict_types=1);

namespace Sonnenglas\MyDHL\ValueObjects;

use Sonnenglas\MyDHL\Exceptions\InvalidAddressException;

class Contact
{
    public function __construct(
        protected string $phone,
        protected string $companyName,
        protected string $fullName,
        protected string $email = '',
        protected string $mobilePhone = '',
    ) {
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getMobilePhone(): string
    {
        return $this->mobilePhone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAsArray(): array
    {
        $result = [
            'phone' => $this->phone,
            'companyName' => $this->companyName,
            'fullName' => $this->fullName,
        ];

        if ($this->email !== '') {
            $result['email'] = $this->email;
        }

        if ($this->mobilePhone !== '') {
            $result['mobilePhone'] = $this->mobilePhone;
        }

        return $result;
    }
}
