<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use SprykerTest\Zed\ProductCategory\ProductCategoryBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductCategory
 * @group Business
 * @group CreateProductAbstractCategoriesByUuidTest
 *
 * Add your own group annotations below this line
 */
class CreateProductAbstractCategoriesByUuidTest extends Unit
{
    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected ProductCategoryBusinessTester $tester;

    public function testCreateProductAbstractCategoriesResolvesCategoryByUuid(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $categoryUuid = SpyCategoryQuery::create()->findOneByIdCategory($categoryTransfer->getIdCategory())->getUuid();
        $this->assertNotNull($categoryUuid);

        $productConcreteTransfer = $this->tester->haveProduct();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->addCategoryUuid($categoryUuid);

        // Act
        $this->tester->getFacade()->createProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkCategory($categoryTransfer->getIdCategory())
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(1, $mappingCount);
    }

    public function testCreateProductAbstractCategoriesSkipsUnknownCategoryUuid(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->addCategoryUuid(static::UNKNOWN_UUID);

        // Act
        $this->tester->getFacade()->createProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(0, $mappingCount);
    }

    public function testCreateProductAbstractCategoriesPrefersCategoryIdsOverCategoryUuids(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $otherCategoryTransfer = $this->tester->haveLocalizedCategory();
        $otherCategoryUuid = SpyCategoryQuery::create()->findOneByIdCategory($otherCategoryTransfer->getIdCategory())->getUuid();

        $productConcreteTransfer = $this->tester->haveProduct();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->setCategoryIds([$categoryTransfer->getIdCategory()])
            ->addCategoryUuid($otherCategoryUuid);

        // Act
        $this->tester->getFacade()->createProductAbstractCategories($productAbstractTransfer);

        // Assert
        $this->assertSame(1, SpyProductCategoryQuery::create()
            ->filterByFkCategory($categoryTransfer->getIdCategory())
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count());
        $this->assertSame(0, SpyProductCategoryQuery::create()
            ->filterByFkCategory($otherCategoryTransfer->getIdCategory())
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count());
    }
}
