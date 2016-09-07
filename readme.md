# Quafzi ProductScore Extension
[![Build
Status](https://travis-ci.org/quafzi/magento-product-score.svg?branch=develop)](https://travis-ci.org/quafzi/magento-product-score)

Magento module to calculate product scores based on data from different providers.

## Facts
- version: see config.xml
- composer package name: quafzi/product-score

## Description
Calculate product scores based on different providers.
Currently supported providers are:

* Google Analytics
  * buy-to-detail rate
  * cart-to-detail rate
  * product-click-through rate
* Magento
  * number of ordered units
  * average order item price
  * catalog price

The library part may of course be used independend from Magento itself.

## Requirements
- PHP >= 5.6.0

## Installation Instructions
-------------------------
1. Install the extension via Composer with the package name shown above or copy all the files into your document root.
2. Clear the cache, logout from the admin panel and then login again.
3. Assign the newly created product attributes (``score``, ``score_calculated``, ``score_manual``)to your attribute sets.
4. Configure and activate the extension under System → Configuration → Catalog → Product Score:
   * Select a data storage depending on your system capabilities.
   * Configure your data providers (see [Data Providers](#data-providers) section).
5. Make sure, your cron jobs are enabled.

## Data Providers
There is a configuration section for every single data provider in Magento admin panel under System → Configuration → Catalog → Product Score. After enabling a provider, you will be able to see its configuration options. For every data source, you can enter a weight for score calculation. If you leave a weight empty or enter just ``0``, this data source will be skipped.

### Google Analytics
To configure Google Analytics as a data provider, you need to first use the [setup tool](https://console.developers.google.com/start/api?id=analyticsreporting.googleapis.com&credential=client_key), which guides you through creating a project in the Google API Console, enabling the API, and creating credentials.

There are three data sources, providing Cart-to-Detail rate, Buy-to-Detail rate, and Product CTR.

#### Create credentials

  Note: When prompted click Furnish a new private key and for the Key type select JSON, and save the generated key as ``service-account-credentials.json``; you will need to enter some of its content into your Magento configuration later.

1. Open the Service accounts page. If prompted, select a project.
2. Click Create service account.
3. In the Create service account window, type a name for the service account, and select Furnish a new private key. Then click Create.

Your new public/private key pair is generated and downloaded to your machine; it serves as the only copy of this key. You are responsible for storing it securely.

#### Add service account to the Google Analytics account

The newly created service account will have an email address that looks similar to:

  quickstart@PROJECT-ID.iam.gserviceaccount.com

Use this email address to add a user to the Google analytics view you want to access via the API. For this tutorial only Read & Analyze permissions are needed.

#### Add Google Account information to Magento

Open the downloaded ``service-account-credentials.json`` and you will find json contents, while most of its keys match a configuration field at System → Configuration → Catalog → Product Score → Google Analytics:

* project_id,
* private_key,
* client_email,
* client_id,
* auth_uri,
* token_uri,
* auth_provider_x509_cert_url
* client_x509_cert_url

In addition to these, you will need to enter a view id (which may be store-dependend), which can be found at https://ga-dev-tools.appspot.com/account-explorer/ (it's the number in the view column in there).

### Magento

Since we already are in context of Magento, there is not much left to be configured here. Just enable your desired data source(s) by entering a weight:

* Count of ordered items
* Average price of order items
* Catalog price

## Usage
Score is recalculated once a day, at 3:07 AM (see config.xml) by default. You may run it manually by using Magerun:

    magerun sys:cron:run fetchProductScores

After running it, you might need to rebuild your Flat Catalog.

## Testing
There are some tests for data consumers and providers. Run ``make test -B``.

## Uninstallation
1. Remove all extension files from your Magento installation
2. Optionally delete score attributes

## Developer

Thomas Birke (https://twitter.com/quafzi)

## Licence
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

## Copyright
(c) 2016 Thomas Birke
