<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\ProductCategory\Communication\Form\DataProvider;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use Spryker\Zed\ProductCategory\Communication\Form\CategoryFormAdd;

class AbstractCategoryFormDataProvider
{

    const PK_CATEGORY = 'id_category';

    /**
     * @var CategoryQueryContainerInterface
     */
    protected $categoryQueryContainer;

    /**
     * @var LocaleTransfer
     */
    protected $locale;

    /**
     * @param CategoryQueryContainerInterface $categoryQueryContainer
     * @param LocaleTransfer $locale
     */
    public function __construct(CategoryQueryContainerInterface $categoryQueryContainer, LocaleTransfer $locale)
    {
        $this->categoryQueryContainer = $categoryQueryContainer;
        $this->locale = $locale;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $formOptions = [
            CategoryFormAdd::OPTION_PARENT_CATEGORY_NODE_CHOICES => $this->getCategoriesWithPaths($this->locale->getIdLocale()),
        ];

        return $formOptions;
    }

    /**
     * @param int $idLocale
     *
     * @return array
     */
    protected function getCategoriesWithPaths($idLocale)
    {
        $categoryEntityList = $this->categoryQueryContainer
            ->queryCategory($this->locale->getIdLocale())
            ->find();

        $categories = [];
        $pathCache = [];
        foreach ($categoryEntityList as $categoryEntity) {
            foreach ($categoryEntity->getNodes() as $nodeEntity) {
                if (!array_key_exists($nodeEntity->getFkParentCategoryNode(), $pathCache)) {
                    $path = $this->buildPath($nodeEntity);
                } else {
                    $path = $pathCache[$nodeEntity->getFkParentCategoryNode()];
                }

                $categories[$path][$nodeEntity->getIdCategoryNode()] = $categoryEntity
                    ->getLocalisedAttributes($idLocale)
                    ->getFirst()
                    ->getName();
            }
        }

        $categories = $this->sortCategoriesWithPaths($categories);

        return $categories;
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    protected function sortCategoriesWithPaths(array $categories)
    {
        ksort($categories);

        foreach ($categories as $path => $categoryNames) {
            asort($categories[$path], SORT_FLAG_CASE & SORT_STRING);
        }

        return $categories;
    }

    /**
     * @param SpyCategoryNode $node
     *
     * @return string
     */
    protected function buildPath(SpyCategoryNode $node)
    {
        $pathTokens = $this->categoryQueryContainer
            ->queryPath($node->getIdCategoryNode(), $this->locale->getIdLocale(), false, true)
            ->find();

        $formattedPath = [];
        foreach ($pathTokens as $path) {
            $formattedPath[] = $path['name'];
        }

        return '/' . implode('/', $formattedPath);
    }

}