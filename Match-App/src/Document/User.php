<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[MongoDB\Document(collection: "user")]
class User implements PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id(strategy: "NONE", type: "string")]
    private string $id;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    #[Assert\Email]
    private $email;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $password;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $username;

    #[MongoDB\Field(type: "date")]
    #[Assert\NotBlank]
    private ?\DateTime $birthdate = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $gender;

    #[MongoDB\Field(type: "string", nullable: true)]
    private ?string $bio = null;

    #[MongoDB\Field(type: "string", nullable: true)]
    private ?string $picture = null; 

    #[MongoDB\Field(type: "string", nullable: true)]
    private ?string $idCard = null;

    #[MongoDB\Field(type: "boolean")]
    private bool $idCardVerified = false;
    

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    #[MongoDB\ReferenceOne(targetDocument: Inou::class, cascade: ["remove"])]
    private $inou;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTime();
    }

    public function getId(): string 
    { 
        return $this->id; 
    }
    

    public function getUuid(): Uuid
    {
        return Uuid::fromRfc4122($this->id);
    }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getUsername(): ?string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function getBirthdate(): ?\DateTime { return $this->birthdate; }
    public function setBirthdate(?\DateTime $birthdate): self { $this->birthdate = $birthdate; return $this; }

    public function getGender(): ?string { return $this->gender; }
    public function setGender(string $gender): self { $this->gender = $gender; return $this; }

    public function getBio(): ?string { return $this->bio; }
    public function setBio(?string $bio): self { $this->bio = $bio; return $this; }

    public function getPicture(): ?string { return $this->picture; }
    public function setPicture(?string $picture): self { $this->picture = $picture; return $this; }

    public function getIdCard(): ?string { return $this->idCard; }
    public function setIdCard(?string $idCard): self { $this->idCard = $idCard; return $this; }

    public function isIdCardVerified(): bool { return $this->idCardVerified; }
    public function setIdCardVerified(bool $idCardVerified): self { $this->idCardVerified = $idCardVerified; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getInou(): ?Inou { return $this->inou; }
    public function setInou(Inou $inou): self { $this->inou = $inou; return $this; }

    public function getRoles(): array { return []; }
    public function getSalt(): ?string { return null; }
    public function eraseCredentials(): void {}
}