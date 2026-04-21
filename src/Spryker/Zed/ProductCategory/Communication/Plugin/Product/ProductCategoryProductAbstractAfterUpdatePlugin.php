<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Product\Dependency\Plugin\ProductAbstractPluginUpdateInterface;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductCategory\Communication\ProductCategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 */
class ProductCategoryProductAbstractAfterUpdatePlugin extends AbstractPlugin implements ProductAbstractPluginUpdateInterface
{
    /**
     * {@inheritDoc}
     * - Updates product category mappings by diffing current assignments against the provided category IDs.
     *
     * @api
     */
    public function update(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer
    {
        return $this->getFacade()->updateProductAbstractCategories($productAbstractTransfer);
    }
}
