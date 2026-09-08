<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Creator;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\ProductCategory\Business\Manager\ProductCategoryManagerInterface;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryRepositoryInterface;

class ProductCategoryProductAbstractCreator implements ProductCategoryProductAbstractCreatorInterface
{
    public function __construct(
        protected readonly ProductCategoryManagerInterface $productCategoryManager,
        protected readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    ) {
    }

    public function createProductAbstractCategories(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer
    {
        $idProductAbstract = $productAbstractTransfer->getIdProductAbstract();
        if ($idProductAbstract === null) {
            return $productAbstractTransfer;
        }

        $categoryIds = $this->resolveCategoryIds($productAbstractTransfer);

        foreach ($categoryIds as $idCategory) {
            $this->productCategoryManager->createProductCategoryMappings($idCategory, [$idProductAbstract]);
        }

        return $productAbstractTransfer;
    }

    /**
     * @return array<int>
     */
    protected function resolveCategoryIds(ProductAbstractTransfer $productAbstractTransfer): array
    {
        $categoryIds = $productAbstractTransfer->getCategoryIds();

        if ($categoryIds !== []) {
            return $categoryIds;
        }

        $categoryUuids = $productAbstractTransfer->getCategoryUuids();

        if ($categoryUuids === []) {
            return [];
        }

        return $this->productCategoryRepository->getCategoryIdsByUuids($categoryUuids);
    }
}
