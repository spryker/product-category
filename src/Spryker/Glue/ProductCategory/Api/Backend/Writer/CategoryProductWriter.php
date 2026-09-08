<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Writer;

use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\ProductCategoryConditionsTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;
use Spryker\Glue\ProductCategory\Api\Backend\Exception\CategoryProductsExceptionFactory;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface;

class CategoryProductWriter implements CategoryProductWriterInterface
{
    use TransactionTrait;

    public function __construct(
        protected CategoryFacadeInterface $categoryFacade,
        protected ProductFacadeInterface $productFacade,
        protected ProductCategoryFacadeInterface $productCategoryFacade,
        protected CategoryProductsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string> $skus
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function assignProducts(string $categoryKey, array $skus): void
    {
        $idCategory = $this->resolveIdCategory($categoryKey);
        $skus = array_values(array_unique($skus));

        $productAbstractIdsIndexedBySku = $this->getProductAbstractIdsIndexedBySku($skus);

        $unknownSkus = array_values(array_diff($skus, array_keys($productAbstractIdsIndexedBySku)));
        if ($unknownSkus !== []) {
            throw $this->exceptionFactory->createUnknownSkusException($unknownSkus);
        }

        $assignedProductAbstractIds = $this->getAssignedProductAbstractIds($idCategory, array_values($productAbstractIdsIndexedBySku));
        $productAbstractIdsToAssign = array_values(array_diff($productAbstractIdsIndexedBySku, $assignedProductAbstractIds));

        if ($productAbstractIdsToAssign === []) {
            return;
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($idCategory, $productAbstractIdsToAssign): void {
            $this->productCategoryFacade->createProductCategoryMappings($idCategory, $productAbstractIdsToAssign);
        });
    }

    public function unassignAllProducts(string $categoryKey): void
    {
        $idCategory = $this->resolveIdCategory($categoryKey);

        $this->productCategoryFacade->removeAllProductMappingsForCategory($idCategory);
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string> $skus
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function unassignProducts(string $categoryKey, array $skus): void
    {
        $idCategory = $this->resolveIdCategory($categoryKey);
        $skus = array_values(array_unique($skus));

        $productAbstractIdsIndexedBySku = $this->getProductAbstractIdsIndexedBySku($skus);
        $assignedProductAbstractIds = $this->getAssignedProductAbstractIds($idCategory, array_values($productAbstractIdsIndexedBySku));

        $offendingSkus = [];
        foreach ($skus as $sku) {
            $idProductAbstract = $productAbstractIdsIndexedBySku[$sku] ?? null;
            if ($idProductAbstract === null || !in_array($idProductAbstract, $assignedProductAbstractIds, true)) {
                $offendingSkus[] = $sku;
            }
        }

        if ($offendingSkus !== []) {
            throw $this->exceptionFactory->createUnassignableSkusException($categoryKey, $offendingSkus);
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($idCategory, $productAbstractIdsIndexedBySku): void {
            $this->productCategoryFacade->removeProductCategoryMappings($idCategory, array_values($productAbstractIdsIndexedBySku));
        });
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function resolveIdCategory(string $categoryKey): int
    {
        $categoryCollectionTransfer = $this->categoryFacade->getCategoryCollection(
            (new CategoryCriteriaTransfer())->setCategoryConditions(
                (new CategoryConditionsTransfer())->addCategoryKey($categoryKey),
            ),
        );

        /** @var \Generated\Shared\Transfer\CategoryTransfer|null $categoryTransfer */
        $categoryTransfer = $categoryCollectionTransfer->getCategories()->getIterator()->current();

        if ($categoryTransfer === null) {
            throw $this->exceptionFactory->createCategoryNotFoundException();
        }

        return $categoryTransfer->getIdCategoryOrFail();
    }

    /**
     * @param array<string> $skus
     *
     * @return array<string, int>
     */
    protected function getProductAbstractIdsIndexedBySku(array $skus): array
    {
        if ($skus === []) {
            return [];
        }

        $productAbstractIdsIndexedBySku = [];
        foreach ($this->productFacade->getRawProductAbstractTransfersByAbstractSkus($skus) as $productAbstractTransfer) {
            $productAbstractIdsIndexedBySku[$productAbstractTransfer->getSkuOrFail()] = $productAbstractTransfer->getIdProductAbstractOrFail();
        }

        return $productAbstractIdsIndexedBySku;
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int>
     */
    protected function getAssignedProductAbstractIds(int $idCategory, array $productAbstractIds): array
    {
        if ($productAbstractIds === []) {
            return [];
        }

        $productCategoryCollectionTransfer = $this->productCategoryFacade->getProductCategoryCollection(
            (new ProductCategoryCriteriaTransfer())->setProductCategoryConditions(
                (new ProductCategoryConditionsTransfer())->setProductAbstractIds($productAbstractIds),
            ),
        );

        $assignedProductAbstractIds = [];
        foreach ($productCategoryCollectionTransfer->getProductCategories() as $productCategoryTransfer) {
            if ($productCategoryTransfer->getFkCategory() === $idCategory) {
                $assignedProductAbstractIds[] = $productCategoryTransfer->getFkProductAbstractOrFail();
            }
        }

        return $assignedProductAbstractIds;
    }
}
