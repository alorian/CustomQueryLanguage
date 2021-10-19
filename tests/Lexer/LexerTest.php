<?php

namespace App\Tests\Lexer;

use App\Exception\UnexpectedCharacterException;
use App\Exception\UnterminatedStringException;
use App\Lexer\DateToken\CurrentDateModifierToken;
use App\Lexer\KeywordToken\AndToken;
use App\Lexer\KeywordToken\ByToken;
use App\Lexer\KeywordToken\InToken;
use App\Lexer\KeywordToken\IsToken;
use App\Lexer\KeywordToken\NotToken;
use App\Lexer\KeywordToken\OrderToken;
use App\Lexer\KeywordToken\OrToken;
use App\Lexer\Lexer;
use App\Lexer\SimpleToken\CommaToken;
use App\Lexer\SimpleToken\DotToken;
use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\GreaterEqualToken;
use App\Lexer\SimpleToken\GreaterToken;
use App\Lexer\SimpleToken\LessEqualToken;
use App\Lexer\SimpleToken\LessToken;
use App\Lexer\SimpleToken\MinusToken;
use App\Lexer\SimpleToken\NotEqualToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Lexer\SimpleToken\PlusToken;
use App\Lexer\SimpleToken\StarToken;
use App\Lexer\SimpleToken\TildaToken;
use App\Lexer\TypeToken\EmptyToken;
use App\Lexer\TypeToken\FalseToken;
use App\Lexer\TypeToken\NullToken;
use App\Lexer\TypeToken\NumberToken;
use App\Lexer\TypeToken\StringToken;
use App\Lexer\TypeToken\TrueToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LexerTest extends KernelTestCase
{
    protected Lexer $lexer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->lexer = self::getContainer()->get(Lexer::class);
    }

    public function testEmptyString(): void
    {
        $tokensList = $this->lexer->analyze('');
        $this->assertEmpty($tokensList);

        $tokensList = $this->lexer->analyze(' ');
        $this->assertEmpty($tokensList);

        $tokensList = $this->lexer->analyze("\r");
        $this->assertEmpty($tokensList);

        $tokensList = $this->lexer->analyze("\t");
        $this->assertEmpty($tokensList);

        $tokensList = $this->lexer->analyze("\n");
        $this->assertEmpty($tokensList);
    }

    public function unknownSymbolsProvider(): array
    {
        return [
            ['#'],
            ['^'],
            ['8123p'],//wrong spaces after number
        ];
    }

    /**
     * @dataProvider unknownSymbolsProvider
     */
    public function testUnknownSymbolsException(string $stringWithUnknownSymbol): void
    {
        $this->expectException(UnexpectedCharacterException::class);
        $this->lexer->analyze($stringWithUnknownSymbol);
    }

    public function unterminatedStringsProvider(): array
    {
        return [
            ['"test unterminated string'],
            ["'test unterminated string"],
            ['`test unterminated string']
        ];
    }

    /**
     * @dataProvider unterminatedStringsProvider
     */
    public function testUnterminatedStringException(string $unterminatedString): void
    {
        $this->expectException(UnterminatedStringException::class);
        $this->lexer->analyze($unterminatedString);
    }

    public function simpleTokenProvider(): array
    {
        //$code = ', . = => > < <= - != () + * ~';
        return [
            [CommaToken::LEXEME, CommaToken::class],
            [DotToken::LEXEME, DotToken::class],
            [EqualToken::LEXEME, EqualToken::class],
            [GreaterEqualToken::LEXEME, GreaterEqualToken::class],
            [GreaterToken::LEXEME, GreaterToken::class],
            [LessEqualToken::LEXEME, LessEqualToken::class],
            [LessToken::LEXEME, LessToken::class],
            [MinusToken::LEXEME, MinusToken::class],
            [NotEqualToken::LEXEME, NotEqualToken::class],
            [ParenLeftToken::LEXEME, ParenLeftToken::class],
            [ParenRightToken::LEXEME, ParenRightToken::class],
            [PlusToken::LEXEME, PlusToken::class],
            [StarToken::LEXEME, StarToken::class],
            [TildaToken::LEXEME, TildaToken::class],
        ];
    }

    public function dateTokenProvider(): array
    {
        return [
            ['1Y', CurrentDateModifierToken::class],
            ['2y', CurrentDateModifierToken::class],
            ['3M', CurrentDateModifierToken::class],
            ['4D', CurrentDateModifierToken::class],
            ['5d', CurrentDateModifierToken::class],
            ['6W', CurrentDateModifierToken::class],
            ['7w', CurrentDateModifierToken::class],
            ['8H', CurrentDateModifierToken::class],
            ['9h', CurrentDateModifierToken::class],
            ['10m', CurrentDateModifierToken::class],
            ['11S', CurrentDateModifierToken::class],
            ['12s', CurrentDateModifierToken::class],
            ['1Y 2y 3M 4D 5d 6W 7w 8H 10m 11S 12s', CurrentDateModifierToken::class],
        ];
    }

    public function keywordTokenProvider(): array
    {
        return [
            [AndToken::LEXEME, AndToken::class],
            [ByToken::LEXEME, ByToken::class],
            [InToken::LEXEME, InToken::class],
            [IsToken::LEXEME, IsToken::class],
            [NotToken::LEXEME, NotToken::class],
            [OrderToken::LEXEME, OrderToken::class],
            [OrToken::LEXEME, OrToken::class],
        ];
    }

    public function typeTokenProvider(): array
    {
        return [
            [EmptyToken::LEXEME, EmptyToken::class],
            [FalseToken::LEXEME, FalseToken::class],
            [TrueToken::LEXEME, TrueToken::class],
            [NullToken::LEXEME, NullToken::class],
            ['53453453', NumberToken::class],//integer number
            ['53453453.4534', NumberToken::class],// float number
            ['somerandomstring somerandomstring', StringToken::class],
            ['"somerandomstring somerandomstring"', StringToken::class],//quoted string
            ['"somerandomstring somer\"andomstring"', StringToken::class], // string with escaping
            ['"somerandomstring order empty null ndomstring"', StringToken::class],// string with keywords
        ];
    }

    /**
     * @dataProvider dateTokenProvider
     * @dataProvider keywordTokenProvider
     * @dataProvider simpleTokenProvider
     * @dataProvider typeTokenProvider
     */
    public function testSingleTypeTokens(string $lexeme, string $className): void
    {
        $tokensList = $this->lexer->analyze($lexeme);
        $this->assertContainsOnlyInstancesOf($className, $tokensList);
    }

    public function testLongStrings(): void
    {
        $code = '(status=resolved AND project=SysAdmin) OR assignee=bobsmith and 7d';
        $tokensList = $this->lexer->analyze($code);
        $this->assertCount(15, $tokensList);
    }

}
