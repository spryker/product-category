<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductCategory\Communication\Controller;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\Shared\ProductCategory\ProductCategoryConstants;
use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Spryker\Zed\Category\Business\Exception\CategoryUrlExistsException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\ProductCategory\Business\ProductCategoryFacade getFacade()
 * @method \Spryker\Zed\ProductCategory\Communication\ProductCategoryCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainer getQueryContainer()
 */
class AddController extends AbstractController
{

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $idParentNode = $request->get(ProductCategoryConstants::PARAM_ID_PARENT_NODE);
        if ($idParentNode) {
            $idParentNode = $this->castId($idParentNode);
        }

        $dataProvider = $this->getFactory()->createCategoryFormAddDataProvider();
        $form = $this
            ->getFactory()
            ->createCategoryFormAdd(
                $dataProvider->getData($idParentNode),
                $dataProvider->getOptions()
            )
            ->handleRequest($request);

        if ($form->isValid()) {
            $localeTransfer = $this->getFactory()
                ->getCurrentLocale();

            $categoryTransfer = $this->createCategoryTransferFromData($form->getData());
            $categoryNodeTransfer = $this->createCategoryNodeTransferFromData($form->getData());

            try {
                $idCategory = $this
                    ->getFacade()
                    ->addCategory($categoryTransfer, $categoryNodeTransfer, $localeTransfer);

                $this->addSuccessMessage('The category was added successfully.');

                return $this->redirectResponse('/product-category/edit?id-category=' . $idCategory);
            } catch (CategoryUrlExistsException $e) {
                $this->addErrorMessage($e->getMessage());
            }
        }

        return $this->viewResponse([
            'form' => $form->createView(),
            'showProducts' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function productCategoryTableAction(Request $request)
    {
        $idCategory = $this->castId($request->get(ProductCategoryConstants::PARAM_ID_CATEGORY));
        $locale = $this->getFactory()
            ->getCurrentLocale();

        $productCategoryTable = $this->getFactory()
            ->createProductCategoryTable($locale, $idCategory);

        return $this->jsonResponse(
            $productCategoryTable->fetchData()
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function productTableAction(Request $request)
    {
        $idCategory = $this->castId($request->get(ProductCategoryConstants::PARAM_ID_CATEGORY));
        $locale = $this->getFactory()
            ->getCurrentLocale();

        $productTable = $this->getFactory()
            ->createProductTable($locale, $idCategory);

        return $this->jsonResponse(
            $productTable->fetchData()
        );
    }

    /**
     * @param array $data
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function createCategoryTransferFromData(array $data)
    {
        return (new CategoryTransfer())
            ->fromArray($data, true);
    }

    /**
     * @param array $data
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    protected function createCategoryNodeTransferFromData(array $data)
    {
        return (new NodeTransfer())
            ->fromArray($data, true);
    }

}
