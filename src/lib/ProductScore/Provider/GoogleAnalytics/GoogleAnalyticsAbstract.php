<?php
namespace Quafzi\ProductScore\Provider\GoogleAnalytics;

// Load the Google API PHP Client Library.
require_once __DIR__ . '/apiclient.phar';

use Quafzi\ProductScore\Provider\GoogleAnalytics\GoogleAnalyticsAbstract as GoogleAnalytics;
use Quafzi\ProductScore\Provider\ProviderAbstract as Provider;

/**
 * Abstract Google Analytics Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

abstract class GoogleAnalyticsAbstract extends Provider
{
    protected function initializeAnalytics($config)
    {
        $client = new \Google_Client();
        $client->setApplicationName('Quafzi_ProductScore');
        $authConfig = [
            'type' => 'service_account',
            'project_id' => $config['project_id'],
            'private_key_id' => $config['private_key_id'],
            'private_key' => str_replace('\n', "\n", $config['private_key']),
            'client_email' => $config['client_email'],
            'client_id' => $config['client_id'],
            'auth_uri' => $config['auth_uri'],
            'token_uri' => $config['token_uri'],
            'auth_provider_x509_cert_url' => $config['auth_provider_x509_cert_url'],
            'client_x509_cert_url' => $config['client_x509_cert_url']
        ];
        $client->setAuthConfig($authConfig);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        return new \Google_Service_AnalyticsReporting($client);
    }
}
