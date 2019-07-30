<?php

namespace App\Entity;

use App\Helpers\AccessTokenEntityInterface;
use App\Helpers\AccessTokenHelper;
use Doctrine\ORM\Mapping as ORM;
use Faker\Factory;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("email", message="User with the same email already registered in system.")
 */
class User implements AccessTokenEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(
     *     message = "The email {{ value }} is not a valid email.",
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $sex;

    /**
     * @ORM\Column(type="integer")
     */
    private $age;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $aboutMe;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $salt;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = ['ROLE_USER'];

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $renewToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $accessTokenExpiredAt;

    /**
     * @Validator\NotEmpty(message="Password can not be blank.", skipEmptyOn="isNotNew")
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getAboutMe(): ?string
    {
        return $this->aboutMe;
    }

    public function setAboutMe(?string $aboutMe): self
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        if ($this->salt === null) {
            $this->salt = Factory::create()->md5;
        }
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRenewToken(): ?string
    {
        return $this->renewToken;
    }

    public function setRenewToken(string $renewToken): self
    {
        $this->renewToken = $renewToken;

        return $this;
    }

    public function getAccessTokenExpiredAt(): ?\DateTimeInterface
    {
        return $this->accessTokenExpiredAt;
    }

    public function setAccessTokenExpiredAt(\DateTimeInterface $accessTokenExpiredAt): self
    {
        $this->accessTokenExpiredAt = $accessTokenExpiredAt;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;
        return $this;
    }


    public function getIsNew()
    {
        return $this->getId() === null;
    }


    // Implementing UserInterface

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {

    }

    // Implementing Serializable

    public function serialize()
    {
        return serialize([
            $this->getId(),
            $this->getEmail(),
            $this->getFirstName(),
            $this->getLastName(),
            $this->getPassword(),
            $this->getSalt(),
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->passwordHash,
            $this->salt
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }


    // Lifecycle Callbacks

    /**
     * @ORM\PrePersist
     */
    public function beforeCreate()
    {
        if ($this->getAccessToken() === null) {
            $this->setAccessToken(AccessTokenHelper::generateAccessToken($this));
        }
        if ($this->getRenewToken() === null) {
            $this->setRenewToken(AccessTokenHelper::generateAccessToken($this));
        }
        if ($this->getAccessTokenExpiredAt() === null) {
            $this->setAccessTokenExpiredAt(AccessTokenHelper::getAccessTokenExpiredAt());
        }
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
        if ($this->getUpdatedAt() === null) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}
