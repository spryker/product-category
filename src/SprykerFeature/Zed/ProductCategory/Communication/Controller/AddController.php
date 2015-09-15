<?php

namespace SprykerFeature\Zed\ProductCategory\Communication\Controller;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use SprykerFeature\Zed\ProductCategory\Business\ProductCategoryFacade;
use SprykerFeature\Zed\Application\Communication\Controller\AbstractController;
use SprykerFeature\Zed\ProductCategory\Communication\ProductCategoryDependencyContainer;
use SprykerFeature\Zed\ProductCategory\Persistence\ProductCategoryQueryContainer;
use SprykerFeature\Zed\ProductCategory\ProductCategoryConfig;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @method ProductCategoryFacade getFacade()
 * @method ProductCategoryDependencyContainer getDependencyContainer()
 * @method ProductCategoryQueryContainer getQueryContainer()
 */
class AddController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        /**
         * @var Form $form
         */
        $form = $this->getDependencyContainer()
            ->createCategoryFormAdd()
        ;
        $form->handleRequest();

        if ($form->isValid()) {
            $locale = $this->getDependencyContainer()
                ->createCurrentLocale()
            ;

            $categoryTransfer = (new CategoryTransfer())
                ->fromArray($form->getData(), true)
            ;

            $idCategory = $this->getDependencyContainer()
                ->createCategoryFacade()
                ->createCategory($categoryTransfer, $locale)
            ;

            $categoryNodeTransfer = (new NodeTransfer())
                ->fromArray($form->getData(), true)
            ;

            $categoryNodeTransfer->setFkCategory($idCategory);

            $this->getDependencyContainer()
                ->createCategoryFacade()
                ->createCategoryNode($categoryNodeTransfer, $locale)
            ;

            $this->addSuccessMessage('The category was added successfully.');

            return $this->redirectResponse('/category');
        }


        return $this->viewResponse([
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function productCategoryTableAction(Request $request)
    {
        $idCategory = $request->get(ProductCategoryConfig::PARAM_ID_CATEGORY);
        $locale = $this->getDependencyContainer()
            ->createCurrentLocale()
        ;

        $productCategoryTable = $this->getDependencyContainer()
            ->createProductCategoryTable($locale, $idCategory)
        ;

        return $this->jsonResponse(
            $productCategoryTable->fetchData()
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function productTableAction(Request $request)
    {
        $idCategory = $request->get(ProductCategoryConfig::PARAM_ID_CATEGORY);
        $locale = $this->getDependencyContainer()
            ->createCurrentLocale()
        ;

        $productTable = $this->getDependencyContainer()
            ->createProductTable($locale, $idCategory)
        ;

        return $this->jsonResponse(
            $productTable->fetchData()
        );
    }

}
