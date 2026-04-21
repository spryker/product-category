<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Creator;

use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\ProductCategory\Business\Manager\ProductCategoryManagerInterface;

class ProductCategoryProductAbstractCreator implements ProductCategoryProductAbstractCreatorInterface
{
    public function __construct(
        protected readonly ProductCategoryManagerInterface $productCategoryManager,
    ) {
    }

    public function createProductAbstractCategories(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer
    {
        $idProductAbstract = $productAbstractTransfer->getIdProductAbstract();
        if ($idProductAbstract === null) {
            return $productAbstractTransfer;
        }

        foreach ($productAbstractTransfer->getCategoryIds() as $idCategory) {
            $this->productCategoryManager->createProductCategoryMappings($idCategory, [$idProductAbstract]);
        }

        return $productAbstractTransfer;
    }
}
