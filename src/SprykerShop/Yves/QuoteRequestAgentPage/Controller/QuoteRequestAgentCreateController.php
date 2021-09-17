<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Controller;

use SprykerShop\Yves\QuoteRequestAgentPage\Form\QuoteRequestAgentCreateForm;
use SprykerShop\Yves\QuoteRequestAgentPage\Plugin\Router\QuoteRequestAgentPageRouteProviderPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\QuoteRequestAgentPage\QuoteRequestAgentPageFactory getFactory()
 */
class QuoteRequestAgentCreateController extends QuoteRequestAgentAbstractController
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_CREATED = 'quote_request_page.quote_request.created';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $response = $this->executeCreateAction($request);

        if (!is_array($response)) {
            return $response;
        }

        return $this->view($response, [], '@QuoteRequestAgentPage/views/quote-request-create/quote-request-create.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    protected function executeCreateAction(Request $request)
    {
        $quoteRequestCreateForm = $this->getFactory()
            ->getQuoteRequestAgentCreateForm()
            ->handleRequest($request);

        if (!$quoteRequestCreateForm->isSubmitted()) {
            return [
                'quoteRequestCreateForm' => $quoteRequestCreateForm->createView(),
                'impersonatedCustomer' => $this->getFactory()->getCustomerClient()->getCustomer(),
            ];
        }

        $idCompanyUser = $request->request->getInt(QuoteRequestAgentCreateForm::FILED_ID_COMPANY_USER);
        $quoteRequestResponseTransfer = $this->getFactory()
            ->createQuoteRequestAgentCreateHandler()
            ->createQuoteRequest($idCompanyUser);

        $this->handleResponseErrors($quoteRequestResponseTransfer);

        if (!$quoteRequestResponseTransfer->getIsSuccessful()) {
            return [
                'quoteRequestCreateForm' => $quoteRequestCreateForm->createView(),
                'impersonatedCustomer' => $this->getFactory()->getCustomerClient()->getCustomer(),
            ];
        }

        $this->addSuccessMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_CREATED);

        return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_EDIT, [
            static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestResponseTransfer->getQuoteRequest()->getQuoteRequestReference(),
        ]);
    }
}
