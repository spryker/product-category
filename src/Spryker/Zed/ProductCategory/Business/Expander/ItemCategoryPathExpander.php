<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Expander;

use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;
use Spryker\Service\Category\CategoryServiceInterface;
use Spryker\Zed\ProductCategory\Business\Reader\CategoryPathNodeReaderInterface;

class ItemCategoryPathExpander implements ItemCategoryPathExpanderInterface
{
    public function __construct(
        protected CategoryPathNodeReaderInterface $categoryPathNodeReader,
        protected ?CategoryServiceInterface $categoryService
    ) {
    }

    public function expandItems(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        if ($this->categoryService === null) {
            return $cartChangeTransfer;
        }

        $categoryPathNodeCollectionTransfers = $this->categoryPathNodeReader
            ->getCategoryPathNodeCollectionsIndexedByIdProductAbstract($this->getProductAbstractIds($cartChangeTransfer));

        foreach ($cartChangeTransfer->getItems() as $itemTransfer) {
            $categoryPathNodeCollectionTransfer = $categoryPathNodeCollectionTransfers[$itemTransfer->getIdProductAbstract()] ?? new CategoryPathNodeCollectionTransfer();
            $itemTransfer->setCategoryPath($this->categoryService->getCategoryPath($categoryPathNodeCollectionTransfer));
        }

        return $cartChangeTransfer;
    }

    /**
     * @return list<int>
     */
    protected function getProductAbstractIds(CartChangeTransfer $cartChangeTransfer): array
    {
        $productAbstractIds = [];

        foreach ($cartChangeTransfer->getItems() as $itemTransfer) {
            $idProductAbstract = $itemTransfer->getIdProductAbstract();

            if (!$idProductAbstract) {
                continue;
            }

            $productAbstractIds[$idProductAbstract] = $idProductAbstract;
        }

        return array_values($productAbstractIds);
    }
}
