<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductCategory
 * @group Persistence
 * @group ProductCategoryQueryContainerTest
 * Add your own group annotations below this line
 */
class ProductCategoryQueryContainerTest extends Unit
{
    protected const string LOCALE_NAME = 'en_CZ'; // value that we do not have in the data import to avoid test flickering

    protected const string PRODUCT_ABSTRACT_SKU = 'PRODUCT_ABSTRACT';

    protected const string PRODUCT_ATTRIBUTE_NAME = 'PRODUCT_ATTRIBUTE';

    protected const string PRODUCT_ABSTRACT_SKU_1 = 'PRODUCT_ABSTRACT_1';

    protected const string PRODUCT_ATTRIBUTE_NAME_1 = 'PRODUCT_ATTRIBUTE_1';

    protected const string PRODUCT_ABSTRACT_SKU_2 = 'PRODUCT_ABSTRACT_2';

    protected const string PRODUCT_ATTRIBUTE_NAME_2 = 'PRODUCT_ATTRIBUTE_2';

    protected const string PRODUCT_ABSTRACT_ASSIGNED_SKU = 'PRODUCT_ABSTRACT_ASSIGNED';

    /**
     * @var \SprykerTest\Zed\ProductCategory\ProductCategoryPersistenceTester
     */
    protected $tester;

    /**
     * @dataProvider getQueryProductsAbstractBySearchTermData
     *
     * @param array<string, mixed> $productData
     * @param string $term
     * @param int $expectedCount
     *
     * @return void
     */
    public function testQueryProductsAbstractBySearchTermShouldReturnQueryWithProductsFilteredByTerm(
        array $productData,
        string $term,
        int $expectedCount
    ): void {
        // Arrange
        $localeEntity = $this->tester->createLocaleEntity(static::LOCALE_NAME);
        $localeTransfer = (new LocaleTransfer())->fromArray($localeEntity->toArray());

        foreach ($productData as $sku => $attributeName) {
            $this->tester->createProductAbstractEntity($sku, $attributeName, $localeEntity);
        }

        $productCategoryQueryContainer = new ProductCategoryQueryContainer();

        // Act
        $productCategoryQuery = $productCategoryQueryContainer->queryProductsAbstractBySearchTerm($term, $localeTransfer);

        // Assert
        $this->assertSame($expectedCount, $productCategoryQuery->count());
    }

    public function getQueryProductsAbstractBySearchTermData(): array
    {
        return [
            [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1,
                    static::PRODUCT_ABSTRACT_SKU_2 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                static::PRODUCT_ABSTRACT_SKU_1,
                1,
            ],
            [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_2,
                    static::PRODUCT_ABSTRACT_SKU_2 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                static::PRODUCT_ABSTRACT_SKU,
                2,
            ],
            [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                static::PRODUCT_ABSTRACT_SKU_2,
                0,
            ],
            [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1,
                ],
                static::PRODUCT_ATTRIBUTE_NAME_1,
                1,
            ],
            [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1,
                    static::PRODUCT_ABSTRACT_SKU_2 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                static::PRODUCT_ATTRIBUTE_NAME,
                2,
            ],
        ];
    }

    /**
     * @dataProvider getQueryProductsAbstractBySearchTermForAssignmentData
     *
     * @param array<string, string> $unassignedProductData Key is SKU, value is localized name.
     * @param array<string, string> $assignedProductData Key is SKU, value is localized name.
     */
    public function testQueryProductsAbstractBySearchTermForAssignmentShouldReturnOnlyUnassignedProducts(
        array $unassignedProductData,
        array $assignedProductData,
        string $term,
        int $expectedCount
    ): void {
        // Arrange
        $localeEntity = $this->tester->createLocaleEntity(static::LOCALE_NAME);
        $localeTransfer = (new LocaleTransfer())->fromArray($localeEntity->toArray());
        $categoryTransfer = $this->tester->haveCategory();

        foreach ($unassignedProductData as $sku => $name) {
            $this->tester->createProductAbstractEntity($sku, $name, $localeEntity);
        }

        foreach ($assignedProductData as $sku => $name) {
            $productAbstractEntity = $this->tester->createProductAbstractEntity($sku, $name, $localeEntity);
            $this->tester->assignProductToCategory(
                $categoryTransfer->getIdCategory(),
                $productAbstractEntity->getIdProductAbstract(),
            );
        }

        $productCategoryQueryContainer = new ProductCategoryQueryContainer();

        // Act
        $query = $productCategoryQueryContainer->queryProductsAbstractBySearchTermForAssignment(
            $term,
            $categoryTransfer->getIdCategory(),
            $localeTransfer,
        );

        // Assert
        $this->assertSame($expectedCount, $query->count());
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function getQueryProductsAbstractBySearchTermForAssignmentData(): array
    {
        return [
            'returns all unassigned products when no term given' => [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1,
                    static::PRODUCT_ABSTRACT_SKU_2 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                [],
                '',
                2,
            ],
            'excludes products already assigned to the category' => [
                [static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1],
                [static::PRODUCT_ABSTRACT_ASSIGNED_SKU => static::PRODUCT_ATTRIBUTE_NAME_2],
                '',
                1,
            ],
            'returns nothing when all products are assigned to the category' => [
                [],
                [static::PRODUCT_ABSTRACT_ASSIGNED_SKU => static::PRODUCT_ATTRIBUTE_NAME_1],
                '',
                0,
            ],
            'search term filters unassigned products by sku' => [
                [
                    static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1,
                    static::PRODUCT_ABSTRACT_SKU_2 => static::PRODUCT_ATTRIBUTE_NAME_2,
                ],
                [static::PRODUCT_ABSTRACT_ASSIGNED_SKU => static::PRODUCT_ATTRIBUTE_NAME_1],
                static::PRODUCT_ABSTRACT_SKU_1,
                1,
            ],
            'assigned products are excluded even when sku matches the search term' => [
                [],
                [static::PRODUCT_ABSTRACT_ASSIGNED_SKU => static::PRODUCT_ATTRIBUTE_NAME_1],
                static::PRODUCT_ABSTRACT_SKU,
                0,
            ],
            'search term filters unassigned products by localized name' => [
                [static::PRODUCT_ABSTRACT_SKU_1 => static::PRODUCT_ATTRIBUTE_NAME_1],
                [static::PRODUCT_ABSTRACT_ASSIGNED_SKU => static::PRODUCT_ATTRIBUTE_NAME_2],
                static::PRODUCT_ATTRIBUTE_NAME_1,
                1,
            ],
        ];
    }
}
