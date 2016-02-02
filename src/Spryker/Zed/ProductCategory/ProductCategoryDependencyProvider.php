<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductCategory;

use Spryker\Zed\Propel\Communication\Plugin\Connection;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCmsBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToProductBridge;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToTouchBridge;

class ProductCategoryDependencyProvider extends AbstractBundleDependencyProvider
{

    const FACADE_CMS = 'cms facade'; //TODO: https://spryker.atlassian.net/browse/CD-540
    const FACADE_TOUCH = 'touch facade';
    const FACADE_LOCALE = 'locale facade';
    const FACADE_PRODUCT = 'product facade';
    const FACADE_CATEGORY = 'category facade';
    const CATEGORY_QUERY_CONTAINER = 'category query container';
    const PRODUCT_QUERY_CONTAINER = 'product query container';
    const PLUGIN_PROPEL_CONNECTION = 'propel connection plugin';

    /**
     * @param Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container[self::FACADE_CMS] = function (Container $container) {
            return new ProductCategoryToCmsBridge($container->getLocator()->cms()->facade());
        };

        $container[self::FACADE_TOUCH] = function (Container $container) {
            return new ProductCategoryToTouchBridge($container->getLocator()->touch()->facade());
        };

        $container[self::FACADE_LOCALE] = function (Container $container) {
            return new ProductCategoryToLocaleBridge($container->getLocator()->locale()->facade());
        };

        $container[self::FACADE_PRODUCT] = function (Container $container) {
            return new ProductCategoryToProductBridge($container->getLocator()->product()->facade());
        };

        $container[self::FACADE_CATEGORY] = function (Container $container) {
            return new ProductCategoryToCategoryBridge($container->getLocator()->category()->facade());
        };

        $container[self::CATEGORY_QUERY_CONTAINER] = function (Container $container) {
            return $container->getLocator()->category()->queryContainer();
        };

        $container[self::PRODUCT_QUERY_CONTAINER] = function (Container $container) {
            return $container->getLocator()->product()->queryContainer();
        };

        $container[self::PLUGIN_PROPEL_CONNECTION] = function () {
            return (new Connection())->get();
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container[self::FACADE_CMS] = function (Container $container) {
            return new ProductCategoryToCmsBridge($container->getLocator()->cms()->facade());
        };

        $container[self::FACADE_LOCALE] = function (Container $container) {
            return new ProductCategoryToLocaleBridge($container->getLocator()->locale()->facade());
        };

        $container[self::FACADE_PRODUCT] = function (Container $container) {
            return new ProductCategoryToProductBridge($container->getLocator()->product()->facade());
        };

        $container[self::FACADE_CATEGORY] = function (Container $container) {
            return new ProductCategoryToCategoryBridge($container->getLocator()->category()->facade());
        };

        $container[self::CATEGORY_QUERY_CONTAINER] = function (Container $container) {
            return $container->getLocator()->category()->queryContainer();
        };

        $container[self::PRODUCT_QUERY_CONTAINER] = function (Container $container) {
            return $container->getLocator()->product()->queryContainer();
        };

        $container[self::PLUGIN_PROPEL_CONNECTION] = function () {
            return (new Connection())->get();
        };

        return $container;
    }

    /**
     * @param Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function providePersistenceLayerDependencies(Container $container)
    {
        $container[self::CATEGORY_QUERY_CONTAINER] = function (Container $container) {
            return $container->getLocator()->category()->queryContainer();
        };

        return $container;
    }

}
