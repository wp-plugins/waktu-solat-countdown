<?php

/*
Plugin Name: Waktu Solat Countdown
Plugin URI: http://denaihati.com/projek-waktu-solat
Description: Plugin waktu solat beserta jam randik menunjukkan berapa lama sebelum waktu sebelumnya tiba. Projek dengan kerjasama Denaihati Network.
Version: 1.3.4
Author: Mohd Hadihaizil Din
Author URI: http://www.eizil.com
License: GPL2
*/

/*  Copyright 2011  MOHD HADIHAIZIL DIN  (email : eizil@eizil.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
date_default_timezone_set('Asia/Kuala_Lumpur');

include "Hijri_GregorianConvert.class"; // class untuk convert gregorian ke hijri
include_once('waktusolat-init.php');

register_activation_hook(__FILE__,'ezwaktu_solat_install');

// Widget section

class WaktuSolatWidget extends WP_Widget {
    
    /** constructor */
    function WaktuSolatWidget() {
        parent::WP_Widget(false, $name = 'Waktu Solat Widget', $width= '650');
        
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                  <?php if(isset($_COOKIE["kodKawasan"]) && $_COOKIE["kodKawasan"] != ""):
                              waktuSolatMain($_COOKIE["kodKawasan"]);
                        else:      
                              waktuSolatMain($instance['kawasan']);    
                        endif;      
                  ?>  
             
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['kawasan'] = $new_instance['kawasan'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        global $wpdb;
        $title = esc_attr($instance['title']);
        $tableKod = $wpdb->prefix."waktusolatkod2";  
        $kawasan = $wpdb->get_results("SELECT * FROM {$tableKod}",OBJECT);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id( 'kawasan' ); ?>"><?php _e('Kawasan:'); ?></label> 
          <select id="<?php echo $this->get_field_id( 'kawasan' ); ?>" name="<?php echo $this->get_field_name( 'kawasan' ); ?>" class="widefat" style="width:100%;">
          <?php foreach ($kawasan as $data): ?>
            <option value="<?php echo $data->Kod; ?>" <?php if ( $data->Kod == $instance['kawasan'] ) echo 'selected="selected"'; ?>><?php echo $data->Nama ?></option>
              <?php endforeach; ?>
          </select>
        </p>
        <?php 
    }

} // class WaktuSolatWidget

add_action('widgets_init', create_function('', 'return register_widget("WaktuSolatWidget");'));

// enqueue additional script untuk countdown
function waktuSolatMethod() {
   wp_enqueue_script('newscript1', plugins_url('/js/jquery-1.4.1.js', __FILE__), array('jquery'), '1.0', false);
   wp_enqueue_script('newscript2', plugins_url('/js/jquery.lwtCountdown-1.0.js', __FILE__), array('jquery'), '1.0', false);
   wp_enqueue_script('newscript3', plugins_url('/js/jquery.cookies.js', __FILE__), array('jquery'), '1.0', false);

   if(get_option('ezws_color_scheme') != ""):
        $color = get_option('ezws_color_scheme');
   else:
        $color = "default";
   endif;     
   wp_register_style('waktusolat', plugins_url('/style/main_'.$color.'.css', __FILE__), false, 'All'); 
   
   wp_enqueue_style('waktusolat');
}    

add_action('wp_enqueue_scripts', 'waktuSolatMethod');

function waktuSolatMain($kod){
            global $wpdb;
            
            $today = date("d-m-Y", strtotime('today'));                 // tarikh harini.
            $yesterday = date("d-m-Y", strtotime('yesterday'));         // tarikh semalam
            $tomorrow = date("d-m-Y", strtotime('tomorrow'));           // tarikh esok
            $time = strtotime(date("d-m-Y G:i:s"));                     // time sekarang untuk comparison

            $tdate = explode ("-", $today);
            $tdate2 = explode ("-", $today);

            //create date conversion.
            $DateConv=new Hijri_GregorianConvert; 
            $hijri = $DateConv->GregorianToHijri(date("Y/m/d"),"YYYY/MM/DD");

            // create query untuk data dari database.  
            $table = $wpdb->prefix."waktusolat2";  
            $tableKod = $wpdb->prefix."waktusolatkod2";  
            // query data harini
            $row   = $wpdb->get_row("SELECT * FROM $table WHERE tarikh = '$today' AND Kod = '$kod' LIMIT 1", ARRAY_N);
            // query data esok
            $row2  = $wpdb->get_row("SELECT * FROM $table WHERE tarikh = '$tomorrow' AND Kod = '$kod' LIMIT 1", ARRAY_N);
            // query data semalam
            $row3  = $wpdb->get_row("SELECT * FROM $table WHERE tarikh = '$yesterday' AND Kod = '$kod' LIMIT 1", ARRAY_N);
            $namaKawasan  = $wpdb->get_row("SELECT * FROM $tableKod WHERE Kod = '$kod' LIMIT 1", ARRAY_A );

            // control untuk comparison waktu isyak dan imsak
            $timeC = explode("-", $row[3]);
            $timeD = explode("-", $row[9]);

            for($c = 4; $c < 11; $c++):
                
                $data = explode("-", $row[$c]);
                $data2 = explode("-", $row[$c-1]);
                $data3 = explode("-", $row[$c+1]);     


                // check kalau waktu skang belum kul 23:50 tapi dah lebih waktu isyak
                if(date("G:i:s") < "23:59" && strtotime(date("G:i:s")) > strtotime($row[1]." ".$timeD[1]) ):
                            // kalau data skang Isyak dan lebih waktu isyak, create query baru.
                            if($data2[0] == "Isyak" && $time > strtotime($row[1]." ".$timeD[1])):
                              // dapatkan nama ngan waktu isyak harini
                              $now = $data2[0];
                              $nowtime = $data2[1];

                              // dapatkan nama ngan waktu imsak esok
                              $data4 = explode("-", $row2[3]);
                              $next = $data4[0];
                              $nextime = $data4[1];

                              // dapatkan nama ngan waktu subuh esok
                              $data5 = explode("-", $row2[4]);
                              $future = $data5[0];
                              $futuretime = $data5[1];

                              //asingkan jam dan minit untuk imsak esok
                              $t     = explode(":", $data4[1]);
                              $nexthour = $t[0];
                              $nextmin  = $t[1];
                              //asingkan jam dan minit untuk subuh esok
                              $t2     = explode(":", $data5[1]);
                              $nexthour2 = $t2[0];
                              $nextmin2  = $t2[1];
                              // tukar tarikh jadi esok. 
                              $tdate = explode ("-", $tomorrow);
                              $tdate2 = explode ("-", $tomorrow);
                            endif;  
                    // kalau dah lebih kul 12 malam...         
                    elseif(date("G:i:s") > "0:00"):
                            // check data adalah Isyak, dan time sekarang belum masuk waktu imsak.
                            if($data2[0] == "Isyak" && $time < strtotime($row[1]." ".$timeC[1])): 
                              // dapatkan nama ngan waktu isyak semalam
                              $data6 = explode("-", $row3[9]);
                              $now = $data6[0];
                              $nowtime = $data6[1];  
                               
                              // dapatkan nama ngan waktu imsak harini  
                              $data7 = explode("-", $row[3]);
                              // Print out waktu dan nama  
                              $next  = $data7[0];
                              $nextime  = $data7[1];
                              // asingkan jam dan minit dari data 
                              $t     = explode(":", $data7[1]);
                              $nexthour = $t[0];          
                              $nextmin  = $t[1];
                              
                              // dapatkan nama ngan waktu subuh harini  
                              $data8 = explode("-", $row[4]);
                              $future = $data8[0];
                              $futuretime = $data8[1];
                              // asingkan jam dan minit dari data
                              $t2     = explode(":", $data8[1]);
                              $nexthour2 = $t2[0];
                              $nextmin2  = $t2[1];
                              // kekalkan tarikh untuk atas bawah harini. 
                              $tdate = explode ("-", $today);
                              $tdate2 = explode ("-", $today);
                            endif;    
                               
                    endif;
                
                               
               
                if ($time < strtotime($row[1]." ".$data[1]) && $time > strtotime($row[1]." ".$data2[1])):
                    // check kalau waktu Maghrib
                    if($data2[0] == "Maghrib"): 
                        // dapatkan nama dan waktu maghrib harini
                        $now  = $data2[0];
                        $nowtime = $data2[1];
                        // dapatkan nama dan waktu isyak harini
                        $next = $data[0]; 
                        $nextime = $data[1];

                        // asingkan jam dan minit untuk waktu isyak harini
                        $t     = explode(":", $data[1]);
                        $nexthour = $t[0];
                        $nextmin  = $t[1];

                        // dapatkan nama dan waktu imsak esok
                        $dataX = explode("-", $row2[3]);

                        $future = $dataX[0];
                        $futuretime = $dataX[1];
                        // asingkan jam dan minit untuk waktu imsak esok 
                        $t2     = explode(":", $dataX[1]);
                        $nexthour2 = $t2[0];
                        $nextmin2  = $t2[1];
                        // asingkan tarikh untuk next kepada harini, dan future kepada esok
                        $tdate = explode ("-", $today);
                        $tdate2 = explode ("-", $tomorrow);
                    else:
                    
                    // dapatkan nama dan waktu solat  
                    $now  = $data2[0];
                    $nowtime = $data2[1];
                    // dapatkan nama dan waktu solat seterusnya
                    $next = $data[0]; 
                    $nextime = $data[1];
                    // dapatkan nama dan waktu solat selepas yang skangni
                    $future = $data3[0]; 
                    $futuretime = $data3[1];

                    // asingkan jam dan minit untuk waktu solat seterusnya                   
                    $t     = explode(":", $data[1]);
                    $nexthour = $t[0];
                    $nextmin  = $t[1];

                    // asingkan jam dan minit untuk waktu solat selepas yang skangni
                    $t2     = explode(":", $data3[1]);
                    $nexthour2 = $t2[0];
                    $nextmin2  = $t2[1];
                    endif;
                endif;      

            endfor;
 // check if custom css enable or disabled;
   if(get_option('ezws_css_enable') == "Yes"):
          echo '<style>'. get_option('ezws_custom_css'). '</style>';
   endif; 

   if($row[2] == "Ahad"):
        $harijawi = "احد";
   elseif($row[2] == "Isnin"):
        $harijawi = "اثنين";
   elseif($row[2] == "Selasa"):
        $harijawi = "ثلاث";
   elseif($row[2] == "Rabu"):
        $harijawi = "رابو";
   elseif($row[2] == "Khamis"):
        $harijawi = "خميس";  
   elseif($row[2] == "Jumaat"):
        $harijawi = "جمعة";
   elseif($row[2] == "Sabtu"):
        $harijawi = "سبتو";
   endif;  
?>

<div id="wscontainer" <?php if(get_option('ezws_bg_scheme')!= ""): echo 'style="background-color:'.get_option('ezws_bg_scheme').'"'; endif; ?> >
                <div class="info_message" id="ezws_main" <?php if(get_option('ezws_textalign')!= ""): echo 'style="text-align:'.get_option('ezws_textalign').'"'; endif; ?>>
                    <span style="font-size: 20px"><?php echo $harijawi; ?></span><br />
                  <!--   <?php // echo $row[1]; ?> <br /> -->
                    <?php echo $hijri; ?> <br />
                    <?php echo ucfirst(strtolower($namaKawasan['Nama']));  ?> <br />
                    <span id="waktusolat">
                    Now : <?=$now?> (<?=$nowtime?>) <br />
                    Next : <?=$next?> (<?=$nextime?>) <br /></span>
                  </div>
                <div class="info_message" id="complete_info_message" style="display: none; ">
                    Telah Masuk Waktu <?php echo $next; ?> Bagi Kawasan <?php echo $row["Negeri"]; ?>
                </div>  
                <!-- Countdown dashboard start -->
                <div id="countdown_dashboard">
                  <div class="dash hours_dash">
                    <span class="dash_title">jam</span>
                    <div class="digit">0</div>
                    <div class="digit">0</div>
                  </div>

                  <div class="dash minutes_dash">
                    <span class="dash_title">minit</span>
                    <div class="digit">0</div>
                    <div class="digit">0</div>
                  </div>

                  <div class="dash seconds_dash">
                    <span class="dash_title">saat</span>
                    <div class="digit">0</div>
                    <div class="digit">0</div>
                  </div>
                  <div class="info_message">
                      <a href="#ezws_main" id="hideDiv">Tukar Kawasan </a> 
                  </div>
                  <div class="info_message" id="ezwssetting" style="display: none; ">
                    <?php ezws_locator(); ?>
                    <a href="#ezws_main" id="showDiv">batal </a> 
                  </div>   
                  <?php if(get_option('ezws_credit') == "Yes" || get_option('ezws_credit') == ""): ?>
                  <div class="info_message" >
                    <a href="http://denaihati.com/projek-waktu-solat" target="_blank" title="Projek Waktu Solat"><img src="<?php echo plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) ."/images/dome-th.png"; ?>" width="60" height="22" alt="Projek Waktu Solat" /></a> 
                  </div>    
                  <?php endif;  ?>
                </div>
                <!-- Countdown dashboard end -->
                <script language="javascript" type="text/javascript">
                  
                  jQuery(document).ready(function() {
                    // start countdown
                    $('#countdown_dashboard').countDown({
                      targetDate: {
                        'day':    <?php echo $tdate[0] ?>,
                        'month':  <?php echo $tdate[1] ?>,
                        'year':   <?php echo $tdate[2] ?>,
                        'hour':   <?php echo $nexthour; ?>,
                        'min':    <?php echo $nextmin; ?>,
                        'sec':    0
                      },
                      onComplete: function() { 
                          $('#countdown_dashboard').stopCountDown();  
                          $('#waktusolat').html("Now : <?=$next?> (<?=$nextime?>) <br />Next : <?=$future?> (<?=$futuretime?>) <br />");
                          $('#complete_info_message').slideDown();
                           // $('#countdown_dashboard').fadeOut();
                          setTimeout(function() {
                            $('#complete_info_message').fadeOut("slow");
                          }, 10000);  
                          setTimeout(function() {
                              location.reload();
                          }, 60000); 
                          
                          $('#countdown_dashboard').setCountDown({
                            targetDate: {
                              'day':    <?php echo $tdate2[0] ?>,
                              'month':  <?php echo $tdate2[1] ?>,
                              'year':   <?php echo $tdate2[2] ?>,
                              'hour':   <?php echo $nexthour2; ?>,
                              'min':    <?php echo $nextmin2; ?>,
                              'sec':    0
                            }
                          });       
                          $('#countdown_dashboard').startCountDown();  

                           }
                    });

                  // selection untuk kod kawasan  
                  $("#kodKawasan").change(function() { 
                          $.cookie('kodKawasan', $("#kodKawasan").val(), { expires: 7, path: '/' });
                          setTimeout(function() {
                              location.reload();
                          }, 500); 

                  });  
                  // show hide div tukar kawasan
                  $("a#hideDiv").click(function(){
                    $('div#ezwssetting').show('slow');
                    $("#hideDiv").hide('slow');
                  });
                  $("a#showDiv").click(function(){
                    $('div#ezwssetting').hide('fast');
                    $("#hideDiv").show('fast');
                  });
                    
                  });
                </script>
     </div>  
<?php     
}

function ezws_locator(){
        global $wpdb;
        $title = esc_attr($instance['title']);
        $tableKod = $wpdb->prefix."waktusolatkod2";  
        $kawasan = $wpdb->get_results("SELECT * FROM {$tableKod}",OBJECT);
        ?>
         <p>
          <label for="kawasan"><?php _e('Kawasan:'); ?></label> 
          <select id="kodKawasan" name="kodKawasan" class="widefat" style="width:100%;">
          <?php foreach ($kawasan as $data): ?>
            <option value="<?php echo $data->Kod; ?>" <?php if ( $data->Kod == $_COOKIE["kodKawasan"] ) echo 'selected="selected"'; ?>><?php echo ucfirst(strtolower($data->Nama)); ?></option>
              <?php endforeach; ?>
          </select>
        </p>
<?php
}     


// Waktu Solat Options Panel

$shortname = "ezws";
$pluginname = "Waktu Solat";

$options = array (

                  array( "name" => $pluginname." Options",
                    "type" => "title"),

                  array( "name" => "General",
                    "type" => "section"),
                  array( "type" => "open"),

                  array( "name" => "Colour Scheme",
                    "desc" => "Select the colour scheme for the counter",
                    "id" => $shortname."_color_scheme",
                    "type" => "select",
                    "options" => array("default", "blue", "red", "green"),
                    "std" => "default"),

                  array( "name" => "Background Color",
                    "desc" => "Enter a custom background color",
                    "id" => $shortname."_bg_scheme",
                    "type" => "colorpicker",
                    "std" => "#FFF"),  

                  array( "name" => "Text Align",
                    "desc" => "Choose text align",
                    "id" => $shortname."_textalign",
                    "type" => "select",
                    "options" => array("center", "right", "left"),
                    "std" => "center"),
                  
                  array( "name" => "Enable custom css?",
                    "desc" => "Select if you want to enable custom css",
                    "id" => $shortname."_css_enable",
                    "type" => "select",
                    "options" => array("No", "Yes"),
                    "std" => "No"),    

                  array( "name" => "Custom CSS",
                    "desc" => "Want to add any custom CSS code? Put in here, and the rest is taken care of. This overrides any other stylesheets. eg: a.button{color:green}",
                    "id" => $shortname."_custom_css",
                    "type" => "textarea",
                    "std" => "#wscontainer {
  padding: 10px;  
  width: 100%;
  min-height: 130px;
}

#countdown_dashboard {
  width: 140px;
  margin: 10px auto;
}

.dash {
  width: 35px;
  height: 45px;
  background: transparent url('../images/dash.png') 0 0 no-repeat;
  float: left;
  margin-left: 8px;
  position: relative;
}

.dash .digit {
  font-size: 16pt;
  font-weight: bold;
  float: left;
  width: 17px;
  text-align: center;
  font-family: Times;
  color: #555;
  position: relative;
}

.dash_title {
  position: absolute;
  display: block;
  bottom: 0px;
  right: 0px;
  font-size: 8pt;
  font-weight: bold;
  color: #555;
  text-transform: uppercase;
}

#loading {
  text-align: center;
  margin: 10px;
  display: none;
  position: absolute;
  width: 100%;
  top: 60px;
}

.info_message{
  padding-top:10px;
  padding-left: 5px;
  width: 100%;
  font-size: 8pt;
  font-weight: bold;
  text-align: center;
}"),   
                  array( "name" => "Show plugin credit?",
                    "desc" => "Select if you want to enable plugin credit at the bottom of widget",
                    "id" => $shortname."_credit",
                    "type" => "select",
                    "options" => array("Yes", "No"),
                    "std" => "Yes"),

                  array( "type" => "close")
                  );


function waktusolat_add_admin() {

        global $options;

        if ( $_GET['page'] == basename(__FILE__) ) {

          if ( 'save' == $_REQUEST['action'] ) {

            foreach ($options as $value) { update_option( $value['id'], trim($_REQUEST[ $value['id'] ]) ); 
            }

            foreach ($options as $value) {if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], trim($_REQUEST[ $value['id'] ])  ); } else { delete_option( $value['id'] ); } }

                      header("Location: options-general.php?page=waktusolat.php&saved=true");
                die;

              }
              else if( 'reset' == $_REQUEST['action'] ) {

              foreach ($options as $value) {
                delete_option( $value['id'] ); }

              header("Location: options-general.php?page=waktusolat.php&reset=true");
              die;

              }
        }

add_options_page("Waktu Solat", "Waktu Solat", 'administrator', basename(__FILE__), 'waktusolat_admin');
}

function waktusolat_admin() {

			global $options;
			$i=0;
			
			if ( $_REQUEST['saved'] ) echo '
			<div id="message" class="updated fade">
				<p>
					<strong>Waktu Solat settings saved.</strong>
				</p>
			</div>';
			if ( $_REQUEST['reset'] ) echo '
			<div id="message" class="updated fade">
				<p>
					<strong>Waktu Solat settings reset.</strong>
				</p>
			</div>';
			
			?>
			<div class="wrap rm_wrap">
				<h2>Waktu Solat Settings</h2>
			
				<div class="rm_opts">
					<form method="post">
			
						<?php foreach ($options as $value) {
			switch ( $value['type'] ) {
			
			case "open":
						?>
			
						<?php break;
			
			case "close":
						?>
				</div>
			</div>
			<br />
			
			<?php break;
			
			case "title":
			?>
			<p>Please use the options page below to edit the widget settings.</p>
			
			<?php break;
			
			case 'text':
			?>
			
			<div class="rm_input rm_text">
				<label for="<?php echo $value['id']; ?>">
					<?php echo $value['name'];
					?>
				</label>
				<input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>" />
				<small>
					<?php echo $value['desc'];
					?>
				</small>
				<div class="clearfix">
				</div>
			
			</div>
			<?php
			break;
// tambah case untuk color picker laks... :D senang sket nanti nak guna balik
      case 'colorpicker':
      ?>
      
      <div class="rm_input rm_text">
        <label for="<?php echo $value['id']; ?>">
          <?php echo $value['name'];
          ?>
        </label>
        <input name="<?php echo $value['id']; ?>" id="color_<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>" />
        <small>
          <?php echo $value['desc'];
          ?>
        </small>
        <div id="color_picker_<?php echo $value['id']; ?>" style="display:hidden"></div>
        <div class="clearfix">
        </div>
      <script type="text/javascript">
        jQuery(document).ready(function($){
            $("#color_<?php echo $value['id']; ?>").click(function() { 
                    $('#color_picker_<?php echo $value['id']; ?>').show ('slow');
                    $('#color_picker_<?php echo $value['id']; ?>').farbtastic('#color_<?php echo $value['id']; ?>');            
                  });  
        });
    </script>
      </div>
      <?php
      break;
			
			case 'textarea':
			?>
			
			<div class="rm_input rm_textarea">
				<label for="<?php echo $value['id']; ?>">
					<?php echo $value['name'];
					?>
				</label>
				<textarea name="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( get_settings( $value['id'] ) != "") { echo trim(get_settings( $value['id'])); } else { echo trim($value['std']); }	?></textarea>
				<small>
					<?php echo $value['desc'];
					?>
				</small>
				<div class="clearfix">
				</div>
			
			</div>
			
			<?php
			break;
			
			case 'select':
			?>
			
			<div class="rm_input rm_select">
				<label for="<?php echo $value['id']; ?>">
					<?php echo $value['name'];
					?>
				</label>
			
				<select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
					<?php foreach ($value['options'] as $option) {
					?>
					<option <?php if (get_settings( $value['id'] ) == $option) { echo 'selected="selected"'; } ?>
						><?php echo $option;
						?>
					</option><?php }
					?>
				</select>
			
				<small>
					<?php echo $value['desc'];
					?>
				</small>
				<div class="clearfix">
				</div>
			</div>
			<?php
			break;
			
			case "checkbox":
			?>
			
			<div class="rm_input rm_checkbox">
				<label for="<?php echo $value['id']; ?>">
					<?php echo $value['name'];
					?>
				</label>
			
				<?php if(get_option($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = "";}
				?>
				<input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?>
				/>
			
				<small>
					<?php echo $value['desc'];
					?>
				</small>
				<div class="clearfix">
				</div>
			</div>
			<?php break;
			
			case "section":

			$i++;
			
			?>
<div class="rm_section">
	<div class="rm_title">
		<h3><?php echo $value['name']; ?></h3><span class="submit"><input name="save<?php echo $i; ?>" type="submit" value="Save changes" />
		</span><div class="clearfix"></div></div>
		<div class="rm_options">

		<?php break;

		}
		}
		?>

		<input type="hidden" name="action" value="save" />
		</form>
		<form method="post">
			<p class="submit">
				<input name="reset" type="submit" value="Reset" />
				<input type="hidden" name="action" value="reset" />
			</p>
		</form>
		</div>

		<?php
		}

add_action('admin_init', 'waktusolat_add_init');
add_action('admin_menu', 'waktusolat_add_admin');

function waktusolat_add_init() {
      $plgDir = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ); 
      wp_enqueue_style("functions", $plgDir."/style/admin.css", false, "1.0", "all");
      wp_enqueue_style( 'farbtastic' );
      wp_enqueue_script( 'farbtastic' );

      
    }

?>