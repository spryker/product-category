<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory\Business\Facade;

use Codeception\Test\Unit;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategory;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToEventInterface;
use Spryker\Zed\ProductCategory\ProductCategoryDependencyProvider;
use SprykerTest\Zed\ProductCategory\ProductCategoryBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductCategory
 * @group Business
 * @group Facade
 * @group RemoveProductCategoryMappingsTest
 * Add your own group annotations below this line
 */
class RemoveProductCategoryMappingsTest extends Unit
{
    protected ProductCategoryBusinessTester $tester;

    public function testGivenTwoProductsAssignedToACategoryWhenRemoveProductCategoryMappingsForOneThenOnlyTheOtherStaysAssigned(): void
    {
        // Arrange
        $idCategory = $this->tester->haveCategory()->getIdCategoryOrFail();
        $idProductAbstractToUnassign = $this->tester->haveProduct()->getFkProductAbstractOrFail();
        $idProductAbstractToKeep = $this->tester->haveProduct()->getFkProductAbstractOrFail();

        $this->tester->setDependency(
            ProductCategoryDependencyProvider::FACADE_EVENT,
            $this->createStub(ProductCategoryToEventInterface::class),
        );

        $this->tester->getFacade()->createProductCategoryMappings(
            $idCategory,
            [$idProductAbstractToUnassign, $idProductAbstractToKeep],
        );

        // Act
        $this->tester->getFacade()->removeProductCategoryMappings($idCategory, [$idProductAbstractToUnassign]);

        // Assert
        $this->assertSame([$idProductAbstractToKeep], $this->getAssignedProductAbstractIds($idCategory));
    }

    /**
     * @return list<int>
     */
    protected function getAssignedProductAbstractIds(int $idCategory): array
    {
        $productCategoryEntities = SpyProductCategoryQuery::create()
            ->filterByFkCategory($idCategory)
            ->find()
            ->getData();

        return array_map(
            static fn (SpyProductCategory $productCategoryEntity): int => $productCategoryEntity->getFkProductAbstract(),
            $productCategoryEntities,
        );
    }
}
