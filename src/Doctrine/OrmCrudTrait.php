<?php

namespace AcMarche\Presse\Doctrine;

use Doctrine\ORM\EntityManager;

trait OrmCrudTrait
{
    /**
     * @var EntityManager
     */
    protected $_em;

    public function insert(object $object): void
    {
        $this->persist($object);
        $this->flush();
    }

    public function persist(object $object): void
    {
        $this->_em->persist($object);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function remove(object $object): void
    {
        $this->_em->remove($object);
    }

    public function getOriginalEntityData(object $object)
    {
        return $this->_em->getUnitOfWork()->getOriginalEntityData($object);
    }
}
