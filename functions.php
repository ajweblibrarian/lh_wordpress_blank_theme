<?php
	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'hbd-theme', TEMPLATEPATH . '/languages' );

	add_theme_support( 'menus' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable($locale_file) )
	    require_once($locale_file);

	// Get the page number
	function get_page_number() {
	    if ( get_query_var('paged') ) {
	        print ' | ' . __( 'Page ' , 'hbd-theme') . get_query_var('paged');
	    }
	} // end get_page_number

	// Custom callback to list comments in the hbd-theme style
	function custom_comments($comment, $args, $depth) {
	  $GLOBALS['comment'] = $comment;
	    $GLOBALS['comment_depth'] = $depth;
	  ?>
	    <li id="comment-<?php comment_ID() ?>" <?php comment_class() ?>>
	        <div class="comment-author vcard"><?php commenter_link() ?></div>
	        <div class="comment-meta"><?php printf(__('Posted %1$s at %2$s <span class="meta-sep">|</span> <a href="%3$s" title="Permalink to this comment">Permalink</a>', 'hbd-theme'),
	                    get_comment_date(),
	                    get_comment_time(),
	                    '#comment-' . get_comment_ID() );
	                    edit_comment_link(__('Edit', 'hbd-theme'), ' <span class="meta-sep">|</span> <span class="edit-link">', '</span>'); ?></div>
	  <?php if ($comment->comment_approved == '0') _e("\t\t\t\t\t<span class='unapproved'>Your comment is awaiting moderation.</span>\n", 'hbd-theme') ?>
	          <div class="comment-content">
	            <?php comment_text() ?>
	        </div>
	        <?php // echo the comment reply link
	            if($args['type'] == 'all' || get_comment_type() == 'comment') :
	                comment_reply_link(array_merge($args, array(
	                    'reply_text' => __('Reply','hbd-theme'),
	                    'login_text' => __('Log in to reply.','hbd-theme'),
	                    'depth' => $depth,
	                    'before' => '<div class="comment-reply-link">',
	                    'after' => '</div>'
	                )));
	            endif;
	        ?>
	<?php } // end custom_comments

	// Custom callback to list pings
	function custom_pings($comment, $args, $depth) {
	       $GLOBALS['comment'] = $comment;
	        ?>
	            <li id="comment-<?php comment_ID() ?>" <?php comment_class() ?>>
	                <div class="comment-author"><?php printf(__('By %1$s on %2$s at %3$s', 'hbd-theme'),
	                        get_comment_author_link(),
	                        get_comment_date(),
	                        get_comment_time() );
	                        edit_comment_link(__('Edit', 'hbd-theme'), ' <span class="meta-sep">|</span> <span class="edit-link">', '</span>'); ?></div>
	    <?php if ($comment->comment_approved == '0') _e('\t\t\t\t\t<span class="unapproved">Your trackback is awaiting moderation.</span>\n', 'hbd-theme') ?>
	            <div class="comment-content">
	                <?php comment_text() ?>
	            </div>
	<?php } // end custom_pings

	// Produces an avatar image with the hCard-compliant photo class
	function commenter_link() {
	    $commenter = get_comment_author_link();
	    if ( ereg( '<a[^>]* class=[^>]+>', $commenter ) ) {
	        $commenter = ereg_replace( '(<a[^>]* class=[\'"]?)', '\\1url ' , $commenter );
	    } else {
	        $commenter = ereg_replace( '(<a )/', '\\1class="url "' , $commenter );
	    }
	    $avatar_email = get_comment_author_email();
	    $avatar = str_replace( "class='avatar", "class='photo avatar", get_avatar( $avatar_email, 80 ) );
	    echo $avatar . ' <span class="fn n">' . $commenter . '</span>';
	} // end commenter_link

	// For category lists on category archives: Returns other categories except the current one (redundant)
	function cats_meow($glue) {
	    $current_cat = single_cat_title( '', false );
	    $separator = "\n";
	    $cats = explode( $separator, get_the_category_list($separator) );
	    foreach ( $cats as $i => $str ) {
	        if ( strstr( $str, ">$current_cat<" ) ) {
	            unset($cats[$i]);
	            break;
	        }
	    }
	    if ( empty($cats) )
	        return false;

	    return trim(join( $glue, $cats ));
	} // end cats_meow

	// For tag lists on tag archives: Returns other tags except the current one (redundant)
	function tag_ur_it($glue) {
	    $current_tag = single_tag_title( '', '',  false );
	    $separator = "\n";
	    $tags = explode( $separator, get_the_tag_list( "", "$separator", "" ) );
	    foreach ( $tags as $i => $str ) {
	        if ( strstr( $str, ">$current_tag<" ) ) {
	            unset($tags[$i]);
	            break;
	        }
	    }
	    if ( empty($tags) )
	        return false;

	    return trim(join( $glue, $tags ));
	} // end tag_ur_it

	// Register widgetized areas
	function theme_widgets_init() {
	    // Area 1
	    register_sidebar( array (
	    'name' => 'Primary Widget Area',
	    'id' => 'primary_widget_area',
	    'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
	    'after_widget' => "</li>",
	    'before_title' => '<h3 class="widget-title">',
	    'after_title' => '</h3>',
	  ) );

	    // Area 2
	    register_sidebar( array (
	    'name' => 'Secondary Widget Area',
	    'id' => 'secondary_widget_area',
	    'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
	    'after_widget' => "</li>",
	    'before_title' => '<h3 class="widget-title">',
	    'after_title' => '</h3>',
	  ) );
	} // end theme_widgets_init

	add_action( 'init', 'theme_widgets_init' );

	$preset_widgets = array (
	    'primary_widget_area'  => array( 'search', 'pages', 'categories', 'archives' ),
	    'secondary_widget_area'  => array( 'links', 'meta' )
	);
	if ( isset( $_GET['activated'] ) ) {
	    update_option( 'sidebars_widgets', $preset_widgets );
	}
	// update_option( 'sidebars_widgets', NULL );

	// Check for static widgets in widget-ready areas
	function is_sidebar_active( $index ){
	  global $wp_registered_sidebars;

	  $widgetcolums = wp_get_sidebars_widgets();

	  if ($widgetcolums[$index]) return true;

	    return false;
	} // end is_sidebar_active

	function blank_customize_register( $wp_customize ) {
        //All our sections, settings, and controls will be added here
        $colors = array();
        $colors[] = array(
            'slug'=>'content_text_color', 
            'default' => '#333',
            'label' => __('Content Text Color', 'Blank')
        );
        $colors[] = array(
            'slug'=>'content_link_color', 
            'default' => '#88C34B',
            'label' => __('Content Link Color', 'Blank')
        );
        foreach( $colors as $color ) {
            // SETTINGS
            $wp_customize->add_setting(
                $color['slug'], array(
                    'default' => $color['default'],
                    'type' => 'option', 
                    'capability' => 
                    'edit_theme_options'
                )
            );
            // CONTROLS
            $wp_customize->add_control(
                new WP_Customize_Color_Control(
                    $wp_customize,
                    $color['slug'], 
                    array('label' => $color['label'], 
                    'section' => 'colors',
                    'settings' => $color['slug'])
                )
            );
        }
	}
	add_action( 'customize_register', 'blank_customize_register' );

    //////////////////////////////////////////////////////////////////////////
    /////////////////////////////Custom Sections//////////////////////////////
    //////////////////////////////////////////////////////////////////////////
    
    /*
        Site name:
            Logo
            Church Name
        Contact:
            Address
            Phone
            Email
            Social Media
        Featured Image:
            Presets
            Upload
        Welcome/Statement of Belief:
            Text
        Worship Times:
            Text
        Color Scheme:
            Options
    */
    
    class customize_Contact_Control extends WP_Customize_Control {
        public $type = 'textarea';
        public function render_content() {
    ?>
        <label>
            <span class="customize-contact-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5" style="width:100%;" <?php $this->link(); ?> > <?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>
    <?php
        }
    }
    $wp_customize->add_setting('contact_setting', array('default' => 'default text',));
    $wp_customize->add_control(new customize_Contact_Control($wp_customize, 'contact_setting', array(
        'label' => 'Contact',
        'section' => 'contact',
        'settings' => 'contact_setting',
    )));
    $wp_customize->add_section('contact' , array(
        'title' => __('Contact',''),
        'priority' => 20,
    ));
    
    
    
    class customize_Featured_Image_Control extends WP_Customize_Control {
        public $type = 'textarea';
        public function render_content() {
    ?>
        <label>
            <span class="customize-image-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5" style="width:100%;" <?php $this->link(); ?> > <?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>
    <?php
        }
    }
    $wp_customize->add_setting( 'img-upload' );
 
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,'img-upload',array(
            'label' => 'Upload Featured Image',
            'section' => 'featuredimage',
            'settings' => 'img-upload'
        )));
        
    $wp_customize->add_section('featuredimage' , array(
        'title' => __('Featured Image',''),
        'priority' => 30,
    ));
    
    
    
    class customize_Welcome_Control extends WP_Customize_Control {
        public $type = 'textarea';
        public function render_content() {
    ?>
        <label>
            <span class="customize-welcome-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5" style="width:100%;" <?php $this->link(); ?> > <?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>
    <?php
        }
    }
    $wp_customize->add_setting('welcome_setting', array('default' => 'default text',));
    $wp_customize->add_control(new customize_Welcome_Control($wp_customize, 'welcome_setting', array(
        'label' => 'Welcome/Statment of Belief',
        'section' => 'welcome',
        'settings' => 'welcome_setting',
    )));
    $wp_customize->add_section('welcome' , array(
        'title' => __('Welcome/Statement of Belief',''),
        'priority' => 40,
    ));
   
   
   
   class customize_Worship_Times_Control extends WP_Customize_Control {
        public $type = 'textarea';
        public function render_content() {
    ?>

        <label>
            <span class="customize-worship-title"><?php echo esc_html( $this->label ); ?></span>
            <textarea rows="5" style="width:100%;" <?php $this->link(); ?> > <?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>

    <?php
        }
    }
    $wp_customize->add_setting('worship_setting', array('default' => 'default text',));
    $wp_customize->add_control(new customize_Worship_Times_Control($wp_customize, 'worship_setting', array(
        'label' => 'Worship Times',
        'section' => 'worship',
        'settings' => 'worship_setting',
    )));
    $wp_customize->add_section('worship' , array(
        'title' => __('Worship Times',''),
        'priority' => 50,
    ));
   
?>