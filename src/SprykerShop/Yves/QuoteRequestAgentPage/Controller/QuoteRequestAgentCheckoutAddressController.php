<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Controller;

use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\QuoteRequestAgentPage\QuoteRequestAgentPageFactory getFactory()
 */
class QuoteRequestAgentCheckoutAddressController extends QuoteRequestAgentAbstractController
{
//    protected const GLOSSARY_KEY_QUOTE_REQUEST_CONVERTED_TO_CHECKOUT_ADDRESS = 'quote_request_page.quote_request.converted_to_checkout_address';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $quoteRequestReference
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request, string $quoteRequestReference)
    {
        $quoteTransfer = $this->getFactory()->getQuoteClient()->getQuote();

//        if ($quoteTransfer->getQuoteRequestReference() && ($quoteTransfer->getQuoteRequestReference() !== $quoteRequestReference)) {
//            return $this->redirectResponseInternal(static::ROUTE_QUOTE_REQUEST_AGENT_EDIT_ITEMS_CONFIRM, [
//                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestReference,
//            ]);
//        }

        $quoteRequestTransfer = $this->getQuoteRequestByReference($quoteRequestReference);

        return $this->convertQuoteRequest($quoteRequestTransfer, $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function convertQuoteRequest(QuoteRequestTransfer $quoteRequestTransfer, QuoteTransfer $quoteTransfer): RedirectResponse
    {
        $redirectResponse = $this->checkCompanyUserImpersonation($quoteRequestTransfer, $quoteTransfer);

        if ($redirectResponse) {
            return $redirectResponse;
        }

        $quoteResponseTransfer = $this->getFactory()
            ->getQuoteRequestAgentClient()
            ->convertQuoteRequestToQuote($quoteRequestTransfer);

        if ($quoteResponseTransfer->getIsSuccessful()) {
//            $this->addSuccessMessage(static::GLOSSARY_KEY_QUOTE_REQUEST_CONVERTED_TO_CART);
        }

        $this->handleQuoteResponseErrors($quoteResponseTransfer);

        $companyUserTransfer = $this->getFactory()
            ->getCompanyUserClient()
            ->findCompanyUser();

        if (!$companyUserTransfer) {
            return $this->redirectResponseInternal(static::ROUTE_CHECKOUT_ADDRESS, [
                static::PARAM_SWITCH_USER => $quoteRequestTransfer->getCompanyUser()->getCustomer()->getEmail(),
            ]);
        }

        return $this->redirectResponseInternal(static::ROUTE_CHECKOUT_ADDRESS);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    protected function checkCompanyUserImpersonation(QuoteRequestTransfer $quoteRequestTransfer, QuoteTransfer $quoteTransfer): ?RedirectResponse
    {
        $companyUserTransfer = $this->getFactory()
            ->getCompanyUserClient()
            ->findCompanyUser();

        if ($companyUserTransfer && ($companyUserTransfer->getIdCompanyUser() !== $quoteRequestTransfer->getCompanyUser()->getIdCompanyUser())) {
            return $this->redirectResponseInternal(static::ROUTE_QUOTE_REQUEST_AGENT_EDIT_ITEMS, [
                static::PARAM_QUOTE_REQUEST_REFERENCE => $quoteRequestTransfer->getQuoteRequestReference(),
                static::PARAM_SWITCH_USER => '_exit',
            ]);
        }

        if ($quoteTransfer->getQuoteRequestReference() === $quoteRequestTransfer->getQuoteRequestReference()) {
            return $this->redirectResponseInternal(static::ROUTE_CART);
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteResponseTransfer $quoteResponseTransfer
     *
     * @return void
     */
    protected function handleQuoteResponseErrors(QuoteResponseTransfer $quoteResponseTransfer): void
    {
        foreach ($quoteResponseTransfer->getErrors() as $errorTransfer) {
            $this->addErrorMessage($errorTransfer->getMessage());
        }
    }
}
