<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Persistence;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\ProductCategoryCollectionTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;
use Generated\Shared\Transfer\ProductCategoryTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\ProductCategory\Persistence\Map\SpyProductCategoryTableMap;
use Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryPersistenceFactory getFactory()
 */
class ProductCategoryRepository extends AbstractRepository implements ProductCategoryRepositoryInterface
{
    /**
     * @var string
     */
    protected const TABLE_JOIN_CATEGORY = 'Category';

    /**
     * @uses \Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap::COL_UUID
     */
    protected const string COLUMN_UUID = 'uuid';

    /**
     * @var array<string, string>
     */
    protected const array SORTABLE_COLUMNS = [
        ProductCategoryTransfer::PRODUCT_ORDER => SpyProductCategoryTableMap::COL_PRODUCT_ORDER,
    ];

    public function getCategoryTransferCollectionByIdProductAbstract(int $idProductAbstract, int $idLocale): CategoryCollectionTransfer
    {
        $spyCategoryCollection = $this->queryCategoriesByIdProductAbstract($idProductAbstract, $idLocale)->find();

        return $this->getFactory()
            ->createCategoryMapper()
            ->mapCategoryCollection($spyCategoryCollection, new CategoryCollectionTransfer());
    }

    protected function queryCategoriesByIdProductAbstract(int $idProductAbstract, int $idLocale): SpyProductCategoryQuery
    {
        /** @var \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery $productCategoryQuery */
        $productCategoryQuery = $this->getFactory()
            ->createProductCategoryQuery()
            ->innerJoinWithSpyCategory()
            ->useSpyCategoryQuery()
                ->joinWithAttribute()
                ->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'name')
                ->addAnd(
                    SpyCategoryAttributeTableMap::COL_FK_LOCALE,
                    $idLocale,
                    Criteria::EQUAL,
                )
                ->addAscendingOrderByColumn(SpyCategoryAttributeTableMap::COL_NAME)
            ->endUse();

        return $productCategoryQuery
            ->addAnd(
                SpyProductCategoryTableMap::COL_FK_PRODUCT_ABSTRACT,
                $idProductAbstract,
                Criteria::EQUAL,
            )
            ->groupByFkCategory()
            ->groupBy(SpyCategoryAttributeTableMap::COL_NAME);
    }

    /**
     * @module Product
     *
     * @param array<int> $categoryIds
     *
     * @return array<int>
     */
    public function getProductConcreteIdsByCategoryIds(array $categoryIds): array
    {
        /** @var \Propel\Runtime\Collection\ArrayCollection $productConcreteIds */
        $productConcreteIds = $this->getFactory()
            ->createProductCategoryQuery()
            ->select(SpyProductTableMap::COL_ID_PRODUCT)
            ->filterByFkCategory_In($categoryIds)
            ->useSpyProductAbstractQuery()
                ->innerJoinSpyProduct()
            ->endUse()
            ->find();

        return $productConcreteIds->toArray();
    }

    /**
     * @module Category
     * @module Locale
     *
     * @param \Generated\Shared\Transfer\ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCollectionTransfer
     */
    public function getProductCategoryCollection(ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer): ProductCategoryCollectionTransfer
    {
        /** @var \Orm\Zed\ProductCategory\Persistence\SpyProductCategoryQuery $productCategoryQuery */
        $productCategoryQuery = $this->getFactory()
            ->createProductCategoryQuery()
            ->joinWithSpyCategory()
            ->useSpyCategoryQuery()
                ->joinWithNode()
                ->joinWithAttribute()
                ->useAttributeQuery()
                    ->joinWithLocale()
                ->endUse()
            ->endUse()
            ->setFormatter(ModelCriteria::FORMAT_ARRAY);

        $productCategoryQuery = $this->applyProductCategoryFilters($productCategoryQuery, $productCategoryCriteriaTransfer);
        $productCategoryQuery = $this->applyProductCategorySorting($productCategoryQuery, $productCategoryCriteriaTransfer);

        $productCategoryCollectionTransfer = new ProductCategoryCollectionTransfer();
        $paginationTransfer = $productCategoryCriteriaTransfer->getPagination();
        if ($paginationTransfer !== null) {
            $productCategoryQuery = $this->applyProductCategoryPagination($productCategoryQuery, $productCategoryCriteriaTransfer, $paginationTransfer);
            $productCategoryCollectionTransfer->setPagination($paginationTransfer);
        }

        return $this->getFactory()
            ->createProductCategoryMapper()
            ->mapProductCategoryArrayToProductCategoryCollectionTransfer(
                $productCategoryQuery->find(),
                $productCategoryCollectionTransfer,
            );
    }

    protected function applyProductCategoryPagination(
        SpyProductCategoryQuery $productCategoryQuery,
        ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer,
        PaginationTransfer $paginationTransfer
    ): SpyProductCategoryQuery {
        $productCategoryPageQuery = $this->getFactory()->createProductCategoryQuery();
        $productCategoryPageQuery = $this->applyProductCategoryMappingFilters($productCategoryPageQuery, $productCategoryCriteriaTransfer);
        $productCategoryPageQuery = $this->applyProductCategorySorting($productCategoryPageQuery, $productCategoryCriteriaTransfer);

        $paginationTransfer->setNbResults($productCategoryPageQuery->count());

        if ($paginationTransfer->getOffset() !== null && $paginationTransfer->getLimit() !== null) {
            $productCategoryPageQuery
                ->limit($paginationTransfer->getLimitOrFail())
                ->offset($paginationTransfer->getOffsetOrFail());
        }

        /** @var array<int> $productCategoryIds */
        $productCategoryIds = $productCategoryPageQuery
            ->select([SpyProductCategoryTableMap::COL_ID_PRODUCT_CATEGORY])
            ->find()
            ->getData();

        return $productCategoryQuery->filterByIdProductCategory_In($productCategoryIds);
    }

    protected function applyProductCategorySorting(
        SpyProductCategoryQuery $productCategoryQuery,
        ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer
    ): SpyProductCategoryQuery {
        if ($productCategoryCriteriaTransfer->getSortCollection()->count() === 0) {
            return $productCategoryQuery;
        }

        foreach ($productCategoryCriteriaTransfer->getSortCollection() as $sortTransfer) {
            $sortColumn = static::SORTABLE_COLUMNS[$sortTransfer->getFieldOrFail()] ?? null;
            if ($sortColumn === null) {
                continue;
            }

            $productCategoryQuery->orderBy($sortColumn, $sortTransfer->getIsAscending() ? Criteria::ASC : Criteria::DESC);
        }

        return $productCategoryQuery->orderByIdProductCategory(Criteria::ASC);
    }

    /**
     * @module Category
     *
     * @param array<int> $categoryNodeIds
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCollectionTransfer
     */
    public function findProductCategoryChildrenMappingsByCategoryNodeIds(array $categoryNodeIds): ProductCategoryCollectionTransfer
    {
        $productCategoryEntities = $this->getFactory()
            ->createProductCategoryQuery()
            ->useSpyCategoryQuery()
                ->useNodeQuery()
                    ->useDescendantQuery()
                        ->filterByFkCategoryNode_In($categoryNodeIds)
                    ->endUse()
                ->endUse()
            ->endUse()
            ->setFormatter(ModelCriteria::FORMAT_ARRAY)
            ->find();

        return $this->getFactory()
            ->createProductCategoryMapper()
            ->mapProductCategoryArrayToProductCategoryCollectionTransfer(
                $productCategoryEntities,
                new ProductCategoryCollectionTransfer(),
            );
    }

    protected function applyProductCategoryFilters(
        SpyProductCategoryQuery $productCategoryQuery,
        ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer
    ): SpyProductCategoryQuery {
        $productCategoryConditionsTransfer = $productCategoryCriteriaTransfer->getProductCategoryConditions();

        if (!$productCategoryConditionsTransfer) {
            return $productCategoryQuery;
        }

        $productCategoryQuery = $this->applyProductCategoryMappingFilters($productCategoryQuery, $productCategoryCriteriaTransfer);

        if ($productCategoryConditionsTransfer->getLocaleIds()) {
            $productCategoryQuery
                ->useSpyCategoryQuery()
                    ->useAttributeQuery()
                        ->filterByFkLocale_In($productCategoryConditionsTransfer->getLocaleIds())
                    ->endUse()
                ->endUse();
        }

        return $productCategoryQuery;
    }

    protected function applyProductCategoryMappingFilters(
        SpyProductCategoryQuery $productCategoryQuery,
        ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer
    ): SpyProductCategoryQuery {
        $productCategoryConditionsTransfer = $productCategoryCriteriaTransfer->getProductCategoryConditions();

        if (!$productCategoryConditionsTransfer) {
            return $productCategoryQuery;
        }

        if ($productCategoryConditionsTransfer->getProductAbstractIds()) {
            $productCategoryQuery->filterByFkProductAbstract_In($productCategoryConditionsTransfer->getProductAbstractIds());
        }

        if ($productCategoryConditionsTransfer->getCategoryIds()) {
            $productCategoryQuery->filterByFkCategory_In($productCategoryConditionsTransfer->getCategoryIds());
        }

        return $productCategoryQuery;
    }

    /**
     * @module Category
     *
     * @param array<int> $categoryIds
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCollectionTransfer
     */
    public function findProductCategoryByCategoryIds(array $categoryIds): ProductCategoryCollectionTransfer
    {
        $categoryNodes = $this->getFactory()->createCategoryNodeQuery()
            ->filterByFkCategory_In($categoryIds)
            ->setFormatter(ModelCriteria::FORMAT_ARRAY)
            ->find()
            ->getData();

        $categoryNodeIds = array_column($categoryNodes, 'id_category_node');

        return $this->findProductCategoryChildrenMappingsByCategoryNodeIds($categoryNodeIds);
    }

    /**
     * @module Category
     *
     * @param array<string> $uuids
     *
     * @return array<int>
     */
    public function getCategoryIdsByUuids(array $uuids): array
    {
        if (!$this->isCategoryUuidSupported()) {
            return [];
        }

        return $this->getFactory()
            ->createCategoryQuery()
            ->filterByUuid_In($uuids)
            ->select([SpyCategoryTableMap::COL_ID_CATEGORY])
            ->find()
            ->getData();
    }

    protected function isCategoryUuidSupported(): bool
    {
        return SpyCategoryTableMap::getTableMap()->hasColumn(static::COLUMN_UUID);
    }
}
