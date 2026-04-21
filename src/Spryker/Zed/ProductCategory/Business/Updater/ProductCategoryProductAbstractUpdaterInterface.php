<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Updater;

use Generated\Shared\Transfer\ProductAbstractTransfer;

interface ProductCategoryProductAbstractUpdaterInterface
{
    public function updateProductAbstractCategories(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer;
}
