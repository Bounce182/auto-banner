<?php


// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'helpers'.DS.'banner.php');

class plgAutoBannerHelper{
	
	function getList( $bannerClient, $bannerCat, $bannerCount, $bannerRand ){		
		
		
		$model		= plgAutoBannerHelper::getModel();

		// Model Variables
		$vars['cid']		= $bannerClient;
		$vars['catid']		= $bannerCat;
		$vars['limit']		= $bannerCount;
		$vars['ordering']	= $bannerRand ? 'random' : 0;		
		
		$banners = $model->getList( $vars );
		$model->impress( $banners );

		return $banners;
	}

	function getModel(){
		
		if (!class_exists( 'BannersModelBanner' )){
			
			// Build the path to the model based upon a supplied base path
			$path = JPATH_SITE.DS.'components'.DS.'com_banners'.DS.'models'.DS.'banner.php';
			$false = false;

			// If the model file exists include it and try to instantiate the object
			if (file_exists( $path )) {
				require_once( $path );
				if (!class_exists( 'BannersModelBanner' )) {
					JError::raiseWarning( 0, 'Model class BannersModelBanner not found in file.' );
					return $false;
				}
			} else {
				JError::raiseWarning( 0, 'Model BannersModelBanner not supported. File not found.' );
				return $false;
			}
		}

		$model = new BannersModelBanner();
		return $model;
	}

	function renderBanner( &$item ){
		
		
		
		$link = JRoute::_( 'index.php?option=com_banners&task=click&bid='. $item->bid );
		$baseurl = JURI::base();
		
		$params = &JComponentHelper::getParams( 'com_banners' );
		
		
		$html = '';
		if (trim($item->custombannercode)){
			
			// template replacements
			$html = str_replace( '{CLICKURL}', $link, $item->custombannercode );
			$html = str_replace( '{NAME}', $item->name, $html );
		}
		else if (BannerHelper::isImage( $item->imageurl )){
			
			$image 	= '<img src="'.$baseurl.'images/banners/'.$item->imageurl.'" alt="'.JText::_('Banner').'" />';
			if ($item->clickurl){
				
				switch ($params->get( 'target', 1 )){
					
					// cases are slightly different
					case 1:
						// open in a new window
						$a = '<a href="'. $link .'" target="_blank">';
						break;

					case 2:
						// open in a popup window
						$a = "<a href=\"javascript:void window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\">";
						break;

					default:	// formerly case 2
						// open in parent window
						$a = '<a href="'. $link .'">';
						break;
					}

				$html = $a . $image . '</a>';
			}
			else{				
				$html = $image;
			}
		}
		else if (BannerHelper::isFlash( $item->imageurl )){
			
			//echo $item->params;
			$banner_params = new JParameter( $item->params );
			$width = $banner_params->get( 'width');
			$height = $banner_params->get( 'height');

			$imageurl = $baseurl."images/banners/".$item->imageurl;
					
			 $clickurl = $item->clickurl;
         	$html_flash =   "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0\" border=\"0\" width=\"$width\" height=\"$height\">
                  <param name=\"movie\" value=\"$imageurl\">
                  <param name=\"wmode\" value=\"opaque\">
               <embed src=\"$imageurl\" pluginspage=\"http://www.macromedia.com/go/get/flashplayer\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\" wmode=\"opaque\"></embed>
               </object>";
         	if ($clickurl != '') {  //Add a GIF based clickthrough IF there is a link supplied in teh 'Click url' for this banner in the banner manager.
            	$html = '<div style="position:relative;z-index:1;" style="background:#fff;">';  //Add the banner clickthrough
            	$html .= $html_flash;
            	$html .= '<a href="'.$link.'" target="_blank" style="display:block;position:absolute;width:'.$width.'px;height:'.$height.'px;z-index:9999;top:0px;left:0px;border:none;background:none;"><img src="http://www.mazeikieciai.lt/lt/components/com_banners/x.gif" style="width:'.$width.'px;height:'.$height.'px;" alt="Banner Campaign" /></a>   ';
            $html .= '</div>';
        	 } else {  //Otherwise use the orignal code, so that the flash banner still goes to the right place... (instead of a blank page!)
            $html .= $html_flash;
         }
			
		}

		return $html;
	}
}
