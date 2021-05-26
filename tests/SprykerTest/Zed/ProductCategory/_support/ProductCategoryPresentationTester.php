<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory;

use Codeception\Actor;
use Codeception\Scenario;
use Orm\Zed\Locale\Persistence\SpyLocale;
use Orm\Zed\Locale\Persistence\SpyLocaleQuery;
use Orm\Zed\Product\Persistence\SpyProduct;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Orm\Zed\Product\Persistence\SpyProductAbstractLocalizedAttributes;
use Orm\Zed\Product\Persistence\SpyProductLocalizedAttributes;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategory;
use SprykerTest\Zed\ProductCategory\PageObject\ProductCategoryAssignPage;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class ProductCategoryPresentationTester extends Actor
{
    use _generated\ProductCategoryPresentationTesterActions;

    /**
     * @param \Codeception\Scenario $scenario
     */
    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);

        $this->amZed();
        $this->amLoggedInUser();
    }

    /**
     * @param string $name
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstract
     */
    public function createProductEntity(string $name): SpyProductAbstract
    {
        $localeEntity = $this->createLocaleEntity('en_US');

        $productLocalizedAttributes = new SpyProductLocalizedAttributes();
        $productLocalizedAttributes
            ->setName($name)
            ->setAttributes('[]')
            ->setLocale($localeEntity);

        $productEntity = new SpyProduct();
        $productEntity
            ->setSku($name)
            ->setAttributes('[]')
            ->addSpyProductLocalizedAttributes($productLocalizedAttributes);

        $productAbstractLocalizedAttributesEntity = new SpyProductAbstractLocalizedAttributes();
        $productAbstractLocalizedAttributesEntity
            ->setName($name)
            ->setAttributes('[]')
            ->setLocale($localeEntity);

        $productAbstractEntity = new SpyProductAbstract();
        $productAbstractEntity
            ->setSku($name)
            ->setAttributes('[]')
            ->addSpyProduct($productEntity)
            ->addSpyProductAbstractLocalizedAttributes($productAbstractLocalizedAttributesEntity)
            ->save();

        return $productAbstractEntity;
    }

    /**
     * @param int $idCategory
     * @param int $idProductAbstract
     *
     * @return void
     */
    public function assignProductToCategory(int $idCategory, int $idProductAbstract): void
    {
        $spyProductCategory = new SpyProductCategory();
        $spyProductCategory
            ->setFkCategory($idCategory)
            ->setFkProductAbstract($idProductAbstract)
            ->save();
    }

    /**
     * @param string $localeName
     *
     * @return \Orm\Zed\Locale\Persistence\SpyLocale
     */
    public function createLocaleEntity(string $localeName): SpyLocale
    {
        $localeEntity = SpyLocaleQuery::create()
            ->filterByLocaleName($localeName)
            ->findOneOrCreate();

        $localeEntity->save();

        return $localeEntity;
    }

    /**
     * @param string $productName
     *
     * @return void
     */
    public function searchTableByProductName(string $productName): void
    {
        $this->fillField(ProductCategoryAssignPage::SELECTOR_TABLE_SEARCH, $productName);
        $this->wait(3);
    }
}
