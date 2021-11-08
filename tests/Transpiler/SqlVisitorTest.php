<?php

namespace App\Tests\Transpiler;

use App\Lexer\Lexer;
use App\Parser\Parser;
use App\Transpiler\SqlVisitor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SqlVisitorTest extends KernelTestCase
{
    protected static ?Lexer $lexer;

    protected static ?Parser $parser;

    protected SqlVisitor $sqlVisitor;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        static::$parser = self::getContainer()->get(Parser::class);
        static::$lexer = self::getContainer()->get(Lexer::class);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$parser = null;
        static::$lexer = null;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlVisitor = self::getContainer()->get(SqlVisitor::class);
    }

    public function correctQueryProvider(): array
    {
        return [
            ['name = test', "WHERE name = 'test'"],
            ['name = test and name != test', "WHERE ( name = 'test' and name != 'test' )"],
            ['name = test or name != test', "WHERE ( name = 'test' or name != 'test' )"],
            ['not (name = test or name != test)', "WHERE not ( name = 'test' or name != 'test' )"],
            ['!(name = test or name != test)', "WHERE not ( name = 'test' or name != 'test' )"],
            ['name ~ test', "WHERE name like '%test%'"],
            ['name !~ test', "WHERE name not like '%test%'"],
            ['name is null', "WHERE name is null"],
            ['name is not null', "WHERE name is not null"],
            ['name is empty', "WHERE name is null"],
            ['name is not empty', "WHERE name is not null"],
            ['name in (test1, test2, test3)', "WHERE name in ( 'test1' , 'test2' , 'test3' )"],
            ['name not in (test1, test2, test3)', "WHERE name not in ( 'test1' , 'test2' , 'test3' )"],
        ];
    }

    /**
     * @dataProvider correctQueryProvider
     */
    public function testSqlCreation(string $rawQuery, string $finalSql): void
    {
        $tokensList = self::$lexer->analyze($rawQuery);
        $queryNode = self::$parser->parse($tokensList);

        $sql = $this->sqlVisitor->visit($queryNode);

        $this->assertEquals($finalSql, $sql);
    }

    public function correctDateTimeQueryProvider(): array
    {
        $dataSets = [];

        /**
         * ADDING INTERVAL
         */
        //7 days
        $expectedDate = new \DateTime();
        $expectedDate->add(new \DateInterval('P7D'));
        $dataSets[] = ['name > 7d', "WHERE name > '" . $expectedDate->format('Y-m-d H:')];

        //1 month
        $expectedDate = new \DateTime();
        $expectedDate->add(new \DateInterval('P1M'));
        $dataSets[] = ['name < 1M', "WHERE name < '" . $expectedDate->format('Y-m-d H:')];

        //10 minutes
        $expectedDate = new \DateTime();
        $expectedDate->add(new \DateInterval('PT100M'));
        $dataSets[] = ['name >= 100m', "WHERE name >= '" . $expectedDate->format('Y-m-d H:')];

        //1 Year
        $expectedDate = new \DateTime();
        $expectedDate->add(new \DateInterval('P1Y'));
        $dataSets[] = ['name >= 1y', "WHERE name >= '" . $expectedDate->format('Y-m-d H:')];

        /**
         * SUBTRACTING INTERVAL
         */
        //7 days
        $expectedDate = new \DateTime();
        $expectedDate->sub(new \DateInterval('P7D'));
        $dataSets[] = ['name > -7d', "WHERE name > '" . $expectedDate->format('Y-m-d H:')];

        //1 month
        $expectedDate = new \DateTime();
        $expectedDate->sub(new \DateInterval('P1M'));
        $dataSets[] = ['name < -1M', "WHERE name < '" . $expectedDate->format('Y-m-d H:')];

        //10 minutes
        $expectedDate = new \DateTime();
        $expectedDate->sub(new \DateInterval('PT100M'));
        $dataSets[] = ['name >= -100m', "WHERE name >= '" . $expectedDate->format('Y-m-d H:')];

        //1 Year
        $expectedDate = new \DateTime();
        $expectedDate->sub(new \DateInterval('P1Y'));
        $dataSets[] = ['name >= -1y', "WHERE name >= '" . $expectedDate->format('Y-m-d H:')];

        return $dataSets;
    }

    /**
     * @dataProvider correctDateTimeQueryProvider
     */
    public function testDateTimeSqlCreation(string $rawQuery, string $finalCutSql): void
    {
        $tokensList = self::$lexer->analyze($rawQuery);
        $queryNode = self::$parser->parse($tokensList);

        $sql = $this->sqlVisitor->visit($queryNode);
        $sql = substr($sql, 0, -6);// cutting minutes and seconds

        $this->assertEquals($finalCutSql, $sql);
    }


}
