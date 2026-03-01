<?php

namespace App\Tests\Service;

use App\Enum\BookFormat;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\CatalogService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CatalogServiceTest extends TestCase
{
    private function service(): CatalogService
    {
        return new CatalogService(
            $this->createMock(BookRepository::class),
            $this->createMock(AuthorRepository::class)
        );
    }

    public function testNormalizeFiltersWithValidInputs(): void
    {
        $request = new Request([
            'search' => 'Victor Hugo',
            'author' => '12',
            'format' => 'physical',
            'page' => '3',
            'perPage' => '24',
        ]);

        $filters = $this->service()->normalizeFilters($request);

        self::assertSame('Victor Hugo', $filters['search']);
        self::assertSame(12, $filters['authorId']);
        self::assertSame('physical', $filters['formatFilter']);
        self::assertSame(3, $filters['page']);
        self::assertSame(24, $filters['perPage']);
        self::assertSame(BookFormat::PHYSICAL, $filters['format']);
    }

    public function testNormalizeFiltersFallbacksOnInvalidPerPageAndPage(): void
    {
        $request = new Request([
            'page' => '-5',
            'perPage' => '13',
        ]);

        $filters = $this->service()->normalizeFilters($request);

        self::assertSame(1, $filters['page']);
        self::assertSame(CatalogService::DEFAULT_PER_PAGE, $filters['perPage']);
    }

    public function testNormalizeFiltersKeepsUnknownFormatFilterButEnumIsNull(): void
    {
        $request = new Request([
            'format' => 'audio',
        ]);

        $filters = $this->service()->normalizeFilters($request);

        self::assertSame('audio', $filters['formatFilter']);
        self::assertNull($filters['format']);
    }

    public function testNormalizeFiltersConvertsEmptySearchToNull(): void
    {
        $request = new Request([
            'search' => '',
            'author' => '',
        ]);

        $filters = $this->service()->normalizeFilters($request);

        self::assertNull($filters['search']);
        self::assertNull($filters['authorId']);
    }
}
