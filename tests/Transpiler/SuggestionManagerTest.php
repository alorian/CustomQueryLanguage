<?php

namespace App\Tests\Transpiler;

use App\Transpiler\CustomQueryState;
use App\Transpiler\SuggestionManager;
use App\Transpiler\Transpiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SuggestionManagerTest extends KernelTestCase
{
    protected static ?SuggestionManager $suggestionManager;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        static::$suggestionManager = self::getContainer()->get(SuggestionManager::class);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$suggestionManager = null;
    }


    public function simpleQueryProvider(): array
    {
        return [
            ['name = test', 2],//field suggestions
            ['name ', 5],// token after field suggestion
        ];
    }

    /**
     * @dataProvider simpleQueryProvider
     */
    public function testSimpleQuery(string $rawQuery, int $caretPos): void
    {
        $queryState = new CustomQueryState($rawQuery, $caretPos);
        static::$suggestionManager->addSuggestions($queryState);

        $this->assertNotEmpty($queryState->suggestionsList);
        $this->assertIsArray($queryState->suggestionsList);

        $suggestion = reset($queryState->suggestionsList);
        $this->assertIsArray($suggestion);
        $this->assertArrayHasKey('label', $suggestion);
        $this->assertArrayHasKey('value', $suggestion);
    }

    public function testWrongQuery(): void
    {
        $rawQuery = '"asd ';
        $queryState = new CustomQueryState($rawQuery, 5);
        static::$suggestionManager->addSuggestions($queryState);
        $this->assertEmpty($queryState->suggestionsList);
    }

}
