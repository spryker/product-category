<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Persistence;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\ProductCategoryCollectionTransfer;
use Generated\Shared\Transfer\ProductCategoryCriteriaTransfer;

interface ProductCategoryRepositoryInterface
{
    public function getCategoryTransferCollectionByIdProductAbstract(int $idProductAbstract, int $idLocale): CategoryCollectionTransfer;

    /**
     * @param array<int> $categoryIds
     *
     * @return array<int>
     */
    public function getProductConcreteIdsByCategoryIds(array $categoryIds): array;

    public function getProductCategoryCollection(ProductCategoryCriteriaTransfer $productCategoryCriteriaTransfer): ProductCategoryCollectionTransfer;

    /**
     * @param array<int> $categoryNodeIds
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCollectionTransfer
     */
    public function findProductCategoryChildrenMappingsByCategoryNodeIds(array $categoryNodeIds): ProductCategoryCollectionTransfer;

    /**
     * @param array<int> $categoryIds
     *
     * @return \Generated\Shared\Transfer\ProductCategoryCollectionTransfer
     */
    public function findProductCategoryByCategoryIds(array $categoryIds): ProductCategoryCollectionTransfer;

    /**
     * @param array<string> $uuids
     *
     * @return array<int>
     */
    public function getCategoryIdsByUuids(array $uuids): array;

    /**
     * Returns the category nodes of the categories the given abstract products are assigned to.
     *
     * @param list<int> $productAbstractIds
     *
     * @return array<int, list<int>>
     */
    public function getCategoryNodeIdsIndexedByIdProductAbstract(array $productAbstractIds): array;

    /**
     * Returns the given category nodes and the nodes above them, except the root node, of active categories of the given store.
     *
     * @param list<int> $categoryNodeIds
     *
     * @return array<int, \Generated\Shared\Transfer\NodeTransfer>
     */
    public function getActiveCategoryNodesWithAncestorsIndexedByIdCategoryNode(array $categoryNodeIds, int $idStore): array;

    /**
     * @param list<int> $categoryIds
     *
     * @return array<int, string>
     */
    public function getCategoryNamesIndexedByIdCategory(array $categoryIds, int $idLocale): array;
}
