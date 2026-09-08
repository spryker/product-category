<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Reader;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PaginationTransfer;

interface CategoryProductReaderInterface
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return array<array{sku: string, name: string|null, position: int|null}>
     */
    public function getAssignedProductItems(string $categoryKey, PaginationTransfer $paginationTransfer, ?LocaleTransfer $localeTransfer = null): array;

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return array{sku: string, name: string|null, position: int|null}
     */
    public function getAssignedProductItem(string $categoryKey, string $sku, ?LocaleTransfer $localeTransfer = null): array;
}
