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

    #[MongoDB\ReferenceOne(targetDocument: Chat::class)]
    #[Assert\NotBlank]
    private Chat $chat;

    #[MongoDB\ReferenceOne(targetDocument: User::class)]
    #[Assert\NotBlank]
    private User $sender;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank]
    private string $content;

    #[MongoDB\Field(type: "string")]
    private ?string $type = 'text';

    #[MongoDB\Field(type: "string")]
    private ?string $audioUrl = null;

    #[MongoDB\Field(type: "bool")]
    private bool $read = false;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $timestamp = null;

    public function __construct(Chat $chat, User $sender, string $content, string $type = 'text', ?string $audioUrl = null)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->chat = $chat;
        $this->sender = $sender;
        $this->content = $content;
        $this->type = $type;
        $this->audioUrl = $audioUrl;
        $this->timestamp = new \DateTime();
    }

    public function getId(): string { return $this->id; }

    public function getChat(): Chat { return $this->chat; }
    public function setChat(Chat $chat): self { $this->chat = $chat; return $this; }

    public function getSender(): User { return $this->sender; }
    public function setSender(User $sender): self { $this->sender = $sender; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getAudioUrl(): ?string { return $this->audioUrl; }
    public function setAudioUrl(?string $audioUrl): self { $this->audioUrl = $audioUrl; return $this; }

    public function isRead(): bool { return $this->read; }
    public function setRead(bool $read): self { $this->read = $read; return $this; }

    public function getTimestamp(): ?\DateTime { return $this->timestamp; }
}