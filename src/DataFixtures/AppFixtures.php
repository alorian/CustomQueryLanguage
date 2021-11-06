<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $manager->getClassMetadata(Project::class)->setLifecycleCallbacks([]);

        // create 20 products! Bam!
        for ($i = 0; $i < 30; $i++) {
            $project = new Project();
            $project->setName(ucfirst($faker->words(2, true)));
            $project->setSort($faker->numberBetween(1, 10000));

            $project->setOwner($faker->name());
            $project->setAssignee($faker->name());
            $project->setResponsible($faker->name());

            $project->setSource($faker->word());
            $project->setEpic($faker->word());
            $project->setDescription($faker->paragraph());

            $project->setCreatedAt(\DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-20 week', '-10 week')
            ));

            $project->setUpdatedAt(\DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-10 week', '+10 week')
            ));

            $manager->persist($project);
        }
        $manager->flush();
    }
}
