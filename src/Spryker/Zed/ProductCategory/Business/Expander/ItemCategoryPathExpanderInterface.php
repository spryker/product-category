<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Expander;

use Generated\Shared\Transfer\CartChangeTransfer;

interface ItemCategoryPathExpanderInterface
{
    public function expandItems(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer;
}
