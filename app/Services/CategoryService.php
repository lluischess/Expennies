<?php

declare(strict_types=1);

namespace App\Services;

use App\DataObjects\DataTableQueryParams;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CategoryService
{
    public function __construct(private EntityManager $entityManager)
    {
    }

    public function create(string $name, User $user) : Category
    {
        // Crear nueva instancia de category
        $category = new Category();

        // Asignarle un usuario
        $category->setUser($user);

        // Llamar a la función update que se encarge de actualizar el usuario en la BBDD
        return $this->update($category,$name);
    }

    public function getPaginatedCategories(DataTableQueryParams $params) : Paginator
    {
        $query = $this->entityManager->getRepository(Category::class)
                ->createQueryBuilder('c')
                ->setFirstResult($params->start)
                ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy,['name','createdAt','updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)){
            $query->where('c.name LIKE :name')->setParameter('name','%'.addcslashes($params->searchTerm,'%_').'%');
        }

        $query->orderBy('c.' . $orderBy, $orderDir);

        return new Paginator($query);
    }

    public function delete(int $id) : void
    {
        // Hacemos un find por class Categoty y por id
        $category = $this->entityManager->find(Category::class, $id);

        // Creamos la consulta delete de esa categoria
        $this->entityManager->remove($category);
        // La ejecutamos
        $this->entityManager->flush();
    }

    // El ? en el return significa que puede ser null o el objeto
    public function getById(int $id) : ?Category
    {
        return $this->entityManager->find(Category::class, $id);
    }

    public function update(Category $category, string $name) : Category
    {
        // Asignamos el nuevo nombre al Name
        $category->setName($name);

        // Preparamos la consulta
        $this->entityManager->persist($category);
        // La ejecutamos
        $this->entityManager->flush();

        // devolvemos el objeto
        return $category;
    }

    public function getCategoryNames(): array
    {
        return $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('c.id', 'c.name')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByName(string $name): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findBy(['name' => $name])[0] ?? null;
    }

    public function getAllKeyedByName(): array
    {
        $categories  = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryMap = [];

        foreach ($categories as $category) {
            $categoryMap[strtolower($category->getName())] = $category;
        }

        return $categoryMap;
    }
}