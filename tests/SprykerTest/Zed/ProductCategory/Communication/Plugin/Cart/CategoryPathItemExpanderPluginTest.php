<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory\Communication\Plugin\Cart;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Spryker\Zed\ProductCategory\Communication\Plugin\Cart\CategoryPathItemExpanderPlugin;
use SprykerTest\Zed\ProductCategory\ProductCategoryCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductCategory
 * @group Communication
 * @group Plugin
 * @group Cart
 * @group CategoryPathItemExpanderPluginTest
 * Add your own group annotations below this line
 */
class CategoryPathItemExpanderPluginTest extends Unit
{
    protected ProductCategoryCommunicationTester $tester;

    public function testExpandItemsSetsTheCategoryPathOfTheAbstractProductTopLevelFirst(): void
    {
        // Arrange
        $this->tester->haveCurrentStore();
        $parentCategoryTransfer = $this->tester->haveActiveCategoryOfCurrentStore();
        $categoryTransfer = $this->tester->haveActiveCategoryOfCurrentStore($parentCategoryTransfer->getCategoryNodeOrFail());
        $cartChangeTransfer = $this->createCartChangeTransferWithProductInCategory($categoryTransfer);

        // Act
        $cartChangeTransfer = (new CategoryPathItemExpanderPlugin())->expandItems($cartChangeTransfer);

        // Assert
        $this->assertSame(
            [$this->tester->findCategoryNameInCurrentLocale($parentCategoryTransfer), $this->tester->findCategoryNameInCurrentLocale($categoryTransfer)],
            $cartChangeTransfer->getItems()->offsetGet(0)->getCategoryPath(),
        );
    }

    public function testExpandItemsLeavesOutAnInactiveCategory(): void
    {
        // Arrange
        $this->tester->haveCurrentStore();
        $categoryTransfer = $this->tester->haveActiveCategoryOfCurrentStore(null, false);
        $cartChangeTransfer = $this->createCartChangeTransferWithProductInCategory($categoryTransfer);

        // Act
        $cartChangeTransfer = (new CategoryPathItemExpanderPlugin())->expandItems($cartChangeTransfer);

        // Assert
        $this->assertSame([], $cartChangeTransfer->getItems()->offsetGet(0)->getCategoryPath());
    }

    public function testExpandItemsLeavesOutACategoryThatIsNotInTheCurrentStore(): void
    {
        // Arrange
        $this->tester->haveCurrentStore();
        $categoryTransfer = $this->tester->haveLocalizedCategory([CategoryTransfer::IS_ACTIVE => true]);
        $cartChangeTransfer = $this->createCartChangeTransferWithProductInCategory($categoryTransfer);

        // Act
        $cartChangeTransfer = (new CategoryPathItemExpanderPlugin())->expandItems($cartChangeTransfer);

        // Assert
        $this->assertSame([], $cartChangeTransfer->getItems()->offsetGet(0)->getCategoryPath());
    }

    public function testExpandItemsSetsAnEmptyCategoryPathForAnItemWithoutAbstractProduct(): void
    {
        // Act
        $cartChangeTransfer = (new CategoryPathItemExpanderPlugin())->expandItems((new CartChangeTransfer())->addItem(new ItemTransfer()));

        // Assert
        $this->assertSame([], $cartChangeTransfer->getItems()->offsetGet(0)->getCategoryPath());
    }

    protected function createCartChangeTransferWithProductInCategory(CategoryTransfer $categoryTransfer): CartChangeTransfer
    {
        $idProductAbstract = $this->tester->haveProduct()->getFkProductAbstractOrFail();
        $this->tester->assignProductToCategory($categoryTransfer->getIdCategoryOrFail(), $idProductAbstract);

        return $this->tester->createCartChangeTransferWithItemOfProductAbstract($idProductAbstract);
    }
}
