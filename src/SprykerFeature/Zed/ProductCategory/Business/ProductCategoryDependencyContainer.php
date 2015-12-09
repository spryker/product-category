<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\ProductCategory\Business;

use SprykerEngine\Zed\Kernel\Business\AbstractBusinessDependencyContainer;
use SprykerFeature\Zed\Category\Persistence\CategoryQueryContainerInterface;
use SprykerFeature\Zed\ProductCategory\Dependency\Facade\CmsToCategoryInterface;
use SprykerFeature\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface;
use SprykerFeature\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleInterface;
use SprykerFeature\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductInterface;
use SprykerFeature\Zed\ProductCategory\Dependency\Facade\ProductCategoryToTouchInterface;
use SprykerFeature\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface;
use SprykerFeature\Zed\ProductCategory\ProductCategoryDependencyProvider;

class ProductCategoryDependencyContainer extends AbstractBusinessDependencyContainer
{

    /**
     * @return ProductCategoryManagerInterface
     */
    public function createProductCategoryManager()
    {
        return new ProductCategoryManager(
            $this->createCategoryQueryContainer(),
            $this->createProductCategoryQueryContainer(),
            $this->createProductFacade(),
            $this->createCategoryFacade(),
            $this->createTouchFacade(),
            $this->createCmsFacade(),
            $this->getLocator(),
            $this->getProvidedDependency(ProductCategoryDependencyProvider::PLUGIN_PROPEL_CONNECTION)
        );
    }

    /**
     * @return CategoryQueryContainerInterface
     */
    protected function createCategoryQueryContainer()
    {
        return $this->getLocator()->category()->queryContainer();
    }

    /**
     * @return ProductCategoryQueryContainerInterface
     */
    protected function createProductCategoryQueryContainer()
    {
        return $this->getLocator()->productCategory()->queryContainer();
    }

    /**
     * @return ProductCategoryToLocaleInterface
     */
    protected function createLocaleFacade()
    {
        return $this->getLocator()->locale()->facade();
    }

    /**
     * @return ProductCategoryToProductInterface
     */
    protected function createProductFacade()
    {
        return $this->getLocator()->product()->facade();
    }

    /**
     * @return ProductCategoryToCategoryInterface
     */
    protected function createCategoryFacade()
    {
        return $this->getLocator()->category()->facade();
    }

    /**
     * @return ProductCategoryToTouchInterface
     */
    protected function createTouchFacade()
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_TOUCH);
    }

    /**
     * TODO: https://spryker.atlassian.net/browse/CD-540
     *
     * @return CmsToCategoryInterface
     */
    protected function createCmsFacade()
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_CMS);
    }

    /**
     * @return TransferGeneratorInterface
     */
    public function createProductCategoryTransferGenerator()
    {
        return new TransferGenerator();
    }

}
