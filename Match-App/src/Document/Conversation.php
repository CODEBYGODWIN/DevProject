<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[MongoDB\Document(collection: "conversation")]
class Conversation
{
    #[MongoDB\Id(strategy: "NONE", type: "string")]
    private string $id;

    #[MongoDB\Field(type: "collection")]
    private array $participants = [];

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $lastMessageAt = null;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTime();
        $this->lastMessageAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParticipants(): array
    {
        return $this->participants;
    }

    public function addParticipant(string $userId): self
    {
        if (!in_array($userId, $this->participants)) {
            $this->participants[] = $userId;
        }
        return $this;
    }

    public function getLastMessageAt(): ?\DateTime
    {
        return $this->lastMessageAt;
    }

    public function setLastMessageAt(\DateTime $lastMessageAt): self
    {
        $this->lastMessageAt = $lastMessageAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}