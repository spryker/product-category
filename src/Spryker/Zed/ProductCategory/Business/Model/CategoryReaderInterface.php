<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Model;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\LocaleTransfer;

interface CategoryReaderInterface
{
    public function getCategoryTransferCollectionByIdProductAbstract(int $idProductAbstract, LocaleTransfer $localeTransfer): CategoryCollectionTransfer;
}
