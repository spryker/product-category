<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Business\Reader;

use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;
use Generated\Shared\Transfer\CategoryPathNodeTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\Zed\ProductCategory\Dependency\Facade\ProductCategoryToLocaleInterface;
use Spryker\Zed\ProductCategory\Persistence\ProductCategoryRepositoryInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class CategoryPathNodeReader implements CategoryPathNodeReaderInterface
{
    public function __construct(
        protected ProductCategoryRepositoryInterface $productCategoryRepository,
        protected ProductCategoryToLocaleInterface $localeFacade,
        protected StoreFacadeInterface $storeFacade
    ) {
    }

    /**
     * @param list<int> $productAbstractIds
     *
     * @return array<int, \Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer>
     */
    public function getCategoryPathNodeCollectionsIndexedByIdProductAbstract(array $productAbstractIds): array
    {
        if (!$productAbstractIds) {
            return [];
        }

        if (!$this->storeFacade->isCurrentStoreDefined()) {
            return [];
        }

        $categoryNodeIdsIndexedByIdProductAbstract = $this->productCategoryRepository->getCategoryNodeIdsIndexedByIdProductAbstract($productAbstractIds);

        if (!$categoryNodeIdsIndexedByIdProductAbstract) {
            return [];
        }

        $nodeTransfersIndexedByIdCategoryNode = $this->getActiveNodeTransfersOfCurrentStoreIndexedByIdCategoryNode(
            array_merge(...array_values($categoryNodeIdsIndexedByIdProductAbstract)),
        );
        $categoryNames = $this->getCategoryNames($nodeTransfersIndexedByIdCategoryNode, $this->localeFacade->getCurrentLocale()->getIdLocaleOrFail());

        $categoryPathNodeCollectionTransfers = [];

        foreach ($categoryNodeIdsIndexedByIdProductAbstract as $idProductAbstract => $categoryNodeIds) {
            $categoryPathNodeCollectionTransfers[$idProductAbstract] = $this->createCategoryPathNodeCollection(
                $categoryNodeIds,
                $nodeTransfersIndexedByIdCategoryNode,
                $categoryNames,
            );
        }

        return $categoryPathNodeCollectionTransfers;
    }

    /**
     * The product's category nodes and the nodes above them, without the root node. Storage publishes only active categories of a store,
     * so the cart path is built from the same categories as on the product pages.
     *
     * @param list<int> $categoryNodeIds
     *
     * @return array<int, \Generated\Shared\Transfer\NodeTransfer>
     */
    protected function getActiveNodeTransfersOfCurrentStoreIndexedByIdCategoryNode(array $categoryNodeIds): array
    {
        return $this->productCategoryRepository->getActiveCategoryNodesWithAncestorsIndexedByIdCategoryNode(
            array_values(array_unique($categoryNodeIds)),
            $this->storeFacade->getCurrentStore()->getIdStoreOrFail(),
        );
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\NodeTransfer> $nodeTransfersIndexedByIdCategoryNode
     *
     * @return array<int, string>
     */
    protected function getCategoryNames(array $nodeTransfersIndexedByIdCategoryNode, int $idLocale): array
    {
        $categoryIds = [];

        foreach ($nodeTransfersIndexedByIdCategoryNode as $nodeTransfer) {
            $categoryIds[$nodeTransfer->getFkCategoryOrFail()] = $nodeTransfer->getFkCategoryOrFail();
        }

        return $this->productCategoryRepository->getCategoryNamesIndexedByIdCategory(array_values($categoryIds), $idLocale);
    }

    /**
     * @param list<int> $categoryNodeIds The category nodes the product is assigned to.
     * @param array<int, \Generated\Shared\Transfer\NodeTransfer> $nodeTransfersIndexedByIdCategoryNode
     * @param array<int, string> $categoryNames
     */
    protected function createCategoryPathNodeCollection(
        array $categoryNodeIds,
        array $nodeTransfersIndexedByIdCategoryNode,
        array $categoryNames
    ): CategoryPathNodeCollectionTransfer {
        $categoryPathNodeCollectionTransfer = new CategoryPathNodeCollectionTransfer();
        $addedCategoryIds = [];

        foreach ($categoryNodeIds as $idCategoryNode) {
            $nodeTransfers = $this->getNodeTransferWithItsParents($idCategoryNode, $nodeTransfersIndexedByIdCategoryNode);

            foreach ($nodeTransfers as $nodeTransfer) {
                $idCategory = $nodeTransfer->getFkCategoryOrFail();

                // Two categories of the product can share the categories above them; each category is added once.
                if (isset($addedCategoryIds[$idCategory])) {
                    continue;
                }

                $parentNodeTransfer = $nodeTransfersIndexedByIdCategoryNode[$nodeTransfer->getFkParentCategoryNode()] ?? null;
                $categoryPathNodeCollectionTransfer->addNode($this->createCategoryPathNode($nodeTransfer, $parentNodeTransfer, $categoryNames));
                $addedCategoryIds[$idCategory] = true;
            }
        }

        return $categoryPathNodeCollectionTransfer;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\NodeTransfer> $nodeTransfersIndexedByIdCategoryNode
     *
     * @return list<\Generated\Shared\Transfer\NodeTransfer> The node first, then its parent, its grandparent and so on.
     */
    protected function getNodeTransferWithItsParents(int $idCategoryNode, array $nodeTransfersIndexedByIdCategoryNode): array
    {
        $nodeTransfers = [];
        $nodeTransfer = $nodeTransfersIndexedByIdCategoryNode[$idCategoryNode] ?? null;

        // The walk ends at a node that is not among the given nodes: the root, an inactive category or one outside the current store.
        // A node already in the list ends it too, so a broken tree cannot loop forever.
        while ($nodeTransfer !== null && !isset($nodeTransfers[$nodeTransfer->getIdCategoryNodeOrFail()])) {
            $nodeTransfers[$nodeTransfer->getIdCategoryNodeOrFail()] = $nodeTransfer;
            $nodeTransfer = $nodeTransfersIndexedByIdCategoryNode[$nodeTransfer->getFkParentCategoryNode()] ?? null;
        }

        return array_values($nodeTransfers);
    }

    /**
     * @param array<int, string> $categoryNames
     */
    protected function createCategoryPathNode(NodeTransfer $nodeTransfer, ?NodeTransfer $parentNodeTransfer, array $categoryNames): CategoryPathNodeTransfer
    {
        $parentCategoryIds = [];

        if ($parentNodeTransfer !== null) {
            $parentCategoryIds[] = $parentNodeTransfer->getFkCategoryOrFail();
        }

        return (new CategoryPathNodeTransfer())
            ->setIdCategory($nodeTransfer->getFkCategoryOrFail())
            ->setName($categoryNames[$nodeTransfer->getFkCategoryOrFail()] ?? null)
            ->setParentCategoryIds($parentCategoryIds);
    }
}
