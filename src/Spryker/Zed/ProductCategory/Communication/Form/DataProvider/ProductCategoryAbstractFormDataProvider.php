<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Form\DataProvider;

use Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToCategoryInterface;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleInterface;

class ProductCategoryAbstractFormDataProvider implements ProductCategoryAbstractFormDataProviderInterface
{
    public function __construct(
        protected ProductCategoryToCategoryInterface $categoryFacade,
        protected ProductCategoryToLocaleInterface $localeFacade,
        protected ProductCategoryFacadeInterface $productCategoryFacade,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        $formOptions = [];
        $categoryCollectionTransfer = $this->categoryFacade->getCategoryOptionCollection(
            $this->localeFacade->getCurrentLocale(),
        );

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $formOptions[$categoryTransfer->getNameOrFail()] = $categoryTransfer->getIdCategoryOrFail();
        }

        return $formOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getData(int $idProductAbstract): array
    {
        $localeTransfer = $this->localeFacade->getCurrentLocale();
        $categoryCollectionTransfer = $this->productCategoryFacade->getCategoryTransferCollectionByIdProductAbstract(
            $idProductAbstract,
            $localeTransfer,
        );

        $categoryIds = [];

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $idCategory = $categoryTransfer->getIdCategory();

            if ($idCategory !== null) {
                $categoryIds[] = $idCategory;
            }
        }

        return $categoryIds;
    }
}
