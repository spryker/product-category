<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Creator;

use Generated\Shared\Transfer\ProductAbstractTransfer;

interface ProductCategoryProductAbstractCreatorInterface
{
    public function createProductAbstractCategories(ProductAbstractTransfer $productAbstractTransfer): ProductAbstractTransfer;
}
