<?php
/**
 * Plugin Name: 112 Alarmeringen
 * Description: Laat 112 meldingen zien.
 * Version: 1.0
 * Author: Daan Oostindiën
 * Author URI: http://www.oostindien.eu
 * License: GPL
 */

// Code to make the widget be a widget :)
class alarmeringenWidget extends WP_Widget{

	// Adding the widget to the widget list.
	function alarmeringenWidget(){
		$widgetOptions = array('classname' => 'AlarmeringenWidget', 'description' => 'Laat 112 alarmeringen zien van een opgegeven P2000 capcode.' );
		$this->WP_Widget('AlarmeringenWidget', '112 Alarmeringen', $widgetOptions);
	}
	
	// The form shown in the backend
	function form($instance){
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'capcode' => '', 'updatefreq' => '', 'aantal' => 5, 'showLabel' => '0', 'showTime' => '', 'showMelding' => ''));
		$title = $instance['title'];
		$capcode = $instance['capcode'];
		$updatefreq = $instance['updatefreq'];
		$aantal = $instance['aantal'];
		$showLabel = $instance['showLabel'];
		$showTime = $instance['showTime'];
		$showMelding = $instance['showMelding'];
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('capcode'); ?>"><?php _e('Capcode'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('capcode'); ?>" name="<?php echo $this->get_field_name('capcode'); ?>" type="text" value="<?php echo attribute_escape($capcode); ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('updatefreq'); ?>"><?php _e('Update frequentie'); ?>: 
			<select class="widefat" id="<?php echo $this->get_field_id('updatefreq'); ?>" name="<?php echo $this->get_field_name('updatefreq'); ?>" >
				<option value="1" <?php echo (($updatefreq == 1)? 'selected="selected"' : ''); ?>><?php _e('Elke minuut'); ?></option>
				<option value="5" <?php echo (($updatefreq == 5)? 'selected="selected"' : ''); ?>><?php _e('Elke vijf minuten'); ?></option>
				<option value="10" <?php echo (($updatefreq == 10 || $updatefreq == '')? 'selected="selected"' : ''); ?>><?php _e('Elke tien minuten'); ?></option>
				<option value="15" <?php echo (($updatefreq == 15)? 'selected="selected"' : ''); ?>><?php _e('Elke vijftien minuten'); ?></option>
				<option value="30" <?php echo (($updatefreq == 30)? 'selected="selected"' : ''); ?>><?php _e('Elke dertig minuten'); ?></option>
			</select>
			</label></p>
			<p><label for="<?php echo $this->get_field_id('aantal'); ?>"><?php _e('Aantal alarmeringen weergeven'); ?>: 
			<select class="widefat" id="<?php echo $this->get_field_id('aantal'); ?>" name="<?php echo $this->get_field_name('aantal'); ?>" type="text">
			<?php
				$start = 1;
				$end = 20;
				for($i = $start; $i <= $end; $i++){
					echo '<option value="' . $i . '" ' . (($aantal == $i || ($aantal == '' && $i=5))? 'selected="selected"' : '') . '>' . $i . '</option>';
				}
			?>
			</select></label></p>
			<p><label for="<?php echo $this->get_field_id('showLabel'); ?>"><input type="checkbox" name="<?php echo $this->get_field_name('showLabel'); ?>" value="1" <?php echo (($showLabel == 1)? 'checked' : ''); ?>><?php _e('Laat de label zien.'); ?></label></p>
			<p><label for="<?php echo $this->get_field_id('showTime'); ?>"><input type="checkbox" name="<?php echo $this->get_field_name('showTime'); ?>" value="1" <?php echo (($showTime == 1 || $showTime === '')? 'checked' : ''); ?>><?php _e('Laat de datum en tijd zien.'); ?></label></p>
			<p><label for="<?php echo $this->get_field_id('showMelding'); ?>"><input type="checkbox" name="<?php echo $this->get_field_name('showMelding'); ?>" value="1" <?php echo (($showMelding == 1 || $showMelding === '')? 'checked' : ''); ?>><?php _e('Laat de melding zien.'); ?></label></p>
		<?php
	}
	
	// No clue yet
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['capcode'] = $new_instance['capcode'];
		$instance['updatefreq'] = $new_instance['updatefreq'];
		$instance['aantal'] = $new_instance['aantal'];
		$instance['showLabel'] = $new_instance['showLabel'];
		$instance['showTime'] = $new_instance['showTime'];
		$instance['showMelding'] = $new_instance['showMelding'];
		return $instance;
	}
	
	// The actual widget in frontend.
	function widget($args, $instance){
		wp_enqueue_style( 'alarmeringen', plugins_url( '/css/style.css', __FILE__ ), false, '1.0', 'all' );
		$alarmeringen = get_option('alarmeringen_widget_' . $instance['capcode'] . '_' . $instance['updatefreq'] . '_' . $instance['aantal'] . '_json');
		$timestamp = get_option('alarmeringen_widget_' . $instance['capcode'] . '_' . $instance['updatefreq'] . '_' . $instance['aantal'] . '_json_timestamp');
		if(!$alarmeringen || (time() > ($timestamp + ($instance['updatefreq'] * 60)))){
			$nieuweAlarmeringen = file_get_contents('http://www.zoutkamp.net/json/alarmeringen.php?c=' . $instance['capcode'] . '&n=' . $instance['aantal']);
			if($nieuweAlarmeringen != ''){
				update_option('alarmeringen_widget_' . $instance['capcode'] . '_' . $instance['updatefreq'] . '_' . $instance['aantal'] . '_json' , $nieuweAlarmeringen);
				update_option('alarmeringen_widget_' . $instance['capcode'] . '_' . $instance['updatefreq'] . '_' . $instance['aantal'] . '_json_timestamp' , time());
			}
			$alarmeringen = $nieuweAlarmeringen;
		}
		$alarmeringen = json_decode($alarmeringen, true);
		
		extract($args, EXTR_SKIP);
		echo $before_widget;
		$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
		if (!empty($title))
			echo $before_title . $title . $after_title;
		echo '<div class="alarmeringen-container">';
		$beenhere = false;
		foreach($alarmeringen as $alarmering){
			echo '<div class="alarmering">';
				if($instance['showLabel']){
					echo '<div class="alarmering-label">';
						echo $alarmering['label'];
					echo '</div>';
				}
				if($instance['showTime']){
					echo '<div class="alarmering-datum">';
						echo date("d-m-Y H:i", $alarmering['timestamp']);
					echo '</div>';
				}
				if($instance['showMelding']){
					echo '<div class="alarmering-melding">';
						echo $alarmering['text'];
					echo '</div>';
				}
			echo '</div>';
			$beenhere = true;
		}
		if(!$beenhere){
			_e('Geen recente meldingen');
		}
		echo '</div>';
		// WIDGET CODE GOES HERE
		//echo print_r($alarmeringen,1);
		//echo $this->id;
		//echo "<h1>This is my new widget!</h1>";
		echo $after_widget;
	} 
}

add_action( 'widgets_init', create_function('', 'return register_widget("alarmeringenWidget");') );
/*
add_filter( 'cron_schedules', 'alarmeringen_cron_schedules');
function alarmeringen_cron_schedules(){
	return array(
		'one_minutes' => array(
			'interval' => 60,
			'display' => 'Every mintue',
		'five_minutes' => array(
			'interval' => 60 * 5,
			'display' => 'Every five mintues',
		'ten_minutes' => array(
			'interval' => 60 * 10,
			'display' => 'Every ten mintues',
		'fifteen_minutes' => array(
			'interval' => 60 * 15,
			'display' => 'Every fifteen mintues',
		'thirty_minutes' => array(
			'interval' => 60 * 30,
			'display' => 'Every thirty mintues'
		)
	);
}

add_action('alarmeringen_update', 'alarmeringen_update'); 

function alarmeringen_update(){
	//error_log('update!');
    update_option('jsonalarmeringen', 'Hoi' . date("H:i:s", time()));
}

function alarmeringen_activate() {
	// just to be sure, first deactivate.
	$timestamp = wp_next_scheduled( 'alarmeringen_update' );
	wp_unschedule_event($timestamp, 'alarmeringen_update' );
	//wp_schedule_event( time(), 'five_minutes', 'alarmeringen_update' );
}
register_activation_hook( __FILE__, 'alarmeringen_activate' );

function alarmeringen_deactivate() {
	$timestamp = wp_next_scheduled( 'alarmeringen_update' );
	wp_unschedule_event($timestamp, 'alarmeringen_update' );
}
register_deactivation_hook( __FILE__, 'alarmeringen_deactivate' );

/** Step 2 (from text above). * /
add_action( 'admin_menu', 'alarmeringen_menu' );

/** Step 1. * /
function alarmeringen_menu() {
	add_options_page( 'alarmeringen Options', 'alarmeringen', 'manage_options', 'alarmeringen', 'alarmeringen_options' );
	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}

function register_mysettings() {
	//register our settings
	register_setting( 'alarmeringen-group', 'jsonalarmeringen' );
	register_setting( 'alarmeringen-group', 'capcode' );
	register_setting( 'alarmeringen-group', 'discipline' );
}

/** Step 3. * /
function alarmeringen_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
		<div class="wrap">
			<h2>alarmeringen settings</h2>
				<form method="post" action="options.php"> 
				<?php settings_fields( 'alarmeringen-group' ); ?>
				<?php do_settings_sections( 'alarmeringen-group' ); ?>
				Deze plugin haalt de recente meldingen op van een bepaalde capcode. Een capcode is het nummer waar de pagers van de betreffende dienst op reageren. Er zijn lijsten te vinden met capcodes bijvoorbeel op het <a href="http://www.hulpverleningsforum.nl/index.php?topic=51987.0">Hulpverleningsforum</a>.
				<div id="itemRows">
					Capcode: <input type="text" name="capcode" size="6" value="<?php echo get_option('capcode'); ?>"/> discipline: <input type="text" name="discipline" value=<?php echo get_option('discipline'); ?>/> <!--<input onclick="addRow(this.form);" type="button" value="Add row" />-->
				</div>
				<?php submit_button(); ?>
			</form>
			<div class="donate" style="background-color: #FEFF99; border: 1px solid #FDE8AF; border-radius: 5px; text-align: center;">
				<br>
				Love me? Show me! 
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
					<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
					<img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
				</form>
				<br>
			</div>
		</div>
	<?php
}
 
function tijd($string){
	return date("H:i",strtotime($string)); // Aanpassen? check: http://php.net/manual/en/function.date.php
}

function laadData(){
	// sensor bestand pad
	$lines = file(get_option('pad'));
	 
	 // Alles even netjes in een Array zetten.
	foreach ($lines as $line_num => $line) {
		$spatie = strpos($line," ");
		$data[substr($line,0,$spatie)] = substr($line, $spatie);
	}
	
	return $data;
}
 
// [bartag foo="foo-value"]
function alarmeringen_func( $atts ) {
	extract( shortcode_atts( array(
		'sensor' => '',
	), $atts ) );
	
	if(!$data = laadData()){
		return 'Error!';
	}

	$output = $data[$sensor];

	$dateformatstring = get_option('date_format');
	$timeformatstring = get_option('time_format');
	$spatieren = get_option('spatieren');
	$koppel = get_option('datumtijdkoppel');
	if($spatieren){
		$koppel = ' ' . $koppel . ' ';
	}
	
	if(strstr($sensor, 'puredate')){
		$output = date_i18n( $dateformatstring, strtotime($output));// date("d-m-Y", strtotime($output));
	}elseif(preg_match("/alltime_.*?_time/", $sensor)){
		$output = date_i18n($dateformatstring, strtotime($output)) . (!empty($koppel)? $koppel : ' ') . date_i18n($timeformatstring, strtotime($output));//date("d-m-Y \o\m H:i", strtotime($output));
	}elseif(strstr(str_replace("alltime", "", $sensor), 'time')){
		$output = date_i18n( $timeformatstring, strtotime($output));
	}
	
	return $output;
}
add_shortcode( 'alarmeringen', 'alarmeringen_func' );