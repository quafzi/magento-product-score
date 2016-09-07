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

    protected function fetchAnalyticsColumn($column, $config)
    {
        $analytics = $this->initializeAnalytics($config);

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate('7daysAgo');
        $dateRange->setEndDate('today');

        // Create the Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression($column);

        $dimension = new \Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName('ga:productSku');

        $orderBy = new \Google_Service_AnalyticsReporting_OrderBy();
        $orderBy->setFieldName($column);
        $orderBy->setSortOrder('DESCENDING');

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($config['view_id']);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions]);
        $request->setDimensions([$dimension]);
        $request->setOrderBys([$orderBy]);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests([$request]);

        $reports = $analytics->reports->batchGet($body);
        $this->handleResult($reports[0]);

        return $this;
    }

    protected function handleResult($result)
    {
        $factor = null;
        $rows = $result->getData()->getRows();
        $productIds = array_flip($this->productIdentifiers);
        for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[ $rowIndex ];
            $dimensions = $row->getDimensions();
            $metrics = $row->getMetrics();
            $sku = $dimensions[0];
            $rate = $metrics[0]->getValues()[0];
            if (!array_key_exists($sku, $productIds)) {
                continue;
            }
            if (is_null($factor)) {
                $factor = $this->maxScore/$rate;
            }
            $this->consumer->addItem($productIds[$sku], $rate*$factor);
        }
    }
}
