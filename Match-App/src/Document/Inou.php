<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;

#[MongoDB\Document(collection: "inou")]
class Inou
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\ReferenceOne(targetDocument: User::class, inversedBy: "inou")]
    #[Assert\NotBlank]
    private $userId;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q1;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q2;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q3;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q4;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q5;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q6;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q7;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q8;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q9;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q10;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q11;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q12;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q13;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q14;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q15;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q16;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q17;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q18;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q19;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private $q20;

    #[MongoDB\Field(type: "date")]
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?string { return $this->id; }
    public function getUserId(): ?User { return $this->userId; }
    public function setUserId(User $userId): self { $this->userId = $userId; return $this; }

    public function getQ1(): ?string { return $this->q1; }
    public function setQ1(string $q1): self { $this->q1 = $q1; return $this; }

    public function getQ2(): ?string { return $this->q2; }
    public function setQ2(string $q2): self { $this->q2 = $q2; return $this; }

    public function getQ3(): ?string { return $this->q3; }
    public function setQ3(string $q3): self { $this->q3 = $q3; return $this; }

    public function getQ4(): ?string { return $this->q4; }
    public function setQ4(string $q4): self { $this->q4 = $q4; return $this; }

    public function getQ5(): ?string { return $this->q5; }
    public function setQ5(string $q5): self { $this->q5 = $q5; return $this; }

    public function getQ6(): ?string { return $this->q6; }
    public function setQ6(string $q6): self { $this->q6 = $q6; return $this; }

    public function getQ7(): ?string { return $this->q7; }
    public function setQ7(string $q7): self { $this->q7 = $q7; return $this; }

    public function getQ8(): ?string { return $this->q8; }
    public function setQ8(string $q8): self { $this->q8 = $q8; return $this; }

    public function getQ9(): ?string { return $this->q9; }
    public function setQ9(string $q9): self { $this->q9 = $q9; return $this; }

    public function getQ10(): ?string { return $this->q10; }
    public function setQ10(string $q10): self { $this->q10 = $q10; return $this; }

    public function getQ11(): ?string { return $this->q11; }
    public function setQ11(string $q11): self { $this->q11 = $q11; return $this; }

    public function getQ12(): ?string { return $this->q12; }
    public function setQ12(string $q12): self { $this->q12 = $q12; return $this; }

    public function getQ13(): ?string { return $this->q13; }
    public function setQ13(string $q13): self { $this->q13 = $q13; return $this; }

    public function getQ14(): ?string { return $this->q14; }
    public function setQ14(string $q14): self { $this->q14 = $q14; return $this; }

    public function getQ15(): ?string { return $this->q15; }
    public function setQ15(string $q15): self { $this->q15 = $q15; return $this; }

    public function getQ16(): ?string { return $this->q16; }
    public function setQ16(string $q16): self { $this->q16 = $q16; return $this; }

    public function getQ17(): ?string { return $this->q17; }
    public function setQ17(string $q17): self { $this->q17 = $q17; return $this; }

    public function getQ18(): ?string { return $this->q18; }
    public function setQ18(string $q18): self { $this->q18 = $q18; return $this; }

    public function getQ19(): ?string { return $this->q19; }
    public function setQ19(string $q19): self { $this->q19 = $q19; return $this; }

    public function getQ20(): ?string { return $this->q20; }
    public function setQ20(string $q20): self { $this->q20 = $q20; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): self { $this->createdAt = $createdAt; return $this; }
}
