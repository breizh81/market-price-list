<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Supplier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadSuppliers($manager);
    }

    private function loadSuppliers(ObjectManager $manager): void
    {
        foreach ($this->getSupplierData() as $name) {
            $supplier = (new Supplier())->setName($name);
            $manager->persist($supplier);
        }
        $manager->flush();
    }

    private function getSupplierData(): array
    {
        return ['Fruit supplier', 'Dairy supplier', 'Meat supplier', 'Vegetable supplier', 'Beverage supplier'];
    }
}
