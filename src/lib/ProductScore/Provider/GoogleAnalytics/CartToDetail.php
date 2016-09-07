<?php
namespace Quafzi\ProductScore\Provider\GoogleAnalytics;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\ProviderInterface as Provider;
use Quafzi\ProductScore\Provider\FetchException;
use Quafzi\ProductScore\Provider\GoogleAnalytics\GoogleAnalyticsAbstract as GoogleAnalytics;

/**
 * Google Analytics Cart-to-Detail Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class CartToDetail extends GoogleAnalytics
{
    /**
     * fetch score information
     *
     * @param Consumer $consumer Handler for fetched item score
     * @param array    $config   Provider configuration data
     *
     * @return $this
     */
    public function fetch(Consumer $consumer, array $config)
    {
        $this->consumer = $consumer;

        $analytics = $this->initializeAnalytics($config);

        $viewId = $config['view_id'];

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        //$sessions->setExpression('ga:cartToDetailRate,ga:buyToDetailRate');
        $sessions->setExpression('ga:cartToDetailRate');
        $sessions->setAlias("cartToDetailRate");

        $dimension = new \Google_Service_AnalyticsReporting_Dimension();
        $dimension->setName('ga:productSku');

        $orderBy = new \Google_Service_AnalyticsReporting_OrderBy();
        $orderBy->setFieldName('ga:cartToDetailRate');
        $orderBy->setSortOrder('DESCENDING');

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions]);
        $request->setDimensions([$dimension]);
        $request->setOrderBys([$orderBy]);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );

        $reports = $analytics->reports->batchGet($body);
        $this->handleResults($reports[0]);

        return $this;
    }

    protected function handleResults($report)
    {
        $factor = null;
        $rows = $report->getData()->getRows();
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
