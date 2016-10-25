<?php
/**
 * Plugin Name: Gitium Test
 * Version: 2.0
 * Description: Test if Gitium fails after parallel plugins updates. Go to Tools->Gitium test
 * Author: olarmarius
 */

function gitium_init_plugin_template( $plugin_name ) {
$content = <<<EOT
<?php
/**
 * Plugin Name: {plugin_name}
 * Version: 0.1
 * Description: init {plugin_name}
 */
EOT;
	return str_ireplace( '{plugin_name}', $plugin_name, $content );
}

add_action( 'admin_menu', 'gitium_test_menu' );
function gitium_test_menu() {
	add_management_page( 'Gitium test page', 'Gitium test', 'read', 'gitium-test', 'gitium_test_page' );
}

add_action( 'admin_init', 'gitium_init_plugins' );
function gitium_init_plugins() {
	if ( ! isset( $_POST['gitium_init_plugins' ] ) ) {
		return;
	}
	check_admin_referer( 'gitium-test' );
	$plugins = array( // file => Plugin Name
		'utf8ize/utf8ize.php' => 'Utf8ize',
		'mobile-theme/mobile-theme.php' => 'Mobile Theme',
		'presslabs-site-protection/presslabs-site-protection.php' => 'Site Protection',
		'mapping-of-image-posts/mapping-of-image-posts.php' => 'Mapping of image posts',
		'toplytics/toplytics.php' => 'Toplytics'
	);
	$result  = array();
	foreach ( $plugins as $plugin_file => $plugin_name ):
		$dir  = dirname( dirname( __FILE__ ) ) . '/' . dirname( $plugin_file );
		$file = $dir . '/' . basename( $plugin_file );
		if ( ! file_exists( $dir ) ):
			try {
				mkdir( $dir, 0777, true );
			} catch (Exception $_) { }
		endif;
		file_put_contents( $file, gitium_init_plugin_template( $plugin_name ) );
	endforeach;
}

function gitium_test_page() {
?><div class="wrap">
	<h2>Gitium test</h2>
	<form name="gitium_form_test" id="gitium_test" action="" method="POST">
	<?php wp_nonce_field( 'gitium-test' ); ?>
	<p> INIT! <input type="submit" name="gitium_init_plugins" value="Init empty plugins"> </p>
	<p>Test if Gitium fails after parallel plugins updates. <a href="#" onclick="return jsPurePostParallel();">Click here</a> and in then check out the error log file.</p>
	</form>
	</div><?php
}

add_action( 'admin_head', 'gitium_test_admin_head' );
function gitium_test_admin_head() {
?><script type="application/javascript">
function jsPurePost(url) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) { // response is ready
      console.log(xhr.responseURL + ':' + xhr.status);
    }
  }
  xhr.open('POST', url);
  xhr.send(null);
}

function jsPurePostParallel() {
var list = [
<?php
	$current = get_site_transient( 'update_plugins' );
	$plugins = array_keys( $current->response );
	if ( count( $plugins ) ):
		foreach ( $plugins as $index => $plugin_file ):
			$action   = 'upgrade-plugin';
			$upload_url = str_ireplace( '&amp;', '&', wp_nonce_url(
				self_admin_url( "update.php?action={$action}&plugin=" ) . $plugin_file, "{$action}_{$plugin_file}" )
			);
			if ( $index === count( $plugins ) - 1 ) {
				echo "'$upload_url'\n";
			} else {
				echo "'$upload_url',\n";
			}
		endforeach;
	endif;
?>
  ];
  for (var key=0; key<list.length; key++) {
    jsPurePost(list[key]);
  };
}
</script>
<?php
}

