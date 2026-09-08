<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Provider;

use Generated\Api\Backend\CategoriesProductsBackendResource;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Glue\ProductCategory\Api\Backend\Reader\CategoryProductReaderInterface;

class CategoryProductsBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_CATEGORY_KEY = 'categoryKey';

    protected const string URI_VARIABLE_SKU = 'sku';

    public function __construct(protected CategoryProductReaderInterface $categoryProductReader)
    {
    }

    protected function provideItem(): ?object
    {
        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);
        $sku = (string)$this->getUriVariable(static::URI_VARIABLE_SKU);

        $item = $this->categoryProductReader->getAssignedProductItem(
            $categoryKey,
            $sku,
            $this->hasLocale() ? $this->getLocale() : null,
        );

        return $this->createResource($categoryKey, $item);
    }

    /**
     * @return array<\Generated\Api\Backend\CategoriesProductsBackendResource>
     */
    protected function provideCollection(): array
    {
        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);
        $paginationTransfer = $this->buildPaginationTransfer();

        $items = $this->categoryProductReader->getAssignedProductItems(
            $categoryKey,
            $paginationTransfer,
            $this->hasLocale() ? $this->getLocale() : null,
        );

        $resources = [];
        foreach ($items as $item) {
            $resources[] = $this->createResource($categoryKey, $item);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? count($items),
        );

        return $resources;
    }

    /**
     * @param array{sku: string, name: string|null, position: int|null}|array<string, mixed> $item
     */
    protected function createResource(string $categoryKey, array $item): CategoriesProductsBackendResource
    {
        $categoriesProductsBackendResource = new CategoriesProductsBackendResource();
        $categoriesProductsBackendResource->categoryKey = $categoryKey;
        $categoriesProductsBackendResource->sku = $item['sku'];
        $categoriesProductsBackendResource->name = $item['name'];
        $categoriesProductsBackendResource->position = $item['position'];

        return $categoriesProductsBackendResource;
    }
}
