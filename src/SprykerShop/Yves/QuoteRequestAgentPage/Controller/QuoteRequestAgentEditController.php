<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Controller;

use Generated\Shared\Transfer\QuoteRequestFilterTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use SprykerShop\Yves\QuoteRequestAgentPage\Form\QuoteRequestAgentForm;
use SprykerShop\Yves\QuoteRequestAgentPage\Plugin\Router\QuoteRequestAgentPageRouteProviderPlugin;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\QuoteRequestAgentPage\QuoteRequestAgentPageFactory getFactory()
 */
class QuoteRequestAgentEditController extends QuoteRequestAgentAbstractController
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_SOURCE_PRICE_CHANGES_FORBIDDEN = 'quote_request_page.quote_request.source_price_changes_forbidden';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_UPDATED = 'quote_request_page.quote_request.updated';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_SENT_TO_CUSTOMER = 'quote_request_page.quote_request.sent_to_customer';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS = 'quote_request.validation.error.wrong_status';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $quoteRequestReference
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, string $quoteRequestReference)
    {
        $response = $this->executeEditAction($request, $quoteRequestReference);

        if (!is_array($response)) {
            return $response;
        }

        return $this->view($response, [], '@QuoteRequestAgentPage/views/quote-request-edit/quote-request-edit.twig');
    }

    /**
     * @param string $quoteRequestReference
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendToCustomerAction(string $quoteRequestReference): RedirectResponse
    {
        $response = $this->executeSendToCustomerAction($quoteRequestReference);

        return $response;
    }

    /**
     * @param string $quoteRequestReference
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executeSendToCustomerAction(string $quoteRequestReference): RedirectResponse
    {
        $quoteRequestFilterTransfer = (new QuoteRequestFilterTransfer())
            ->setWithHidden(true)
            ->setQuoteRequestReference($quoteRequestReference);

        $quoteRequestResponseTransfer = $this->getFactory()
            ->getQuoteRequestAgentClient()
            ->sendQuoteRequestToCustomer($quoteRequestFilterTransfer);

        if ($quoteRequestResponseTransfer->getIsSuccessful()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_SENT_TO_CUSTOMER);

            return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_DETAILS, [
                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
            ]);
        }

        $this->handleResponseErrors($quoteRequestResponseTransfer);

        return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_EDIT, [
            static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $quoteRequestReference
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    protected function executeEditAction(Request $request, string $quoteRequestReference)
    {
        $quoteRequestAgentClient = $this->getFactory()->getQuoteRequestAgentClient();
        $quoteRequestTransfer = $this->getQuoteRequestByReference($quoteRequestReference);

        if ($quoteRequestAgentClient->isQuoteRequestRevisable($quoteRequestTransfer)) {
            return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_REVISE, [
                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
            ]);
        }

        if (!$quoteRequestAgentClient->isQuoteRequestEditable($quoteRequestTransfer)) {
            $this->addErrorMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS);

            return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_DETAILS, [
                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
            ]);
        }

        $quoteRequestForm = $this->getFactory()
            ->getQuoteRequestAgentForm($quoteRequestTransfer)
            ->handleRequest($request);

        if ($quoteRequestForm->isSubmitted() && $quoteRequestForm->isValid()) {
            return $this->processQuoteRequestAgentForm($quoteRequestForm, $request);
        }

        $quoteRequestForm = $this->assertQuoteRequestVersion($quoteRequestForm);

        $quoteTransfer = $quoteRequestTransfer->getLatestVersion()->getQuote();
        $shipmentGroupTransfers = $this->getFactory()
            ->createShipmentGrouper()
            ->groupItemsByShippingAddress($quoteTransfer);

        $itemExtractor = $this->getFactory()->createItemExtractor();

        return [
            'quoteRequestForm' => $quoteRequestForm->createView(),
            'shipmentGroups' => $shipmentGroupTransfers,
            'itemsWithShipment' => $itemExtractor->extractItemsWithShipmentAddress($quoteTransfer),
            'itemsWithoutShipment' => $itemExtractor->extractItemsWithoutShipmentAddress($quoteTransfer),
            'shipmentExpenses' => $this->getFactory()->createExpenseExtractor()->extractShipmentExpenses($quoteTransfer),
        ];
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $quoteRequestForm
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function assertQuoteRequestVersion(FormInterface $quoteRequestForm): FormInterface
    {
        if ($quoteRequestForm->getConfig()->getOption(QuoteRequestAgentForm::OPTION_IS_QUOTE_VALID)) {
            return $quoteRequestForm;
        }

        $this->addErrorMessage(static::GLOSSARY_KEY_SOURCE_PRICE_CHANGES_FORBIDDEN);

        // Executes CartFacade::reloadItems() before update.
        $quoteRequestResponseTransfer = $this->getFactory()
            ->getQuoteRequestAgentClient()
            ->updateQuoteRequest($quoteRequestForm->getData());

        $quoteRequestForm
            ->get(QuoteRequestTransfer::LATEST_VERSION)
            ->setData(
                $quoteRequestResponseTransfer
                    ->getQuoteRequest()
                    ->getLatestVersion(),
            );

        return $quoteRequestForm;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $quoteRequestForm
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function processQuoteRequestAgentForm(FormInterface $quoteRequestForm, Request $request): RedirectResponse
    {
        /** @var \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer */
        $quoteRequestTransfer = $quoteRequestForm->getData();

        $quoteRequestResponseTransfer = $this->getFactory()
            ->getQuoteRequestAgentClient()
            ->updateQuoteRequest($quoteRequestTransfer);

        if ($quoteRequestResponseTransfer->getIsSuccessful()) {
            $this->addSuccessMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_UPDATED);
        }

        $this->handleResponseErrors($quoteRequestResponseTransfer);

        if ($request->get(QuoteRequestAgentForm::SUBMIT_BUTTON_SEND_TO_CUSTOMER) !== null && $quoteRequestResponseTransfer->getIsSuccessful()) {
            return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_SEND_TO_CUSTOMER, [
                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestTransfer->getQuoteRequestReference(),
            ]);
        }

        return $this->redirectResponseInternal(QuoteRequestAgentPageRouteProviderPlugin::ROUTE_NAME_QUOTE_REQUEST_AGENT_EDIT, [
            static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestTransfer->getQuoteRequestReference(),
        ]);
    }
}
