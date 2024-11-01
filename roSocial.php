<?php
/*
Plugin Name: Ro Social
Plugin URI: http://ursut.ro/12/18/plugin-wordpress-wp-rosocial.html
Description: Adauga linkuri spre articolele tale in cele mai cunoscute site-uri sau retele sociale romanesti. 
Version: 2.0
Author: Cristi Ursut
Author URI: http://ursut.ro
*/

/**
 * Determina locatia
 */
$socialpluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';

/**
 * For backwards compatibility, esc_attr was added in 2.8
 */
if (! function_exists('esc_attr')) {
	function esc_attr( $text ) {
		return attribute_escape( $text );
	}
}



/**
 * @global array contine toate site-urile disponibile (favicon si url sunt obligatorii):
 * favicon - contine favicon-ul site-ului.
 * url - submit URL pentru fiecare site, trebuie sa contina cel putin PERMALINK
 * description - o scurta descriere, folosita si pt atributul title la generarea linkurilor.
 * pozitia - pozitia 
 */
$social_known_sites = Array(	'email' => Array(
		'favicon' => 'email_link.png',
		'url' => 'mailto:?subject=TITLE&amp;body=PERMALINK',
		'description' => 'Trimite prin e-mail',
		'pozitie' => 10,
	),

	'Facebook' => Array(
		'favicon' => 'facebook.png',
		'url' => 'http://www.facebook.com/share.php?u=PERMALINK&amp;t=TITLE',
		'pozitie' => 5,
		
	),	
	
	 'Adauga la favorite' => Array(	 	
	    'favicon' => 'addtofavorites.png',	 	
		'url' => 'javascript:AddToFavorites();',	
        'description' => 'Adauga linkul la favorite',	
        'pozitie' => 8,
	),

	'LinkedIn' => Array(
		'favicon' => 'linkedin.png',
		'url' => 'http://www.linkedin.com/shareArticle?mini=true&amp;url=PERMALINK&amp;title=TITLE&amp;source=BLOGNAME&amp;summary=EXCERPT',
	    'pozitie' => 12,
	),
	'MySpace' => Array(
		'favicon' => 'myspace.png',
		'url' => 'http://www.myspace.com/Modules/PostTo/Pages/?u=PERMALINK&amp;t=TITLE',
		'pozitie' => 13,
		
	),
	'Print' => Array(
		'favicon' => 'printfriendly.png',
		'url' => 'http://www.printfriendly.com/print?url=PERMALINK&amp;partner=social',
	    'description' => 'Scoate la imprimanta',
		'pozitie' => 7,
	),

	'RSS' => Array(
		'favicon' => 'rss.png',
		'url' => 'FEEDLINK',
		'pozitie' => 14,
	
	),

	'Twitter' => Array(
		'favicon' => 'twitter.png',
		'url' => 'http://twitter.com/home?status=TITLE%20-%20PERMALINK',
		'description' => 'Da pe Twitter',
		'pozitie' => 4,
	
	),

    'ftw' => Array(
		'favicon' => 'ftw.png',
		'url' => 'http://www.ftw.ro/node/add/drigg/?url=PERMALINK',
		'pozitie' => 1,
        ),

    'Ghidoo' => Array(
		'favicon' => 'ghidoo.png',
		'url' => 'http://www.ghidoo.ro/nodes/new?node%5Burl%5D=PERMALINK&amp;node%5Btitle%5D=TITLE',
		'pozitie' => 2,
	),

    'YMessenger' => Array(
		'favicon' => 'ym.png',
		'url' => 'ymsgr:im?msg=PERMALINK+TITLE',
		'description' => 'Trimite pe Messenger',
		'pozitie' => 6,
	),
     'Zilei' => Array(
		'favicon' => 'zilei.png',
		'url' => 'http://zilei.ro/submit/?url=PERMALINK',
		'pozitie' => 18,
	),
          
    'fain Polimedia' => Array(
		'favicon' => 'polimedia.png',
		'url' => 'http://polimedia.us/fain/submit.php?url=PERMALINK',
		'pozitie' => 11,
	),
 
    'voxro' => Array(
		'favicon' => 'voxro.png',
		'url' => 'http://voxro.com/node/add/drigg/?url=PERMALINK',
		'pozitie' => 17,
        ),
    'proddit' => Array(
		'favicon' => 'proddit.png',
		'url' => 'http://proddit.com/submit?url=PERMALINK&amp;title=TITLE',
		'pozitie' => 16,
		),
		
	 'digg_ro' => Array (
		'favicon' => 'digg.png',
		'url' => 'http://www.digg.ro/node/add/drigg/?url=PERMALINK',
		'pozitie' => 3,
	    ),
		
	'Google Buzz' => Array (
		'favicon' => 'google-buzz.png',
		'url' => 'http://www.google.com/reader/link?url=PERMALINK&amp;title=TITLE&amp;srcTitle=BLOGNAME&amp;srcUrl=HOMELINK',
		'pozitie' => 15,
	    ),
		
	'Cafenea' => Array (
		'favicon' => 'cafeneaua.png',
		'url' => 'http://cafeneaua.com/nodes/new?node%5Burl%5D=PERMALINK&amp;node%5Btitle%5D=TITLE',
		'pozitie' => 9,
	    ),
    
);

/**
 * Returns the Ro Social links list.
 *
 * @param array $display optional list of links to return in HTML
 * @global $social_known_sites array the list of sites that Ro Social uses
 * @global $socialpluginpath string the path to the plugin
 * @global $wp_query object - WordPress query object
 * @global $sizee - retine 
 * @return string $html HTML pentru linkuri.
 */
function social_html($display=array()) {
	global $social_known_sites, $socialpluginpath, $wp_query, $post; 
    $sizee = get_option('dimensiuni_iconite');
	if (get_post_meta($post->ID,'_socialoff',true)) {
		return "";
	}

	/**
	 * Make it possible for other plugins or themes to add buttons to Ro Social
	 */
	$social_known_sites = apply_filters('social_known_sites',$social_known_sites);

	$active_sites = get_option('social_active_sites');

	// If a path is specified where Ro Social should find its images, use that, otherwise, 
	// set the image path to the images subdirectory of the Ro Social plugin.
	// Image files need to be png's.
	$imagepath = get_option('social_imagedir');
	if ($imagepath == "")
		$imagepath = $socialpluginpath.'images/';		

	// if no sites are specified, display all active
	// have to check $active_sites has content because WP
	// won't save an empty array as an option
	if (empty($display) and $active_sites)
		$display = $active_sites;
	// if no sites are active, display nothing
	if (empty($display))
		return "";

	// Load the post's and blog's data
	$blogname 	= urlencode(get_bloginfo('name'));
	$blogrss	= get_bloginfo('rss2_url'); 
	$post 		= $wp_query->post;
	
	// Grab the excerpt, if there is no excerpt, create one
	$excerpt	= urlencode(strip_tags(strip_shortcodes($post->post_excerpt)));
	if ($excerpt == "") {
		$excerpt = urlencode(substr(strip_tags(strip_shortcodes($post->post_content)),0,250));
	}
	// Clean the excerpt for use with links
	$excerpt	= str_replace('+','%20',$excerpt);
	$permalink 	= urlencode(get_permalink($post->ID));
	$title 		= str_replace('+','%20',urlencode($post->post_title));
	
	$rss 		= urlencode(get_bloginfo('ref_url'));

	// Start preparing the output
	$html = "\n<div class=\"social\">\n";
	
	// If a tagline is set, display it above the links list
	$tagline = get_option("social_tagline");
	if ($tagline != "") {
		$html .= "<div class=\"social_tagline\">\n";
		$html .= stripslashes($tagline);
		$html .= "\n</div>";
	}
	
	/**
	 * Start the list of links
	 */
	$html .= "\n<ul>\n";

	$i = 0;
	$totalsites = count($display);
	foreach($display as $sitename) {
		/**
		 * If they specify an unknown or inactive site, ignore it.
		 */
		if (!in_array($sitename, $active_sites))
			continue;

		$site = $social_known_sites[$sitename];
        $homelink = get_bloginfo('url');
 		
		$url = $site['url'];
		$url = str_replace('TITLE', $title, $url);
		$url = str_replace('RSS', $rss, $url);
		$url = str_replace('BLOGNAME', $blogname, $url);
		$url = str_replace('EXCERPT', $excerpt, $url);
		$url = str_replace('FEEDLINK', $blogrss, $url);
		$url = str_replace('HOMELINK', $homelink, $url);
		
		if (isset($site['description']) && $site['description'] != "") {
			$description = $site['description'];
		} else {
			$description = $sitename;
		}
		/**
			 * if awe.sm is not used, simply replace PERMALINK with $permalink
			 */ 
			$url = str_replace('PERMALINK', $permalink, $url);		
	
		/**
		 * Start building each list item. They're build up separately to allow filtering by other
		 * plugins.
		 * Give the first and last list item in the list an extra class to allow for cool CSS tricks
		 */
		if ($i == 0) {
			$link = '<li class="socialfirst">';
		} else if ($totalsites == ($i+1)) {
			$link = '<li class="sociallast">';
		} else {
			$link = '<li>';
		}
		
		/**
		 * Start building the link, nofollow it to make sure Search engines don't follow it, 
		 * and optionally add target=_blank to open in a new window if that option is set in the 
		 * backend.
		 */
		$link .= '<a ';
		$link .= 'rel="nofollow"';
		//$link .= ' id="'.esc_attr(strtolower(str_replace(" ", "", $sitename))).'" ';
		/**
		 * Use the iframe option if it is enabled and supported by the service/site
		 */
			if(!($sitename=="Adauga la favorite")) {
				if (get_option('social_usetargetblank')) {
					$link .= " target=\"_blank\"";
				}
				$link .= " href=\"".$url."\" title=\"$description\">";
			} else {
				$link .= " href=\"$url\" title=\"$description\">";			
			} 
		/**
		 * If the option to use text links is enabled in the backend, display a text link, otherwise, 
		 * display an image.
		 */
		if (get_option('social_usetextlinks')) {
			$link .= $description;
		} else {
		
		/**
		* Verifica dimensiunea aleasa
		*/
		switch($sizee)
                {
       				case 0: $size=16; break;
                    case 1: $size=24; break;
				    case 2: $size=32; break;
					}
			/**
			 * If site doesn't have sprite information
			 */
			 if (!isset($site['pozitie']) || get_option('social_disablesprite', false) || is_feed()) {

				if (strpos($site['favicon'], 'http') === 0) {
					$imgsrc = $site['favicon'];
				} else {
					$imgsrc = $imagepath.$site['favicon'];
				}
				
				$link .= "<img src=\"".$imgsrc."\"  width=\"".$size."\" height=\"".$size."\" title=\"$description\" alt=\"$description\" ";
				$link .= "class=\"social-hovers\"";
			/**
			 * If site has sprite information use it
			 */
			 } else {
				$imgsrc = $imagepath."sprite.gif";
				$services_sprite_url = $imagepath . "sprite".$size.".png";
				$help[0]=$size;
				$help[1]=($site['pozitie']-1)*($size);
				$link .= "<img src=\"".$imgsrc."\" title=\"$description\" alt=\"$description\" width=\"".$size."\" height=\"".$size."\" style=\" width: $help[0]px; height: $help[0]px; background: transparent url($services_sprite_url) no-repeat; background-position:-$help[1]px -0px \"";
							
			}
			
			$link .= " />";
		}
		$link .= "</a></li>";
		
		/**
		 * Add the list item to the output HTML, but allow other plugins to filter the content first.
		 * This is used for instance in the Google Analytics for WordPress plugin to track clicks
		 * on Ro Social links.
		 */
		$html .= "\t".apply_filters('social_link',$link)."\n";
		$i++;
	}

	$html .= "</ul>\n</div>\n";

	return $html;
}

/**
 * Hook the_content to output html if we should display on any page
 */
$social_contitionals = get_option('social_conditionals');
if (is_array($social_contitionals) and in_array(true, $social_contitionals)) {
	add_filter('the_content', 'social_display_hook');
	add_filter('the_excerpt', 'social_display_hook');
	
	/**
	 * Loop through the settings and check whether Ro Social should be outputted.
	 */
	function social_display_hook($content='') {
		$conditionals = get_option('social_conditionals');
		if ((is_home()     and $conditionals['is_home']) or
		    (is_single()   and $conditionals['is_single']) or
		    (is_page()     and $conditionals['is_page']) or
		    (is_category() and $conditionals['is_category']) or
			(is_tag() 	   and $conditionals['is_tag']) or
		    (is_date()     and $conditionals['is_date']) or
			(is_author()   and $conditionals['is_author']) or
		    (is_search()   and $conditionals['is_search'])) {
			$content .= social_html();
		} elseif ((is_feed() and $conditionals['is_feed'])) {
			$social_html = social_html();
			$social_html = strip_tags($social_html,"<a><img>");
			$content .= $social_html . "<br/><br/>";
		}
		return $content;
	}
}

/**
 * Set the default settings on activation on the plugin.
 */
function social_activation_hook() {
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = 'socialoff'");
	return social_restore_config(false);
}
register_activation_hook(__FILE__, 'social_activation_hook');

/**
 * Add the Ro Social menu to the Settings menu
 * @param boolean $force if set to true, force updates the settings.
 */
function social_restore_config($force=false) {
	global $social_known_sites;

	if ($force or !is_array(get_option('social_active_sites')))
		update_option('social_active_sites', array(
			'ftw',
			'Ghidoo',
			'Twitter',
			'Facebook',
			'YMessenger',
			'Print',
			'digg_ro'
		));

	if ($force or !is_string(get_option('social_tagline')))
		update_option('social_tagline',"<strong>Trimite si prietenilor:</strong>");

	if ($force or !is_array(get_option('social_conditionals')))
		update_option('social_conditionals', array(
			'is_home' => False,
			'is_single' => True,
			'is_page' => True,
			'is_category' => False,
			'is_tag' => False,
			'is_date' => False,
			'is_search' => False,
			'is_author' => False,
			'is_feed' => False,
		));
		
	if ($force or !( get_option('social_disablesprite')) ) 
		update_option('social_disablesprite', false);	
			
	if ($force OR !( get_option('social_usecss') ) )
		update_option('social_usecss', true);
		
	if ($force OR !( get_option('dimensiuni_iconite'))){ 
	        $size=1;
			$_POST['size']=1;
			update_option('dimensiuni_iconite',$size);	
	}
		
}

/**
 * Add the Ro Social menu to the Settings menu
 */
function rosocial_admin_menu() {
	add_options_page('Ro Social', 'Ro Social', 8, 'ro-social', 'social_submenu');
}
add_action('admin_menu', 'rosocial_admin_menu');

/**
 * Make sure the required javascript files are loaded in the Ro Social backend, and that they are only
 * loaded in the Ro Social settings page, and nowhere else.
 */
function social_admin_js() {
	if (isset($_GET['page']) && $_GET['page'] == 'ro-social') {
		global $socialpluginpath;
		
		wp_enqueue_script('jquery'); 
		wp_enqueue_script('jquery-ui-core',false,array('jquery')); 
		wp_enqueue_script('jquery-ui-sortable',false,array('jquery','jquery-ui-core')); 
		wp_enqueue_script('social-js',$socialpluginpath.'rosocial-admin.js', array('jquery','jquery-ui-core','jquery-ui-sortable')); 
	}
}
add_action('admin_print_scripts', 'social_admin_js');

/**
 * Make sure the required stylesheet is loaded in the Ro Social backend, and that it is only
 * loaded in the Ro Social settings page, and nowhere else.
 */
function social_admin_css() {
	global $socialpluginpath;
	if (isset($_GET['page']) && $_GET['page'] == 'ro-social')
		wp_enqueue_style('social-css',$socialpluginpath.'rosocial-admin.css'); 
}
add_action('admin_print_styles', 'social_admin_css');

/**
 * If Wists is active, load it's js file. This is the only site that historically has had a JS file
 * in Ro Social. For all other sites this has so far been refused.
 */
function social_js() {
	
	if (in_array('Adauga la favorite',get_option('social_active_sites'))) {
		global $socialpluginpath;
		wp_enqueue_script('social-addtofavorites',$socialpluginpath.'addtofavorites.js');
	}
}
add_action('wp_print_scripts', 'social_js');

/**
 * If the user has the (default) setting of using the Ro Social CSS, load it.
 */
function social_css() {
	
	if (get_option('social_usecss') == true) {
		global $socialpluginpath;
		wp_enqueue_style('social-front-css',$socialpluginpath.'rosocial.css'); 
	}
}
add_action('wp_print_styles', 'social_css');

/**
 * Update message, used in the admin panel to show messages to users.
 */
function mesaj_social($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}

/**
 * Displays a checkbox that allows users to disable Ro Social on a
 * per post or page basis.
 */
function social_meta() {
	global $post;
	$socialoff = false;
	if (get_post_meta($post->ID,'_socialoff',true)) {
		$socialoff = true;
	} 
	?>
	<input type="checkbox" id="socialoff" name="socialoff" <?php checked($socialoff); ?>/> <label for="socialoff">Dezactivezi Ro Social?</label>
	<?php
}

/**
 * Add the checkbox defined above to post and page edit screens.
 */
function social_meta_box() {
	add_meta_box('ro-social','Ro Social','social_meta','post','side');
	add_meta_box('ro-social','Ro Social','social_meta','page','side');
}
add_action('admin_menu', 'social_meta_box');

/**
 * If the post is inserted, set the appropriate state for the Ro Social off setting.
 */
function social_insert_post($pID) {
	if (isset($_POST['socialoff'])) {
		if (!get_post_meta($post->ID,'_socialoff',true))
			add_post_meta($pID, '_socialoff', true, true);
	} else {
		if (get_post_meta($post->ID,'_socialoff',true))
			delete_post_meta($pID, '_socialoff');
	}
}
add_action('wp_insert_post', 'social_insert_post');

/**
 * Displays the Ro Social admin menu, first section (re)stores the settings.
 */
function social_submenu() {
	global $social_known_sites, $social_date, $socialpluginpath;

	$social_known_sites = apply_filters('social_known_sites',$social_known_sites);
	
	if (isset($_REQUEST['restore']) && $_REQUEST['restore']) {
		check_admin_referer('social-config');
		social_restore_config(true);
		mesaj_social("Setarile initiale au fost reluate.");
	} else if (isset($_REQUEST['save']) && $_REQUEST['save']) {
		check_admin_referer('social-config');
		$active_sites = Array();
		if (!$_REQUEST['active_sites'])
			$_REQUEST['active_sites'] = Array();
		foreach($_REQUEST['active_sites'] as $sitename=>$dummy)
			$active_sites[] = $sitename;
		update_option('social_active_sites', $active_sites);
		/**
		 * Have to delete and re-add because update doesn't hit the db for identical arrays
		 * (sorting does not influence associated array equality in PHP)
		 */
		delete_option('social_active_sites', $active_sites);
		add_option('social_active_sites', $active_sites);

		foreach ( array('usetargetblank', 'usecss', 'usetextlinks', 'disablesprite') as $val ) {
			if ( isset($_POST[$val]) && $_POST[$val] )
				update_option('social_'.$val,true);
			else
				update_option('social_'.$val,false);
		}
			foreach ( array('tagline', 'imagedir') as $val ) {
			if ( !$_POST[$val] )
				update_option( 'social_'.$val, '');
			else
				update_option( 'social_'.$val, $_POST[$val] );
		}
		
		if (isset($_POST["imagedir"]) && !trim($_POST["imagedir"]) == "" ) {
			update_option('social_disablesprite', true);
		}
		
		/**
		 * Update conditional displays
		 */
		$conditionals = Array();
		if (!$_POST['conditionals'])
			$_POST['conditionals'] = Array();
		
		$curconditionals = get_option('social_conditionals');
		if (!array_key_exists('is_feed',$curconditionals)) {
			$curconditionals['is_feed'] = false;
		}
		foreach($curconditionals as $condition=>$toggled)
			$conditionals[$condition] = array_key_exists($condition, $_POST['conditionals']);
			
		update_option('social_conditionals', $conditionals);

		mesaj_social("Modificari salvate.");
	}
	
	/**
	 * Show active sites first and in the right order.
	 */
	$active_sites = get_option('social_active_sites');
	$active = Array(); 
	$disabled = $social_known_sites;
	foreach( $active_sites as $sitename ) {
		$active[$sitename] = $disabled[$sitename];
		unset($disabled[$sitename]);
	}
	uksort($disabled, "strnatcasecmp");
	
	/**
	 * Display options.
	 */
?>
<form action="<?php echo attribute_escape( $_SERVER['REQUEST_URI'] ); ?>" method="post">
<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('social-config');
?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Optiuni RO Social</h2>
	<table class="form-table">
	<tr>
		<th>
			Site-uri:<br/>
			<small> Bifeaza site-urile care vrei sa fie active. Daca vrei sa le schimbi ordinea, o poti face cu un simplu drag and drop (tragi de ele cu mouse-ul unde vrei sa fie pozitionate). </small>
		</th>
		<td>
			<div style="width: 100%; height: 100%">
			<ul id="social_site_list">
				<?php foreach (array_merge($active, $disabled) as $sitename=>$site) { ?>
					<li id="<?php echo $sitename; ?>"					
						class="social_site <?php echo (in_array($sitename, $active_sites)) ? "active" : "inactive"; ?>">
						<input
							type="checkbox"
							id="cb_<?php echo $sitename; ?>"
							name="active_sites[<?php echo $sitename; ?>]"
							<?php echo (in_array($sitename, $active_sites)) ? ' checked="checked"' : ''; ?>
						/>
						<?php
						$imagepath = get_option('social_imagedir');
						
						if ($imagepath == "") {
							$imagepath = $socialpluginpath.'images/';
						} else {		
							$imagepath .= (substr($imagepath,strlen($imagepath)-1,1)=="/") ? "" : "/";
						}
						 if (strpos($site['favicon'], 'http') === 0) {
								$imgsrc = $site['favicon'];
							} else {
								$imgsrc = $imagepath.$site['favicon'];
							}
							echo "<img src=\"$imgsrc\" width=\"16\" height=\"16\" />";
						
						
						echo $sitename; ?>
					</li>
				<?php } ?>
			</ul>
			</div>
			<input type="hidden" id="site_order" name="site_order" value="<?php echo join('|', array_keys($social_known_sites)) ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Tagline:
		</th>
		<td>
			 Schimba textul care vrei sa fie afisat inaintea iconitelor. Pentru o mai buna customizare, copiaza continutul fisierului <em>rosocial.css</em> in continutul fisierului css al themei tale <em>style.css</em> dupa care dezactiveaza mai jos folosirea styleshetului css. <br/>
			<input size="80" type="text" name="tagline" value="<?php echo attribute_escape(stripslashes(get_option('social_tagline'))); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Pozitie: 
		</th>
		<td>
			 Iconitele apar la sfarsitul fiecarui post, dar posturile pot fi afisate in diferite tipuri de pagini. Bifand casutele de mai jos, poti alege unde sa plasezi iconitele active. <br/>
			<br/>
			<?php
			/**
			 * Load conditions under which Ro Social displays
			 */
			$conditionals 	= get_option('social_conditionals');
			?>
			<input type="checkbox" name="conditionals[is_home]"<?php checked($conditionals['is_home']); ?> />  Prima pagina a blogului <br/>
			<input type="checkbox" name="conditionals[is_single]"<?php checked($conditionals['is_single']); ?> />  Blogposturi individuale <br/>
			<input type="checkbox" name="conditionals[is_page]"<?php checked($conditionals['is_page']); ?> />  Pagini individuale<br/>
			<input type="checkbox" name="conditionals[is_category]"<?php checked($conditionals['is_category']); ?> />  Arhiva categoriilor <br/>
			<input type="checkbox" name="conditionals[is_tag]"<?php checked($conditionals['is_tag']); ?> />  Arhiva tagurilor <br/>
			<input type="checkbox" name="conditionals[is_date]"<?php checked($conditionals['is_date']); ?> />  Arhiva postuurilor bazata pe data <br/>
			<input type="checkbox" name="conditionals[is_author]"<?php checked($conditionals['is_author']); ?> />  Arhiva autorului <br/>
			<input type="checkbox" name="conditionals[is_search]"<?php checked($conditionals['is_search']); ?> />  Rezultatele cautarii <br/>
			<input type="checkbox" name="conditionals[is_feed]"<?php checked($conditionals['is_feed']); ?> />  In Feedul RSS <br/>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Foloseste CSS: 
		</th>
		<td>
			<input type="checkbox" name="usecss" <?php checked( get_option('social_usecss'), true ); ?> />  Folosesti styleshetul implicit? 
		</td>
	</tr>
	<tr>
	    <th scope="row" valign="top">
		    Selecteaza dimensiunea iconitelor:
			</th>
		<td>	
		    <?php 
	        
			
			$size=get_option('dimensiuni_iconite');
			
			if(!isset($_POST['size']))
			   $_POST['size']=$size;
			   
			if($_POST['size']!=$size)  
	           update_option('dimensiuni_iconite',$_POST['size']);
	
			?> 
			 Mai jos poti selecta dimensiunea iconitelor:<br/>
			<input type="radio" name="size" value="0" <?php if($_POST['size']==0) echo'checked'; ?>> 16x16 <br/>
			<input type="radio" name="size" value="1" <?php if($_POST['size']==1) echo'checked'; ?>> 24x24 <br/>
			<input type="radio" name="size" value="2" <?php if($_POST['size']==2) echo'checked'; ?>> 32x32 
			
		</td>	
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Foloseste linkuri text: 
		</th>
		<td>
			<input type="checkbox" name="usetextlinks" <?php checked( get_option('social_usetextlinks'), true ); ?> />  Doresti sa folosesti linkuri text in locul imaginilor? 
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Dezactiveaza CSS Sprite la imagini
		</th>
		<td> <input type="checkbox" name="disablesprite" <?php checked( get_option('social_disablesprite'), true ); ?> /> Incepand cu versiunea 2, folosim <a href="http://css-tricks.com/css-sprites/" target="_blank">CSS Sprite</a> la afisarea butoanelor, pentru a micsora viteza de incarcare a paginii tale.
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Directorul imaginilor: 
		</th>
		<td>
			 Daca vrei sa folosesti propriile tale imagini, atunci introdu URL-ul directorului tau. Asigura-te insa ca imaginile au acelasi nume  <br/>
			<input size="80" type="text" name="imagedir" value="<?php echo attribute_escape(stripslashes(get_option('social_imagedir'))); ?>" /><br />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			 Deschide in fereastra noua:
		</th>
		<td>
			<input type="checkbox" name="usetargetblank" <?php checked( get_option('social_usetargetblank'), true ); ?> />  Doresti sa adaugi <code>target=_blank</code> la fiecare link? (Acest atribut deschide un link intr-o fereastra/tab nou/noua)
		</td>		
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<span class="submit"><input name="save" value="Salveaza modificarile" type="submit" /></span>
			<span class="submit"><input name="restore" value="Revino la setarile initiale " type="submit"/></span>
		</td>
	</tr>
</table>

<h2>Sustine WP RoSocial</h2>
<p>Cum? Asa:</p>
<ul class="socialmenu">
	<li>Spune parerea ta despre plugin impreuna cu eventuale sugestii pe <a href="http://ursut.ro/12/18/plugin-wordpress-wp-rosocial.html" target="_blank">pagina oficiala a pluginului</a>.</li>
	<li>Scrie despre el pe blogul tau, ca sa il vada cat mai multa lume.</li>
	<li><a href="http://wordpress.org/extend/plugins/wp-ro-social/">Da-i un rating (bun :D)</a> pe WordPress.org.</li>
</ul>


<h2>Despre</h2>
<p>WP RoSocial e bazat pe pluginul <a href="http://blogplay.com/plugin/">Sociable</a> care a fost initial dezvoltat de catre <a href="http://push.cx/">Peter Harkins</a>. Pe urma a fost administrat de catre <a href="http://yoast.com/">Joost de Valk</a>. Incepand cu Septembrie 2009, pluginul este intretinut de catre <a href="http://blogplay.com">BlogPlay.com</a>. WP RoSocial este o versiune a Sociable destinata exclusiv blogurilor romanesti, fiind adaptat de catre <a href="http://ursut.ro" title="Cristi">Ursut Cristi</a> catre site-urile social media autohtone. WP RoSocial pastreaza o buna parte din functionalitatile Sociable, insa a avut parte si de noi functionalitati (ex. selectarea dimensiunilor iconitei), traducere si evident lista de site-uri destinata exclusiv romanilor. Pluginul este distribuit sub licenta GNU GPL version 2.</p>


</div>
</form>
<?php
}
function social_filter_plugin_actions( $links, $file ){
	
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=ro-social">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); 
	}
	return $links;
}
add_filter( 'plugin_action_links', 'social_filter_plugin_actions', 10, 2 );
?>