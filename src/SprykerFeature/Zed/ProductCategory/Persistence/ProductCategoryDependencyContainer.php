<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\ProductCategory\Persistence;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Zed\Ide\FactoryAutoCompletion\ProductCategoryPersistence;
use SprykerEngine\Zed\Kernel\Persistence\AbstractPersistenceDependencyContainer;
use SprykerFeature\Zed\Category\Persistence\CategoryQueryContainer;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use SprykerFeature\Zed\ProductCategory\Persistence\QueryExpander\ProductCategoryPathQueryExpander;

class ProductCategoryDependencyContainer extends AbstractPersistenceDependencyContainer
{

    /**
     * @param LocaleTransfer $locale
     *
     * @return ProductCategoryPathQueryExpander
     */
    public function createProductCategoryPathQueryExpander(LocaleTransfer $locale)
    {
        return new ProductCategoryPathQueryExpander(
            $this->getCategoryQueryContainer(),
            $locale
        );
    }

    /**
     * @return CategoryQueryContainer
     */
    protected function getCategoryQueryContainer()
    {
        return $this->getLocator()->category()->queryContainer();
    }

    /**
     * @return SpyProductCategoryQuery
     */
    public function createProductCategoryQuery()
    {
        return SpyProductCategoryQuery::create();
    }

}
