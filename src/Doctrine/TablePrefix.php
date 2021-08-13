<?php

namespace AcMarche\Presse\Doctrine;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TablePrefix
{
    protected string $prefix = '';
    protected string $namespace = '';

    public function __construct(string $prefix, string $namespace)
    {
        $this->prefix = $prefix;
        $this->namespace = $namespace;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (!preg_match('#'.$this->namespace.'#', $classMetadata->namespace)) {
            return;
        }

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName(
            ) === $classMetadata->rootEntityName) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix.$classMetadata->getTableName(),
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix.$mappedTableName;
            }
        }
    }

}

