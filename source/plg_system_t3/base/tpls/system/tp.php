<?php 
	$cls = array('t3-layout-pos', 'block-' . $vars['name']);
	$attr = '';
	
	if(isset($vars['data-original'])){
		$attr = ' data-original="'. $vars['data-original'] . '"';
	} else {
		$cls[] = 't3-layout-uneditable'; 
	}
?>
<div class="<?php echo implode(' ', $cls) ?>"<?php echo $attr ?>>
	<h3><?php echo $vars['name'] ?></h3>
</div>