<?php
/*
Plugin Name: Individual Page
Plugin URL: http://individualpage.bnmng.com
Description: Opens the post content as an independent html page without the WP framework.  Settings are under the Appearance menu.
Version: 1.1
Author: Benjamin Goldberg
Author URI: https://bnmng.com
Text Domain: bnmng-individual-page
Licence: GPL2
 */

/* 
 * Place the shortcode on the post
 */
function bnmng_individual_page_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'set' => '',
		'css_head' => '',
		'css_url' => '',
		'link_style' =>  '',
		'link_button' => '',
		'link_text' => 'Individual Page',
	), $atts, 'bnmng_individual_page' );

	if( intval( get_the_ID() ) > 0 ) {
		$set_key = '';
		if ( '' < $atts['set'] ) {
			$set_name = sanitize_key( $atts['set'] );
			if ( '' < $set_name ) {

				$option_name = 'bnmng_individual_page';
				$options = get_option( $option_name );
				if ( is_array( $options ) ) {
					if ( isset( $options['sets'] ) && is_array( $options['sets'] ) ) {
						$sets = $options['sets'];
						$set_key = array_search( $set_name, array_column( $sets, 'set_name' ) );
						if ( is_numeric( $set_key ) ) {
							$set = $sets[ $set_key ];

							if (  ! ( '' < $atts['css_head'] ) ) {
								if ( isset( $set['css_head'] ) && '' < $set['css_head'] ) {
									$atts['css_head'] = wp_strip_all_tags( $set['css_head'] );
								}
							}
							if ( ! '' < $atts['css_url'] ) {
								if ( isset( $set['css_url'] ) && $set['css_url'] ) {
									$atts['css_url'] = wp_http_validate_url( $set['css_url'] );
								}
							}
							if ( ! '' < $atts['link_style'] ) {
								if ( isset( $set['link_style'] ) && $set['link_style'] ) {
									$atts['link_style'] = wp_strip_all_tags( $set['link_style'] );
								}
							}
						}
					}
				}
			}
		}
		$link_attributes  = 'name="bnmng_individual_page" ';
		$link_attributes .= 'data-post_id="' . get_the_ID() . '" ';
		$link_attributes .= 'data-set_key="' . $set_key . '" ';
		$link_attributes .= 'data-css_head="' . $atts['css_head'] . '" ';
		$link_attributes .= 'data-css_url="' . $atts['css_url'] . '" ';
		$link_attributes .= 'style="' . $atts['link_style'] . '" ';

		$link_element = '<a href="#" ' . $link_attributes . '>' . $atts['link_text'] . '</a>';

		return $link_element;
	}
}
add_shortcode( 'individual_page', 'bnmng_individual_page_shortcode' );

/*
 * Place the javascript at the end of page 
 */
function bnmng_individual_page_javascript() { ?>
<script type="text/javascript" >
	var links = document.getElementsByName("bnmng_individual_page");
	for ( var l = 0; l < links.length; l++ ) {
		links[ l ].addEventListener( 'click',  function ( event ) {
			event.preventDefault();
			var stamp = Date.now();
			var data = {
				'action': 'bnmng_individual_page',
				'stamp' : stamp,
				'post_id': this.dataset.post_id,
				'set_key': this.dataset.set_key,
				'css_url': this.dataset.css_url,
				'css_head': this.dataset.css_head,
			};
			var ajaxRequest = new XMLHttpRequest();
			ajaxRequest.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var response = this.responseText;

					var individualPage = window.open("", "Individual_Document", "width=500,height=700");
					makePage();

					function makePage() {

						var title_reg = new RegExp( '\<title_' + stamp + '\>(.*)\<\/title_' + stamp + '\>' );
						var title_matches = response.match( title_reg );

						
						var cssUrlRegExp = new RegExp( '\<css_url_' + stamp + '\>(.*)\<\/css_url_' + stamp + '\>' );
						var cssUrlMatches = response.match( cssUrlRegExp );
						if ( typeof cssUrlMatches !== 'undefined' && null !== cssUrlMatches ) {
							var cssUrlElement = individualPage.document.createElement( 'link' );
							cssUrlElement.setAttribute( 'rel', 'stylesheet' );
							cssUrlElement.setAttribute( 'href', cssUrlMatches[1] );
							individualPage.document.head.appendChild( cssUrlElement );
						}

						var cssHeadRegExp = new RegExp( '\<css_head_' + stamp + '\>([^]+)\<\/css_head_' + stamp + '\>', 'm' );
						var cssHeadMatches = response.match( cssHeadRegExp );
						if ( typeof cssHeadMatches !== 'undefined' && null !== cssHeadMatches ) {
							var cssHeadElement = individualPage.document.createElement( 'style' );
							cssHeadElement.setAttribute( 'type', 'text/css' );
							var cssHeadText = individualPage.document.createTextNode( cssHeadMatches[1] );
							cssHeadElement.appendChild( cssHeadText );
							individualPage.document.head.appendChild( cssHeadElement );
						}

						var pageHeaderRegExp = new RegExp( '\<page_header_' + stamp + '\>([^]+)\<\/page_header_' + stamp + '\>', 'm' );
						var pageHeaderMatches = response.match( pageHeaderRegExp );
						if ( typeof pageHeaderMatches !== 'undefined' && null !== pageHeaderMatches ) {
							var pageHeaderElement = individualPage.document.createElement( 'header' );
							pageHeaderElement.innerHTML = pageHeaderMatches[1];
							individualPage.document.body.appendChild( pageHeaderElement );
						}

						var articleElement = individualPage.document.createElement('article');
						individualPage.document.body.appendChild( articleElement );

						var articleHeaderElement = individualPage.document.createElement('header');
						articleElement.appendChild( articleHeaderElement );

						if ( typeof title_matches !== 'undefined' && null !== title_matches ) {
							individualPage.document.title = title_matches[1];
							var h1Element = individualPage.document.createElement('h1');
							h1Element.innerHTML = title_matches[1];
							articleHeaderElement.appendChild( h1Element );
						}

						var contentRegExp = new RegExp( '\<content_' + stamp + '\>([^]+)\<\/content_' + stamp + '\>', 'm' );
						var contentMatches = response.match( contentRegExp );
						if ( typeof contentMatches !== 'undefined' && null !== contentMatches ) {
							var articleElement = individualPage.document.getElementsByTagName( 'article' )[0];
							var contentElement = individualPage.document.createElement( 'div' );
							contentElement.setAttribute( 'id', 'content' );
							contentElement.innerHTML = contentMatches[1];
							articleElement.appendChild( contentElement );
						}

						var pageFooterRegExp = new RegExp( '\<page_footer_' + stamp + '\>([^]+)\<\/page_footer_' + stamp + '\>', 'm' );
						var pageFooterMatches = response.match( pageFooterRegExp );
						if ( typeof pageFooterMatches !== 'undefined' && null !== pageFooterMatches ) {
							var pageFooterElement = individualPage.document.createElement( 'footer' );
							pageFooterElement.innerHTML = pageFooterMatches[1];
							individualPage.document.body.appendChild( pageFooterElement );
						}
					};

				}
			};
			ajaxRequest.open("POST", "<?php echo admin_url( 'admin-ajax.php' ); ?>", true );
			ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajaxRequest.send( 'action=bnmng_individual_page&post_id=' + this.dataset.post_id + '&stamp=' + stamp + '&set_key=' + this.dataset.set_key + '&css_head=' + this.dataset.css_head + '&css_url=' + this.dataset.css_url );
		});
	}

</script>
<?php
}

add_action( 'wp_footer', 'bnmng_individual_page_javascript' );

function bnmng_individual_page_ajax() {

	$post = false;
	if ( isset( $_POST['post_id'] ) && is_numeric( $_POST['post_id'] ) ) {
		$post = get_post ( intval( $_POST['post_id'] ) );
	}
	if ( ! $post ) {
		die( 'no post' );
	}
	if ( isset( $_POST['stamp'] ) && is_numeric( $_POST['stamp'] ) ) {
		$stamp = $_POST['stamp'];
	} else {
		die('no stamp');
	}

	$content = $post->post_content;

	if ( isset( $_POST['shortcodes_to_strip'] ) && '' < $_POST['shortcodes_to_strip'] ) {
		$shortcodes_to_strip = explode( ',', $_POST['shortcodes_to_strip'] );
		foreach ( $shortcodes_to_strip AS $shortcode_to_strip ) {
			$shortcode_to_strip = sanitize_key( $shortcode_to_strip );
			$content = preg_replace( '@\[' . $shortcode_to_strip . '.*?\[\/' . $shortcode_to_strip . '\]@', '', $content );
			$content = preg_replace( '@\[' . $shortcode_to_strip . '[^\]]*?\]@', '', $content );
		}
	}
			
	$content = do_shortcode( $content ); //This will remove the invidual_page shortcode.  There will be no post, so individual page will return nothing.

	$css = '';
	$css_head = '';
	$css_url = '';
	$page_header = '';
	$page_footer = '';

	if ( isset( $_POST['set_key'] ) && is_numeric( $_POST['set_key'] ) ) {
		$option_name = 'bnmng_individual_page';
		$options = get_option( $option_name );
		if ( is_array( $options ) ) {
			if ( isset( $options['sets'] ) && is_array( $options['sets'] ) ) {
				$set = $options['sets'][ intval( $_POST['set_key'] ) ];
				if ( is_array( $set ) ) {
					if ( isset( $set['css_head'] ) ) {
						$css_head = wp_strip_all_tags( $set['css_head'] );
					}
					if ( isset( $set['css_url'] ) ) {
						$css_url = wp_http_validate_url( $set['css_url'] );
					}
					if ( isset( $set['page_header'] ) ) {
						$page_header = wp_kses_post( $set['page_header'] );
					}
					if ( isset( $set['page_header_autop'] ) ) {
						$page_header_autop = true;
					}
					if ( isset( $set['page_footer'] ) ) {
						$page_footer = wp_kses_post( $set['page_footer'] );
					}
					if ( isset( $set['page_footer_autop'] ) ) {
						$page_footer_autop = true;
					}
				}
			}
		}
	}

	if ( isset( $_POST['css_head'] ) && '' < $_POST['css_head'] ) {
		$css_head = wp_strip_all_tags( $_POST['css_head'] );
	}
	if ( isset( $_POST['css_url'] ) && '' < $_POST['css_url'] ) {
		$css_url = wp_http_validate_url( $_POST['css_url'] );
	}
	$response = '';

	$response .= '	<title_' . $stamp . '>' . $post->post_title . '</title_' . $stamp . '>' . "\n";
	$response .= '	<content_' . $stamp . '>' . $content . '</content_' . $stamp . '>' . "\n";
	if( '' < $css_url ) {
		$response .= '	<css_url_' . $stamp . '>' . $css_url . '</css_url_' . $stamp . '>' . "\n";
	}
	if ( '' < $css_head ) {
		$response .= '	<css_head_' . $stamp . '>' . $css_head . '</css_head_' . $stamp . '>' . "\n";
	}
	if ( '' < $page_header ) {
		if ( isset( $page_header_autop ) ) {
			$page_header = wpautop( $page_header );
		}
		$response .= '	<page_header_' . $stamp . '>' . $page_header . '</page_header_' . $stamp . '>' . "\n";
	}
	if ( '' < $page_footer ) {
		if ( isset( $page_footer_autop ) ) {
			$page_footer = wpautop( $page_footer );
		}
		$response .= '	<page_footer_' . $stamp . '>' . $page_footer . '</page_footer_' . $stamp . '>' . "\n";
	}

	echo $response;

	wp_die();
}


add_action( 'wp_ajax_nopriv_bnmng_individual_page', 'bnmng_individual_page_ajax' );
add_action( 'wp_ajax_bnmng_individual_page', 'bnmng_individual_page_ajax' );


/**
 * Add the options page using function bnmng_individual_page_options
 */
function bnmng_individual_page_menu() {
		add_theme_page( 'Individual Pages', 'Individual Pages', 'manage_options', 'bnmng-individual-page', 'bnmng_individual_page_options' );
}
add_action( 'admin_menu', 'bnmng_individual_page_menu' );

/*
 * calls the functions to save and display options
 */
function bnmng_individual_page_options() {

	bnmng_individual_page_save_options();
	bnmng_individual_page_display_options();

}

/**
 * Saves options if changes were submitted
 */
function bnmng_individual_page_save_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
	}

	$option_name = 'bnmng_individual_page';

	if ( ! empty( $_POST ) && check_admin_referer( 'bnmng_individual_page', 'bnmng_individual_page_nonce' ) ) {
		$options = [];
		$save_sets_lap = 0;

		if ( isset( $_POST[ $option_name ]['sets'] ) ) {
			foreach ( $_POST[ $option_name ]['sets'] as &$posted_set ) {

				$posted_set_name = sanitize_key( wp_unslash( $posted_set['set_name'] ) );
				if ( $posted_set['set_name'] === $posted_set_name ) {

					$save_set = array();

					$save_set['set_name'] = $posted_set_name;

					if ( isset( $posted_set['link_style'] ) ) {
						$save_set['link_style'] = wp_strip_all_tags( $posted_set['link_style'] );
					}

					if ( isset( $posted_set['link_text'] ) ) {
						$save_set['link_text'] = wp_strip_all_tags( $posted_set['link_text'] );
					}

					if ( isset( $posted_set['css_url'] ) ) {
						$save_set['css_url'] = esc_url_raw( $posted_set['css_url'] );
					}

					if ( isset( $posted_set['css_head'] ) ) {
						$save_set['css_head'] = wp_strip_all_tags( $posted_set['css_head'] );
					}

					if ( isset( $posted_set['page_header'] ) ) {
						$save_set['page_header'] = wp_kses_post( $posted_set['page_header'] );
					}

					if ( isset( $posted_set['page_header_autop'] ) ) {
						$save_set['page_header_autop'] = true;
					}

					if ( isset( $posted_set['page_footer'] ) ) {
						$save_set['page_footer'] = wp_kses_post( $posted_set['page_footer'] );
					}

					if ( isset( $posted_set['page_footer_autop'] ) ) {
						$save_set['page_footer_autop'] = true;
					}

					$options['sets'][ $save_sets_lap ] = $save_set;
				
					$save_sets_lap++;
				}
			}
		}

		if ( $_POST[ $option_name ]['new_set_set_name'] ) {

			$new_set_set_name = sanitize_key( $_POST[ $option_name ]['new_set_set_name'] );
			if ( '' < $new_set_set_name ) {
				$save_set = array();
				$save_set['set_name'] = $new_set_set_name;
				$save_set['page_header_autop'] = 'on';
				$save_set['page_footer_autop'] = 'on';

				$options['sets'][ $save_sets_lap ] = $save_set;
			}
		}
		update_option( $option_name, $options );
	}
}
/**
 * Displays the options page
 */
function bnmng_individual_page_display_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bnmng-individual-page' ) );
	}

	$option_name = 'bnmng_individual_page';

	$intro  = '<p>';
	$intro .= __( 'This plugin adds text to the beginning and end of a post when the post is displayed. It does not alter the post in the database. ', 'bnmng-individual-page' );
	$intro .= '</p>' . "\n";
	$intro .= '<p>';
	$intro .= __( 'HTML and shortcodes may be used.  Tags opened above post content can be closed below. ', 'bnmng-individual-page' );
	$intro .= ' </p>';

	$set_label                 = 'Style %1$d';
	$help_label                = __( 'Show Help', 'bnmng-individual-page' );
	$up_label                  = __( 'Up', 'bnmng-individual-page' );
	$down_label                = __( 'Down', 'bnmng-individual-page' );
	$delete_label              = __( 'Delete', 'bnmng-individual-page' );
	$set_name_label            = __( 'Set Name', 'bnmng-individual-page' );
	$set_name_help             = __( 'To add a new style set, type a new set name and click "Save Changes" ', 'bnmng-individual-page' );
	$set_name_help            .= __( 'The new set name should not have spaces or unusual characters. examples: "MainStyle", "Politics", "Official_Letters"', 'bnmng-individual-page' );
	$shortcode_label           = __( 'Shortcode to Use', 'bnmng-individual-page' );
	$shortcode_help            = __( 'This is the shortcode that you will use in your post or page. ', 'bnmng-individual-page' );
	$link_style_label          = __( 'Link Style', 'bnmng-individual-page' );
	$link_style_help           = __( 'The style attribute for the link which opens the page. ', 'bnmng-individual-page' );
	$link_style_help          .= __( 'This is for the link that appears in your post - it doesn\'t affect the individual page.  ', 'bnmng-individual-page' );
	$link_text_label           = __( 'Link Text', 'bnmng-individual-page' );
	$link_text_help            = __( 'The text for the link which opens the page, which, if left blank, will be "Individual Page". ', 'bnmng-individual-page' );
	$link_text_help           .= __( 'This is for the link that appears in your post - it doesn\'t affect the individual page.  ', 'bnmng-individual-page' );
	$css_url_label             = __( 'External Stylesheet', 'bnmng-individual-page' );
	$css_url_help              = __( 'The URL of an external stylesheet for your page. ', 'bnmng-individual-page' );
	$css_url_help             .= __( 'This is what goes in the tag &lt;link rel="stylesheet" ... in the document head. ', 'bnmng-individual-page' );
	$css_head_label            = __( 'CSS in Head', 'bnmng-individual-page' );
	$css_head_help             = __( 'This is what goes between the &lt;style&gt; and &lt;/style&gt; tags in the document head. ', 'bnmng-individual-page' );
	$page_header_label         = __( 'Page Header', 'bnmng-individual-page' );
	$page_header_help          = __( 'If filled in, this text will create a &lt;header&gt; element immediately below the opening &lt;body&gt; tag, with whatever you place here between the &lt;header&gt; and &lt;/header&gt; tags', 'bnmng-individual-page' );
	$page_header_autop_label   = __( 'Page Header Auto &lt;p&gt;', 'bnmng-individual-page' );
	$page_header_autop_help    = __( 'If checked, html paragraphs and breaks will be added to the ends of lines ', 'bnmng-individual-page' );
	$page_footer_label         = __( 'Page Footer', 'bnmng-individual-page' );
	$page_footer_help          = __( 'If , this text will create a &lt;footer&gt; element immediately above the closing &lt;/body&gt; tag, with whatever you place here between the &lt;footer&gt; and &lt;/footer&gt; tags', 'bnmng-individual-page' );
	$page_footer_autop_label   = __( 'Page Footer Auto &lt;p&gt;', 'bnmng-individual-page' );
	$page_footer_autop_help    = __( 'If checked, html paragraphs and breaks will be added to the ends of lines ', 'bnmng-individual-page' );
	$new_set_label             = __( 'Add a new set', 'bnmng-individual-page' );
	$controlname_pat           = $option_name . '[sets][%1$d][%2$s]';
	$controlid_pat             = $option_name . '_%1$d_%2$s';
	$multicontrolname_pat      = $option_name . '[sets][%1$d][%2$s][%3$s][]';
	$multicontrolid_pat        = $option_name . '_%1$d_%2$s_%3$s';
	$global_controlname_pat    = $option_name . '[%1$s]';
	$global_controlid_pat      = $option_name . '_%1$s';

	echo "\n";
	echo '<div class = "wrap">', "\n";
	echo '	<div class="bnmng-individual-page-intro">', $intro, '</div>', "\n";
	echo '	<form method = "POST" action="">', "\n";
	echo '		', wp_nonce_field( 'bnmng_individual_page', 'bnmng_individual_page_nonce', true, false ), "\n";

	$options = get_option( $option_name );

	if ( isset( $options['sets'] ) ) {
		$saved_sets_sum = count( $options['sets'] );

		foreach ( $options['sets'] AS $saved_set_key => $saved_set ) {
			
			echo '		<div class="set_wrapper">', "\n";
			if ( isset( $saved_set['is_new'] ) && $saved_set['is_new'] ) {
				echo '<span id="span_new"> (new) </span>';	
			}
			echo '			<div class="set_header">', "\n";
			echo '				<div class="set_label">', "\n";
			echo '					', sprintf( $set_label, ( $saved_set_key + 1 ) ), "\n";
			echo '				</div>', "\n";
			echo '				<div class="set_buttons">', "\n";
			echo '					<button type="button" name="toggle_help" data-key="' . $saved_set_key . '" >', $help_label, '</button>', "\n";
			if ( $saved_set_key > 0 ) {
				echo '					<button type="button" name="move_up" data-key="' . $saved_set_key . '" >', $up_label, '</button>', "\n";
			}
			if ( $saved_set_key < ( $saved_sets_sum - 1 ) ) {
				echo '					<button type="button" name="move_down" data-key="' . $saved_set_key . '" >', $down_label, '</button>', "\n";
			}
			echo '					<button type="button" name="delete" data-key="' . $saved_set_key . '" >', $delete_label, '</button>', "\n";
			echo '				</div>', "\n";
			echo '			<div id="div_set_', $saved_set_key, '">', "\n";
			echo '				<table class="form-table bnmng-individual-page">', "\n";
			echo '					<tr>', "\n";
			echo '						<th>', $set_name_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>';
			echo '								', $saved_set['set_name'], "\n";
			echo '								<input type="hidden" id="', sprintf( $controlid_pat, $saved_set_key, 'set_name' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'set_name' ), '" value="', $saved_set['set_name'], '">',  "\n";
			echo '							</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $shortcode_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>';
			echo '								[individual_page set="', $saved_set['set_name'], '"]';
			echo ' <button type="button" name="bnmng_individual_page_shortcode_copy" data-set_name="'. $saved_set['set_name'] . '">Copy</button>';
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $shortcode_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $link_style_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<input id="', sprintf( $controlid_pat, $saved_set_key, 'link_style' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'link_style' ), '" ';
			if ( isset( $saved_set['link_style'] ) ) {
				echo 'value="', stripslashes( $saved_set['link_style'] ), '" ';
			}
			echo '>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $link_style_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $link_text_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<input id="', sprintf( $controlid_pat, $saved_set_key, 'link_text' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'link_text' ), '" ';
			if ( isset( $saved_set['link_text'] ) ) {
				echo 'value="', stripslashes( $saved_set['link_text'] ), '" ';
			}
			echo '>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $link_text_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $css_url_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<input id="', sprintf( $controlid_pat, $saved_set_key, 'css_url' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'css_url' ), '" ';
			if ( isset( $saved_set['css_url'] ) ) {
				echo 'value="', stripslashes( $saved_set['css_url'] ), '" ';
			}
			echo '>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $css_url_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";


			echo '					<tr>', "\n";
			echo '						<th>', $css_head_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<textarea id="', sprintf( $controlid_pat, $saved_set_key, 'css_head' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'css_head' ), '">';
			if ( isset( $saved_set['css_head'] ) ) {
				echo stripslashes( $saved_set['css_head'] );
			}
			echo '</textarea>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $css_head_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $page_header_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<textarea id="', sprintf( $controlid_pat, $saved_set_key, 'page_header' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'page_header' ), '">';
			if ( isset( $saved_set['page_header'] ) ) {
				echo stripslashes( $saved_set['page_header'] );
			}
			echo '</textarea>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $page_header_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $page_header_autop_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<input type="checkbox" id="', sprintf( $controlid_pat, $saved_set_key, 'page_header_autop' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'page_header_autop' ), '" ';
			if ( isset( $saved_set['page_header_autop'] ) ) {
				echo 'checked = "checked" ';
			}
			echo '>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $page_header_autop_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $page_footer_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<textarea id="', sprintf( $controlid_pat, $saved_set_key, 'page_footer' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'page_footer' ), '">';
			if ( isset( $saved_set['page_footer'] ) ) {
				echo stripslashes( $saved_set['page_footer'] );
			}
			echo '</textarea>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $page_footer_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";

			echo '					<tr>', "\n";
			echo '						<th>', $page_footer_autop_label, '</th>', "\n";
			echo '						<td>', "\n";
			echo '							<div>', "\n";
			echo '								<input type="checkbox" id="', sprintf( $controlid_pat, $saved_set_key, 'page_footer_autop' ), '" name="', sprintf( $controlname_pat, $saved_set_key, 'page_footer_autop' ), '" ';
			if ( isset( $saved_set['page_footer_autop'] ) ) {
				echo 'checked = "checked" ';
			}
			echo '>', "\n";
			echo '							</div>', "\n";
			echo '							<div class="bnmng-individual-page-help">', $page_footer_autop_help, '</div>', "\n";
			echo '						</td>', "\n";
			echo '					</tr>', "\n";


			echo '				</table>', "\n";
			echo '			</div>', "\n";
			echo '		</div>', "\n";
			
		}
	} else {
		$saved_sets_sum = 0;
	}

	echo '		<div class="set_header">', "\n";
	echo '			<div class="set_label">', "\n";
	echo '				Add', "\n";
	echo '			</div>', "\n";
	echo '			<div class="set_buttons">', "\n";
	echo '				<button type="button" name="toggle_help" data-key="add" >', $help_label, '</button>', "\n";
	echo '			</div>', "\n";
	echo '		</div>', "\n";
	echo '		<div id="div_set_add">', "\n";
	echo '			<table class="form-table bnmng-individual-page">', "\n";
	echo '				<tr>', "\n";
	echo '					<th colspan="2">Add a New Style Set</th>', "\n";
	echo '				</tr>', "\n";
	echo '				<tr>', "\n";
	echo ' 					<th>', "\n";
	echo '						', $set_name_label, "\n";
	echo '					</th>', "\n";	
	echo '					<td>', "\n";
	echo '						<div>', "\n";
	echo '							<input type="text" id="', sprintf( $global_controlid_pat, 'new_set_set_name_', $set_name ), '" name="' . sprintf( $global_controlname_pat, 'new_set_set_name' ), '" value="" >', "\n";
	echo '						</div>', "\n";
	echo '						<div class="bnmng-individual-page-help">', $set_name_help, '</div>', "\n";
	echo '					</td>', "\n";
	echo '				</tr>', "\n";
	echo '			</table>', "\n";
	echo '		</div>', "\n";

	echo '		<div id="div_submit">', get_submit_button(), '</div>', "\n";
	echo '	</form>', "\n";
	echo '</div>', "\n";
}


add_action( 'admin_head-settings_page_bnmng-individual-page', 'bnmng_admin_individual_page_style' );

function bnmng_admin_individual_page_style() {
	?>
<style type="text/css">
	table.bnmng-individual-page {
		border: 1px solid black;
	}
	table.bnmng-individual-page th {
		padding-left: 1em;
	}
	table.bnmng-individual-page th,
	table.bnmng-individual-page td {
		padding-top: .5em;
		padding-bottom: .5em;
	}
	table.bnmng-individual-page table {
		border: none;
	}
	table.bnmng-individual-page table.bnmng-individual-page td {
		padding: 0 1px .5px 1px;
	}
	table.bnmng-individual-page textarea {
		resize:both;
	}
</style>
	<?php
}
add_action( 'admin_footer-settings_page_bnmng-individual-page', 'bnmng_admin_individual_page_script' );

function bnmng_admin_individual_page_script() {
	?>
<script type="text/javascript">
	function move( direction, key ) {

		direction = parseInt( direction );
		key = parseInt( key );
		
		var tempDiv = document.createElement("div");

		var thisDiv = document.getElementById( "div_set_" + key );
		var theseChildren = thisDiv.children;
		for ( var i = 0; i < theseChildren.length; i++ ) {
			if ( theseChildren[ i ].hasAttribute( "name" ) ) {
				theseChildren[ i ].name = theseChildren[ i ].name.replace( "[" + key + "]", "[" + ( key + direction ) + "]" );
			}
			var theseDescendents = theseChildren[ i ].querySelectorAll("*");
			for ( j = 0; j < theseDescendents.length; j++ ) {
				if ( theseDescendents[ j ].hasAttribute( "name" ) ) {
					theseDescendents[ j ].name = theseDescendents[ j ].name.replace( "[" + key + "]", "[" + ( key + direction ) + "]" );
				}
			}
			tempDiv.appendChild( theseChildren[ i ] );
		}

		var thatDiv = document.getElementById( "div_set_" + ( key + direction ) );
		var thoseChildren = thatDiv.children;
		for ( var i = 0; i < thoseChildren.length; i++ ) {
			if ( thoseChildren[ i ].hasAttribute( "name" ) ) {
				thoseChildren[ i ].name = thoseChildren[ i ].name.replace( "[" + ( key + direction ) + "]", "[" + key + "]" );
			}
			var thoseDescendents = thoseChildren[ i ].querySelectorAll("*");
			for ( j = 0; j < thoseDescendents.length; j++ ) {
				if ( thoseDescendents[ j ].hasAttribute( "name" ) ) {
					thoseDescendents[ j ].name = thoseDescendents[ j ].name.replace( "[" + ( key + direction ) + "]", "[" + key + "]" );
				}
			}
			thisDiv.appendChild( thoseChildren[ i ] );
		}

		var theseChildren = tempDiv.children;
		for ( var i = 0; i < theseChildren.length; i++ ) {
			thatDiv.appendChild( theseChildren[ i ] );
		}

	}
	function deleteInstance( key ) {

		var eachKey = parseInt( key );

		var thisDiv = document.getElementById( "div_set_" + key );
		var thatDiv = document.getElementById( "div_set_" + ( key + 1 ) );

		while ( null !== thatDiv ) {
			theseChildren = thisDiv.children;
			for ( var i = 0; i < theseChildren.length; i++ ) {
				theseChildren[ i ].remove();
			}
			var thoseChildren = thatDiv.children;
			for ( var i = 0; i < thoseChildren.length; i++ ) {
				if ( thoseChildren[ i ].hasAttribute( "name" ) ) {
					thoseChildren[ i ].name = thoseChildren[ i ].name.replace( "[" + ( key + 1 ) + "]", "[" + key + "]" );
				}
				var thoseDescendents = thoseChildren[ i ].querySelectorAll("*");
				for ( j = 0; j < thoseDescendents.length; j++ ) {
					if ( thoseDescendents[ j ].hasAttribute( "name" ) ) {
						thoseDescendents[ j ].name = thoseDescendents[ j ].name.replace( "[" + ( key + 1 ) + "]", "[" + key + "]" );
					}
				}
				thisDiv.appendChild( thoseChildren[ i ] );
			}
			key++;
		}
		var downmovers = document.getElementsByName("move_down");
		if ( 0 < downmovers.length ) {
			 downmovers[ downmovers.length - 1 ].remove();
		}
		
		thisDiv.parentNode.remove();
	}

	var upmovers = document.getElementsByName("move_up");
	for ( var i = 0; i < upmovers.length; i++ ) {
		upmovers[ i ].addEventListener( "click", function( event ) {
				move( -1, this.dataset.key );
		} );
	}
	var downmovers = document.getElementsByName("move_down");
	for ( var i = 0; i < downmovers.length; i++ ) {
		downmovers[ i ].addEventListener( "click", function( event ) {
				move( 1, this.dataset.key );
		} );
	}

	var deleters = document.getElementsByName("delete");
	for ( var i = 0; i < deleters.length; i++ ) {
		deleters[ i ].addEventListener( "click", function( event ) {
				deleteInstance( this.dataset.key );
		} );
	}

	var shortcode_copiers = document.getElementsByName("bnmng_individual_page_shortcode_copy");
	for ( var i = 0; i < shortcode_copiers.length; i++ ) {
		shortcode_copiers[ i ].addEventListener( "click", function( event ) {
				event.preventDefault();
				var shortcode_textarea = document.createElement('textarea');
				shortcode_textarea.value = '[individual_page set="' + this.dataset.set_name + '"]';
				document.body.appendChild( shortcode_textarea );
				shortcode_textarea.select();
				document.execCommand('copy');
				document.body.removeChild( shortcode_textarea );
		} );
	}

	var help_divs  = document.getElementsByClassName("bnmng-individual-page-help");
	for ( var i = 0; i < help_divs.length; i++ ) {
		help_divs[ i ].style.display = "none";
	}

	var help_buttons  = document.getElementsByName("toggle_help");
	for ( var i = 0; i < help_buttons.length; i++ ) {
		help_buttons[ i ].addEventListener( "click", function( event ) {
			event.preventDefault();
			var help_divs = document.getElementById( "div_set_" + this.dataset.key ).getElementsByClassName("bnmng-individual-page-help") ;
			for ( var i = 0; i < help_divs.length; i++ ) {
				if ( "none" == help_divs[ i ].style.display ) {
					help_divs[ i ].style.display = "block";
				} else {
					help_divs[ i ].style.display = "none";
				}
			}
		} );
		
	}

	

</script>
	<?php
}


/*This is just for troubleshooting*/
if ( ! function_exists( 'bnmng_echo' ) ) {
function bnmng_echo ( ) {
	echo '<pre>';
	$args = func_get_args();
	foreach ( $args as $arg ) {
		if ( is_array( $arg ) ) {
			print_r( $arg );
		} else {
			echo( $arg );
		}
	}
	echo "\n", '</pre>';
}
}
