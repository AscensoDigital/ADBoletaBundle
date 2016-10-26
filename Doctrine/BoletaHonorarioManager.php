<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AscensoDigital\BoletaBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;


class BoletaHonorarioManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param ObjectManager           $om
     * @param string                  $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Crea una boleta de honorarios
     * @return mixed
     */
    public function createBoletaHonorario()
    {
        $class = $this->getClass();
        $bh = new $class();

        return $bh;
    }

    public function deleteBoletaHonorario($boletaHonorario)
    {
        $this->objectManager->remove($boletaHonorario);
        $this->objectManager->flush();
    }

    public function findBoletaHonorarioBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findBoletaHonoarios()
    {
        return $this->repository->findAll();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }


    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }


    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user)
    {
        $this->objectManager->refresh($user);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $this->objectManager->persist($user);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }
}
