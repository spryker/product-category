<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\ProductCategory\Business\Creator\ProductCategoryProductAbstractCreator;
use Spryker\Zed\ProductCategory\Business\Creator\ProductCategoryProductAbstractCreatorInterface;
use Spryker\Zed\ProductCategory\Business\Event\ProductCategoryEventTrigger;
use Spryker\Zed\ProductCategory\Business\Event\ProductCategoryEventTriggerInterface;
use Spryker\Zed\ProductCategory\Business\Expander\ProductConcreteExpander;
use Spryker\Zed\ProductCategory\Business\Expander\ProductConcreteExpanderInterface;
use Spryker\Zed\ProductCategory\Business\Manager\ProductCategoryManager;
use Spryker\Zed\ProductCategory\Business\Model\CategoryReader;
use Spryker\Zed\ProductCategory\Business\Model\CategoryReaderInterface;
use Spryker\Zed\ProductCategory\Business\Reader\ProductCategoryReader;
use Spryker\Zed\ProductCategory\Business\Reader\ProductCategoryReaderInterface;
use Spryker\Zed\ProductCategory\Business\Updater\ProductCategoryProductAbstractUpdater;
use Spryker\Zed\ProductCategory\Business\Updater\ProductCategoryProductAbstractUpdaterInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleInterface;
use Spryker\Zed\ProductCategory\ProductCategoryDependencyProvider;

/**
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryRepositoryInterface getRepository()
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface getQueryContainer()
 */
class ProductCategoryBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\ProductCategory\Business\Manager\ProductCategoryManagerInterface
     */
    public function createProductCategoryManager()
    {
        return new ProductCategoryManager(
            $this->getQueryContainer(),
            $this->getCategoryFacade(),
            $this->getProductFacade(),
            $this->getEventFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductInterface
     */
    protected function getProductFacade()
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_PRODUCT);
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToEventInterface
     */
    protected function getEventFacade()
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_EVENT);
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface
     */
    protected function getCategoryFacade()
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_CATEGORY);
    }

    public function createCategoryReader(): CategoryReaderInterface
    {
        return new CategoryReader(
            $this->getRepository(),
            $this->getCategoryFacade(),
        );
    }

    public function createProductCategoryReader(): ProductCategoryReaderInterface
    {
        return new ProductCategoryReader(
            $this->createProductCategoryManager(),
        );
    }

    public function createProductConcreteExpander(): ProductConcreteExpanderInterface
    {
        return new ProductConcreteExpander(
            $this->getRepository(),
            $this->getCategoryFacade(),
        );
    }

    public function createProductCategoryEventTrigger(): ProductCategoryEventTriggerInterface
    {
        return new ProductCategoryEventTrigger(
            $this->getRepository(),
            $this->getEventFacade(),
        );
    }

    public function createProductCategoryProductAbstractCreator(): ProductCategoryProductAbstractCreatorInterface
    {
        return new ProductCategoryProductAbstractCreator(
            $this->createProductCategoryManager(),
        );
    }

    public function createProductCategoryProductAbstractUpdater(): ProductCategoryProductAbstractUpdaterInterface
    {
        return new ProductCategoryProductAbstractUpdater(
            $this->createProductCategoryManager(),
            $this->createCategoryReader(),
            $this->getLocaleFacade(),
        );
    }

    public function getLocaleFacade(): ProductCategoryToLocaleInterface
    {
        return $this->getProvidedDependency(ProductCategoryDependencyProvider::FACADE_LOCALE);
    }
}
