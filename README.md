# Magento Snippets #


Direct safe SQL queries in Magento using named binding for security:

Step 1: Initialize a resource to interact with the database:

``$read = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' ); // To read from the database
$write = Mage::getSingleton( 'core/resource' )->getConnection( 'core_write' ); // To write to the database``


Step 2: Choose which tables you want to connect with. Using this method also takes care of the problem of prefixed tables on some stores. It’s the safest way to go about this.

``$productTable = Mage::getSingleton( 'core/resource' )->getTableName( 'catalog_product_entity' );``
 

Step 3: Prepare your query

``$query = "SELECT product_id FROM " . $productTable . " WHERE created_at BETWEEN :fromdate AND :todate";
$binds = array(
	'fromdate' => $unsafePostedValue1,
	'todate' => $unsafePostedValue2
);``
 

Step 4: Execute your query!

``$result = $read->query( $query, $binds );
while ( $row = $result->fetch() ) {
	echo 'Product ID: ' . $row['product_id'] . '<br>';
}``
 

Explanation:
Our read resource takes our query and looks for something like :this . It then looks for a bind array which it then maps the value of this to :this . What’s more is that it automatically quotes and prepares all the security measures before executing the query. So you don’t need to quote the query at all! In fact, it will give you an error if you quote the query!

 

Inserting into the database:

Here’s how you insert into the database:

``$query = "INSERT INTO " . $productTable . " SET product_id = :product_id";
$binds = array(
	'product_id' => $unsafePostedId
);
$write->query( $query, $binds );``


## Download extension manually using pear/mage ##
Pear for 1.4, mage for 1.5. File downloaded into /downloader/.cache/community/

./pear download magento-community/Shipping_Agent
./mage download community Shipping_Agent

## Clear cache/reindex ##

```php
<?php
// clear cache
Mage::app()->removeCache('catalog_rules_dirty');
// reindex prices
Mage::getModel('index/process')->load(2)->reindexEverything();
/*
1 = Product Attributes
2 = Product Attributes
3 = Catalog URL Rewrites
4 = Product Flat Data
5 = Category Flat Data
6 = Category Products
7 = Catalog Search Index
8 = Tag Aggregation Data
9 = Stock Status
*/
?>
```

## Load category by id ##

```php
<?php
$_category = Mage::getModel('catalog/category')->load(89);
$_category_url = $_category->getUrl();
?>
```

## Load product by id or sku ##

```php
<?php
$_product_1 = Mage::getModel('catalog/product')->load(12);
$_product_2 = Mage::getModel('catalog/product')->loadByAttribute('sku','cordoba-classic-6-String-guitar');
?>
```


## Get Configurable product's Child products ##

```php
<?php
// input is $_product and result is iterating child products
$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
?>
```

## Get Configurable product's Children's (simple product) custom attributes ##

```php
<?php
// input is $_product and result is iterating child products
$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($_product);
$col = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
foreach($col as $simple_product){
    var_dump($simple_product->getId());
}
?>
```

## Log to custom file ##

```php
<?php Mage::log('Your Log Message', Zend_Log::INFO, 'your_log_file.log'); ?>
```

## Call Static Block ##

```php
<?php echo $this->getLayout()->createBlock('cms/block')->setBlockId('block-name')->toHtml(); ?>
```

## Add JavaScript to page ##

First approach: page.xml - you can add something like

```xml
<action method="addJs"><script>path/to/my/file.js</script></action>
```

Second approach: Find `page/html/head.phtml` in your theme and add the code directly to `page.html`.

Third approach: If you look at the stock page.html mentioned above, you'll see this line

```php
<?php echo $this->getChildHtml() ?>
```

Normally, the getChildHtml method is used to render a specific child block. However, if called with no paramater, getChildHtml will automatically render all the child blocks. That means you can add something like

```xml
<!-- existing line --> 
<block type="page/html_head" name="head" as="head">
    <!-- new sub-block you're adding --> 
    <block type="core/template" name="mytemplate" as="mytemplate" template="page/mytemplate.phtml"/>

</block>```

    to `page.xml`, and then add the `mytemplate.phtml` file. Any block added to the head block will be automatically rendered. (this automatic rendering doesn't apply for all layout blocks, only for blocks where getChildHtml is called without paramaters).

    ## Check if customer is logged in ##

    ```php
    <?php $logged_in = Mage::getSingleton('customer/session')->isLoggedIn(); // (boolean) ?>
    ```

    ## Get the current category/product/cms page ##

    ```php
    <?php
    $currentCategory = Mage::registry('current_category');
    $currentProduct = Mage::registry('current_product');
    $currentCmsPage = Mage::registry('cms_page');
    ?>
    ```

    ## Run Magento Code Externally ##

    ```php
    <?php
    require_once('app/Mage.php'); //Path to Magento
    umask(0);
    Mage::app();
    // Run you code here
    ?>
    ```

    ## Programmatically change Magento’s core config data ##

    ```php
    <?php
    // find 'path' in table 'core_config_data' e.g. 'design/head/demonotice'
    $my_change_config = new Mage_Core_Model_Config();
    // turns notice on
    $my_change_config->saveConfig('design/head/demonotice', "1", 'default', 0);
    // turns notice off
    $my_change_config->saveConfig('design/head/demonotice', "0", 'default', 0);
    ?>
    ```

    ## Changing the Admin URL ##

    Open up the `/app/etc/local.xml` file, locate the `<frontName>` tag, and change the ‘admin’ part it to something a lot more random, eg:

```xml
        <frontName><![CDATA[supersecret-admin-name]]></frontName>
```

        Clear your cache and sessions.

## Magento: Mass Exclude/Unexclude Images ##

        By default, Magento will check the 'Exclude' box for you on all imported images, making them not show up as a thumbnail under the main product image on the product view.

```sql
        # Mass Unexclude
        UPDATE`catalog_product_entity_media_gallery_value` SET `disabled` = '0' WHERE `disabled` = '1';
        # Mass Exclude
        UPDATE`catalog_product_entity_media_gallery_value` SET `disabled` = '1' WHERE `disabled` = '0';
```

## getBaseUrl – Magento URL Path ##

```php
        <?php
        // http://example.com/
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        // http://example.com/js/
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
        // http://example.com/index.php/
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        // http://example.com/media/
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        // http://example.com/skin/
        echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        ?>
```

## Get The Root Category In Magento ##

```php
        <?php
        $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
        $_category = Mage::getModel('catalog/category')->load($rootCategoryId);
        // You can then get all of the top level categories using:
        $_subcategories = $_category->getChildrenCategories();
        ?>
```

## Get The Current URL In Magento ##

```php
        <?php echo Mage::helper('core/url')->getCurrentUrl(); ?>
```

## Category Navigation Listings in Magento ##

        Make sure the block that you’re working is of the type catalog/navigation. If you’re editing catalog/navigation/left.phtml then you should be okay.

```php
        <div id="leftnav">
            <?php $helper = $this->helper('catalog/category') ?>
            <?php $categories = $this->getStoreCategories() ?>
            <?php if (count($categories) > 0): ?>
                <ul id="leftnav-tree" class="level0">
                    <?php foreach($categories as $category): ?>
                        <li class="level0<?php if ($this->isCategoryActive($category)): ?> active<?php endif; ?>">
                            <a href="<?php echo $helper->getCategoryUrl($category) ?>"><span><?php echo $this->escapeHtml($category->getName()) ?></span></a>
                            <?php if ($this->isCategoryActive($category)): ?>
                                <?php $subcategories = $category->getChildren() ?>
                                <?php if (count($subcategories) > 0): ?>
                                    <ul id="leftnav-tree-<?php echo $category->getId() ?>" class="level1">
                                        <?php foreach($subcategories as $subcategory): ?>
                                            <li class="level1<?php if ($this->isCategoryActive($subcategory)): ?> active<?php endif; ?>">
                                                <a href="<?php echo $helper->getCategoryUrl($subcategory) ?>"><?php echo $this->escapeHtml(trim($subcategory->getName(), '- ')) ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <script type="text/javascript">decorateList('leftnav-tree-<?php echo $category->getId() ?>', 'recursive')</script>
                                <?php endif; ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <script type="text/javascript">decorateList('leftnav-tree', 'recursive')</script>
            <?php endif; ?>
        </div>
```

## Debug using zend ##

```php
        <?php echo Zend_Debug::dump($thing_to_debug, 'debug'); ?>
```

## $_GET, $_POST & $_REQUEST Variables ##

```php
        <?php
        // $_GET
        $productId = Mage::app()->getRequest()->getParam('product_id');
        // The second parameter to getParam allows you to set a default value which is returned if the GET value isn't set
        $productId = Mage::app()->getRequest()->getParam('product_id', 44);
        $postData = Mage::app()->getRequest()->getPost();
        // You can access individual variables like...
        $productId = $postData['product_id']);
?>
```

## Get methods of an object ##

        First, use `get_class` to get the name of an object's class.

```php
        <?php $class_name = get_class($object); ?>
```

        Then, pass that `get_class_methods` to get a list of all the callable methods on an object

```php
        <?php
        $class_name = get_class($object);
        $methods = get_class_methods($class_name);
        foreach($methods as $method)
        {
            var_dump($method);
        }
        ?>
```

## Is product purchasable? ##

```php
        <?php if($_product->isSaleable()) { // do stuff } ?>
```

## Load Products by Category ID ##

```php
        <?php
        $_category = Mage::getModel('catalog/category')->load(47);
        $_productCollection = $_category->getProductCollection();
        if($_productCollection->count()) {
            foreach( $_productCollection as $_product ):
                echo $_product->getProductUrl();
                echo $this->getPriceHtml($_product, true);
                echo $this->htmlEscape($_product->getName());
            endforeach;
        }
        ?>
```

## Update all subscribers into a customer group (e.g. 5) ##

```sql
        UPDATE
        customer_entity,
        newsletter_subscriber
        SET
        customer_entity.`group_id` = 5
        WHERE
        customer_entity.`entity_id` = newsletter_subscriber.`customer_id`
        AND
        newsletter_subscriber.`subscriber_status` = 1;
```

## Get associated products

        In /app/design/frontend/default/site/template/catalog/product/view/type/

``` php
        <?php $_helper = $this->helper('catalog/output'); ?>
        <?php $_associatedProducts = $this->getAllowProducts() ?>
        <?php //var_dump($_associatedProducts); ?>
        <br />
        <br />
        <?php if (count($_associatedProducts)): ?>
            <?php foreach ($_associatedProducts as $_item): ?>
                <a href="<?php echo $_item->getProductUrl() ?>"><?php echo $_helper->productAttribute($_item, $_item->getName(), 'name') ?> | <?php echo $_item->getName() ?> | <?php echo $_item->getPrice() ?></a>
                <br />
                <br />
            <?php endforeach; ?>
        <?php endif; ?>
```

## Get An Array of Country Names/Codes in Magento ##

```php
        <?php
        $countryList = Mage::getResourceModel('directory/country_collection')
            ->loadData()
            ->toOptionArray(false);

        echo '<pre>';
        print_r( $countryList);
        exit('</pre>');
        ?>
```

## Create a Country Drop Down in the Frontend of Magento ##

```php
        <?php
        $_countries = Mage::getResourceModel('directory/country_collection')
            ->loadData()
            ->toOptionArray(false) ?>
        <?php if (count($_countries) > 0): ?>
            <select name="country" id="country">
                <option value="">-- Please Select --</option>
                <?php foreach($_countries as $_country): ?>
                    <option value="<?php echo $_country['value'] ?>">
                        <?php echo $_country['label'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
```

## Create a Country Drop Down in the Magento Admin ##

```php
        <?php
        $fieldset->addField('country', 'select', array(
            'name'  => 'country',
            'label'     => 'Country',
            'values'    => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
        ));
        ?>
```

## Return Product Attributes ##

```php
        <?php
        $_product->getThisattribute();
        $_product->getAttributeText('thisattribute');
        $_product->getResource()->getAttribute('thisattribute')->getFrontend()->getValue($_product);
        $_product->getData('thisattribute');
        // The following returns the option IDs for an attribute that is a multiple-select field:
        $_product->getData('color'); // i.e. 456,499
        // The following returns the attribute object, and instance of Mage_Catalog_Model_Resource_Eav_Attribute:
        $_product->getResource()->getAttribute('color'); // instance of Mage_Catalog_Model_Resource_Eav_Attribute
        // The following returns an array of the text values for the attribute:
        $_product->getAttributeText('color') // Array([0]=>'red', [1]=>'green')
// The following returns the text for the attribute
if ($attr = $_product->getResource()->getAttribute('color')):
    echo $attr->getFrontend()->getValue($_product); // will display: red, green
endif;
?>
```

## Cart Data ##

```php
        <?php
        $cart = Mage::getModel('checkout/cart')->getQuote()->getData();
        print_r($cart);
        $cart = Mage::helper('checkout/cart')->getCart()->getItemsCount();
        print_r($cart);
        $session = Mage::getSingleton('checkout/session');
        foreach ($session->getQuote()->getAllItems() as $item) {
            echo $item->getName();
            Zend_Debug::dump($item->debug());
        }
        ?>
```

## Get Simple Products of a Configurable Product ##

```php
        <?php
        if($_product->getTypeId() == "configurable") {
            $ids = $_product->getTypeInstance()->getUsedProductIds();
            ?>
            <ul>
                <?php
                foreach ($ids as $id) {
                    $simpleproduct = Mage::getModel('catalog/product')->load($id);
                    ?>
                    <li>
                        <?php
                        echo $simpleproduct->getName() . " - " . (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($simpleproduct)->getQty();
                        ?>
                    </li>
                <?php
                }
                ?>
            </ul>
        <?php
        }
        ?>
```

## Turn template hints on/off via database ##

```sql
        UPDATE
        `core_config_data`
        SET
        `value` = 0
        WHERE
        `path` = "dev/debug/template_hints"
        OR
        `path` = "dev/debug/template_hints_blocks";
```

## Delete all products ##

```sql
        DELETE FROM `catalog_product_entity`;
        -- thanks to https://gist.github.com/paales
```

## Getting Configurable Product from Simple Product ID in Magento 1.5+ ##

```php
        <?php
        $simpleProductId = 465;
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($simpleProductId);
        $product = Mage::getModel('catalog/product')->load($parentIds[0]);
        echo $product->getId(); // ID = 462 (aka, Parent of 465)
        ?>
```


## Export Customers SQL ##

```SQL
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'customer';
SELECT entity_type_id INTO @id_type_address FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'customer_address';
SELECT attribute_id INTO @id_prefix FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'prefix' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_firstname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_lastname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_default_billing FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'default_billing' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_default_shipping FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'default_shipping' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_company FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'company' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_firstname_addr FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_lastname_addr FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_street FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'street' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_postcode FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'postcode' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_city FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'city' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_region FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'region' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_country_id FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'country_id' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_telephone FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'telephone' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_fax FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'fax' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_street FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'street' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_postcode FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'postcode' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_city FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'city' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_region FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'region' AND entity_type_id = @id_type_address;
SELECT attribute_id INTO @id_country_id FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'country_id' AND entity_type_id = @id_type_address;

SELECT
  e.entity_id AS entity_id,
  'Y' AS Active,
  e.email AS Name,
  IFNULL(_table_prefix.value, TRIM(SPACE(32))) AS Salutation,
  IFNULL(_table_firstname.value, TRIM(SPACE(32))) AS FirstName,
  IFNULL(_table_lastname.value, TRIM(SPACE(32))) AS LastName,
  IFNULL(CONCAT(IFNULL(_table_firstname.value, TRIM(SPACE(32))), ' ', IFNULL(_table_lastname.value, TRIM(SPACE(32))) ), TRIM(SPACE(32))) AS CustomerName,
  IFNULL(_table_billing_company.value, TRIM(SPACE(32))) AS Company,
  IFNULL(CONCAT(_table_billing_firstname.value, ' ', _table_billing_lastname.value), TRIM(SPACE(32))) AS BillToContact,
  IFNULL(_table_billing_street.value, TRIM(SPACE(32))) AS BillAddressAddr1,
  TRIM(SPACE(32)) AS BillAddressAddr2,
  IFNULL(_table_billing_city.value, TRIM(SPACE(32))) AS BillAddressCity,
  IFNULL(_table_billing_region.value, TRIM(SPACE(32))) AS BillAddressState,
  IFNULL(_temporary_billing_country.country_name, TRIM(SPACE(32))) AS BillAddressCountry,
  IFNULL(_table_billing_postcode.value, TRIM(SPACE(32))) AS BillAddressPostalCode,
  IFNULL(CONCAT(IFNULL(_table_shipping_firstname.value, TRIM(SPACE(32))), ' ', IFNULL(_table_shipping_lastname.value, TRIM(SPACE(32)))), TRIM(SPACE(32))) AS ShipToName,
  IFNULL(_table_shipping_street.value, TRIM(SPACE(32))) AS ShipAddressAddr1,
  TRIM(SPACE(32)) AS ShipAddressAddr2,
  IFNULL(_table_shipping_city.value, TRIM(SPACE(32))) AS ShipAddressCity,
  IFNULL(_table_shipping_region.value, TRIM(SPACE(32))) AS ShipAddressState,
  IFNULL(_temporary_shipping_country.country_name, TRIM(SPACE(32))) AS ShipAddressCountry,
  IFNULL(_table_shipping_postcode.value, TRIM(SPACE(32))) AS ShipAddressPostalCode,
  IFNULL(_table_billing_telephone.value, TRIM(SPACE(32))) AS Phone,
  IFNULL(_table_billing_fax.value, TRIM(SPACE(32))) AS Fax,
  IFNULL(e.email, TRIM(SPACE(32))) AS Email,
  TRIM(SPACE(32)) AS WebAddress
FROM  /*PREFIX*/customer_entity AS e
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_prefix ON (_table_prefix.entity_id = e.entity_id) AND (_table_prefix.attribute_id = @id_prefix)
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_firstname ON (_table_firstname.entity_id = e.entity_id) AND (_table_firstname.attribute_id = @id_firstname)
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_lastname ON (_table_lastname.entity_id = e.entity_id) AND (_table_lastname.attribute_id = @id_lastname)
  LEFT JOIN  /*PREFIX*/customer_entity_int AS _table_default_billing ON (_table_default_billing.entity_id = e.entity_id) AND (_table_default_billing.attribute_id = @id_default_billing)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_company ON (_table_billing_company.entity_id = _table_default_billing.value) AND (_table_billing_company.attribute_id = @id_company)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_firstname ON (_table_billing_firstname.entity_id = _table_default_billing.value) AND (_table_billing_firstname.attribute_id = @id_firstname_addr)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_lastname ON (_table_billing_lastname.entity_id = _table_default_billing.value) AND (_table_billing_lastname.attribute_id = @id_lastname_addr)
  LEFT JOIN  /*PREFIX*/customer_address_entity_text AS _table_billing_street ON (_table_billing_street.entity_id = _table_default_billing.value) AND (_table_billing_street.attribute_id = @id_street)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_postcode ON (_table_billing_postcode.entity_id = _table_default_billing.value) AND (_table_billing_postcode.attribute_id = @id_postcode)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_city ON (_table_billing_city.entity_id = _table_default_billing.value) AND (_table_billing_city.attribute_id = @id_city)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_region ON (_table_billing_region.entity_id = _table_default_billing.value) AND (_table_billing_region.attribute_id = @id_region)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_country_id ON (_table_billing_country_id.entity_id = _table_default_billing.value) AND (_table_billing_country_id.attribute_id = @id_country_id) 
  LEFT JOIN temporary_billing_country AS _temporary_billing_country ON (_temporary_billing_country.country_id = _table_billing_country_id.value)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_telephone ON (_table_billing_telephone.entity_id = _table_default_billing.value) AND (_table_billing_telephone.attribute_id = @id_telephone)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_billing_fax ON (_table_billing_fax.entity_id = _table_default_billing.value) AND (_table_billing_fax.attribute_id = @id_fax)
  LEFT JOIN  /*PREFIX*/customer_entity_int AS _table_default_shipping ON (_table_default_shipping.entity_id = e.entity_id) AND (_table_default_shipping.attribute_id = @id_default_shipping)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_firstname ON (_table_shipping_firstname.entity_id = _table_default_shipping.value) AND (_table_shipping_firstname.attribute_id = @id_firstname_addr)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_lastname ON (_table_shipping_lastname.entity_id = _table_default_shipping.value) AND (_table_shipping_lastname.attribute_id = @id_lastname_addr)
  LEFT JOIN  /*PREFIX*/customer_address_entity_text AS _table_shipping_street ON (_table_shipping_street.entity_id = _table_default_shipping.value) AND (_table_shipping_street.attribute_id = @id_street)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_postcode ON (_table_shipping_postcode.entity_id = _table_default_shipping.value) AND (_table_shipping_postcode.attribute_id = @id_postcode)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_city ON (_table_shipping_city.entity_id = _table_default_shipping.value) AND (_table_shipping_city.attribute_id = @id_city)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_region ON (_table_shipping_region.entity_id = _table_default_shipping.value) AND (_table_shipping_region.attribute_id = @id_region)
  LEFT JOIN  /*PREFIX*/customer_address_entity_varchar AS _table_shipping_country_id ON (_table_shipping_country_id.entity_id = _table_default_shipping.value) AND (_table_shipping_country_id.attribute_id = @id_country_id)
  LEFT JOIN temporary_shipping_country AS _temporary_shipping_country ON (_temporary_shipping_country.country_id = _table_shipping_country_id.value)
WHERE (e.entity_type_id = @id_type) /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id
```

## Export Order Details ##


```SQL
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'order';
SELECT attribute_id INTO @id_status FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'status' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_customer_email FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'customer_email' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_customer_firstname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'customer_firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_customer_lastname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'customer_lastname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_billing_address_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'billing_address_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_shipping_address_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'shipping_address_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_shipping_description FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'shipping_description' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_company FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'company' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_firstname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_lastname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_street FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'street' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_postcode FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'postcode' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_city FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'city' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_region FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'region' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_country_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'country_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_telephone FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'telephone' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_fax FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'fax' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_method FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'method' AND entity_type_id = @id_type;

SELECT
 e.entity_id AS OrderID,
 e.created_at AS Date,
 IFNULL(_table_status.value, TRIM(SPACE(32))) AS Status,
 IFNULL(_table_email.value, TRIM(SPACE(32))) AS Name,
 IFNULL(_table_billing_company.value, TRIM(SPACE(32))) AS Company,
 IFNULL(_table_firstname.value, TRIM(SPACE(32))) AS FirstName,
 IFNULL(_table_lastname.value, TRIM(SPACE(32))) AS LastName,
 TRIM(CONCAT(IFNULL(_table_firstname.value, TRIM(SPACE(32))), ' ', IFNULL(_table_lastname.value, TRIM(SPACE(32))))) AS CustomerName,
 IFNULL(_table_billing_company.value, TRIM(SPACE(32))) AS Company, 
 IFNULL(CONCAT(_table_billing_firstname.value, ' ', _table_billing_lastname.value), TRIM(SPACE(32))) AS BillToContact,
 IFNULL(_table_billing_street.value, TRIM(SPACE(32))) AS BillAddressAddr1,
 TRIM(Space(32)) AS BillAddressAddr2,
 IFNULL(_table_billing_city.value, TRIM(SPACE(32))) AS BillAddressCity,
 IFNULL(_table_billing_region.value, TRIM(SPACE(32))) AS BillAddressState,
 IFNULL(_temporary_billing_country.country_name, TRIM(SPACE(32))) AS BillAddressCountry,
 IFNULL(_table_billing_postcode.value, TRIM(SPACE(32))) AS BillAddressPostalCode,
 IFNULL(CONCAT(IFNULL(_table_shipping_firstname.value, TRIM(SPACE(32))), ' ', IFNULL(_table_shipping_lastname.value, TRIM(SPACE(32)))), TRIM(SPACE(32))) AS ShipToName,
 IFNULL(_table_shipping_street.value, TRIM(SPACE(32))) AS ShipAddressAddr1,
 TRIM(SPACE(32)) AS ShipAddressAddr2,
 IFNULL(_table_shipping_city.value, TRIM(SPACE(32))) AS ShipAddressCity,
 IFNULL(_table_shipping_region.value, TRIM(SPACE(32))) AS ShipAddressState,
 IFNULL(_temporary_shipping_country.country_name, TRIM(SPACE(32))) AS ShipAddressCountry,
 IFNULL(_table_shipping_postcode.value, TRIM(SPACE(32))) AS ShipAddressPostalCode,
 IFNULL(_table_billing_telephone.value, TRIM(SPACE(32))) AS Phone,
 IFNULL(_table_billing_fax.value, TRIM(SPACE(32))) AS Fax,
 IFNULL(_table_email.value, TRIM(SPACE(32))) AS Email,
 TRIM(SPACE(32)) AS WebAddress,
 TRIM(SPACE(32)) AS URL,

 p.product_id AS ProductID,
 p.price AS OrderedPrice,
 p.original_price AS Price,
 p.qty_ordered AS Quantity,
 p.sku AS ProductCode,
 p.name AS Product,  

 IFNULL(_table_shipping_description.value, TRIM(SPACE(32))) AS ShippingMethod,  
 ABS(e.discount_amount) AS Discount,
 e.subtotal AS Subtotal,
 e.tax_amount AS Tax,
 e.shipping_amount AS ShippingCost,
 e.grand_total AS Total,   
 _temporary_method_name.items AS PaymentMethod

FROM /*PREFIX*/sales_order e
  LEFT JOIN /*PREFIX*/sales_order_varchar AS _table_status ON (_table_status.entity_id = e.entity_id) AND (_table_status.attribute_id = @id_status)
  LEFT JOIN /*PREFIX*/sales_order_varchar AS _table_email ON (_table_email.entity_id = e.entity_id) AND (_table_email.attribute_id = @id_customer_email) 
  LEFT JOIN /*PREFIX*/sales_order_varchar AS _table_firstname ON (_table_firstname.entity_id = e.entity_id) AND (_table_firstname.attribute_id = @id_customer_firstname)
  LEFT JOIN /*PREFIX*/sales_order_varchar AS _table_lastname ON (_table_lastname.entity_id = e.entity_id) AND (_table_lastname.attribute_id = @id_customer_lastname) 
  LEFT JOIN /*PREFIX*/sales_order_int AS _table_billing_address ON (_table_billing_address.entity_id = e.entity_id) AND (_table_billing_address.attribute_id = @id_billing_address_id) 
  LEFT JOIN /*PREFIX*/sales_order_int AS _table_shipping_address ON (_table_shipping_address.entity_id = e.entity_id) AND (_table_shipping_address.attribute_id = @id_shipping_address_id)
  LEFT JOIN /*PREFIX*/sales_order_varchar AS _table_shipping_description ON (_table_shipping_description.entity_id = e.entity_id) AND (_table_shipping_description.attribute_id = @id_shipping_description) 
 
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_company ON (_table_billing_company.entity_id = _table_billing_address.value) AND (_table_billing_company.attribute_id = @id_company)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_firstname ON (_table_billing_firstname.entity_id = _table_billing_address.value) AND (_table_billing_firstname.attribute_id = @id_firstname)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_lastname ON (_table_billing_lastname.entity_id = _table_billing_address.value) AND (_table_billing_lastname.attribute_id = @id_lastname)
  LEFT JOIN  /*PREFIX*/sales_order_entity_text AS _table_billing_street ON (_table_billing_street.entity_id = _table_billing_address.value) AND (_table_billing_street.attribute_id = @id_street)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_postcode ON (_table_billing_postcode.entity_id = _table_billing_address.value) AND (_table_billing_postcode.attribute_id = @id_postcode)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_city ON (_table_billing_city.entity_id = _table_billing_address.value) AND (_table_billing_city.attribute_id = @id_city)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_region ON (_table_billing_region.entity_id = _table_billing_address.value) AND (_table_billing_region.attribute_id = @id_region)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_country_id ON (_table_billing_country_id.entity_id = _table_billing_address.value) AND (_table_billing_country_id.attribute_id = @id_country_id)
  LEFT JOIN temporary_billing_country AS _temporary_billing_country ON (_temporary_billing_country.country_id = _table_billing_country_id.value)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_firstname ON (_table_shipping_firstname.entity_id = _table_shipping_address.value) AND (_table_shipping_firstname.attribute_id = @id_firstname)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_lastname ON (_table_shipping_lastname.entity_id = _table_shipping_address.value) AND (_table_shipping_lastname.attribute_id = @id_lastname)
  LEFT JOIN  /*PREFIX*/sales_order_entity_text AS _table_shipping_street ON (_table_shipping_street.entity_id = _table_shipping_address.value) AND (_table_shipping_street.attribute_id = @id_street)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_postcode ON (_table_shipping_postcode.entity_id = _table_shipping_address.value) AND (_table_shipping_postcode.attribute_id = @id_postcode)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_city ON (_table_shipping_city.entity_id = _table_shipping_address.value) AND (_table_shipping_city.attribute_id = @id_city)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_region ON (_table_shipping_region.entity_id = _table_shipping_address.value) AND (_table_shipping_region.attribute_id = @id_region)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_shipping_country_id ON (_table_shipping_country_id.entity_id = _table_shipping_address.value) AND (_table_shipping_country_id.attribute_id = @id_country_id)
  LEFT JOIN temporary_shipping_country AS _temporary_shipping_country ON (_temporary_shipping_country.country_id = _table_shipping_country_id.value)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_telephone ON (_table_billing_telephone.entity_id = _table_billing_address.value) AND (_table_billing_telephone.attribute_id = @id_telephone)
  LEFT JOIN  /*PREFIX*/sales_order_entity_varchar AS _table_billing_fax ON (_table_billing_fax.entity_id = _table_billing_address.value) AND (_table_billing_fax.attribute_id = @id_fax)
  
  INNER JOIN /*PREFIX*/sales_flat_order_item AS p ON (p.order_id = e.entity_id)

  LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS _table_method_name ON (_table_method_name.entity_id = e.entity_id) AND (_table_method_name.attribute_id = @id_method)
  LEFT JOIN temporary_payment_method AS _temporary_method_name ON (_temporary_method_name.id = _table_method_name.value)
  WHERE 1=1 /*FILTER*/
ORDER BY e.entity_id, p.product_id
```

## Export Orders DOBA ##

``sql
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'catalog_product';
SELECT attribute_id INTO @id_doba FROM /*PREFIX*/eav_attribute WHERE attribute_code = '%2:s' AND entity_type_id = @id_type;

SELECT t1.entity_id AS po_number,
  t1.created_at AS orders_date,
  t2.firstname AS shipping_firstname,
  t2.lastname AS shipping_lastname,
  t2.street AS shipping_street,
  t2.city AS shipping_city,
  cr.code AS shipping_state,
  ct.country_name AS shipping_country,
  t2.postcode AS shipping_postal,
  t2.company AS shipping_company,
  t1.`%0:s` AS doba_orders_id,
  t2.telephone AS shipping_phone
FROM /*PREFIX*/sales_flat_order t1
  LEFT JOIN /*PREFIX*/sales_flat_order_address t2 ON t1.entity_id = t2.parent_id
    AND t2.address_type = 'shipping'
  LEFT JOIN temporary_shipping_country AS ct ON (ct.country_id = t2.country_id)
  LEFT JOIN /*PREFIX*/directory_country_region AS cr ON (cr.region_id = t2.region_id)
WHERE (ISNULL(t1.`%0:s`)) AND
     (t1.`status` = '%1:s') AND
     (t2.country_id = 'US') AND
     (t1.entity_id IN
       (
            SELECT t10.order_id
            FROM /*PREFIX*/sales_flat_order_item t10
              INNER JOIN /*PREFIX*/catalog_product_entity_varchar t11 ON (t10.product_id = t11.entity_id)
                AND t11.attribute_id = @id_doba
            WHERE (NOT ISNULL(t11.value))
       )
     )
/*FILTER*/
ORDER BY t1.entity_id;
```

## Export Orders Products DOBA ##

```sql
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'catalog_product';
SELECT attribute_id INTO @id_doba FROM /*PREFIX*/eav_attribute WHERE attribute_code = '%0:s' AND entity_type_id = @id_type;

SELECT t3.value AS item_id, t1.qty_ordered AS quantity
FROM /*PREFIX*/sales_flat_order_item AS t1
  LEFT JOIN /*PREFIX*/sales_flat_order_item t2 ON t2.parent_item_id = t1.item_id
  INNER JOIN /*PREFIX*/catalog_product_entity_varchar t3 ON (t1.product_id = t3.entity_id)
    AND t3.attribute_id = @id_doba
WHERE (t1.order_id = %1:s)
  AND ((t1.parent_item_id IS NULL) OR (t1.parent_item_id = 0))
  AND (NOT ISNULL(t3.value));

----------------------------- 


SELECT
  e.entity_id AS OrderID,
  e.created_at AS Date,
  IFNULL(e.status, TRIM(SPACE(32))) AS Status,
  IFNULL(e.customer_email, TRIM(SPACE(32))) AS Name,
  IFNULL(e.customer_firstname, TRIM(SPACE(32))) AS FirstName,
  IFNULL(e.customer_lastname, TRIM(SPACE(32))) AS LastName,
  IFNULL(_table_billing_address.company, TRIM(SPACE(32))) AS Company,
  IFNULL(_table_billing_address.telephone, TRIM(SPACE(32))) AS Phone,
  TRIM(SPACE(32)) AS URL,
  IFNULL(e.customer_email, TRIM(SPACE(32))) AS Email
FROM /*PREFIX*/sales_flat_order e
LEFT JOIN /*PREFIX*/sales_flat_order_address AS _table_billing_address ON (_table_billing_address.entity_id = e.billing_address_id)
WHERE 1=1 /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id


```


## Export Products SQL ##

```sql
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'catalog_product';
SELECT attribute_id INTO @id_name FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'name' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_price FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'price' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_weight FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'weight' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_desc FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'description' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_status FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'status' AND entity_type_id = @id_type;


SELECT e.entity_id AS entity_id,
       e.entity_id AS ProductID,
       e.sku AS ProductCode,
       IFNULL(_product_name.value, _product_name_default.value) AS Product,
       IFNULL(_product_price.value, _product_price_default.value) AS Price,
       IF((IFNULL(_product_status.value, _product_status_default.value)), True, False) AS Active,
       IFNULL(_product_description.value, _product_description_default.value) AS Description,
       _qty.qty AS Quantity,
       IFNULL(_product_weight.value, _product_weight_default.value) AS Weight
FROM /*PREFIX*/catalog_product_entity e
     LEFT JOIN /*PREFIX*/catalog_product_entity_varchar _product_name ON e.entity_id = _product_name.entity_id
          AND _product_name.attribute_id = @id_name
          AND _product_name.store_id = %0:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_varchar _product_name_default ON e.entity_id = _product_name_default.entity_id
          AND _product_name_default.attribute_id = @id_name
          AND _product_name_default.store_id = %1:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_decimal _product_price ON _product_price.entity_id = e.entity_id
          AND _product_price.attribute_id = @id_price
          AND _product_price.store_id = %0:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_decimal _product_price_default ON _product_price_default.entity_id = e.entity_id
          AND _product_price_default.attribute_id = @id_price
          AND _product_price_default.store_id = %1:s
     LEFT JOIN /*PREFIX*/cataloginventory_stock_item _qty ON (_qty.product_id = e.entity_id)
          AND _qty.stock_id = %2:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_decimal _product_weight ON e.entity_id = _product_weight.entity_id
          AND _product_weight.attribute_id = @id_weight
          AND _product_weight.store_id = %0:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_decimal _product_weight_default ON e.entity_id = _product_weight_default.entity_id
          AND _product_weight_default.attribute_id = @id_weight
          AND _product_weight_default.store_id = %1:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_text _product_description ON e.entity_id = _product_description.entity_id
          AND _product_description.attribute_id = @id_desc
          AND _product_description.store_id = %0:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_text _product_description_default ON e.entity_id = _product_description_default.entity_id
          AND _product_description_default.attribute_id = @id_desc
          AND _product_description_default.store_id = %1:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_int _product_status ON e.entity_id = _product_status.entity_id
          AND _product_status.attribute_id = @id_status
          AND _product_status.store_id = %0:s
     LEFT JOIN /*PREFIX*/catalog_product_entity_int _product_status_default ON e.entity_id = _product_weight_default.entity_id
          AND _product_status_default.attribute_id = @id_status
          AND _product_status_default.store_id = %1:s
WHERE 1 = 1 /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id /* 0 Store_view, 1 Def_Store_View, 2 Def_Stock */
LIMIT 30000
```



## fund orders DOBA ##

```sql
SELECT t1.entity_id AS po_number, t1.`%0:s` AS doba_orders_id
FROM /*PREFIX*/sales_flat_order t1
WHERE (t1.`%0:s` IN(%1:s)) AND (t1.`status` = '%2:s')
ORDER BY t1.entity_id;
```


## Get Shipping Orders SQL ##
```sql
SELECT entity_type_id INTO @id_type_item FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'shipment_item';
SELECT attribute_id INTO @id_qty FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'qty' AND entity_type_id = @id_type_item;
SELECT attribute_id INTO @id_price FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'price' AND entity_type_id = @id_type_item;
SELECT attribute_id INTO @id_weight FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'weight' AND entity_type_id = @id_type_item;

SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'shipment';
SELECT attribute_id INTO @id_name FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'name' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_order_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'order_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_shipping_address_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'shipping_address_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_company FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'company' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_firstname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_lastname FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_street FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'street' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_city FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'city' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_region_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'region_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_country_id FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'country_id' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_postcode FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'postcode' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_telephone FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'telephone' AND entity_type_id = @id_type;

SELECT entity_type_id INTO @id_type_track FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'shipment_track';
SELECT attribute_id INTO @id_title FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'title' AND entity_type_id = @id_type_track;
SELECT attribute_id INTO @id_number FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'number' AND entity_type_id = @id_type_track;


SELECT e.entity_id AS OrderID,
       DATE(e.created_at) AS OrderDate,
       CAST('0' AS DECIMAL) AS ShippingCost,
       (SELECT SUM(IFNULL(table_shipment_item_price2.value, 0) * IFNULL(table_shipment_item_qty2.value, 0))      
        FROM /*PREFIX*/sales_order_entity e2
        LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_price2 ON (table_shipment_item_price2.entity_id = e2.entity_id) AND (table_shipment_item_price2.attribute_id = @id_price)
        LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_qty2 ON (table_shipment_item_qty2.entity_id = e2.entity_id) AND (table_shipment_item_qty2.attribute_id = @id_qty)
        WHERE e2.entity_type_id = @id_type_item AND e2.parent_id = e.entity_id) AS Total,
       (SELECT SUM(IFNULL(table_shipment_item_weight2.value, 0))      
        FROM /*PREFIX*/sales_order_entity e2
        LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_weight2 ON (table_shipment_item_weight2.entity_id = e2.entity_id) AND (table_shipment_item_weight2.attribute_id = @id_weight)
        WHERE e2.entity_type_id = @id_type_item AND e2.parent_id = e.entity_id) AS Weight,
       TRIM(SPACE(32)) AS `Status`,
       IFNULL(table_shipping_address_company.value, TRIM(SPACE(32))) AS SCompany,
       TRIM(CONCAT(IFNULL(table_shipping_address_firstname.value, TRIM(SPACE(32))), ' ', IFNULL(table_shipping_address_lastname.value, TRIM(SPACE(32))))) AS SFullName,
       IFNULL(table_shipping_address_firstname.value, TRIM(SPACE(32))) AS SFirstname,
       IFNULL(table_shipping_address_lastname.value, TRIM(SPACE(32))) AS SLastname,
       IFNULL(table_shipping_address_street.value, TRIM(SPACE(32))) AS SAddress1,
       TRIM(SPACE(64)) AS SAddress2,
       IFNULL(table_shipping_address_city.value, TRIM(SPACE(32))) AS SCity,
       TRIM(SPACE(32)) AS SCounty,
       IFNULL(table_shipping_address_region.code, TRIM(SPACE(32))) AS SState,
       IFNULL(table_shipping_address_country.value, TRIM(SPACE(32))) AS SCountry,
       IFNULL(table_shipping_address_postcode.value, TRIM(SPACE(32))) AS SZipCode,
       IFNULL(table_shipping_address_telephone.value, TRIM(SPACE(32))) AS SPhone,
       IFNULL(table_shipment_track_title.value, TRIM(SPACE(32))) AS ShippingName,
       IFNULL(table_shipment_track_number.value, TRIM(SPACE(32))) AS TrackingNumber
FROM /*PREFIX*/sales_order_entity e
INNER JOIN /*PREFIX*/sales_order_entity_int AS table_order_id ON (table_order_id.entity_id = e.entity_id) AND (table_order_id.attribute_id = @id_order_id) 
INNER JOIN /*PREFIX*/sales_order_entity_int AS table_shipping_address_id ON (table_shipping_address_id.entity_id = e.entity_id) AND (table_shipping_address_id.attribute_id = @id_shipping_address_id)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_company ON (table_shipping_address_company.entity_id = table_shipping_address_id.value) AND (table_shipping_address_company.attribute_id = @id_company)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_firstname ON (table_shipping_address_firstname.entity_id = table_shipping_address_id.value) AND (table_shipping_address_firstname.attribute_id = @id_firstname)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_lastname ON (table_shipping_address_lastname.entity_id = table_shipping_address_id.value) AND (table_shipping_address_lastname.attribute_id = @id_lastname)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_street ON (table_shipping_address_street.entity_id = table_shipping_address_id.value) AND (table_shipping_address_street.attribute_id = @id_street)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_city ON (table_shipping_address_city.entity_id = table_shipping_address_id.value) AND (table_shipping_address_city.attribute_id = @id_city)
LEFT JOIN /*PREFIX*/sales_order_entity_int AS table_shipping_address_region_id ON (table_shipping_address_region_id.entity_id = table_shipping_address_id.value) AND (table_shipping_address_region_id.attribute_id = @id_region_id)
LEFT JOIN /*PREFIX*/directory_country_region AS table_shipping_address_region ON (table_shipping_address_region.region_id = table_shipping_address_region_id.value)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_country ON (table_shipping_address_country.entity_id = table_shipping_address_id.value) AND (table_shipping_address_country.attribute_id = @id_country_id)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_postcode ON (table_shipping_address_postcode.entity_id = table_shipping_address_id.value) AND (table_shipping_address_postcode.attribute_id = @id_postcode)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipping_address_telephone ON (table_shipping_address_telephone.entity_id = table_shipping_address_id.value) AND (table_shipping_address_telephone.attribute_id = @id_telephone)

LEFT JOIN /*PREFIX*/sales_order_entity AS table_shipment_track_id ON (table_shipment_track_id.parent_id = e.entity_id) AND (table_shipment_track_id.entity_type_id = @id_type_track)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipment_track_title ON (table_shipment_track_title.entity_id = table_shipment_track_id.entity_id) AND (table_shipment_track_title.attribute_id = @id_title)
LEFT JOIN /*PREFIX*/sales_order_entity_text AS table_shipment_track_number ON (table_shipment_track_number.entity_id = table_shipment_track_id.entity_id) AND (table_shipment_track_number.attribute_id = @id_number)
WHERE e.entity_type_id = @id_type /*FILTER*/
/*LIMIT 10*/
```


## Mapped Customers SQL ##
```sql
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'customer';
SELECT attribute_id INTO @id_firstname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_lastname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type;

SELECT
  e.entity_id AS entity_id,
  e.entity_id AS customer_id,
  e.email AS Name,
  IFNULL(_table_firstname.value, TRIM(SPACE(32))) AS FirstName,
  IFNULL(_table_lastname.value, TRIM(SPACE(32))) AS LastName,
  TRIM(SPACE(32)) AS Company, 
  CONCAT(_table_firstname.value, ' ', _table_lastname.value ) AS customer
FROM  /*PREFIX*/customer_entity AS e
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_firstname ON (_table_firstname.entity_id = e.entity_id) AND (_table_firstname.attribute_id = @id_firstname)
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_lastname ON (_table_lastname.entity_id = e.entity_id) AND (_table_lastname.attribute_id = @id_lastname)
WHERE 1=1 /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id
```


##  Mapped Customers ##

```sql
SELECT entity_type_id INTO @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'customer';
SELECT attribute_id INTO @id_firstname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'firstname' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_lastname FROM  /*PREFIX*/eav_attribute WHERE attribute_code = 'lastname' AND entity_type_id = @id_type;

SELECT
  e.entity_id AS entity_id,
  e.entity_id AS customer_id,
  e.email AS Name,
  IFNULL(_table_firstname.value, TRIM(SPACE(32))) AS FirstName,
  IFNULL(_table_lastname.value, TRIM(SPACE(32))) AS LastName,
  TRIM(SPACE(32)) AS Company, 
  CONCAT(_table_firstname.value, ' ', _table_lastname.value ) AS customer
FROM  /*PREFIX*/customer_entity AS e
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_firstname ON (_table_firstname.entity_id = e.entity_id) AND (_table_firstname.attribute_id = @id_firstname)
  LEFT JOIN  /*PREFIX*/customer_entity_varchar AS _table_lastname ON (_table_lastname.entity_id = e.entity_id) AND (_table_lastname.attribute_id = @id_lastname)
WHERE 1=1 /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id
```


## Mapped Products ###

```sql
SELECT entity_type_id INTO @ID_TYPE FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'catalog_product';
SELECT attribute_id INTO @ID_NAME FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'name' AND entity_type_id = @ID_TYPE;

SELECT
 e.entity_id AS entity_id,
 e.entity_id AS ProductID,
 e.sku AS ProductCode,
 IFNULL(IFNULL(_product_name.value, _product_name_default.value), TRIM(SPACE(32))) AS Product
FROM /*PREFIX*/catalog_product_entity e 
LEFT JOIN /*PREFIX*/catalog_product_entity_varchar _product_name ON e.entity_id = _product_name.entity_id 
  AND _product_name.attribute_id = @ID_NAME
  AND _product_name.store_id = %0:s
LEFT JOIN /*PREFIX*/catalog_product_entity_varchar _product_name_default ON e.entity_id = _product_name_default.entity_id
  AND _product_name_default.attribute_id = @ID_NAME
  AND _product_name_default.store_id = %1:s
WHERE 1=1 /*FILTER*/
GROUP BY e.entity_id
ORDER BY e.entity_id
```

## Order - Products ##

```sql
SELECT entity_type_id SET @id_type FROM /*PREFIX*/eav_entity_type WHERE entity_type_code = 'shipment_item';

SELECT attribute_id INTO @id_sku FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'sku' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_name FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'name' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_weight FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'weight' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_qty FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'qty' AND entity_type_id = @id_type;
SELECT attribute_id INTO @id_price FROM /*PREFIX*/eav_attribute WHERE attribute_code = 'price' AND entity_type_id = @id_type;

SELECT e.entity_id AS ProductID,
       IFNULL(table_shipment_item_sku.value, TRIM(SPACE(32))) AS ProductCode,
       IFNULL(table_shipment_item_name.value, TRIM(SPACE(32))) AS Product,
       IFNULL(table_shipment_item_weight.value, 0) * IFNULL(table_shipment_item_qty.value, 0) AS Weight,      
       IFNULL(table_shipment_item_price.value, TRIM(SPACE(32))) AS Price,
       IFNULL(table_shipment_item_qty.value, TRIM(SPACE(32))) AS Qty
FROM /*PREFIX*/sales_order_entity e
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipment_item_sku ON (table_shipment_item_sku.entity_id = e.entity_id) AND (table_shipment_item_sku.attribute_id = @id_sku)
LEFT JOIN /*PREFIX*/sales_order_entity_varchar AS table_shipment_item_name ON (table_shipment_item_name.entity_id = e.entity_id) AND (table_shipment_item_name.attribute_id = @id_name)
LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_weight ON (table_shipment_item_weight.entity_id = e.entity_id) AND (table_shipment_item_weight.attribute_id = @id_weight)
LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_qty ON (table_shipment_item_qty.entity_id = e.entity_id) AND (table_shipment_item_qty.attribute_id = @id_qty)
LEFT JOIN /*PREFIX*/sales_order_entity_decimal AS table_shipment_item_price ON (table_shipment_item_price.entity_id = e.entity_id) AND (table_shipment_item_price.attribute_id = @id_price)
WHERE e.entity_type_id = @id_type /*FILTER*/

---------------

SELECT e.entity_id AS ProductID,
       IFNULL(e.sku, TRIM(SPACE(32))) AS ProductCode,
       IFNULL(e.name, TRIM(SPACE(32))) AS Product,
       IFNULL(e.weight, 0) * IFNULL(e.qty, 0) AS Weight,
       IFNULL(e.price, 0) AS Price,
       IFNULL(e.qty, 0) AS Quantity
FROM /*PREFIX*/sales_flat_shipment_item e
WHERE 1=1 /*FILTER*/
```








