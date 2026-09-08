<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Processor;

use ApiPlatform\Metadata\HttpOperation;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Glue\ProductCategory\Api\Backend\Exception\CategoryProductsExceptionFactory;
use Spryker\Glue\ProductCategory\Api\Backend\Writer\CategoryProductWriterInterface;

class CategoryProductsBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_CATEGORY_KEY = 'categoryKey';

    protected const string URI_TEMPLATE_SUFFIX_UNASSIGN = '/unassign-products';

    public function __construct(
        protected CategoryProductWriterInterface $categoryProductWriter,
        protected CategoryProductsExceptionFactory $exceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        /** @var \Generated\Api\Backend\CategoriesProductsBackendResource $categoriesProductsBackendResource */
        $categoriesProductsBackendResource = $data;

        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);

        if ($this->isUnassignOperation()) {
            $this->processUnassign($categoryKey, $categoriesProductsBackendResource->skus, $categoriesProductsBackendResource->all === true);

            return null;
        }

        $this->processAssign($categoryKey, $categoriesProductsBackendResource->skus, $categoriesProductsBackendResource->all);

        return null;
    }

    protected function isUnassignOperation(): bool
    {
        $operation = $this->getOperation();

        return $operation instanceof HttpOperation
            && str_ends_with((string)$operation->getUriTemplate(), static::URI_TEMPLATE_SUFFIX_UNASSIGN);
    }

    /**
     * @param array<mixed>|null $rawSkus
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processUnassign(string $categoryKey, ?array $rawSkus, bool $isAll): void
    {
        if (($rawSkus !== null) === $isAll) {
            throw $this->exceptionFactory->createInvalidUnassignRequestException();
        }

        if ($isAll) {
            $this->categoryProductWriter->unassignAllProducts($categoryKey);

            return;
        }

        $skus = $this->extractSkus((array)$rawSkus);
        if ($skus === []) {
            throw $this->exceptionFactory->createInvalidUnassignRequestException();
        }

        $this->categoryProductWriter->unassignProducts($categoryKey, $skus);
    }

    /**
     * @param array<mixed>|null $rawSkus
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processAssign(string $categoryKey, ?array $rawSkus, ?bool $all): void
    {
        if ($all !== null || $rawSkus === null) {
            throw $this->exceptionFactory->createInvalidAssignRequestException();
        }

        $skus = $this->extractSkus($rawSkus);
        if ($skus === []) {
            throw $this->exceptionFactory->createInvalidAssignRequestException();
        }

        $this->categoryProductWriter->assignProducts($categoryKey, $skus);
    }

    /**
     * @param array<mixed> $skus
     *
     * @return array<string>
     */
    protected function extractSkus(array $skus): array
    {
        $cleanedSkus = [];
        foreach ($skus as $sku) {
            $sku = trim((string)$sku);
            if ($sku !== '') {
                $cleanedSkus[] = $sku;
            }
        }

        return $cleanedSkus;
    }
}
