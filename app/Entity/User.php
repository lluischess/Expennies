<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Contracts\UserInterface;
use App\Entity\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

# Creación de Entity y tabla mediante atributos y anotaciones ORM
#[Entity, Table('users')]
#[HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;
    # We map de propertis to de table
    # Es un Id con la columna unigned integer y con un autogenerador de values
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column]
    private string $email;

    #[Column]
    private string $password;

    #[OneToMany(mappedBy: 'user',targetEntity: Category::class)]
    private Collection $categories;

    #[OneToMany(mappedBy: 'user',targetEntity: Transaction::class)]
    private Collection $transactions;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->transactions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getCategories(): ArrayCollection|Collection
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     * @return User
     */
    public function addCategories(Category $category): User
    {
        $this->categories->add($category);
        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getTransactions(): ArrayCollection|Collection
    {
        return $this->transactions;
    }

    /**
     * @param Transaction $transaction
     * @return User
     */
    public function addTransactions(Transaction $transaction): User
    {
        $this->transactions->add($transaction);
        return $this;
    }
}