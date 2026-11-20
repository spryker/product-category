<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Plugin\Cart;

use Generated\Shared\Transfer\CartChangeTransfer;
use Spryker\Zed\CartExtension\Dependency\Plugin\ItemExpanderPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryBusinessFactory getBusinessFactory()
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductCategory\Communication\ProductCategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface getQueryContainer()
 */
class CategoryPathItemExpanderPlugin extends AbstractPlugin implements ItemExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Requires `ItemTransfer.idProductAbstract`, e.g. set by `ProductCartConnector`'s `ProductCartPlugin`; register this plugin after it.
     * - Reads the categories of the items' abstract products, and the categories above them, from persistence:
     *   active categories of the current store only, names in the current locale, the root category left out.
     * - Sets `ItemTransfer.categoryPath` with {@link \Spryker\Service\Category\CategoryServiceInterface::getCategoryPath()}: the category names, top level first.
     * - Sets an empty `ItemTransfer.categoryPath` for an item without `idProductAbstract` or without such categories.
     * - Requires `spryker/category` 5.28.0 or higher, which provides the Category service; with an older version, returns the items unchanged.
     *
     * @api
     */
    public function expandItems(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        return $this->getBusinessFactory()
            ->createItemCategoryPathExpander()
            ->expandItems($cartChangeTransfer);
    }
}
