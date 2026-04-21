<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Plugin\Product;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductExtension\Dependency\Plugin\ProductAbstractPostCreatePluginInterface;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductCategory\Communication\ProductCategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 */
class ProductCategoryProductAbstractPostCreatePlugin extends AbstractPlugin implements ProductAbstractPostCreatePluginInterface
{
    /**
     * {@inheritDoc}
     * - Creates product category mappings for all category IDs provided in the transfer.
     *
     * @api
     */
    public function postCreate(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer
    {
        return $this->getFacade()->createProductAbstractCategories($productAbstractTransfer);
    }
}
