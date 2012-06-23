<?php
/*
Plugin Name: sh-hook-debug
Description: A plug-in that adds a 'hook search' to the admin bar and displays the callbacks hooked onto a selected hook
Version: 1.0
Author: Stephen Harris
Author URI: http://stephenharris.info

Based on Debug Hooked filter callback functions by Stephen Harris, Franz Josef Kaiser. See Gist https://gist.github.com/2238329
*/

add_action( 'after_setup_theme', array( 'SH_Hook_Debug', 'init' ) );
final class SH_Hook_Debug{

	/**
	 * The Class Object
	 * @var
	 */
	static private $class = null;


	/**
	 * The Storage container
	 * @var array
	 */
	public $debug_filters_storage = array();


	/**
	 * Handler for the action 'init'. Instantiates this class.
	 * 
	 * @return void
	 */
	public static function init()
	{
		if ( null === self::$class ) 
			self :: $class = new self;

		return self :: $class;
	}


	/**
	 * Hook the functions
	 * 
	 * @return void
	 */
	public function __construct(){
		if ( ! current_user_can( 'manage_options' ) )
			return;

		add_action( 'all', array( $this, 'store_fired_filters' ) );
		add_action( 'wp_footer', array( $this, 'display_fired_filters' ),999 );
		add_action( 'admin_print_footer_scripts', array( $this, 'display_fired_filters' ),999 );
		add_action('wp_enqueue_scripts',array( $this, 'load_script' ) );
		add_Action('admin_enqueue_scripts', array( $this, 'load_script' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar' ));
	}

	/**
	 * Enqueues the necessary scripts and styles
	 * 
	 * @return void
	 */
	function load_script(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('sh-hook-debug',plugins_url( 'sh-hook-debug.js' , __FILE__ ), array('jquery','jquery-ui-autocomplete','jquery-ui-dialog'));
		wp_enqueue_style('google-jquery-ui','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css');
		//Custom styling for search bar
		wp_add_inline_style('admin-bar',"#hooksearch {margin: 2px;padding: 0;line-height: 20px;}");	
	}

	/**
	 * Adds hook search to admin bar
	 * 
	 * @return void
	 */
	function admin_bar(){
		global $wp_admin_bar;
		if ( ! current_user_can( 'manage_options' ) )
			return;

		$wp_admin_bar->add_menu( array(
			'id'        => 'hooks',
			'title'     => 'Hooks <input type="text" id="hooksearch" value="">',
		) );
	}


	/**
	 * @return array $debug_filters_storage
	 */
	public function store_fired_filters( $tag ){
		global $wp_filter;

		if ( ! isset( $wp_filter[ $tag ] ) )
			return;

		$hooked = $wp_filter[ $tag ];
		ksort( $hooked );

		foreach ( $hooked as $priority => $function )
			$hooked[] = $function;

		$this->debug_filters_storage[] = array(
			'tag'    => $tag,
			'hooked' => $wp_filter[ $tag ],
		);
	}

	/**
	 * Gather hooked functions and print for javascript
	 * @return string $output
	 */
	public function display_fired_filters(){
		$callbacks =array();
		$hooks =array();
		foreach ( $this->debug_filters_storage as $index => $the_ ){
			$hook_callbacks =array();
			if( !in_array($the_['tag'], $hooks) )
				$hooks[] = $the_['tag'];

	            	foreach($the_['hooked'] as $priority => $hooked){
				foreach($hooked as $id => $function){
					if( is_string($function['function'] ) ){
						$hook_callbacks[] = array(
							'name'=>$function['function'],
							'args'=>$function['accepted_args'],
							'priority'=>$priority
						);
					}
	               	}
			}
			$callbacks[$the_['tag']][] =$hook_callbacks;
		}
		?>
		<script>
			callbacks = <?php echo json_encode($callbacks); ?>;
			var hooks = <?php echo json_encode($hooks); ?>;
		</script>
		<?php
	}

} // END Class
