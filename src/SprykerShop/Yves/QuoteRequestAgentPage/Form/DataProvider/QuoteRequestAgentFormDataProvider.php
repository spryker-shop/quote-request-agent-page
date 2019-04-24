<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage\Form\DataProvider;

use Generated\Shared\Transfer\QuoteRequestTransfer;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToCartClientInterface;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToPriceClientInterface;
use SprykerShop\Yves\QuoteRequestAgentPage\Form\QuoteRequestAgentForm;

class QuoteRequestAgentFormDataProvider
{
    /**
     * @var \SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToCartClientInterface
     */
    protected $cartClient;

    /**
     * @var \SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToPriceClientInterface
     */
    protected $priceClient;

    /**
     * @param \SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToCartClientInterface $cartClient
     * @param \SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToPriceClientInterface $priceClient
     */
    public function __construct(
        QuoteRequestAgentPageToCartClientInterface $cartClient,
        QuoteRequestAgentPageToPriceClientInterface $priceClient
    ) {
        $this->cartClient = $cartClient;
        $this->priceClient = $priceClient;
    }

    /**
     * @uses \Spryker\Shared\Calculation\CalculationPriceMode::PRICE_MODE_GROSS
     */
    public const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return array
     */
    public function getOptions(QuoteRequestTransfer $quoteRequestTransfer): array
    {
        return [
            QuoteRequestAgentForm::OPTION_PRICE_MODE => $this->getPriceMode($quoteRequestTransfer),
            QuoteRequestAgentForm::OPTION_IS_QUOTE_VALID => $this->isQuoteValid($quoteRequestTransfer),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return string
     */
    protected function getPriceMode(QuoteRequestTransfer $quoteRequestTransfer): string
    {
        return $quoteRequestTransfer->getLatestVersion()->getQuote()->getPriceMode() ?? $this->priceClient->getCurrentPriceMode();
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteRequestTransfer $quoteRequestTransfer
     *
     * @return bool
     */
    protected function isQuoteValid(QuoteRequestTransfer $quoteRequestTransfer): bool
    {
        $latestQuoteRequestVersion = $quoteRequestTransfer
            ->requireLatestVersion()
            ->getLatestVersion()
                ->requireQuote();

        return $this->cartClient->validateSpecificQuote($latestQuoteRequestVersion->getQuote())
            ->getIsSuccessful();
    }
}
