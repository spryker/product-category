<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Dependency\Facade;

use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;

class ProductCategoryToCategoryBridge implements ProductCategoryToCategoryInterface
{
    /**
     * @var \Spryker\Zed\Category\Business\CategoryFacadeInterface
     */
    protected $categoryFacade;

    /**
     * @param \Spryker\Zed\Category\Business\CategoryFacadeInterface $categoryFacade
     */
    public function __construct($categoryFacade)
    {
        $this->categoryFacade = $categoryFacade;
    }

    public function touchCategoryActive(int $idCategory): void
    {
        $this->categoryFacade->touchCategoryActive($idCategory);
    }

    public function getNodePath(int $idNode, LocaleTransfer $localeTransfer): string
    {
        return $this->categoryFacade->getNodePath($idNode, $localeTransfer);
    }

    public function getCategoryListUrl(): string
    {
        return $this->categoryFacade->getCategoryListUrl();
    }

    public function findCategory(CategoryCriteriaTransfer $categoryCriteriaTransfer): ?CategoryTransfer
    {
        return $this->categoryFacade->findCategory($categoryCriteriaTransfer);
    }
}
