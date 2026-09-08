<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\ProductCategory\Business\Expander;

use Generated\Shared\Transfer\ProductAbstractCollectionTransfer;
use Generated\Shared\Transfer\ProductAbstractCriteriaTransfer;
use Generated\Shared\Transfer\ProductCategoryConditionsTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryRepositoryInterface;

class ProductAbstractCollectionExpander implements ProductAbstractCollectionExpanderInterface
{
    public function __construct(
        protected readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    ) {
    }

    public function expandProductAbstractCollectionWithProductCategories(
        ProductAbstractCollectionTransfer $productAbstractCollectionTransfer,
        ProductAbstractCriteriaTransfer $productAbstractCriteriaTransfer,
    ): ProductAbstractCollectionTransfer {
        if (!$productAbstractCriteriaTransfer->getProductAbstractRelations()?->getWithProductCategories()) {
            return $productAbstractCollectionTransfer;
        }

        $productAbstractIds = [];

        foreach ($productAbstractCollectionTransfer->getProductAbstracts() as $productAbstractTransfer) {
            if ($productAbstractTransfer->getIdProductAbstract() !== null) {
                $productAbstractIds[] = $productAbstractTransfer->getIdProductAbstract();
            }
        }

        if (!$productAbstractIds) {
            return $productAbstractCollectionTransfer;
        }

        $productCategoryCollectionTransfer = $this->productCategoryRepository->getProductCategoryCollection(
            (new ProductCategoryCriteriaTransfer())
                ->setProductCategoryConditions(
                    (new ProductCategoryConditionsTransfer())
                        ->setProductAbstractIds($productAbstractIds),
                ),
        );

        $categoriesByProductAbstractId = [];

        foreach ($productCategoryCollectionTransfer->getProductCategories() as $productCategoryTransfer) {
            $categoriesByProductAbstractId[(int)$productCategoryTransfer->getFkProductAbstract()][] = $productCategoryTransfer;
        }

        foreach ($productAbstractCollectionTransfer->getProductAbstracts() as $productAbstractTransfer) {
            $idProductAbstract = $productAbstractTransfer->getIdProductAbstract();

            foreach ($categoriesByProductAbstractId[$idProductAbstract] ?? [] as $productCategoryTransfer) {
                $productAbstractTransfer->addProductCategory($productCategoryTransfer);
            }
        }

        return $productAbstractCollectionTransfer;
    }
}
