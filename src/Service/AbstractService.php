<?php


namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractService
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}