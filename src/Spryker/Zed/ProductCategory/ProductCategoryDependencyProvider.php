<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToEventBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductBridge;
use Spryker\Zed\ProductCategory\Dependency\QueryContainer\ProductCategoryToCategoryBridge as ProductCategoryToCategoryQueryContainerBridge;
use Spryker\Zed\ProductCategory\Dependency\Service\ProductCategoryToUtilEncodingBridge;

/**
 * @method \Spryker\Zed\ProductCategory\ProductCategoryConfig getConfig()
 */
class ProductCategoryDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_LOCALE = 'locale facade';

    /**
     * @var string
     */
    public const FACADE_PRODUCT = 'product facade';

    /**
     * @var string
     */
    public const FACADE_CATEGORY = 'category facade';

    /**
     * @var string
     */
    public const FACADE_EVENT = 'facade event';

    /**
     * @var string
     */
    public const CATEGORY_QUERY_CONTAINER = 'category query container';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'util encoding service';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new ProductCategoryToLocaleBridge($container->getLocator()->locale()->facade());
        });

        $container->set(static::FACADE_PRODUCT, function (Container $container) {
            return new ProductCategoryToProductBridge($container->getLocator()->product()->facade());
        });

        $container->set(static::FACADE_EVENT, function (Container $container) {
            return new ProductCategoryToEventBridge($container->getLocator()->event()->facade());
        });

        $container = $this->addCategoryFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new ProductCategoryToLocaleBridge($container->getLocator()->locale()->facade());
        });

        $container->set(static::CATEGORY_QUERY_CONTAINER, function (Container $container) {
            return new ProductCategoryToCategoryQueryContainerBridge($container->getLocator()->category()->queryContainer());
        });

        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new ProductCategoryToUtilEncodingBridge($container->getLocator()->utilEncoding()->service());
        });

        $container = $this->addCategoryFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCategoryFacade(Container $container): Container
    {
        $container->set(static::FACADE_CATEGORY, function (Container $container) {
            return new ProductCategoryToCategoryBridge($container->getLocator()->category()->facade());
        });

        return $container;
    }
}
