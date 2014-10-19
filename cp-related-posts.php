<?php 
/*  
Plugin Name: CP Related Posts
Plugin URI: http://wordpress.dwbooster.com/content-tools/related-posts
Version: 1.0.1
Author: codepeople
Description: CP Related Posts is a plugin that displays related articles on your website, manually, or by the terms in the content, title or abstract, including the tags assigned to the articles.
*/

 
include dirname( __FILE__ ).'/includes/tools.clss.php';

// Default values

$cprp_default_settings = array(
    'title' => 'Related Posts',
    'number_of_posts'   => 5,
    'post_type'         => array( 'page', 'post' ),
	
    'percentage_symbol'	=> 'star',
	'available_symbols' => array( 'star', 'frame', 'ball' ),
	'similarity'		=> 30,
	
    'selection_type'    => array(
        'manually'      => true,
        'by_user_tags'  => true,
        'by_content'    => true
    ),
    
    'display_in_single' => array(
        'activate'          => true,
        'show_thumbnail'    => true,
        'show_percentage'   => true,
        'show_excerpt'      => true,
        'show_tags'         => true,
        'mode'              => 'list' // slider, list, column
    ),
    
    'display_in_multiple' => array(
        'activate'          => true,
		'display_in'		=> array(
						'type' => 'all', // possible values 'all', 'home', 'list'
						'exclude_home' => false, // Exclude related posts from homepage, valid when type=all
						'exclude_id' => array(), // Exclude related posts from pages or posts with ID, valid when type=all
						'include_id' => array()  // Display related posts only on specific posts or pages, valid when type=list
					   ),
        'show_thumbnail'    => true,
        'show_percentage'   => true,
        'show_excerpt'      => true,
        'show_tags'         => true,
        'mode'              => 'list' // slider, list, column
    )
    
);

add_action( 'init', 'cprp_init', 1 );
function cprp_init(){
    load_plugin_textdomain( 'cprp-text', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
    add_action( 'widgets_init', 'cprp_load_widgets' );
} // End cprp_init    

add_action('admin_init', 'cprp_admin_init');
function cprp_admin_init(){
    global $wpdb, $last_processed, $cprp_tags_obj;
    
    if( isset( $_REQUEST[ 'cprp-action' ] )){
        
        switch( strtolower( $_REQUEST[ 'cprp-action' ] ) ){
            case 'extract-all':
                $cprp_tags_obj = new CPTagsExtractor;
                $query = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_status = 'publish'";
                if( isset( $_REQUEST[ 'id' ] ) ){
                    $query = $wpdb->prepare( $query." AND ID > %d", $_REQUEST[ 'id' ] );
                }
                $query .= " ORDER BY ID";
                
                $results = $wpdb->get_results( $query );
                if( count( $results ) ){
                    register_shutdown_function('cprp_shutdown');
                    print '<div style="text-align:center;"><h1>'.__( 'Processing Posts', 'cprp-text' ).'</h1></div>';
                    foreach( $results as $post){
                        $last_processed = $post->ID;
                        cprp_process_post( $post );
                    }
                    exit;
                }
                
                print '<div style="text-align:center;"><h1>'.__( 'All Posts Processed', 'cprp-text' ).'</h1></div>';
                exit;
                    
            break;
            case 'extract-tags':
				$cprp_tags_obj = new CPTagsExtractor;
                if( isset( $_REQUEST[ 'id' ] ) && isset( $_REQUEST[ 'text' ] ) ){
                    $post = get_post( $_REQUEST[ 'id' ]);
                    $tags = $cprp_tags_obj->get_tags( $_REQUEST[ 'text' ] );
                    $associated_tags = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
                    if( !empty( $associated_tags ) ){
                        $tags_as_text = "";
                        foreach( $associated_tags as $t ){
                            $tags_as_text .= str_replace( '"', '', $t )." ";
                        }
                        
                        $associated_tags = $cprp_tags_obj->get_tags( $tags_as_text );
                    }
                    
                    $obj = new stdClass;
                    $obj->recommended_tags = $tags;
                    $obj->associated_tags = $associated_tags;
                    
                    print json_encode( $obj );
                }
                exit;
            break;
            case 'get-post':
                global $wp_query;
                if( isset( $_REQUEST[ 'terms' ] ) ){
                    $params = array(
                      's' => $_REQUEST[ 'terms' ],
                      'showposts' => -1,
                      'post_type' => cprp_get_settings( 'post_type' ),
                      'post_status' => 'publish',
                    );
                    $wp_query->query($params);
                    print json_encode( $wp_query->posts );
                }
                exit;
            break;
        }
        
    }
    
    $post_types = cprp_get_settings( 'post_type' );
    $form_title = __('Select the posts related and tags proposed', 'cprp-text');
    foreach($post_types as $post_type){
        add_meta_box( 'cprp_related_post_form', $form_title, 'cprp_related_post_form', $post_type, 'normal' );
    }
    
    add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'cprp_customization_links' );  
}

add_action('admin_menu', 'cprp_admin_menu');
function cprp_admin_menu(){
    add_options_page('CP Related Posts', 'CP Related Posts', 'manage_options', 'cprp-settings', 'cprp_settings_page');
} // End cprp_admin_menu

function cprp_settings_page(){
    print "<h2>CP Related Posts Settings</h2>";
?>	
	<p  style="border:1px solid #E6DB55;margin-bottom:10px;padding:5px;background-color: #FFFFE0;">
	<?php _e('For any issues with the plugin, go to our <a href="http://wordpress.dwbooster.com/contact-us" target="_blank">contact page</a> and leave us a message.'); ?><br/><br />
	<?php _e('If you want test the premium version of CP Related Posts go to the following links:<br/> <a href="http://demos.net-factor.com/related-posts/wp-login.php" target="_blank">Administration area: Click to access the administration area demo</a><br/> <a href="http://demos.net-factor.com/related-posts/" target="_blank">Public page: Click to access the CP Related Posts</a>'); ?><br/><br />
	<?php _e('To get the premium version of CP Related Posts go to the following links:<br/> <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a>'); ?>
	</p>
<?php	
    if (isset($_POST['cprp_settings']) && wp_verify_nonce( $_POST['cprp_settings'], plugin_basename( __FILE__ ) ) ){
        $settings = array(
            'title' => '',
            'number_of_posts' => 0,
			'similarity' 	  => 30,
            'post_type' => array( 'post', 'page' ),
            'selection_type'    => array(
                'manually'      => true,
                'by_user_tags'  => true,
                'by_content'    => true
            ),
    
            'display_in_single' => array(
                'activate'          => false,
                'show_thumbnail'    => false,
                'show_percentage'   => false,
                'show_excerpt'      => false,
                'show_tags'         => false,
                'mode'              => 'list'
            ),
    
            'display_in_multiple' => array(
                'activate'          => false,
				'display_in'		=> array(
											'type' => 'all',
											'exclude_home' => false,
											'exclude_id' => array(),
											'include_id' => array()
									),
                'show_thumbnail'    => false,
                'show_percentage'   => false,
                'show_excerpt'      => false,
                'show_tags'         => false,
                'mode'              => 'list'
            )
        );
        if( isset( $_REQUEST[ 'cprp_title' ] ) ) $settings[ 'title' ] = $_REQUEST[ 'cprp_title' ];
		if( is_int( trim( $_REQUEST[ 'cprp_number_of_posts' ] ) *1 ) ) $settings[ 'number_of_posts' ] = trim( $_REQUEST[ 'cprp_number_of_posts' ] );
        if( is_int( trim( $_REQUEST[ 'cprp_similarity' ] ) *1 ) ) $settings[ 'similarity' ] = trim( $_REQUEST[ 'cprp_similarity' ] );
        if( isset( $_REQUEST[ 'cprp_display_in_single_activate' ] ) )           $settings[ 'display_in_single' ][ 'activate' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_single_show_thumbnail' ] ) )     $settings[ 'display_in_single' ][ 'show_thumbnail' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_single_show_percentage' ] ) )    $settings[ 'display_in_single' ][ 'show_percentage' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_single_show_excerpt' ] ) )       $settings[ 'display_in_single' ][ 'show_excerpt' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_single_show_tags' ] ) )          $settings[ 'display_in_single' ][ 'show_tags' ] = true;
        
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_activate' ] ) )           $settings[ 'display_in_multiple' ][ 'activate' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_type' ] ) )           	$settings[ 'display_in_multiple' ][ 'display_in' ][ 'type' ] = $_REQUEST[ 'cprp_display_in_multiple_type' ];
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_exclude_home' ] ) )     $settings[ 'display_in_multiple' ][ 'display_in' ][ 'exclude_home' ] = true;
        if( !empty( $_REQUEST[ 'cprp_display_in_multiple_exclude_id' ] ) )      $settings[ 'display_in_multiple' ][ 'display_in' ][ 'exclude_id' ] = explode( ',', str_replace( ' ', '', $_REQUEST[ 'cprp_display_in_multiple_exclude_id' ] ) );
		if( !empty( $_REQUEST[ 'cprp_display_in_multiple_include_id' ] ) )      $settings[ 'display_in_multiple' ][ 'display_in' ][ 'include_id' ] = explode( ',', str_replace( ' ', '', $_REQUEST[ 'cprp_display_in_multiple_include_id' ] ) );		
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_show_thumbnail' ] ) )     $settings[ 'display_in_multiple' ][ 'show_thumbnail' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_show_percentage' ] ) )    $settings[ 'display_in_multiple' ][ 'show_percentage' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_show_excerpt' ] ) )       $settings[ 'display_in_multiple' ][ 'show_excerpt' ] = true;
        if( isset( $_REQUEST[ 'cprp_display_in_multiple_show_tags' ] ) )          $settings[ 'display_in_multiple' ][ 'show_tags' ] = true;
        
        update_option( 'cprp_settings', $settings );
        echo '<div class="updated"><p><strong>'.__("Settings Updated").'</strong></div>';
    }
    
    $display_modes = array( 'slider', 'list', 'column', 'accordion' );
	
    $images_dir = plugin_dir_url(__FILE__).'images/';
	$list_of_symbols = cprp_get_settings( 'available_symbols' );
	
    ?>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <div class="postbox" style="margin: 5px 15px;">
            <h3 class='hndle' style="padding:5px;"><span><?php _e( 'Related Posts Settings', 'cprp-text' ); ?></span></h3>
            <div class="inside">
                <table class="form-table">
                    <tr valign="top">
                        <th><?php _e( 'Section title', 'cprp-text' ); ?></th>
                        <td>
                            <input type="text" name="cprp_title" value="<?php echo esc_attr( cprp_get_settings( 'title' ) ); ?>" style="width:150px;" />
                            <em><?php _e( 'The title of the related posts section', 'cprp-text' ); ?></em>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th><?php _e( 'Number of related posts', 'cprp-text' ); ?></th>
                        <td>
                            <input type="text" name="cprp_number_of_posts" value="<?php echo esc_attr( cprp_get_settings( 'number_of_posts' ) ); ?>" style="width:150px;" />
                            <em><?php _e( 'Number of posts to display as related posts', 'cprp-text' ); ?></em>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th><?php _e( 'Post types that allow related posts', 'cprp-text' ); ?></th>
                        <td>
                            <?php
                                $post_types = get_post_types(array('public' => true), 'names');
                                $cprp_post_types = cprp_get_settings( 'post_type' );
                            ?>
                            <select multiple size="3" style="width:150px;" DISABLED >
                            <?php   
                                foreach( $post_types as $post_type ){
                                    $selected = ( in_array( $post_type, $cprp_post_types ) ) ? 'SELECTED' : '';
                                    print '<option value="'.$post_type.'" '.$selected.'>'.$post_type.'</option>';
                                }
                            ?>
                            </select>
                            <em><?php _e( 'Select the posts types that will display related posts', 'cprp-text' ); ?></em>
                            <p style="color:red;">To display related posts with custom post types will be required the premium version of plugin. Please, <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a> to get the premium version of plugin</p>
                        </td>
                    </tr>
					
					<tr valign="top">
                        <th><?php _e( 'Display percentage of similarity with the symbol', 'cprp-text' ); ?></th>
                        <td>
					<?php	
						foreach( $list_of_symbols as $symbol )
						{
                            echo '<input type="radio" DISABLED value="'.$symbol.'" '.( ( cprp_get_settings( 'percentage_symbol' ) == $symbol ) ? 'CHECKED' : '' ).' />';
							for( $i = 0; $i < 3; $i++) echo '<img src="'.$images_dir.$symbol.'_on.png" />';
							for( $i = 0; $i < 2; $i++) echo '<img src="'.$images_dir.$symbol.'_off.png" />';
							echo '<br />';
                        }	
					?>		
						<p style="color:red;">To use a different icon graphic to display the percentage of similarity, will be required the premium version of plugin. Please, <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a>  to get the premium version of plugin</p>
                        </td>
                    </tr>
					
					<tr valign="top">
                        <th><?php _e( 'Display related posts with a percentage of similarity bigger than', 'cprp-text' ); ?></th>
                        <td>
							<input type="text" name="cprp_similarity" value="<?php echo esc_attr( cprp_get_settings( 'similarity' ) ); ?>" style="width:150px;" /> %
						</td>
                    </tr>
                </table>
                <div style="border: 1px solid #CCC; padding: 10px;" >
                    <?php
                        $cprp_display_in_single = cprp_get_settings( 'display_in_single' );
                    ?>
                    <table class="form-table">
                        <tr>
                            <td colspan="2">
                                <h2><?php _e( 'How to display related posts in single pages', 'cprp-text' ); ?></h2>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display related posts in single pages', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_single_activate" <?php if( isset( $cprp_display_in_single[ 'activate' ] ) && $cprp_display_in_single[ 'activate' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display featured images in related posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_single_show_thumbnail" <?php if( isset( $cprp_display_in_single[ 'show_thumbnail' ] ) && $cprp_display_in_single[ 'show_thumbnail' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display percentage of similarity', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_single_show_percentage" <?php if( isset( $cprp_display_in_single[ 'show_percentage' ] ) && $cprp_display_in_single[ 'show_percentage' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display excerpt of related posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_single_show_excerpt" <?php if( isset( $cprp_display_in_single[ 'show_excerpt' ] ) && $cprp_display_in_single[ 'show_excerpt' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display related terms between related posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_single_show_tags" <?php if( isset( $cprp_display_in_single[ 'show_tags' ] ) && $cprp_display_in_single[ 'show_tags' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display mode', 'cprp-text' ); ?></th>
                            <td>
                                <select style="width:150px;" DISABLED >
                                <?php
                                    foreach( $display_modes as $mode ){
                                        print '<option value="'.$mode.'" '.( ( $mode == $cprp_display_in_single[ 'mode' ] ) ? 'selected' : '' ).'>'.$mode.'</option>';
                                    }
                                ?>
                                </select>
								<p style="color:red;">The premium version of plugin allows display the related posts with different formats like: slider, accordion or column. Please, <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a>  to get the premium version of plugin</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="border: 1px solid #CCC;margin-top:10px;padding:10px;" >
                    <?php
                        $cprp_display_in_multiple = cprp_get_settings( 'display_in_multiple' );
                    ?>
                    <table class="form-table">
                        <tr>
                            <td colspan="2">
                                <h2><?php _e( 'How to display related posts in multiple-posts pages', 'cprp-text' ); ?></h2>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display related posts in multiple-posts pages', 'cprp-text' ); ?></th>
                            <table style="width:100%;">
									<tr>
										<td style="vertical-align:top;">
											<input type="checkbox" name="cprp_display_in_multiple_activate" <?php if( isset( $cprp_display_in_multiple[ 'activate' ] ) && $cprp_display_in_multiple[ 'activate' ] ) echo 'CHECKED'; ?> />
										</td>
										<td style="vertical-align:top;padding:0 10px;">	
											<table>
												<tr >
													<td  style="vertical-align:top;border-bottom:1px solid #CCC;">
														<input type="radio" name="cprp_display_in_multiple_type" value="all" <?php if( !isset( $cprp_display_in_multiple[ 'display_in' ][ 'type' ] ) || $cprp_display_in_multiple[ 'display_in' ][ 'type' ] == 'all' ) echo 'CHECKED'; ?> > Display in all Multiple-post pages
													</td>
													<td style="vertical-align:top;border-bottom:1px solid #CCC;">
														<input type="checkbox" name="cprp_display_in_multiple_exclude_home" <?php if( isset( $cprp_display_in_multiple[ 'display_in' ][ 'exclude_home' ] ) && $cprp_display_in_multiple[ 'display_in' ][ 'exclude_home' ] ) echo 'CHECKED'; ?> > Exclude related posts from Homepage<br><br>
														Exclude from posts and pages with IDs <input type="text" name="cprp_display_in_multiple_exclude_id" value="<?php echo ( ( isset( $cprp_display_in_multiple[ 'display_in' ][ 'exclude_id' ] ) ) ? implode( ',',  $cprp_display_in_multiple[ 'display_in' ][ 'exclude_id' ] ) : '' ); ?>"><br>(separated by comma ",")
													</td>
												</tr>
												<tr>
													<td colspan="2" style="vertical-align:top;border-bottom:1px solid #CCC;">
														<input type="radio" name="cprp_display_in_multiple_type" value="home" <?php if( isset( $cprp_display_in_multiple[ 'display_in' ][ 'type' ] ) && $cprp_display_in_multiple[ 'display_in' ][ 'type' ] == 'home' ) echo 'CHECKED'; ?> > Display in Homepage only
													</td>
												</tr>
												<tr>
													<td>
														<input type="radio" name="cprp_display_in_multiple_type" value="list" <?php if( isset( $cprp_display_in_multiple[ 'display_in' ][ 'type' ] ) && $cprp_display_in_multiple[ 'display_in' ][ 'type' ] == 'list' ) echo 'CHECKED'; ?> > Display in the following posts and pages
													</td>
													<td>
														Enter the IDs of posts and pages <input type="text" name="cprp_display_in_multiple_include_id" value="<?php echo ( ( isset( $cprp_display_in_multiple[ 'display_in' ][ 'include_id' ] ) ) ? implode( ',',  $cprp_display_in_multiple[ 'display_in' ][ 'include_id' ] ) : '' ); ?>" ><br>(separated by comma ",")
													</td>
												</tr>
												
											</table>
										</td>
									</tr>
								</table>	
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display featured images in realted posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_multiple_show_thumbnail" <?php if( isset( $cprp_display_in_multiple[ 'show_thumbnail' ] ) && $cprp_display_in_multiple[ 'show_thumbnail' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display percentage of similarity', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_multiple_show_percentage" <?php if( isset( $cprp_display_in_multiple[ 'show_percentage' ] ) && $cprp_display_in_multiple[ 'show_percentage' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display excerpt of related posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_multiple_show_excerpt" <?php if( isset( $cprp_display_in_multiple[ 'show_excerpt' ] ) && $cprp_display_in_multiple[ 'show_excerpt' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display related terms between related posts', 'cprp-text' ); ?></th>
                            <td>
                                <input type="checkbox" name="cprp_display_in_multiple_show_tags" <?php if( isset( $cprp_display_in_multiple[ 'show_tags' ] ) && $cprp_display_in_multiple[ 'show_tags' ] ) echo 'CHECKED'; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th><?php _e( 'Display mode', 'cprp-text' ); ?></th>
                            <td>
                                <select DISABLED style="width:150px;" >
                                <?php
                                    foreach( $display_modes as $mode ){
                                        print '<option value="'.$mode.'" '.( ( $mode == $cprp_display_in_multiple[ 'mode' ] ) ? 'SELECTED' : '' ).' >'.$mode.'</option>';
                                    }
                                ?>
                                </select>
								<p style="color:red;">The premium version of plugin allows display the related posts with different formats like: slider, accordion or column. Please, <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a>  to get the premium version of plugin</p>
                            </td>
                        </tr>
                    </table>
                </div>    
                <?php wp_nonce_field( plugin_basename( __FILE__ ), 'cprp_settings' ); ?>
                <div class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Update Settings', 'cprp-text'); ?>" /> 
                    <a class="button" href="<?php print( cprp_site_url().'wp-admin/?cprp-action=extract-all' ); ?>" target="_blank"><?php _e( 'Process Previous Posts' ); ?></a>
                </div>
            </div>
        </div>    
	</form>
    <?php
} // End cprp_settings_page

function cprp_related_post_form(){
    global $post;
    
    $cprp_tags = array();
    $tags      = array();
    
    
    if ( isset( $post  ) ){
        $cprp_exclude_from_posts = get_post_meta( $post->ID, 'cprp_exclude_from_posts', true );
        $cprp_hide_related_posts = get_post_meta( $post->ID, 'cprp_hide_related_posts', true );
		
		// Get cprp_tags
        $cprp_tags = get_post_meta( $post->ID, 'cprp_tags' );
        if( !empty( $cprp_tags ) ){
			if( is_string( $cprp_tags ) )
			{
				$cprp_tags = unserialize( $cprp_tags );
			}
            $cprp_tags = $cprp_tags[ 0 ];
            if( $post->post_status == 'auto-draft' && isset( $cprp_tags[ 'auto' ] ) && isset( $cprp_tags[ 'draft' ] ) ){
                unset( $cprp_tags[ 'auto' ] );
                unset( $cprp_tags[ 'draft' ] );
            }
        }
        // Get tags associated to the post.
        $tags = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
    }

?>
	<p  style="border:1px solid #E6DB55;margin-bottom:10px;padding:5px;background-color: #FFFFE0;">
	<?php _e('For any issues with the plugin, go to our <a href="http://wordpress.dwbooster.com/contact-us" target="_blank">contact page</a> and leave us a message.'); ?><br/><br />
	<?php _e('If you want test the premium version of CP Related Posts go to the following links:<br/> <a href="http://demos.net-factor.com/related-posts/wp-login.php" target="_blank">Administration area: Click to access the administration area demo</a><br/> <a href="http://demos.net-factor.com/related-posts/" target="_blank">Public page: Click to access the CP Related Posts</a>'); ?><br/><br />
	<?php _e('To get the premium version of CP Related Posts go to the following links:<br/> <a href="http://wordpress.dwbooster.com/content-tools/related-posts#download" target="_blank">CLICK HERE</a>'); ?>
	</p>
	<p>
	 <input type="checkbox" name="cprp_exclude_from_posts" <?php echo ( ( !empty( $cprp_exclude_from_posts ) ) ? 'CHECKED' : '' ); ?> /> <?php _e( 'Exclude this post from others related posts' ); ?><br />
	 <input type="checkbox" name="cprp_hide_related_posts" <?php echo ( ( !empty( $cprp_hide_related_posts ) ) ? 'CHECKED' : '' ); ?> /> <?php _e( 'Hide the related posts from this post' ); ?>
	</p>
    <p><?php _e( 'After complete the post writing, press the "Get recommended tags" button to get a list of possible tags determined by content, and select the most relevant tags', 'cprp-text' ); ?></p>
    <div><input type="button" value="Get recommended tags" onclick="cprp_get_tags(<?php print $post->ID; ?>);" /></div>
    <div style="width:100%; height:150px; overflow: auto; border: 1px solid #CCC;" id="cprp_tags">
    <?php
        foreach( $cprp_tags as $tag => $count ){
            $checked = '';
            if( in_array( $tag, $tags ) ){
                $checked = 'CHECKED';
                if( $count > 100 ) $count = $count - 100;
            }
            
            print '<span style="border:1px solid #CCC;display:inline-block;padding:5px;margin:5px;"><input type="checkbox" name="cprp_tag[]" value="'.$tag.'"' .$checked.' value="'.$tag.'" /> '.$tag.' ('.$count.') </span>';
        }
    ?>
    </div>
    <p><?php _e( 'If there is a post or page related directly to the current article, then it is possible associate both items manually. Search the related article, and press the "+" symbol.', 'cprp-text' ); ?></p>
    <div><input type="text" name="cprp_search" id="cprp_search" style="width: 300px;" /> <input type="button" value="Search" onclick="cprp_search_manually();" /></div>
    <div id="cprp_manually_added"><div class="cprp-section-title"><?php _e( 'Items manually related, press "-" symbol to remove item', 'cprp-text' ); ?></div><div class="cprp-container"><ul>
    <?php
        $cprp_manually_related = get_post_meta( $post->ID, 'cprp_manually_related' );
        if( !empty( $cprp_manually_related ) ) 
		{
			if( is_string( $cprp_manually_related ) )
			{
				$cprp_manually_related = unserialize( $cprp_manually_related );
			}
			$cprp_manually_related = $cprp_manually_related[ 0 ];
		}	
        foreach( $cprp_manually_related as $post_id ){
            $tmp_post = get_post( $post_id );
            print '<li><span class="cprp-hndl" onclick="cprp_remove_manually(this);">-</span><input type="hidden" name="cprp_manually[]" value="'.$post_id. '" />'.$tmp_post->post_title.'</li>';
        }
    ?>
    </ul></div></div>
    <div id="cprp_found"><div class="cprp-section-title"><?php _e( 'Items found, press "+" symbol to associate the item', 'cprp-text' ); ?></div><div class="cprp-container"></div></div>
<?php    
} // End cprp_related_post_form

function cprp_customization_links( $links ){
	array_unshift(
        $links, 
        '<a href="admin.php?page=cprp-settings">'.__( 'Settings', 'cprp-text' ).'</a>',
        '<a href="http://wordpress.dwbooster.com/contact-us" target="_blank">'.__( 'Request custom changes', 'cprp-text' ).'</a>'
        ); 
    return $links; 		
} // End cprp_customization_links

add_action( 'save_post', 'cprp_save' );
function cprp_save( $id ){
    global $cprp_tags_obj;
    
    $cprp_tags_obj = new CPTagsExtractor;
    $post = get_post( $id );
    cprp_process_post( $post );
    //update_post_meta( $id, 'cprp_manually_related', array( 278, 280, 282) );
    
    if( isset( $_REQUEST[ 'cprp_tag' ] ) ){
        $cprp_tag = $_REQUEST[ 'cprp_tag' ];
        wp_set_post_terms( $id, $_REQUEST[ 'cprp_tag' ], 'post_tag', true);
    }
    
    if( isset( $_REQUEST[ 'cprp_manually' ] ) ){
        update_post_meta( $id, 'cprp_manually_related', $_REQUEST[ 'cprp_manually' ] );
    }
	else
    {
        delete_post_meta( $id, 'cprp_manually_related', $_REQUEST[ 'cprp_manually' ] );
    }
    
	if( isset( $_REQUEST[ 'cprp_exclude_from_posts' ] ) ){
        update_post_meta( $id, 'cprp_exclude_from_posts', 1 );
    }
	else
	{
		delete_post_meta( $id, 'cprp_exclude_from_posts' );
	}
	
	if( isset( $_REQUEST[ 'cprp_hide_related_posts' ] ) ){
        update_post_meta( $id, 'cprp_hide_related_posts', 1 );
    }
	else
	{
		delete_post_meta( $id, 'cprp_hide_related_posts' );
	}
	
} // End cprp_save

add_action('admin_enqueue_scripts', 'cprp_load_admin_resources', 1);
function cprp_load_admin_resources(){
    global $post;

    $post_types = cprp_get_settings( 'post_type' );
    if( isset( $post ) && in_array( $post->post_type, $post_types) ){
        $plugin_dir_url = plugin_dir_url(__FILE__);
        wp_enqueue_style  ( 'cprp_admin_style', $plugin_dir_url.'styles/cprp_admin.css' );
        wp_enqueue_script ( 'jquery' );
        wp_enqueue_script ( 'jquery-ui-sortable' );
        wp_enqueue_script ( 'cprp_admin_script', $plugin_dir_url.'scripts/cprp_admin.js', array( 'jquery', 'jquery-ui-sortable' ), false, true );
        wp_localize_script('cprp_admin_script', 'cprp', array( 'admin_url' => cprp_site_url().'wp-admin/' ) );
    }    
} // End cprp_load_admin_resources

add_action( 'wp_enqueue_scripts', 'cprp_enqueue_scripts', 10, 1 );
function cprp_enqueue_scripts(){
    global $post;
    $plugin_dir_url = plugin_dir_url(__FILE__);
    
    wp_enqueue_script( 'jquery' );
    
    if( is_singular() ) $display = cprp_get_settings( 'display_in_single' );
    else $display = cprp_get_settings( 'display_in_multiple' );
    $dependencies = array( 'jquery' );
    
    switch( strtolower( $display[ 'mode' ] ) ){
        case 'slider':
        case 'column':
        case 'list':
        case 'accordion':		
        break;
    }
    
	$percentage_symbol = cprp_get_settings( 'percentage_symbol' );
	
    wp_enqueue_script( 'cprp_script', $plugin_dir_url.'scripts/cprp.js', $dependencies, false, true );
    wp_enqueue_style ( 'cprp_style',  $plugin_dir_url.'styles/cprp.css' );
    wp_localize_script('cprp_script', 'cprp', array( 'star_on' => $plugin_dir_url.'images/'.$percentage_symbol.'_on.png', 'star_off' => $plugin_dir_url.'images/'.$percentage_symbol.'_off.png' ));
} // End cprp_enqueue_scripts

add_filter( 'the_content', 'cprp_content' );

function cprp_display( $page, $is_widget = false )
{
	global $post;
	
	if( $page == 'single' )
	{
		$display = cprp_get_settings( 'display_in_single' );
		if( !$display[ 'activate' ] && !$is_widget ) return false;
		$cprp_hide_related_posts = get_post_meta( $post->ID, 'cprp_hide_related_posts' );
		if( !empty( $cprp_hide_related_posts ) ) return false;
	}
	else
	{
		$display = cprp_get_settings( 'display_in_multiple' );
		if( !$display[ 'activate' ] ) return false;
		if( !empty( $display[ 'display_in' ] ) )
		{
			if( $display[ 'display_in' ][ 'type' ] == 'home' && !( is_home() || is_front_page() ) ) return false;
			if( 
				$display[ 'display_in' ][ 'type' ] == 'list' && 
				( 
					empty( $display[ 'display_in' ][ 'include_id' ] ) ||
					(
						!is_category( $display[ 'display_in' ][ 'include_id' ] ) &&
						!is_tag( $display[ 'display_in' ][ 'include_id' ] )
					)
				)	
			) return false;
			if( 
				$display[ 'display_in' ][ 'type' ] == 'all' && 
				(
					( $display[ 'display_in' ][ 'exclude_home' ] && ( is_home() || is_front_page() ) ) || 
					(
						!empty( $display[ 'display_in' ][ 'exclude_id' ] ) &&
						( 
							is_category( $display[ 'display_in' ][ 'exclude_id' ] ) ||
							is_tag( $display[ 'display_in' ][ 'exclude_id' ] )
						)	
					)	
				)	
			) return false;
		}
	}
	
	return true;
}


function _cprp_content( $the_content, $mode = '' ){
    global $post, $wpdb;
    
    $str = '';
    $related_posts = array();
    $manually_related = array();
    
    // Checks if the post_type is valid
    if( !in_array( $post->post_type, cprp_get_settings( 'post_type' ) ) ) return $str;
    
    // Checks if the element is displayed on single or multiple page, and if the related posts are activated for it
    if( is_singular() ){
		
        $display = cprp_get_settings( 'display_in_single' );
		if( !cprp_display( 'single', strlen( $mode ) > 0 ) ) return $str;
		
    }else{
		$display = cprp_get_settings( 'display_in_multiple' );
        if( !cprp_display( 'multiple' ) ) return $str;
		
    }
    
    $mode = ( strlen( $mode ) ) ? $mode : $display[ 'mode' ];
    
    $selection_type = cprp_get_settings( 'selection_type' );
    
    // Get posts related manually to the current post
    if( $selection_type[ 'manually' ] ){
        $manually_related = get_post_meta( $post->ID, 'cprp_manually_related' );
        if( !empty( $manually_related ) ){
			if( is_string( $manually_related ) )
			{
				$manually_related = unserialize( $manually_related );
			}
            $manually_related = $manually_related[ 0 ];
            foreach ( $manually_related as $id ){
                $r_post = get_post( $id );
                $cprp_exclude_from_posts = get_post_meta( $id, 'cprp_exclude_from_posts' );
                if( $r_post && empty( $cprp_exclude_from_posts ) ) {
                    $r_post->percentage = 100;
                    $related_posts[] = $r_post;
                }
            }
        }    
    }
    
    $tags_arr = get_post_meta( $post->ID, 'cprp_tags' );
    if( !empty( $tags_arr ) ){
        if( is_string( $tags_arr ) )
		{
			$tags_arr = unserialize( $tags_arr );
		}
            
        $query = "";
		
        $tags_arr = $tags_arr[0];
        
        $s = array_sum( $tags_arr );
        
        $tags = array_keys( $tags_arr );
        $query = "SELECT posts.*, postmeta.meta_value FROM $wpdb->posts as posts, $wpdb->postmeta as postmeta WHERE posts.post_type IN ('".implode( "','", cprp_get_settings( 'post_type' ) )."') AND posts.post_status='publish' AND posts.ID = postmeta.post_id AND posts.ID <> ".$post->ID." AND postmeta.meta_key = 'cprp_tags' AND (postmeta.meta_value LIKE '%".implode( "%' OR postmeta.meta_value LIKE '%", $tags )."%') AND postmeta.post_id NOT IN ( SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'cprp_exclude_from_posts')";
        $results = $wpdb->get_results( $query );
        
        if( count( $results ) ){
		
            $similarity = cprp_get_settings( 'similarity' )/100;
			
            foreach( $results as $key => $result ){
                if( in_array( $result->ID, $manually_related ) ) {
                    unset( $results[ $key ] );
                    continue;
                }    
                $c = 0;
                
                $post_tags  = unserialize( $result->meta_value );
				if( is_string( $post_tags ) )
				{
					$post_tags  = unserialize( $post_tags );
				}
				
                $t = array_sum( $post_tags );
                $result->matching = array();
                foreach ( $post_tags as $tag => $value) {
                    if( isset( $tags_arr[ $tag ] ) ){
                        array_push( $result->matching, $tag );
                        $c += $tags_arr[ $tag ]+$value;
                    }
                }
                $result->percentage = round( $c/($s+$t), 2 );
				if( $result->percentage < $similarity )
				{
					unset( $results[ $key ] );
					continue;
				}
            }
            
            usort( $results, 'cprp_sort' );
            $related_posts = array_merge( $related_posts, $results );
        }    
    }
    
    if( count( $related_posts ) ){
        $h = min( count( $related_posts ), cprp_get_settings( 'number_of_posts' ) ); 
        $str .= '<div class="cprp_items '.$mode.'"><ul>';
    
        for( $i = 0; $i < $h; $i++ ){
            $str .= '<li>';
            $thumb = '';
            $link = get_permalink( $related_posts[ $i ]->ID );
            
            if ( $display[ 'show_thumbnail' ] && has_post_thumbnail( $related_posts[ $i ]->ID ) ){
                $image = wp_get_attachment_image_src( get_post_thumbnail_id(  $related_posts[ $i ]->ID  ), 'single-post-thumbnail' );
                $thumb = '<a href="'.$link.'"><img src="'.$image[ 0 ].'" class="cprp_thumbnail" /></a>';
            }
            
            $str .= '<div class="cprp_data">';
            $str .= '<div class="cprp_title"><a href="'.$link.'">'.$related_posts[ $i ]->post_title.'</a></div>';
            
            
            if( $display[ 'show_percentage' ] )
			{
                $str .= '<div class="cprp_percentage">'.( $related_posts[ $i ]->percentage * 100 ).'</div>';
            }
            
            if( $display[ 'show_excerpt' ] )
			{
                $str .= '<div class="cprp_excerpt">'.$thumb.'<span class="cprp_excerpt_content">'.wp_trim_words( strip_shortcodes( $related_posts[ $i ]->post_content ) ).'</span></div>';
            }
            else
			{
				$str .= $thumb;
			}
			
            if( $display[ 'show_tags' ] && !empty( $related_posts[ $i ]->matching ) )
			{
                $str .= '<div class="cprp_tags">Tags: '.implode( ', ', array_slice( $related_posts[ $i ]->matching, 0, 10 ) ).'</div>';
            }
            
            $str .= '</div>';
            $str .= '</li>';
        }
        
        $str .= '</ul></div>';
    }        
    
    return $str;
} // End _cprp_content

function cprp_content( $the_content ){
    $str = _cprp_content( $the_content );
    if( strlen( $str ) ){
        $str = '<h2>'.__( cprp_get_settings( 'title' ), 'cprp-text' ).'</h2>'.$str;
		$the_content .= $str;
    }        
    return $the_content;
} // End cprp_content

// Additional routines

function cprp_get_settings( $key = '' ){
    global $cprp_default_settings;
    
    $cprp_settings = get_option( 'cprp_settings' );
    if( $cprp_settings !== false ){
        if ( !empty( $key ) ){
            if( isset( $cprp_settings[ $key ] ) ) return $cprp_settings[ $key ];
            else return $cprp_default_settings[ $key ];
        }else{
            return $cprp_settings;
        }
    }else{
        if ( !empty( $key ) ){
            return $cprp_default_settings[ $key ];
        }else{
            return $cprp_default_settings;
        }
    }
} // cprp_get_settings

function cprp_sort( $a, $b ){
    if( $a->percentage == $b->percentage ) return 0;
    return ( $a->percentage < $b->percentage ) ? 1 : -1;
} // End cprp_sort

function cprp_process_post( $post, $include_user_tags = true ){
    global $cprp_tags_obj;
    $tags = $cprp_tags_obj->get_tags( $post->post_title.' '.$post->post_excerpt.' '.$post->post_content );
    if( $include_user_tags ) $associated_tags = wp_get_post_tags( $post->ID );
    if( !empty( $associated_tags ) ){
        $tags_as_text = "";
        foreach( $associated_tags as $t ){
            $tags_as_text .= str_replace( '"', '', $t->name )." ";
        }
        
        $associated_tags = $cprp_tags_obj->get_tags( $tags_as_text );
        foreach($associated_tags as $key => $value){
            if( isset( $tags[ $key ] ) ) $tags[ $key ] += 100;
            else $tags[ $key ] = 100;
        }
    }
    update_post_meta( $post->ID, 'cprp_tags', $tags );
    return $tags;
}

/**
* tags_extractor_shutdown is executed when the PHP script is stopped
*/
function cprp_shutdown(){
    global $last_processed;
    print "<script>document.location='".cprp_site_url()."wp-admin/?cprp-action=extract-all&id=".$last_processed."';</script>";
}

function cprp_site_url(){
    $url_parts = parse_url(get_site_url());
    return rtrim( 
                    ((!empty($url_parts["scheme"])) ? $url_parts["scheme"] : "http")."://".
                    $_SERVER["HTTP_HOST"].
                    ((!empty($url_parts["path"])) ? $url_parts["path"] : ""),
                    "/"
                )."/";
}

// ************************************** CREATE WIDGETS *********************************************/ 

function cprp_load_widgets(){
    register_widget( 'CPRPWidget' );
}
        
/**
 * CPRPWidget Class
 */
class CPRPWidget extends WP_Widget {
    
    /** constructor */
    function CPRPWidget() {
        parent::WP_Widget(false, $name = 'CP Related Posts');	
    }

    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        if( is_singular() )
        {
            global $post;
            $str = _cprp_content( $post->post_content, 'cprp-widget' );
            if( strlen( $str ) )
            {
                echo $before_widget;
                if ( $title ) echo $before_title . $title . $after_title; 
                echo $str;
                echo $after_widget;
            }    
        }
        //echo $GLOBALS['music_store']->load_product_list($atts);
    }

    function update($new_instance, $old_instance) {				
        $instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
    }

    function form( $instance ) {
    
        /* Set up some default widget settings. */
		$defaults = array( 'title' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 
        
        $title      = $instance[ 'title' ];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php 
    }

} // class CPRPWidget
        
?>