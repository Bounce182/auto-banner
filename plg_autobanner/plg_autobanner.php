<?php
/**
 * Joomla! 1.5 plugin
 *
 * @author ANV
 * @package Joomla
 * @subpackage Plugin Auto Banner
 * @license GNU/GPL
 *
 * Plugin Auto Banner
 *
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// include banners helper functions
require_once (dirname(__FILE__).DS.'plg_autobanner'.DS.'helpers'.DS.'helper.php');

jimport('joomla.plugin.plugin');


class plgContentPlg_AutoBanner extends JPlugin{

	var $_plugin_params;

 	/**
    * plgAutoBanner
    *
    * Plugin constructor
    * 
    */
    function plgContentPlg_AutoBanner(&$subject){
        
		parent::__construct($subject);		
		
		// loading plugin parameters
        $plugin = JPluginHelper::getPlugin('content', 'plg_autobanner');
        $params = new JParameter($plugin->params);
		
		$this->_plugin_params['paragraph_divider']     = preg_split('/,/',$params->get('paragraph_divider'),-1, PREG_SPLIT_NO_EMPTY);
		$this->_plugin_params['paragraph_list']        = $params->get('paragraph_list');
		$this->_plugin_params['top_banner_divider']    = $params->get('top_banner_divider');
		$this->_plugin_params['bot_banner_divider']    = $params->get('bot_banner_divider');
		$this->_plugin_params['banner_client']    	   = $params->get('banner_client');
		$this->_plugin_params['banner_category']  	   = $params->get('banner_category');
		$this->_plugin_params['banner_randomise']      = $params->get('banner_randomise');
		$this->_plugin_params['content_section_list']  = $params->get('content_section_list');
		$this->_plugin_params['content_category_list'] = $params->get('content_category_list');		
		$this->_plugin_params['ban_on_frontpage']      = $params->get('ban_on_frontpage');	
    }
	
	/**
	* Show banners in articles.
	*
	* @param object Content item
	* @param JParameter Content parameters
	* @param int Page number
	*/

	function onPrepareContent(&$article, &$params, $limitstart){							
		
		$sectionList 		= array();
		$catList     		= array();
		$pArr        		= array();   //paragraphs positions array
		$banners			= array();
		
		$paragraphList		= $this->_plugin_params['paragraph_list'];
		$sectionList 		= $this->_plugin_params['content_section_list'];
		$catList     		= $this->_plugin_params['content_category_list'];
		$bannerClient 		= $this->_plugin_params['banner_client'];
		$bannerCat 			= $this->_plugin_params['banner_category'];
		$bannerCount		= sizeof($this->_plugin_params['paragraph_list']);
		$bannerRand  		= $this->_plugin_params['banner_randomise'];
		$pDividers   		= $this->_plugin_params['paragraph_divider'];
		$topBannerDiv	    = $this->_plugin_params['top_banner_divider'];
		$botBannerDiv	    = $this->_plugin_params['bot_banner_divider'];
		$showOnFrontPage    = $this->_plugin_params['ban_on_frontpage'];
		
		
		$view = JRequest::getVar('view','','request','string');
		
		//if do not show banners on front page
		if( $view == 'frontpage' && $showOnFrontPage == 0 ){
			return;	
		}
		
		
		//check section 
		
		if( is_array($sectionList)){		//if selected more than one section
			if( !in_array( $article->sectionid, $sectionList )){			
				return;
			}
		}
		else{								//if only one item is selected	
			if( $article->sectionid != $sectionList ){			
				return;
			}
		}
		
		//check categories
		
		if( is_array($catList)){		//if selected more than one section
			if( !in_array( $article->catid, $catList )){			
				return;
			}
		}
		else{								//if only one item is selected	
			if( $article->catid != $catList ){			
				return;
			}
		}
		
			
		$pDividers = $this->_plugin_params['paragraph_divider'];
				
		
		if( count($pDividers) != 0 ){
			$pArr = $this->_countParagraphs( $article->text, $pDividers );
			
			if( sizeof( $pArr ) == 0 ){
				return;
			}
		}
		else{
			echo '<span style="color:#ff0000;">'.JText::_('Plugin plg_autobanner: Error. You must input paragraph divider param in admin zone.').'</span>';	
			return;
		}
			
		//get all banners
		$list = $this->_getBannersList( $bannerClient, $bannerCat, $bannerCount, $bannerRand );						
			
		if( sizeof($list) > 0 ){
			$banners = $this->_renderBanners( $list );	
				
			$this->_insertBannerCodeIntoPlaces( $article->text, $paragraphList, $banners, $pArr, $topBannerDiv, $botBannerDiv );
				
		}						
	
	}
	
	/**

	* Count paragraphs in text. 
	* <p>,<br /><br />,<p><br /> - 1 paragraph
	*
	* @param object Article text
	*/
	function _countParagraphs( $text, $pDividers ){						
		
		$pCounter = 1;
		$pArr = array();  				//paragraph array

		foreach( $pDividers as $key => $val){
			
			$run = true;
			$firstRun = true;
			$pos = 0; 
			
			while( $run != false){
				if( $firstRun ){
					$pos = strpos( $text, $val );	
				}
				else{
					$pos = strpos( $text, $val, $pos + strlen( $val ) );	
				}
			
				if( $firstRun || $pos ){
					$pArr[$pCounter++] = $pos;  
					$firstRun = false;
				}
				else{
					$run = false;	
				}
			
			}
		}
		
		//remove empty values from array(if is there any). depending on if( $firstRun || $pos ){...}
		
		foreach ($pArr as $key => $value) { 
  			
			$value = (int)$value;
			
			if ( $key > 1 && $value == 0) { 
    			unset($pArr[$key]); 
  			} 
		} 			
		
		sort( $pArr, SORT_NUMERIC ); 
		$pArr = array_unique( $pArr );
		
		return $pArr;
		
	}
	
	/**
	* Render banners html
	* @param array Banners data list
	* @return banners html array
	*/
	function _renderBanners( $list ){
		$banners = array();
		
		foreach($list as $item){
			$banners[] = plgAutoBannerHelper::renderBanner( $item );
		}

		return $banners;
	}		
	
	/**
	* Insert banner code into text. 
	* @params article html,paragraph list from params, banners html, paragraph positions
	*/
	function _insertBannerCodeIntoPlaces( &$html, $paragraphList, $banners, $pArr, $topBannerDiv, $botBannerDiv ){	
						
		$first = true;
		$bannerHtmlLen = array();
		$i=0;
		$n = sizeof( $banners );
		
		if( sizeof( $paragraphList ) == 1){
			$temp = $paragraphList;
			$paragraphList = array();
			$paragraphList[] = $temp;
		}
		
		foreach ( $paragraphList as $p ){
			if( isset( $pArr[ $p-1 ] ) ){
				
				if( $i >= $n ){
					$i=0;	
				}			
					
				$bannerHtml = $topBannerDiv.$banners[$i++].$botBannerDiv;
				$bannerHtmlLen[] = strlen( $bannerHtml ); 

				if( $first == true ){
					$html = substr( $html, 0, $pArr[ $p-1 ] ) . $bannerHtml . substr( $html, $pArr[ $p-1 ] );
					$first = false;
				}
				else{
					$totalLength = $this->_countTotalLength( $bannerHtmlLen );
					$html = substr( $html, 0, $pArr[ $p-1 ] +  $totalLength ) . $bannerHtml . substr( $html, $pArr[ $p-1 ] + $totalLength );	
				}					
				
			}
		}				
		
		return $html;
		
	}
	
	/**
	* Count total string length
	* @params array banners html length
	* @return int total length
	*/
	function _countTotalLength( $bannerHtmlLen ){
		$totalLength = 0;
		for( $i=0; $i < sizeof($bannerHtmlLen)-1;  $i++ ){
			$totalLength += $bannerHtmlLen[ $i ];	
		}
		return $totalLength;
	}
	
	
	/**
	* Get banners list
	* @params client list, category list, randomise
	* @return array banners list
	*/
	function _getBannersList( $bannerClient, $bannerCat, $bannerCount, $bannerRand ){						
		
		return plgAutoBannerHelper::getList($bannerClient, $bannerCat, $bannerCount, $bannerRand );
		
	}
	
	
}

?>