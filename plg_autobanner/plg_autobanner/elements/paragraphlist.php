<?php 

defined('_JEXEC') or die( 'Restricted access' );

class JElementParagraphList extends JElement{

	var	$_name = 'ParagraphList';	
	
	/**
	* fetch Element 
	*/
	function fetchElement($name, $value, &$node, $control_name){
		
		// Base name of the HTML control.				
        $ctrl  = $control_name .'['. $name .']';
 
        // Construct an array of the HTML OPTION statements.
        $options = array ();
        
		for( $i=1; $i<=100; $i++){
		  	$val = $i;
			$text = $i;
            $options[] = JHTML::_('select.option', $val, $text);
        }
 
        // Construct the various argument calls that are supported.
        $attribs = '';
        
		if ($v = $node->attributes( 'size' )) {
        	$attribs .= ' size="'.$v.'"';
        }
        
		if ($v = $node->attributes( 'class' )) {
        	$attribs .= ' class="'.$v.'"';
        } else {
        	$attribs .= ' class="inputbox"';
        }
        
		if ($m = $node->attributes( 'multiple' )){
        	$attribs .= ' multiple="multiple"';
            $ctrl    .= '[]';
        }
		
		if($s = $node->attributes( 'style' )){
			$attribs	.= ' style="'.$s.'"'; 
		}
 
        // Render the HTML SELECT list.
        return JHTML::_('select.genericlist', $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name );
		
	}
	

}

?>
