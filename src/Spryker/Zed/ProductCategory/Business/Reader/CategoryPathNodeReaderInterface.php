<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Reader;

interface CategoryPathNodeReaderInterface
{
    /**
     * @param list<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer>
     */
    public function getCategoryPathNodeCollectionsIndexedByIdProductAbstract(array $productAbstractIds): array;
}
