<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[MongoDB\Document(collection: "message")]
class Message
{
    #[MongoDB\Id(strategy: "NONE", type: "string")]
    private string $id;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private string $conversationId;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private string $senderId;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private string $content;

    #[MongoDB\Field(type: "boolean")]
    private bool $read = false;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setConversationId(string $conversationId): self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): self
    {
        $this->senderId = $senderId;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function setRead(bool $read): self
    {
        $this->read = $read;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}