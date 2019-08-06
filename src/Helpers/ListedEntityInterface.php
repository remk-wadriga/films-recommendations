<?php


namespace App\Helpers;

interface ListedEntityInterface
{
    public function getId(): ?int;

    public function getName(): ?string;
}