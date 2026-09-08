<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\ProductCategory\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductAbstractCollectionTransfer;
use Generated\Shared\Transfer\ProductAbstractCriteriaTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductExtension\Dependency\Plugin\ProductAbstractCollectionExpanderPluginInterface;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface getQueryContainer()
 */
class ProductCategoryAbstractCollectionExpanderPlugin extends AbstractPlugin implements ProductAbstractCollectionExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns the collection unchanged when `ProductAbstractCriteriaTransfer.productAbstractRelations.withProductCategories` is not set.
     * - Reads the product category assignments of all abstract products of the collection with a single query.
     * - Adds the resolved assignments to `ProductAbstractTransfer.productCategories`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductAbstractCollectionTransfer $productAbstractCollectionTransfer
     * @param \Generated\Shared\Transfer\ProductAbstractCriteriaTransfer $productAbstractCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductAbstractCollectionTransfer
     */
    public function expand(
        ProductAbstractCollectionTransfer $productAbstractCollectionTransfer,
        ProductAbstractCriteriaTransfer $productAbstractCriteriaTransfer
    ): ProductAbstractCollectionTransfer {
        return $this->getBusinessFactory()
            ->createProductAbstractCollectionExpander()
            ->expandProductAbstractCollectionWithProductCategories(
                $productAbstractCollectionTransfer,
                $productAbstractCriteriaTransfer,
            );
    }
}
