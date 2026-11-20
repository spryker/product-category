<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory;

use Codeception\Actor;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\StoreTransfer;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class ProductCategoryCommunicationTester extends Actor
{
    use _generated\ProductCategoryCommunicationTesterActions;

    protected const string STORE_NAME_DE = 'DE';

    public function haveCurrentStore(): StoreTransfer
    {
        $storeTransfer = $this->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $this->addCurrentStore($storeTransfer);

        return $storeTransfer;
    }

    public function haveActiveCategoryOfCurrentStore(?NodeTransfer $parentCategoryNodeTransfer = null, bool $isActive = true): CategoryTransfer
    {
        $categoryTransfer = $this->haveLocalizedCategory([
            CategoryTransfer::IS_ACTIVE => $isActive,
            CategoryTransfer::PARENT_CATEGORY_NODE => $parentCategoryNodeTransfer ?? $this->getRootCategoryNode(),
        ]);
        $this->haveCategoryStoreRelation(
            $categoryTransfer->getIdCategoryOrFail(),
            $this->getLocator()->store()->facade()->getCurrentStore()->getIdStoreOrFail(),
        );

        return $categoryTransfer;
    }

    public function getRootCategoryNode(): NodeTransfer
    {
        // Without a parent, the category helper places a category below a demo category; below the root node it is top level in the path.
        $nodeCollectionTransfer = $this->getLocator()->category()->facade()->getCategoryNodes(
            (new CategoryNodeCriteriaTransfer())->setIsRoot(true),
        );

        return $nodeCollectionTransfer->getNodes()->offsetGet(0);
    }

    public function findCategoryNameInCurrentLocale(CategoryTransfer $categoryTransfer): ?string
    {
        $idLocale = $this->getLocator()->locale()->facade()->getCurrentLocale()->getIdLocale();

        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            if ($categoryLocalizedAttributesTransfer->getLocale()?->getIdLocale() === $idLocale) {
                return $categoryLocalizedAttributesTransfer->getName();
            }
        }

        return null;
    }

    public function createCartChangeTransferWithItemOfProductAbstract(int $idProductAbstract): CartChangeTransfer
    {
        return (new CartChangeTransfer())->addItem((new ItemTransfer())->setIdProductAbstract($idProductAbstract));
    }
}
