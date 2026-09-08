<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory\Business\Plugin\Product;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractCollectionTransfer;
use Generated\Shared\Transfer\ProductAbstractCriteriaTransfer;
use Generated\Shared\Transfer\ProductAbstractRelationsTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\ProductCategory\Communication\Plugin\Product\ProductCategoryAbstractCollectionExpanderPlugin;
use SprykerTest\Zed\ProductCategory\ProductCategoryBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductCategory
 * @group Business
 * @group Plugin
 * @group Product
 * @group ProductCategoryAbstractCollectionExpanderPluginTest
 *
 * Add your own group annotations below this line
 */
class ProductCategoryAbstractCollectionExpanderPluginTest extends Unit
{
    protected ProductCategoryBusinessTester $tester;

    public function testExpandAddsProductCategoriesToProductAbstractWhenWithProductCategoriesEnabled(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $this->tester->assignProductToCategory($categoryTransfer->getIdCategory(), $productConcreteTransfer->getFkProductAbstract());

        $productAbstractCollectionTransfer = (new ProductAbstractCollectionTransfer())
            ->addProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract()));
        $productAbstractCriteriaTransfer = (new ProductAbstractCriteriaTransfer())
            ->setProductAbstractRelations((new ProductAbstractRelationsTransfer())->setWithProductCategories(true));

        // Act
        $productAbstractCollectionTransfer = (new ProductCategoryAbstractCollectionExpanderPlugin())
            ->expand($productAbstractCollectionTransfer, $productAbstractCriteriaTransfer);

        // Assert
        $this->assertGreaterThan(0, $productAbstractCollectionTransfer->getProductAbstracts()->offsetGet(0)->getProductCategories()->count());
    }

    public function testExpandDoesNotAddProductCategoriesWhenWithProductCategoriesDisabled(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $this->tester->assignProductToCategory($categoryTransfer->getIdCategory(), $productConcreteTransfer->getFkProductAbstract());

        $productAbstractCollectionTransfer = (new ProductAbstractCollectionTransfer())
            ->addProductAbstract((new ProductAbstractTransfer())->setIdProductAbstract($productConcreteTransfer->getFkProductAbstract()));
        $productAbstractCriteriaTransfer = new ProductAbstractCriteriaTransfer();

        // Act
        $productAbstractCollectionTransfer = (new ProductCategoryAbstractCollectionExpanderPlugin())
            ->expand($productAbstractCollectionTransfer, $productAbstractCriteriaTransfer);

        // Assert
        $this->assertCount(0, $productAbstractCollectionTransfer->getProductAbstracts()->offsetGet(0)->getProductCategories());
    }
}
