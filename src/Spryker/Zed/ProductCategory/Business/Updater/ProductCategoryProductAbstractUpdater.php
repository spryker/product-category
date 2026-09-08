<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Updater;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\ProductCategory\Business\Manager\ProductCategoryManagerInterface;
use Spryker\Zed\ProductCategory\Business\Model\CategoryReaderInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleInterface;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryRepositoryInterface;

class ProductCategoryProductAbstractUpdater implements ProductCategoryProductAbstractUpdaterInterface
{
    public function __construct(
        protected readonly ProductCategoryManagerInterface $productCategoryManager,
        protected readonly CategoryReaderInterface $categoryReader,
        protected readonly ProductCategoryToLocaleInterface $localeFacade,
        protected readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    ) {
    }

    public function updateProductAbstractCategories(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer
    {
        $idProductAbstract = $productAbstractTransfer->getIdProductAbstract();
        if ($idProductAbstract === null) {
            return $productAbstractTransfer;
        }

        $categoryIdsToAssign = $this->resolveCategoryIds($productAbstractTransfer);

        if ($categoryIdsToAssign === null) {
            return $productAbstractTransfer;
        }

        $assignedCategoryIds = $this->getExistingCategoryIds($idProductAbstract);

        foreach (array_diff($categoryIdsToAssign, $assignedCategoryIds) as $idCategory) {
            $this->productCategoryManager->createProductCategoryMappings($idCategory, [$idProductAbstract]);
        }

        foreach (array_diff($assignedCategoryIds, $categoryIdsToAssign) as $idCategory) {
            $this->productCategoryManager->removeProductCategoryMappings($idCategory, [$idProductAbstract]);
        }

        return $productAbstractTransfer;
    }

    /**
     * @return array<int>|null
     */
    protected function resolveCategoryIds(ProductAbstractTransfer $productAbstractTransfer): ?array
    {
        if ($productAbstractTransfer->isPropertyModified(ProductAbstractTransfer::CATEGORY_IDS)) {
            return $productAbstractTransfer->getCategoryIds();
        }

        if (!$productAbstractTransfer->isPropertyModified(ProductAbstractTransfer::CATEGORY_UUIDS)) {
            return null;
        }

        $categoryUuids = $productAbstractTransfer->getCategoryUuids();

        if ($categoryUuids === []) {
            return [];
        }

        return $this->productCategoryRepository->getCategoryIdsByUuids($categoryUuids);
    }

    /**
     * @return array<int>
     */
    protected function getExistingCategoryIds(int $idProductAbstract): array
    {
        $localeTransfer = $this->localeFacade->getCurrentLocale();
        $categoryCollectionTransfer = $this->categoryReader->getCategoryTransferCollectionByIdProductAbstract(
            $idProductAbstract,
            $localeTransfer,
        );

        $categoryIds = [];

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $idCategory = $categoryTransfer->getIdCategory();

            if ($idCategory !== null) {
                $categoryIds[] = $idCategory;
            }
        }

        return $categoryIds;
    }
}
