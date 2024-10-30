<?php
/*
Plugin Name: Contact Form 7 : Wysiwyg Field
Plugin URI: http://www.devictio.fr
Description: Add wysiwyg fields to the popular Contact Form 7 plugin.
Author: Nicolas Grillet.
Author URI: http://www.erreurs404.net
Version: 1.5
*/

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'liens_pages_extensions_cf7wf' );
function liens_pages_extensions_cf7wf( $links ) {
   $links[] = '<a href="http://www.devictio.fr target="_blank">www.devictio.fr <img src="http://apps.devictio.fr/Contact_Form7_Wysiwyg_Field.png" alt="logo" /></a>';
   return $links;
}

add_action( 'init', 'wpcf7_add_shortcode_wysiwyg',5 );

function wpcf7_add_shortcode_wysiwyg() {
    add_filter( 'wpcf7_mail_components', 'wpcf7_wysiwyg_validation_filter', 10, 2 );
	wpcf7_add_shortcode( array( 'wysiwyg', 'wysiwyg*' ),
		'wpcf7_wysiwyg_shortcode_handler', true );
}
function convert_HTML(&$c){
    $c[1]['body']=strip_tags(html_entity_decode($c[1]['body']),"<p><a><br><br /><ul><li><ol><del><ins><img><code><em><strong><blockquote>");    
    return $c;
}
function wpcf7_wysiwyg_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' wpcf7-not-valid';

	$atts = array();

	$atts['cols'] = $tag->get_cols_option( '40' );
	$atts['rows'] = $tag->get_rows_option( '10' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	if ( $tag->has_option( 'readonly' ) )
		$atts['readonly'] = 'readonly';

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );

	if ( '' !== $tag->content )
		$value = $tag->content;

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}

	if ( wpcf7_is_posted() && isset( $_POST[$tag->name] ) )
		$value = stripslashes_deep( $_POST[$tag->name] );

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );
	ob_start();
	$settings = array( 
		'media_buttons' => false ,
		'textarea_name' => $tag->name,
		'teeny'=>true,
		'editor_class'=>"wpcf7_form_novalidate ".$tag->get_class_option( $class ),
		'wpautop'=>false
	);
	wp_enqueue_script('jquery');
	wp_editor( $value, $tag->name,$settings);
	$h=ob_get_contents();
	$html = '<span class="wpcf7-form-control-wrap '.$tag->name.'">'.$h.$validation_error.'</span>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery(".wpcf7-form").submit(function(e){
			jQuery("#'.$tag->name.'").val(tinyMCE.get("'.$tag->name.'").getContent());
			return true;
		});
	});
	</script>';
	ob_end_clean();
	return $html;
}


/* Validation filter */

add_filter( 'wpcf7_validate_wysiwyg*', 'wpcf7_wysiwyg_validation_filter', 10, 2 );

function wpcf7_wysiwyg_validation_filter( $result, $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	$type = $tag->type;
	$name = $tag->name;

	$value = isset( $_POST[$name] ) ? $_POST[$name] : '';

	if ( 'wysiwyg*' == $type ) {
		if ( '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = wpcf7_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'wpcf7_add_tag_generator_wysiwyg', 20 );

function wpcf7_add_tag_generator_wysiwyg() {
	if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
		return;

	wpcf7_add_tag_generator( 'wysiwyg', __( 'wysiwyg', 'contact-form-7' ),
		'wpcf7-tg-pane-wysiwyg', 'wpcf7_tg_pane_wysiwyg' );
}

function wpcf7_tg_pane_wysiwyg( &$contact_form ) {
?>
<div id="wpcf7-tg-pane-wysiwyg" class="hidden">
<form action="">
<table>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'contact-form-7' ) ); ?></td></tr>
<tr><td><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><code>cols</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="number" name="cols" class="numeric oneline option" min="1" /></td>

<td><code>rows</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="number" name="rows" class="numeric oneline option" min="1" /></td>
</tr>

<tr>
<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
<input type="number" name="maxlength" class="numeric oneline option" min="1" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br /><input type="text" name="values" class="oneline" /></td>

<td>
<br /><input type="checkbox" name="placeholder" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as placeholder?', 'contact-form-7' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'contact-form-7' ) ); ?><br /><input type="text" name="wysiwyg" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'contact-form-7' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}


?>