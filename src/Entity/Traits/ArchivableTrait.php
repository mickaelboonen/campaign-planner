<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ArchivableTrait
{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function archive(): void
    {
        if ($this->archivedAt === null) {
            $this->archivedAt = new \DateTimeImmutable();
        }
    }

    public function restore(): void
    {
        $this->archivedAt = null;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }
}