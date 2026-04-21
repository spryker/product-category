<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Plugin\ProductManagement;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductManagementExtension\Dependency\Plugin\ProductAbstractFormExpanderPluginInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductCategory\Communication\ProductCategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 */
class ProductCategoryAbstractFormExpanderPlugin extends AbstractPlugin implements ProductAbstractFormExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Expands product form with product category IDs.
     *
     * @api
     */
    public function expand(FormBuilderInterface $builder, array $options): FormBuilderInterface
    {
        return $this->getFactory()->createProductCategoryIdsProductFormExpander()->expand($builder, $options);
    }
}
