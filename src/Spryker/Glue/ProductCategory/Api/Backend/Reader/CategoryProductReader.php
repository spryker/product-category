<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ProductCategory\Api\Backend\Reader;

use ArrayObject;
use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\ProductAbstractConditionsTransfer;
use Generated\Shared\Transfer\ProductAbstractCriteriaTransfer;
use Generated\Shared\Transfer\ProductAbstractRelationsTransfer;
use Generated\Shared\Transfer\ProductCategoryCollectionTransfer;
use Generated\Shared\Transfer\ProductCategoryConditionsTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;
use Generated\Shared\Transfer\ProductCategoryTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Spryker\Glue\ProductCategory\Api\Backend\Exception\CategoryProductsExceptionFactory;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use Spryker\Zed\Product\Business\ProductFacadeInterface;
use Spryker\Zed\ProductCategory\Business\ProductCategoryFacadeInterface;

class CategoryProductReader implements CategoryProductReaderInterface
{
    public function __construct(
        protected CategoryFacadeInterface $categoryFacade,
        protected ProductCategoryFacadeInterface $productCategoryFacade,
        protected ProductFacadeInterface $productFacade,
        protected LocaleFacadeInterface $localeFacade,
        protected CategoryProductsExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array{sku: string, name: string|null, position: int|null}>
     */
    public function getAssignedProductItems(string $categoryKey, PaginationTransfer $paginationTransfer, ?LocaleTransfer $localeTransfer = null): array
    {
        $idCategory = $this->resolveIdCategory($categoryKey);

        $productCategoryCriteriaTransfer = $this->createProductCategoryCriteriaTransfer($idCategory)
            ->setPagination($paginationTransfer)
            ->addSort((new SortTransfer())->setField(ProductCategoryTransfer::PRODUCT_ORDER)->setIsAscending(true));

        $productCategoryCollectionTransfer = $this->productCategoryFacade->getProductCategoryCollection($productCategoryCriteriaTransfer);

        if ($productCategoryCollectionTransfer->getPagination() !== null) {
            $paginationTransfer->fromArray($productCategoryCollectionTransfer->getPaginationOrFail()->toArray(), true);
        }

        return $this->mapProductCategoryCollectionToItems($productCategoryCollectionTransfer, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return array{sku: string, name: string|null, position: int|null}
     */
    public function getAssignedProductItem(string $categoryKey, string $sku, ?LocaleTransfer $localeTransfer = null): array
    {
        $idCategory = $this->resolveIdCategory($categoryKey);

        $idProductAbstract = $this->productFacade->findProductAbstractIdBySku($sku);
        if ($idProductAbstract === null) {
            throw $this->exceptionFactory->createProductAssignmentNotFoundException($categoryKey, $sku);
        }

        $productCategoryCriteriaTransfer = $this->createProductCategoryCriteriaTransfer($idCategory);
        $productCategoryCriteriaTransfer->getProductCategoryConditionsOrFail()->addIdProductAbstract($idProductAbstract);

        $items = $this->mapProductCategoryCollectionToItems(
            $this->productCategoryFacade->getProductCategoryCollection($productCategoryCriteriaTransfer),
            $localeTransfer,
        );

        if ($items === []) {
            throw $this->exceptionFactory->createProductAssignmentNotFoundException($categoryKey, $sku);
        }

        return $items[0];
    }

    protected function createProductCategoryCriteriaTransfer(int $idCategory): ProductCategoryCriteriaTransfer
    {
        return (new ProductCategoryCriteriaTransfer())->setProductCategoryConditions(
            (new ProductCategoryConditionsTransfer())->addIdCategory($idCategory),
        );
    }

    /**
     * @return array<array{sku: string, name: string|null, position: int|null}>
     */
    protected function mapProductCategoryCollectionToItems(
        ProductCategoryCollectionTransfer $productCategoryCollectionTransfer,
        ?LocaleTransfer $localeTransfer
    ): array {
        $productAbstractIds = [];
        foreach ($productCategoryCollectionTransfer->getProductCategories() as $productCategoryTransfer) {
            $productAbstractIds[] = $productCategoryTransfer->getFkProductAbstractOrFail();
        }

        if ($productAbstractIds === []) {
            return [];
        }

        $localeName = ($localeTransfer ?? $this->localeFacade->getCurrentLocale())->getLocaleNameOrFail();
        $productAbstractTransfersIndexedByIdProductAbstract = $this->getProductAbstractTransfersIndexedByIdProductAbstract($productAbstractIds);

        $items = [];
        foreach ($productCategoryCollectionTransfer->getProductCategories() as $productCategoryTransfer) {
            $productAbstractTransfer = $productAbstractTransfersIndexedByIdProductAbstract[$productCategoryTransfer->getFkProductAbstractOrFail()] ?? null;
            if ($productAbstractTransfer === null) {
                continue;
            }

            $items[] = [
                'sku' => $productAbstractTransfer->getSkuOrFail(),
                'name' => $this->findLocalizedName($productAbstractTransfer->getLocalizedAttributes(), $localeName),
                'position' => $productCategoryTransfer->getProductOrder(),
            ];
        }

        return $items;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\LocalizedAttributesTransfer> $localizedAttributesTransfers
     */
    protected function findLocalizedName(ArrayObject $localizedAttributesTransfers, string $localeName): ?string
    {
        foreach ($localizedAttributesTransfers as $localizedAttributesTransfer) {
            if ($localizedAttributesTransfer->getLocale()?->getLocaleName() === $localeName) {
                return $localizedAttributesTransfer->getName();
            }
        }

        return null;
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\ProductAbstractTransfer>
     */
    protected function getProductAbstractTransfersIndexedByIdProductAbstract(array $productAbstractIds): array
    {
        $productAbstractCollectionTransfer = $this->productFacade->getProductAbstractCollection(
            (new ProductAbstractCriteriaTransfer())
                ->setProductAbstractConditions((new ProductAbstractConditionsTransfer())->setIds($productAbstractIds))
                ->setProductAbstractRelations((new ProductAbstractRelationsTransfer())->setWithLocalizedAttributes(true)),
        );

        $productAbstractTransfersIndexedByIdProductAbstract = [];
        foreach ($productAbstractCollectionTransfer->getProductAbstracts() as $productAbstractTransfer) {
            $productAbstractTransfersIndexedByIdProductAbstract[$productAbstractTransfer->getIdProductAbstractOrFail()] = $productAbstractTransfer;
        }

        return $productAbstractTransfersIndexedByIdProductAbstract;
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
}
