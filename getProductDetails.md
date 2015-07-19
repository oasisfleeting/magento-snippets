## Get Product Details
```
$product = Mage::getModel('catalog/product')->load($productId);
$attributes = $product->getAttributes();
```

## getAllProductDetails
```
foreach ($attributes as $attribute) {    	
	$attributeCode = $attribute->getAttributeCode();
	$label = $attribute->getStoreLabel($product);	
	$value = $attribute->getFrontend()->getValue($product);
	echo $attributeCode . '-' . $label . '-' . $value; 
	echo "<br />";	    
} 
```

## getFrontEndProductDetails
```
foreach ($attributes as $attribute) {
    if ($attribute->getIsVisibleOnFront()) {
		$attributeCode = $attribute->getAttributeCode();
		$label = $attribute->getFrontend()->getLabel($product);		
        $value = $attribute->getFrontend()->getValue($product);
        echo $attributeCode . '-' . $label . '-' . $value; echo "<br />";		
    }
}
```

## getIndividualProductDetails
```
foreach ($attributes as $attribute) {    
	$attributeCode = $attribute->getAttributeCode();
	$code = 'color';
	if ($attributeCode == $code) {
		$label = $attribute->getStoreLabel($product);	
		$value = $attribute->getFrontend()->getValue($product);
		echo $attributeCode . '-' . $label . '-' . $value;
	} 
}
```
