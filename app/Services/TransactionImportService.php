<?php

declare(strict_types = 1);

namespace App\Services;

use App\DataObjects\TransactionData;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class TransactionImportService
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly TransactionService $transactionService,
        private readonly EntityManager $entityManager
    ) {
    }

    public function importFromFile(string $file, User $user): void
    {
        $resource   = fopen($file, 'r');
        $categories = $this->categoryService->getAllKeyedByName();

        fgetcsv($resource);

        $count     = 1;
        $batchSize = 250;
        while (($row = fgetcsv($resource)) !== false) {
            [$date, $description, $category, $amount] = $row;

            $date     = new \DateTime($date);
            $category = $categories[strtolower($category)] ?? null;
            $amount   = str_replace(['$', ','], '', $amount);

            $transactionData = new TransactionData($description, (float) $amount, $date, $category);

            $this->transactionService->create($transactionData, $user);

            if ($count % $batchSize === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(Transaction::class);

                $count = 1;
            } else {
                $count++;
            }
        }

        if ($count > 1) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }
}