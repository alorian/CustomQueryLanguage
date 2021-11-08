<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProjectFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $manager->getClassMetadata(Project::class)->setLifecycleCallbacks([]);

        $project = new Project();
        $project->name = 'test';
        $project->sort = 100;

        $project->description = 'Test Paragraph';

        $project->source = 'source';
        $project->epic = 'epic';

        $project->owner = 'Test User';
        $project->assignee = 'Test User';
        $project->responsible = 'Test User';

        $project->created_at = \DateTimeImmutable::createFromMutable(
            $faker->dateTimeBetween('-20 week', '-10 week')
        );

        $project->updated_at = \DateTimeImmutable::createFromMutable(
            $faker->dateTimeBetween('-10 week', '+10 week')
        );

        $manager->persist($project);

        // create 20 products! Bam!
        for ($i = 0; $i < 30; $i++) {
            $project = new Project();
            $project->name = ucfirst($faker->words(2, true));
            $project->sort = $faker->numberBetween(1, 10000);

            $project->description = $faker->paragraph();

            $project->source = $faker->word();
            $project->epic = $faker->word();

            $project->owner = $faker->name();
            $project->assignee = $faker->name();
            $project->responsible = $faker->name();

            $project->created_at = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-20 week', '-10 week')
            );

            $project->updated_at = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('-10 week', '+10 week')
            );

            $manager->persist($project);
        }
        $manager->flush();
    }
}
