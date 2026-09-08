<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Writer;

interface CategoryProductWriterInterface
{
    /**
     * @param array<string> $skus
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function assignProducts(string $categoryKey, array $skus): void;

    /**
     * @param array<string> $skus
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function unassignProducts(string $categoryKey, array $skus): void;

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function unassignAllProducts(string $categoryKey): void;
}
