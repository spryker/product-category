<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductCategory;

use Codeception\Actor;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\ProductCategoryConditionsTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;
use Orm\Zed\Category\Persistence\SpyCategoryAttribute;
use Orm\Zed\Locale\Persistence\SpyLocale;
use Orm\Zed\Locale\Persistence\SpyLocaleQuery;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Orm\Zed\Product\Persistence\SpyProductAbstractLocalizedAttributes;

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
class ProductCategoryPersistenceTester extends Actor
{
    use _generated\ProductCategoryPersistenceTesterActions;

    /**
     * @var string
     */
    protected const ATTRIBUTES = '[]';

    /**
     * @param string $sku
     * @param string $attributeName
     * @param \Orm\Zed\Locale\Persistence\SpyLocale $localeEntity
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstract
     */
    public function createProductAbstractEntity(string $sku, string $attributeName, SpyLocale $localeEntity): SpyProductAbstract
    {
        $productAbstractLocalizedAttributesEntity = new SpyProductAbstractLocalizedAttributes();
        $productAbstractLocalizedAttributesEntity
            ->setName($attributeName)
            ->setAttributes(static::ATTRIBUTES)
            ->setLocale($localeEntity);

        $productAbstractEntity = new SpyProductAbstract();
        $productAbstractEntity
            ->setSku($sku)
            ->setAttributes(static::ATTRIBUTES)
            ->addSpyProductAbstractLocalizedAttributes($productAbstractLocalizedAttributesEntity)
            ->save();

        return $productAbstractEntity;
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
     * @param string $sku
     * @param array $categoriesData
     * @param \Orm\Zed\Locale\Persistence\SpyLocale $localeEntity
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstract
     */
    public function createProductAbstractEntityWithCategories(string $sku, array $categoriesData, SpyLocale $localeEntity): SpyProductAbstract
    {
        $productAbstractEntity = $this->createProductAbstractEntity($sku, $sku, $localeEntity);

        foreach ($categoriesData as $categoryName => $attributeNames) {
            $categoryEntity = $this->haveCategory([
                CategoryTransfer::NAME => $categoryName,
            ]);
            $this->createCategoryAttributesEntities($categoryEntity->getIdCategory(), $attributeNames, $localeEntity);
            $this->assignProductToCategory($categoryEntity->getIdCategory(), $productAbstractEntity->getIdProductAbstract());
        }

        return $productAbstractEntity;
    }

    /**
     * @param string $sku
     * @param string $productLocale
     * @param array<string> $localeNamesForCategory
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstract
     */
    public function createProductAbstractEntityWithCategoryWithLocalizedAttributes(
        string $sku,
        string $productLocale,
        array $localeNamesForCategory
    ): SpyProductAbstract {
        $productAbstractEntity = $this->createProductAbstractEntity($sku, $sku, $this->createLocaleEntity($productLocale));
        $categoryTransfer = $this->haveCategory();
        $this->assignProductToCategory($categoryTransfer->getIdCategory(), $productAbstractEntity->getIdProductAbstract());

        foreach ($localeNamesForCategory as $localeName) {
            $localeTransfer = $this->haveLocale([
                LocaleTransfer::LOCALE_NAME => $localeName,
            ]);
            $categoryLocalizedAttributesTransfer = $this->haveCategoryLocalizedAttributeForCategory(
                $categoryTransfer->getIdCategory(),
                [
                    LocalizedAttributesTransfer::NAME => 'test_' . $localeName,
                    LocalizedAttributesTransfer::LOCALE => $localeTransfer,
                ],
            );

            $categoryTransfer->addLocalizedAttributes($categoryLocalizedAttributesTransfer);
        }

        return $productAbstractEntity;
    }

    /**
     * @param int $idProductAbstract
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCriteriaTransfer
     */
    public function createProductCategoryCriteriaTransferBy(int $idProductAbstract): ProductCategoryCriteriaTransfer
    {
        $productCategoryConditionsTransfer = (new ProductCategoryConditionsTransfer())
            ->addIdProductAbstract($idProductAbstract);

        return (new ProductCategoryCriteriaTransfer())
            ->setProductCategoryConditions($productCategoryConditionsTransfer);
    }

    /**
     * @param int $idCategory
     * @param array $attributesNames
     * @param \Orm\Zed\Locale\Persistence\Base\SpyLocale $spyLocale
     *
     * @return void
     */
    protected function createCategoryAttributesEntities(int $idCategory, array $attributesNames, SpyLocale $spyLocale): void
    {
        foreach ($attributesNames as $attributeName) {
            $categoryAttributeEntity = new SpyCategoryAttribute();
            $categoryAttributeEntity->setName($attributeName);
            $categoryAttributeEntity->setFkCategory($idCategory);
            $categoryAttributeEntity->setLocale($spyLocale);

            $categoryAttributeEntity->save();
        }
    }
}
