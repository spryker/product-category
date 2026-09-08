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
 * @group UpdateProductAbstractCategoriesByUuidTest
 *
 * Add your own group annotations below this line
 */
class UpdateProductAbstractCategoriesByUuidTest extends Unit
{
    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected ProductCategoryBusinessTester $tester;

    public function testUpdateProductAbstractCategoriesResolvesCategoryByUuid(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $categoryUuid = SpyCategoryQuery::create()->findOneByIdCategory($categoryTransfer->getIdCategory())->getUuid();
        $this->assertNotNull($categoryUuid);

        $productConcreteTransfer = $this->tester->haveProduct();
        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->setCategoryUuids([$categoryUuid]);

        // Act
        $this->tester->getFacade()->updateProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkCategory($categoryTransfer->getIdCategory())
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(1, $mappingCount);
    }

    public function testUpdateProductAbstractCategoriesRemovesMappingsWhenCategoryUuidsAreEmpty(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $productConcreteTransfer = $this->tester->haveProduct();
        $this->tester->assignProductToCategory(
            $categoryTransfer->getIdCategory(),
            $productConcreteTransfer->getFkProductAbstract(),
        );

        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->setCategoryUuids([]);

        // Act
        $this->tester->getFacade()->updateProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(0, $mappingCount);
    }

    public function testUpdateProductAbstractCategoriesKeepsMappingsWhenNoCategoryPropertyModified(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $productConcreteTransfer = $this->tester->haveProduct();
        $this->tester->assignProductToCategory(
            $categoryTransfer->getIdCategory(),
            $productConcreteTransfer->getFkProductAbstract(),
        );

        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract());

        // Act
        $this->tester->getFacade()->updateProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkCategory($categoryTransfer->getIdCategory())
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(1, $mappingCount);
    }

    public function testUpdateProductAbstractCategoriesRemovesMappingsWhenCategoryUuidIsUnknown(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $productConcreteTransfer = $this->tester->haveProduct();
        $this->tester->assignProductToCategory(
            $categoryTransfer->getIdCategory(),
            $productConcreteTransfer->getFkProductAbstract(),
        );

        $productAbstractTransfer = (new ProductAbstractTransfer())
            ->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->setCategoryUuids([static::UNKNOWN_UUID]);

        // Act
        $this->tester->getFacade()->updateProductAbstractCategories($productAbstractTransfer);

        // Assert
        $mappingCount = SpyProductCategoryQuery::create()
            ->filterByFkProductAbstract($productConcreteTransfer->getFkProductAbstract())
            ->count();
        $this->assertSame(0, $mappingCount);
    }
}
