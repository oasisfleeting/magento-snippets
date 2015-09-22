<?php

						if($fullAction == 'checkout_cart_product_add_after') {

							  //$this->log($fullAction);

							  $item = $observer->getEvent()->getQuoteItem();
							  $infoArr = array();
							  $options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
							  $addOpts = Mage::app()->getRequest()->getParam('extra_options');
							  foreach($addOpts as $adOptKey => $adOptVal) {
									foreach($options as $option) {
										  //$this->log($option['code']);
										  //is there a danger of this being merged twice
										  // in the case of two similarinstances of $option['label'] occuring
										  // one after the other?
										  //
										  // The only way to identify a custom option without
										  // hardcoding ID's is the label :-(
										  // But manipulating options this way is hackish anyway
										  if($adOptKey === $option['label']) {

												//$optId = $option['option_id'];

												//$additionalOptions = array(
												//	'label'       => $adOptKey,					//['']; //Fabric Choice
										  		//	'value'       => $adOptVal,					//['']; //Big-Holed-Mesh-White
										  		//	'print_value' => $adOptVal,					//['']; //Big-Holed-Mesh-White
										  		//	'option_id'   => $option['option_id'], 		//108105
										  		//	'option_type' => $option['option_type'], 	//drop_down
										  		//	'custom_view' => $option['custom_view'], 	//
										  		//	'code'        => $option['code']			//option_108105
												//);

												//bozoSquad! Transform and roll out!
												//$option = array_merge($option,$optionIdsOption);

												// Remove real custom option id from option_ids list
												$optionIdsOption = $item->getProduct()->getCustomOption('option_ids');
												if ($optionIdsOption) {
													  $optionIds = explode(',', $optionIdsOption->getValue());

													  $this->log($optionIds);

													  if (false !== ($idx = array_search($optId, $optionIds))) {
															//unset($optionIds[$idx]);
															$optionIdsOption->setValue(implode(',', $optionIds));
															$item->addOption($optionIdsOption);
													  }
												}
										  }
									}
							  }
						}
