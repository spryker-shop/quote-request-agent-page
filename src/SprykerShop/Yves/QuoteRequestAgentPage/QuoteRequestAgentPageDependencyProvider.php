<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\QuoteRequestAgentPage;

use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToCompanyUserClientBridge;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToCustomerClientBridge;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToQuoteClientBridge;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToQuoteRequestAgentClientBridge;
use SprykerShop\Yves\QuoteRequestAgentPage\Dependency\Client\QuoteRequestAgentPageToQuoteRequestClientBridge;

class QuoteRequestAgentPageDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CLIENT_QUOTE_REQUEST = 'CLIENT_QUOTE_REQUEST';
    public const CLIENT_QUOTE_REQUEST_AGENT = 'CLIENT_QUOTE_REQUEST_AGENT';
    public const CLIENT_COMPANY_USER = 'CLIENT_COMPANY_USER';
    public const CLIENT_QUOTE = 'CLIENT_QUOTE';
    public const CLIENT_CUSTOMER = 'CLIENT_CUSTOMER';

    public const PLUGINS_QUOTE_REQUEST_AGENT_FORM_METADATA_FIELD = 'PLUGINS_QUOTE_REQUEST_AGENT_FORM_METADATA_FIELD';

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addQuoteRequestClient($container);
        $container = $this->addQuoteRequestAgentClient($container);
        $container = $this->addCompanyUserClient($container);
        $container = $this->addQuoteClient($container);
        $container = $this->addCustomerClient($container);
        $container = $this->addQuoteRequestAgentFormMetadataFieldPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addQuoteRequestClient(Container $container): Container
    {
        $container[static::CLIENT_QUOTE_REQUEST] = function (Container $container) {
            return new QuoteRequestAgentPageToQuoteRequestClientBridge($container->getLocator()->quoteRequest()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addQuoteRequestAgentClient(Container $container): Container
    {
        $container[static::CLIENT_QUOTE_REQUEST_AGENT] = function (Container $container) {
            return new QuoteRequestAgentPageToQuoteRequestAgentClientBridge($container->getLocator()->quoteRequestAgent()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addCompanyUserClient(Container $container): Container
    {
        $container[static::CLIENT_COMPANY_USER] = function (Container $container) {
            return new QuoteRequestAgentPageToCompanyUserClientBridge($container->getLocator()->companyUser()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addQuoteClient(Container $container): Container
    {
        $container[static::CLIENT_QUOTE] = function (Container $container) {
            return new QuoteRequestAgentPageToQuoteClientBridge($container->getLocator()->quote()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addCustomerClient(Container $container): Container
    {
        $container[static::CLIENT_CUSTOMER] = function (Container $container) {
            return new QuoteRequestAgentPageToCustomerClientBridge($container->getLocator()->customer()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addQuoteRequestAgentFormMetadataFieldPlugins(Container $container): Container
    {
        $container[static::PLUGINS_QUOTE_REQUEST_AGENT_FORM_METADATA_FIELD] = function () {
            return $this->getQuoteRequestAgentFormMetadataFieldPlugins();
        };

        return $container;
    }

    /**
     * @return \SprykerShop\Yves\QuoteRequestAgentPageExtension\Dependency\Plugin\QuoteRequestAgentFormMetadataFieldPluginInterface[]
     */
    protected function getQuoteRequestAgentFormMetadataFieldPlugins(): array
    {
        return [];
    }
}
