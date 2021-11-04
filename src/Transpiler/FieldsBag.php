<?php

namespace App\Transpiler;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class FieldsBag
{

    protected array $fieldsList;

    public function __construct(
        protected EntityManagerInterface $em
    ) {
        $fieldsList = $em->getClassMetadata(Project::class)->getColumnNames();
        $this->fieldsList = array_flip($fieldsList);
    }

    public function fieldExists(string $fieldName): bool
    {
        return isset($this->fieldsList[$fieldName]);
    }

    public function getPossibleNames(): array
    {
        return array_keys($this->fieldsList);
    }

}