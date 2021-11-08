<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[Entity(repositoryClass: ProjectRepository::class)]
#[HasLifecycleCallbacks]
class Project
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    public ?int $id;

    #[Column(type: 'string', length: 255)]
    public ?string $name;

    #[Column(type: 'text', nullable: true)]
    public ?string $description;

    #[Column(type: 'integer', nullable: true)]
    public ?int $sort;

    #[Column(type: 'string', length: 255)]
    public ?string $source;

    #[Column(type: 'string', length: 255)]
    public ?string $epic;

    #[Column(type: 'string', length: 255)]
    public ?string $assignee;

    #[Column(type: 'string', length: 255)]
    public ?string $owner;

    #[Column(type: 'string', length: 255)]
    public ?string $responsible;

    #[Column(type: 'datetime_immutable')]
    public ?DateTimeImmutable $created_at;

    #[Column(type: 'datetime_immutable')]
    public ?DateTimeImmutable $updated_at;

    /**
     * Gets triggered only on insert
     */
    #[PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new DateTimeImmutable('now');
        $this->updated_at = new DateTimeImmutable('now');
    }

    /**
     * Gets triggered every time on update
     */
    #[PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new DateTimeImmutable('now');
    }

}
