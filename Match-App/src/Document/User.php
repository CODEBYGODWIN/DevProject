<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class User
{
    #[ODM\Id]
    private $id;

    #[ODM\Field(type: "string")]
    private $firstName;

    #[ODM\Field(type: "string")]
    private $lastName;

    #[ODM\Field(type: "string")]
    private $email;

    public function getId(): ?string { return $this->id; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): void { $this->firstName = $firstName; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
}