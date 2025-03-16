<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

#[MongoDB\Document(collection: "chat")]
class Chat
{
    #[MongoDB\Id(strategy: "NONE", type: "string")]
    private string $id;

    #[MongoDB\ReferenceOne(targetDocument: User::class)]
    #[Assert\NotBlank]
    private User $user1;

    #[MongoDB\ReferenceOne(targetDocument: User::class)]
    #[Assert\NotBlank]
    private User $user2;

    #[MongoDB\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    #[MongoDB\Field(type: "collection")]
    private array $messages = [];

    public function __construct(User $user1, User $user2)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->user1 = $user1;
        $this->user2 = $user2;
        $this->createdAt = new \DateTime();
    }

    public function getId(): string { return $this->id; }

    public function getUser1(): User { return $this->user1; }
    public function setUser1(User $user1): self { $this->user1 = $user1; return $this; }

    public function getUser2(): User { return $this->user2; }
    public function setUser2(User $user2): self { $this->user2 = $user2; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }

    public function getMessages(): array { return $this->messages; }
    public function addMessage(string $senderId, string $content): self 
    {
        $this->messages[] = [
            'sender' => $senderId,
            'content' => $content,
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ];
        return $this;
    }
}
