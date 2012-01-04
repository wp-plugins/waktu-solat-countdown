<?php

/*
Plugin Name: Waktu Solat Countdown
Plugin URI: http://denaihati.com/projek-waktu-solat
Description: Prayer Time Plugin with countdown, a collaboration project with <a href="http://denaihati.com/projek-waktu-solat">Denaihati Network</a>.
Version: 2.0.5
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

function waktusolat_init() {
    load_plugin_textdomain('wpwsc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action('init', 'waktusolat_init');


include "Hijri_GregorianConvert.class"; // class untuk convert gregorian ke hijri
include('PrayTime.php');                // prayer time calculation


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
                  <?php if(isset($_COOKIE["latitude"]) && isset($_COOKIE["longitude"]) && $_COOKIE["latitude"] != "" && $_COOKIE["longitude"] != ""):
                              waktuSolatMain($_COOKIE['latitude'], $_COOKIE['longitude'], $instance['timezone'], $instance['calcmethod']);
                        else:      
                              waktuSolatMain($instance['latitude'], $instance['longitude'], $instance['timezone'], $instance['calcmethod']);
                        endif;      
                  ?>  
             
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['latitude'] = $new_instance['latitude'];
        $instance['longitude'] = $new_instance['longitude'];
        $instance['timezone'] = $new_instance['timezone'];
        $instance['calcmethod'] = $new_instance['calcmethod'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        global $wpdb;
        $title = esc_attr($instance['title']);
        $latitude = esc_attr($instance['latitude']);
        $longitude = esc_attr($instance['longitude']);
        $timezone = esc_attr($instance['timezone']);
        $calcmethod = esc_attr($instance['calcmethod']);
        $tableKod = $wpdb->prefix."waktusolatkod2";  
        $timezones = array(
                'Pacific/Midway'    => "(GMT-11:00) Midway Island",
                'US/Samoa'          => "(GMT-11:00) Samoa",
                'US/Hawaii'         => "(GMT-10:00) Hawaii",
                'US/Alaska'         => "(GMT-09:00) Alaska",
                'US/Pacific'        => "(GMT-08:00) Pacific Time (US &amp; Canada)",
                'America/Tijuana'   => "(GMT-08:00) Tijuana",
                'US/Arizona'        => "(GMT-07:00) Arizona",
                'US/Mountain'       => "(GMT-07:00) Mountain Time (US &amp; Canada)",
                'America/Chihuahua' => "(GMT-07:00) Chihuahua",
                'America/Mazatlan'  => "(GMT-07:00) Mazatlan",
                'America/Mexico_City' => "(GMT-06:00) Mexico City",
                'America/Monterrey' => "(GMT-06:00) Monterrey",
                'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
                'US/Central'        => "(GMT-06:00) Central Time (US &amp; Canada)",
                'US/Eastern'        => "(GMT-05:00) Eastern Time (US &amp; Canada)",
                'US/East-Indiana'   => "(GMT-05:00) Indiana (East)",
                'America/Bogota'    => "(GMT-05:00) Bogota",
                'America/Lima'      => "(GMT-05:00) Lima",
                'America/Caracas'   => "(GMT-04:30) Caracas",
                'Canada/Atlantic'   => "(GMT-04:00) Atlantic Time (Canada)",
                'America/La_Paz'    => "(GMT-04:00) La Paz",
                'America/Santiago'  => "(GMT-04:00) Santiago",
                'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
                'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
                'Greenland'         => "(GMT-03:00) Greenland",
                'Atlantic/Stanley'  => "(GMT-02:00) Stanley",
                'Atlantic/Azores'   => "(GMT-01:00) Azores",
                'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
                'Africa/Casablanca' => "(GMT) Casablanca",
                'Europe/Dublin'     => "(GMT) Dublin",
                'Europe/Lisbon'     => "(GMT) Lisbon",
                'Europe/London'     => "(GMT) London",
                'Africa/Monrovia'   => "(GMT) Monrovia",
                'Europe/Amsterdam'  => "(GMT+01:00) Amsterdam",
                'Europe/Belgrade'   => "(GMT+01:00) Belgrade",
                'Europe/Berlin'     => "(GMT+01:00) Berlin",
                'Europe/Bratislava' => "(GMT+01:00) Bratislava",
                'Europe/Brussels'   => "(GMT+01:00) Brussels",
                'Europe/Budapest'   => "(GMT+01:00) Budapest",
                'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
                'Europe/Ljubljana'  => "(GMT+01:00) Ljubljana",
                'Europe/Madrid'     => "(GMT+01:00) Madrid",
                'Europe/Paris'      => "(GMT+01:00) Paris",
                'Europe/Prague'     => "(GMT+01:00) Prague",
                'Europe/Rome'       => "(GMT+01:00) Rome",
                'Europe/Sarajevo'   => "(GMT+01:00) Sarajevo",
                'Europe/Skopje'     => "(GMT+01:00) Skopje",
                'Europe/Stockholm'  => "(GMT+01:00) Stockholm",
                'Europe/Vienna'     => "(GMT+01:00) Vienna",
                'Europe/Warsaw'     => "(GMT+01:00) Warsaw",
                'Europe/Zagreb'     => "(GMT+01:00) Zagreb",
                'Europe/Athens'     => "(GMT+02:00) Athens",
                'Europe/Bucharest'  => "(GMT+02:00) Bucharest",
                'Africa/Cairo'      => "(GMT+02:00) Cairo",
                'Africa/Harare'     => "(GMT+02:00) Harare",
                'Europe/Helsinki'   => "(GMT+02:00) Helsinki",
                'Europe/Istanbul'   => "(GMT+02:00) Istanbul",
                'Asia/Jerusalem'    => "(GMT+02:00) Jerusalem",
                'Europe/Kiev'       => "(GMT+02:00) Kyiv",
                'Europe/Minsk'      => "(GMT+02:00) Minsk",
                'Europe/Riga'       => "(GMT+02:00) Riga",
                'Europe/Sofia'      => "(GMT+02:00) Sofia",
                'Europe/Tallinn'    => "(GMT+02:00) Tallinn",
                'Europe/Vilnius'    => "(GMT+02:00) Vilnius",
                'Asia/Baghdad'      => "(GMT+03:00) Baghdad",
                'Asia/Kuwait'       => "(GMT+03:00) Kuwait",
                'Europe/Moscow'     => "(GMT+03:00) Moscow",
                'Africa/Nairobi'    => "(GMT+03:00) Nairobi",
                'Asia/Riyadh'       => "(GMT+03:00) Riyadh",
                'Europe/Volgograd'  => "(GMT+03:00) Volgograd",
                'Asia/Tehran'       => "(GMT+03:30) Tehran",
                'Asia/Baku'         => "(GMT+04:00) Baku",
                'Asia/Muscat'       => "(GMT+04:00) Muscat",
                'Asia/Tbilisi'      => "(GMT+04:00) Tbilisi",
                'Asia/Yerevan'      => "(GMT+04:00) Yerevan",
                'Asia/Kabul'        => "(GMT+04:30) Kabul",
                'Asia/Yekaterinburg' => "(GMT+05:00) Ekaterinburg",
                'Asia/Karachi'      => "(GMT+05:00) Karachi",
                'Asia/Tashkent'     => "(GMT+05:00) Tashkent",
                'Asia/Kolkata'      => "(GMT+05:30) Kolkata",
                'Asia/Kathmandu'    => "(GMT+05:45) Kathmandu",
                'Asia/Almaty'       => "(GMT+06:00) Almaty",
                'Asia/Dhaka'        => "(GMT+06:00) Dhaka",
                'Asia/Novosibirsk'  => "(GMT+06:00) Novosibirsk",
                'Asia/Bangkok'      => "(GMT+07:00) Bangkok",
                'Asia/Jakarta'      => "(GMT+07:00) Jakarta",
                'Asia/Krasnoyarsk'  => "(GMT+07:00) Krasnoyarsk",
                'Asia/Chongqing'    => "(GMT+08:00) Chongqing",
                'Asia/Hong_Kong'    => "(GMT+08:00) Hong Kong",
                'Asia/Irkutsk'      => "(GMT+08:00) Irkutsk",
                'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
                'Australia/Perth'   => "(GMT+08:00) Perth",
                'Asia/Singapore'    => "(GMT+08:00) Singapore",
                'Asia/Taipei'       => "(GMT+08:00) Taipei",
                'Asia/Ulaanbaatar'  => "(GMT+08:00) Ulaan Bataar",
                'Asia/Urumqi'       => "(GMT+08:00) Urumqi",
                'Asia/Seoul'        => "(GMT+09:00) Seoul",
                'Asia/Tokyo'        => "(GMT+09:00) Tokyo",
                'Asia/Yakutsk'      => "(GMT+09:00) Yakutsk",
                'Australia/Adelaide' => "(GMT+09:30) Adelaide",
                'Australia/Darwin'  => "(GMT+09:30) Darwin",
                'Australia/Brisbane' => "(GMT+10:00) Brisbane",
                'Australia/Canberra' => "(GMT+10:00) Canberra",
                'Pacific/Guam'      => "(GMT+10:00) Guam",
                'Australia/Hobart'  => "(GMT+10:00) Hobart",
                'Australia/Melbourne' => "(GMT+10:00) Melbourne",
                'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
                'Australia/Sydney'  => "(GMT+10:00) Sydney",
                'Asia/Vladivostok'  => "(GMT+10:00) Vladivostok",
                'Asia/Magadan'      => "(GMT+11:00) Magadan",
                'Pacific/Auckland'  => "(GMT+12:00) Auckland",
                'Pacific/Fiji'      => "(GMT+12:00) Fiji",
                'Asia/Kamchatka'    => "(GMT+12:00) Kamchatka",
            );   
         $calcmethods = array(      
                              '0'   => "Ithna Ashari",
                              '1'   => "University of Islamic Sciences, Karachi",
                              '2'    => "Islamic Society of North America (ISNA)",
                              '3'    => "Muslim World League (MWL)",
                              '4'    => "Umm al-Qura, Makkah",
                              '5'    => "Egyptian General Authority of Survey",
                              '7'    => "Institute of Geophysics, University of Tehran",
            );
        echo '
         <p>
          <label for="'.$this->get_field_id('title').'">'. __('Title', 'wpwsc').':</label> 
          <input class="widefat" id="'. $this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'. $title.'" />
        </p>
        <p>
          <label for="'.$this->get_field_id( 'latitude' ).'">'. __('Latitude', 'wpwsc').':</label> 
          <input class="widefat" id="'. $this->get_field_id('latitude').'" name="'. $this->get_field_name('latitude').'" type="text" value="'. $latitude.'" />
        </p>
        <p>
          <label for="'.$this->get_field_id( 'longitude' ).'">'. __('Longitude', 'wpwsc').':</label> 
          <input class="widefat" id="'. $this->get_field_id('longitude').'" name="'. $this->get_field_name('longitude').'" type="text" value="'. $longitude.'" />
        </p>
        <p>
          <label for="'.$this->get_field_id( 'timezone' ).'">'. __('Timezone', 'wpwsc').':</label> 
          <select class="widefat" id="'. $this->get_field_id('timezone').'" name="'. $this->get_field_name('timezone').'"/>';
          foreach($timezones as $val => $nam):
            if($val == $timezone):
               $selected = 'selected="selected"';
            else:
               $selected = "";
            endif;   
            echo '<option value="'.$val.'" '.$selected.' >'.$nam.'</option>';
          endforeach;
        echo '</select></p>
        <p>
          <label for="'.$this->get_field_id( 'calcmethod' ).'">'. __('Calculation Method', 'wpwsc').':</label> 
          <select class="widefat" id="'. $this->get_field_id('calcmethod').'" name="'. $this->get_field_name('calcmethod').'"/>';
          foreach($calcmethods as $valc => $namc):
            if($valc == $calcmethod):
               $selc = 'selected="selected"';
            else:
               $selc = "";
            endif;   
            echo '<option value="'.$valc.'" '.$selc.' >'.$namc.'</option>';
          endforeach;
        echo '</select></p>';


         


        
    }

} // class WaktuSolatWidget

add_action('widgets_init', create_function('', 'return register_widget("WaktuSolatWidget");'));

// enqueue additional script untuk countdown
function waktuSolatMethod() {

       wp_deregister_script( 'jquery' );
       wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
       wp_enqueue_script( 'jquery' );
       wp_enqueue_script('newscript3', plugins_url('/js/jquery.cookies.js', __FILE__), array('jquery'), '1.0', false);

       if(get_option('ezws_color_scheme') != ""):
            $color = get_option('ezws_color_scheme');
       else:
            $color = "default";
       endif;     
       wp_register_style('waktusolat', plugins_url('/style/main_'.$color.'.css', __FILE__), false, '2.0.4'); 
       wp_enqueue_style('waktusolat');

}    

add_action('wp_enqueue_scripts', 'waktuSolatMethod');

function waktuSolatMain($lat, $long, $timezone, $calc){

        if($timezone == ""):
            $timez0n3 = "Asia/Kuala_Lumpur ";
        else:
            $timez0n3 = $timezone;
        endif;

        date_default_timezone_set($timez0n3);
    
        $today = date("d-m-Y", strtotime('today'));                 // tarikh harini.
        $yesterday = date("d-m-Y", strtotime('yesterday'));         // tarikh semalam
        $tomorrow = date("d-m-Y", strtotime('tomorrow'));           // tarikh esok
        $time = strtotime(date("d-m-Y G:i:s"));                     // time sekarang untuk comparison

        $tdate = explode ("-", $today);
        $tdate2 = explode ("-", $today);

        //create date conversion.
        $DateConv=new Hijri_GregorianConvert; 
        $hijri = $DateConv->GregorianToHijri(date("Y/m/d"),"YYYY/MM/DD");

        // create query untuk data dari class.  
        if($calc == ""):
            $calc = "5";
        endif;

        $prayTime = new PrayTime($calc);

        $todayTime = strtotime('today');                // tarikh harini.
        $yesterdayTime = strtotime('yesterday');         // tarikh semalam
        $tomorrowTime = strtotime('tomorrow');           // tarikh esok

        if($lat == ""):
            $lat = "3.13333";
        endif;

        if($long == ""):
            $long = "101.7";
        endif;

        $latitude = $lat;          //3.13333;101.7 KL
        $longitude = $long;

        $timezones = array(
                'Pacific/Midway'    => "-11",
                'US/Samoa'          => "-11",
                'US/Hawaii'         => "-10",
                'US/Alaska'         => "-9",
                'US/Pacific'        => "-8",
                'America/Tijuana'   => "-8",
                'US/Arizona'        => "-7",
                'US/Mountain'       => "-7",
                'America/Chihuahua' => "-7",
                'America/Mazatlan'  => "07",
                'America/Mexico_City' => "-6",
                'America/Monterrey' => "-6",
                'Canada/Saskatchewan' => "-6",
                'US/Central'        => "-6",
                'US/Eastern'        => "-5",
                'US/East-Indiana'   => "-5",
                'America/Bogota'    => "-5",
                'America/Lima'      => "-5",
                'America/Caracas'   => "-4:30",
                'Canada/Atlantic'   => "-4",
                'America/La_Paz'    => "-4",
                'America/Santiago'  => "-4",
                'Canada/Newfoundland'  => "-3:30",
                'America/Buenos_Aires' => "-3",
                'Greenland'         => "-3",
                'Atlantic/Stanley'  => "-2",
                'Atlantic/Azores'   => "-1",
                'Atlantic/Cape_Verde' => "-1",
                'Africa/Casablanca' => "0",
                'Europe/Dublin'     => "0",
                'Europe/Lisbon'     => "0",
                'Europe/London'     => "0",
                'Africa/Monrovia'   => "0",
                'Europe/Amsterdam'  => "+1",
                'Europe/Belgrade'   => "+1",
                'Europe/Berlin'     => "+1",
                'Europe/Bratislava' => "+1",
                'Europe/Brussels'   => "+1",
                'Europe/Budapest'   => "+1",
                'Europe/Copenhagen' => "+1",
                'Europe/Ljubljana'  => "+1",
                'Europe/Madrid'     => "+1",
                'Europe/Paris'      => "+1",
                'Europe/Prague'     => "+1",
                'Europe/Rome'       => "+1",
                'Europe/Sarajevo'   => "+1",
                'Europe/Skopje'     => "+1",
                'Europe/Stockholm'  => "+1",
                'Europe/Vienna'     => "+1",
                'Europe/Warsaw'     => "+1",
                'Europe/Zagreb'     => "+1",
                'Europe/Athens'     => "+2",
                'Europe/Bucharest'  => "+2",
                'Africa/Cairo'      => "+2",
                'Africa/Harare'     => "+2",
                'Europe/Helsinki'   => "+2",
                'Europe/Istanbul'   => "+2",
                'Asia/Jerusalem'    => "+2",
                'Europe/Kiev'       => "+2",
                'Europe/Minsk'      => "+2",
                'Europe/Riga'       => "+2",
                'Europe/Sofia'      => "+2",
                'Europe/Tallinn'    => "+2",
                'Europe/Vilnius'    => "+2",
                'Asia/Baghdad'      => "+3",
                'Asia/Kuwait'       => "+3",
                'Europe/Moscow'     => "+3",
                'Africa/Nairobi'    => "+3",
                'Asia/Riyadh'       => "+3",
                'Europe/Volgograd'  => "+3",
                'Asia/Tehran'       => "+3:30",
                'Asia/Baku'         => "+4",
                'Asia/Muscat'       => "+4",
                'Asia/Tbilisi'      => "+4",
                'Asia/Yerevan'      => "+4",
                'Asia/Kabul'        => "+4:30",
                'Asia/Yekaterinburg' => "+5",
                'Asia/Karachi'      => "+5",
                'Asia/Tashkent'     => "+5",
                'Asia/Kolkata'      => "+5:30",
                'Asia/Kathmandu'    => "+5:45",
                'Asia/Almaty'       => "+6",
                'Asia/Dhaka'        => "+6",
                'Asia/Novosibirsk'  => "+6",
                'Asia/Bangkok'      => "+7",
                'Asia/Jakarta'      => "+7",
                'Asia/Krasnoyarsk'  => "+7",
                'Asia/Chongqing'    => "+8",
                'Asia/Hong_Kong'    => "+8",
                'Asia/Irkutsk'      => "+8",
                'Asia/Kuala_Lumpur' => "+8",
                'Australia/Perth'   => "+8",
                'Asia/Singapore'    => "+8",
                'Asia/Taipei'       => "+8",
                'Asia/Ulaanbaatar'  => "+8",
                'Asia/Urumqi'       => "+8",
                'Asia/Seoul'        => "+9",
                'Asia/Tokyo'        => "+9",
                'Asia/Yakutsk'      => "+9",
                'Australia/Adelaide' => "+9:30",
                'Australia/Darwin'  => "+9:30",
                'Australia/Brisbane' => "+10",
                'Australia/Canberra' => "+10",
                'Pacific/Guam'      => "+10",
                'Australia/Hobart'  => "+10",
                'Australia/Melbourne' => "+10",
                'Pacific/Port_Moresby' => "+10",
                'Australia/Sydney'  => "+10",
                'Asia/Vladivostok'  => "+10",
                'Asia/Magadan'      => "+11",
                'Pacific/Auckland'  => "+12",
                'Pacific/Fiji'      => "+12",
                'Asia/Kamchatka'    => "+12",
            );    
        
        $timeZone = "+8";

        foreach($timezones as $val => $tc0de):
            
            if($val == $timezone):
                  $timeZone = $tc0de;
            break;                  
            endif;
        
        endforeach;   

        $dataYes = $prayTime->getPrayerTimes($yesterdayTime, $latitude, $longitude, $timeZone);
        $dataTod = $prayTime->getPrayerTimes($todayTime, $latitude, $longitude, $timeZone);
        $dataTmr = $prayTime->getPrayerTimes($tomorrowTime, $latitude, $longitude, $timeZone);

        // check kalau waktu skang belum kul 23:50 tapi dah lebih waktu isyak
        if(date("G:i:s") < "23:59" && strtotime(date("G:i:s")) > strtotime($dataTmr[0]) ):
                // kalau data skang Isyak dan lebih waktu isyak, create query baru.
                if( $time > strtotime($dataTod[6])):
                  // dapatkan nama ngan waktu isyak harini
                  $nowtime = $dataTod[6];
                  $now = __('Isha', 'wpwsc');

                  // dapatkan nama ngan waktu subuh esok
                  $futuretime = $dataTmr[1];

                  //asingkan jam dan minit untuk imsak esok
                  $t     = explode(":", $dataTmr[0]);
                  $nexthour = $t[0];
                  $nextmin  = $t[1];
                  $next = __('Imsak', 'wpwsc');
                  // dapatkan nama ngan waktu imsak esok
                  if($nextmin > 10):
                      $nextime = $nexthour .":". ($nextmin - 10);
                  else:
                      $nextime = ($nexthour - 1) .":". ($nextmin + 50);    
                  endif;

                  //asingkan jam dan minit untuk subuh esok
                  $t2     = explode(":", $dataTmr[0]);
                  $nexthour2 = $t2[0];
                  $nextmin2  = $t2[1];
                  // tukar tarikh jadi esok. 
                  $tdate = explode ("-", $tomorrow);
                  $tdate2 = explode ("-", $tomorrow);
                endif;  
        // kalau dah lebih kul 12 malam...         
        elseif(date("G:i:s") > "0:00"):
                // check data adalah Isyak, dan time sekarang belum masuk waktu imsak.
                if( $time < strtotime($dataTod[0]) ): 
                  // dapatkan nama ngan waktu isyak semalam
                  $nowtime = $dataYes[6];  
                  $now = __('Isha', 'wpwsc');
                   
                  // dapatkan nama ngan waktu imsak harini  
                  // asingkan jam dan minit dari data 
                  $t     = explode(":", $dataTod[0]);
                  $nexthour = $t[0];          
                  $nextmin  = $t[1];
                  if($nextmin > 10):
                      $nextime = $nexthour .":". ($nextmin - 10);
                  else:
                      $nextime = ($nexthour - 1) .":". ($nextmin + 50);    
                  endif;
                  $next = __('Imsak', 'wpwsc');
                  
                  // dapatkan nama ngan waktu subuh harini  
                  $futuretime = $dataTod[1];

                  // asingkan jam dan minit dari data
                  $t2     = explode(":", $dataTod[0]);
                  $nexthour2 = $t2[0];
                  $nextmin2  = $t2[1];

                  // kekalkan tarikh untuk atas bawah harini. 
                  $tdate = explode ("-", $today);
                  $tdate2 = explode ("-", $today);
                endif;


        endif;
        
        // Imsak ke Subuh
        if ($time < strtotime($dataTod[0]) && $time > strtotime($dataTod[0])-600):

            // dapatkan nama dan waktu solat
            
            // dapatkan nama dan waktu solat seterusnya
            $nextime = $dataTod[0];
            // dapatkan nama dan waktu solat selepas yang skangni
            $futuretime = $dataTod[1];

            // asingkan jam dan minit untuk waktu solat seterusnya
            $t     = explode(":", $dataTod[0]);
            $nexthour = $t[0];
            $nextmin  = $t[1];
            if($nextmin > 10):
              $nowtime = $nexthour .":". ($nextmin - 10);
            else:
              $nowtime = ($nexthour - 1) .":". ($nextmin + 50);    
            endif;
            $now = __('Imsak', 'wpwsc');

            // asingkan jam dan minit untuk waktu solat selepas yang skangni
            $t2     = explode(":", $dataTod[1]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1];
            $next = __('Fajr', 'wpwsc');
        endif;
        
        // Subuh ke Syuruk
        if ($time < strtotime($dataTod[1]) && $time > strtotime($dataTod[0])):

            // dapatkan nama dan waktu solat
            $nowtime = $dataTod[0];
            $now = __('Fajr', 'wpwsc');
            // dapatkan nama dan waktu solat seterusnya
            $nextime = $dataTod[1];
            $next = __('Sunrise', 'wpwsc');
            // dapatkan nama dan waktu solat selepas yang skangni
            $futuretime = $dataTod[2];

            // asingkan jam dan minit untuk waktu solat seterusnya
            $t     = explode(":", $dataTod[1]);
            $nexthour = $t[0];
            $nextmin  = $t[1];

            // asingkan jam dan minit untuk waktu solat selepas yang skangni
            $t2     = explode(":", $dataTod[2]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1];
        endif;
        
        // Syuruk ke Zuhur  
        if ($time < strtotime($dataTod[2]) && $time > strtotime($dataTod[1])):

            // dapatkan nama dan waktu solat
            $nowtime = $dataTod[1];
            $now = __('Sunrise', 'wpwsc');
            // dapatkan nama dan waktu solat seterusnya
            $nextime = $dataTod[2];
            $next = __('Dhuhr', 'wpwsc');
            // dapatkan nama dan waktu solat selepas yang skangni
            $futuretime = $dataTod[3];

            // asingkan jam dan minit untuk waktu solat seterusnya
            $t     = explode(":", $dataTod[2]);
            $nexthour = $t[0];
            $nextmin  = $t[1];

            // asingkan jam dan minit untuk waktu solat selepas yang skangni
            $t2     = explode(":", $dataTod[3]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1];
        endif;

        // Zuhur ke Asar  
        if ($time < strtotime($dataTod[3]) && $time > strtotime($dataTod[2])):

            // dapatkan nama dan waktu solat
            $nowtime = $dataTod[2];
            $now = __('Dhuhr', 'wpwsc');
            // dapatkan nama dan waktu solat seterusnya
            $nextime = $dataTod[3];
            $next = __('Asr', 'wpwsc');
            // dapatkan nama dan waktu solat selepas yang skangni
            $futuretime = $dataTod[5];

            // asingkan jam dan minit untuk waktu solat seterusnya
            $t     = explode(":", $dataTod[3]);
            $nexthour = $t[0];
            $nextmin  = $t[1];

            // asingkan jam dan minit untuk waktu solat selepas yang skangni
            $t2     = explode(":", $dataTod[5]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1];
        endif;

        // Asar ke Maghrib  
        if ($time < strtotime($dataTod[5]) && $time > strtotime($dataTod[3])):

            // dapatkan nama dan waktu solat
            $nowtime = $dataTod[3];
            $now = __('Asr', 'wpwsc');
            // dapatkan nama dan waktu solat seterusnya
            $nextime = $dataTod[5];
            $next = __('Maghrib', 'wpwsc');
            // dapatkan nama dan waktu solat selepas yang skangni
            $futuretime = $dataTod[6];

            // asingkan jam dan minit untuk waktu solat seterusnya
            $t     = explode(":", $dataTod[5]);
            $nexthour = $t[0];
            $nextmin  = $t[1];

            // asingkan jam dan minit untuk waktu solat selepas yang skangni
            $t2     = explode(":", $dataTod[6]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1];
        endif;

        // Maghrib ke isyak
        if ($time < strtotime($dataTod[6]) && $time > strtotime($dataTod[4])):
            // dapatkan nama dan waktu maghrib harini
            $nowtime = $dataTod[5];
            $now = __('Maghrib', 'wpwsc');
            // dapatkan nama dan waktu isyak harini
            $nextime = $dataTod[6];
            $next = __('Isha', 'wpwsc');

            // asingkan jam dan minit untuk waktu isyak harini
            $t     = explode(":", $dataTod[6]);
            $nexthour = $t[0];
            $nextmin  = $t[1];

            

            // asingkan jam dan minit untuk waktu imsak esok
            $t2     = explode(":", $dataTmr[0]);
            $nexthour2 = $t2[0];
            $nextmin2  = $t2[1]+10;
            // dapatkan nama dan waktu imsak esok
            $futuretime = $nexthour2 .":".$nextmin2 ;

            // asingkan tarikh untuk next kepada harini, dan future kepada esok
            $tdate = explode ("-", $today);
            $tdate2 = explode ("-", $tomorrow);
        
        endif;
$counter = "default";
if(date('l', $todayTime) == "Sunday"):
        $harijawi = __('احد', 'wpwsc');
   elseif(date('l', $todayTime) == "Monday"):
        $harijawi = __('اثنين', 'wpwsc');
   elseif(date('l', $todayTime) == "Tuesday"):
        $harijawi = __('ثلاث', 'wpwsc');
   elseif(date('l', $todayTime) == "Wednesday"):
        $harijawi = __('رابو', 'wpwsc');
   elseif(date('l', $todayTime) == "Thursday"):
        $harijawi = __('خميس', 'wpwsc');  
   elseif(date('l', $todayTime) == "Friday"):
        $harijawi = __('جمعة', 'wpwsc');
   elseif(date('l', $todayTime) == "Saturday"):
        $harijawi = __('سبتو', 'wpwsc');
   endif;
?>

<div id="wscontainer" <?php if(get_option('ezws_bg_enable') == "Yes"): echo 'style="background-repeat: '.get_option('ezws_bg_repeat').';  background-image: url('.get_option('ezws_bg_image').');"';
                            elseif(get_option('ezws_bg_scheme')!= "" && get_option('ezws_bg_enable') == "No"): echo 'style="background-color:'.get_option('ezws_bg_scheme').'"'; 
                            endif; 
                      ?> >
                <div class="info_message" id="ezws_main" <?php if(get_option('ezws_textalign')!= ""): echo 'style="text-align:'.get_option('ezws_textalign').'"'; endif; ?>>
                    
                    <span style="font-size: 20px"><?php echo $harijawi; ?></span><br />
                    <?php echo $hijri; ?> <br />
                     <span id="waktusolat">
                    <?php echo __('Now','wpwsc'); ?> : <?=$now?> (<?=$nowtime?>) <br />
                    <?php echo __('Next','wpwsc'); ?> : <?=$next?> (<?=$nextime?>) <br /></span>
                  </div>
                <div id="countbox"></div>
                <!-- Countdown dashboard start -->
                  <div class="info_message">
                      <a href="#ezws_main" id="hideDiv"><?php echo __('Change location', 'wpwsc'); ?> </a> 
                  </div>
                  <div class="info_message" id="ezwssetting" style="display: none; ">
                    <button id="waktuSolatGetLocation" onclick="getLocation();" ><?php echo __('Get Location', 'wpwsc'); ?></button>
                    <button id="waktuSolatClearLocation" onclick="clearLocation();" ><?php echo __('Clear Location', 'wpwsc'); ?></button>
                    <button id="showDiv"><?php echo __('Cancel', 'wpwsc'); ?></button> 
                  </div>
                  <?php if(get_option('ezws_credit') == "Yes"): ?>
                  <div class="info_message" >
                    <a href="http://denaihati.com/projek-waktu-solat" ><img src="<?php echo plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) ."/images/dome-th.png"; ?>" width="60" height="22" alt="<?php echo __('Projek Waktu Solat', 'wpwsc'); ?>" /></a> 
                  </div>    
                  <?php endif;  ?>
                <!-- Countdown dashboard end -->
                <script language="javascript" type="text/javascript">
                //geolocator
                    function showLocation(position) {
                      var latitude = position.coords.latitude;
                      var longitude = position.coords.longitude;
                      jQuery.cookie('latitude', latitude, { expires: 7, path: '/' });
                      jQuery.cookie('longitude', longitude, { expires: 7, path: '/' });

                      var answer = confirm("<?php echo __('Use this location?', 'wpwsc'); ?> <?php echo __('Latitude', 'wpwsc'); ?> : " + latitude + " <?php echo __('Longitude', 'wpwsc'); ?> : " + longitude);
                      if (answer){
                        window.location.reload(true);
                      }
                    }

                    function errorHandler(err) {
                      if(err.code == 1) {
                        alert("<?php echo __('Error: Access is denied!', 'wpwsc'); ?>");
                      }else if( err.code == 2) {
                        alert("<?php echo __('Error: Position is unavailable!', 'wpwsc'); ?>");
                      }
                    }
                    function getLocation(){

                       if(navigator.geolocation){
                          // timeout at 60000 milliseconds (60 seconds)
                          var options = {timeout:60000};
                          navigator.geolocation.getCurrentPosition(showLocation, 
                                                                   errorHandler,
                                                                   options);
                       }else{
                          alert("<?php echo __('Sorry, browser does not support geolocation!', 'wpwsc'); ?>");
                       }
                    }

                    function clearLocation(){

                      jQuery.cookie('latitude', "", { expires: 7, path: '/' });
                      jQuery.cookie('longitude', "", { expires: 7, path: '/' });

                      var answer = confirm("<?php echo __('Clear location?', 'wpwsc'); ?>");
                      if (answer){
                        window.location.reload(true);
                      }
                    }

                    
                  
                  jQuery(document).ready(function(ez) {
                  

                  // show hide div tukar kawasan
                  ez("a#hideDiv").click(function(){
                    ez('div#ezwssetting').show('slow');
                    ez("#hideDiv").hide('slow');
                  });
                  ez("#showDiv").click(function(){
                    ez('div#ezwssetting').hide('fast');
                    ez("#hideDiv").show('fast');
                  });

                                    
                  });

                  // counter baru
                  (function() {
                    jQuery(document).ready(function() {GetCounter();});

                    function GetCounter() { 
                      var  dateFuture = new Date(<?php echo $tdate[2] ?>,<?php echo $tdate[1] ?>,<?php echo $tdate[0] ?>,<?php echo $nexthour; ?>,<?php echo $nextmin; ?>,0);
                        var dateNow = new Date();                                                            
                        var amount = dateFuture.getTime() - dateNow.getTime();               
                        delete dateNow;
                        /* time is already past */
                        if(amount < 0){
                                out=  "Refreshing countdown in <br />" + 
                                      "<div id='hours'><span></span>0<div id='hours_text'></div></div>" + 
                                      "<div id='mins'><span></span>0<div id='mins_text'></div></div>" + 
                                      "<div id='secs'><span></span>10<div id='secs_text'></div></div>" ;
                                document.getElementById('countbox').innerHTML=out;    
                                location.reload();
                        }
                        /* date is still good */
                        else{
                                days=0;hours=0;mins=0;secs=0;out="";
                                amount = Math.floor(amount/1000); /* kill the milliseconds */
                                days=Math.floor(amount/86400); /* days */
                                amount=amount%86400;
                                hours=Math.floor(amount/3600); /* hours */
                                amount=amount%3600;
                                mins=Math.floor(amount/60); /* minutes */
                                amount=amount%60;
                                secs=Math.floor(amount); /* seconds */
                                out=  "<div id='hours'><dl>" + ('0' + hours).slice(-2) +"</dl><span></span></div><div id='mins'><dl>" +('0' + mins).slice(-2)  +"</dl><span></span></div><div id='secs'><dl>" + ('0' + secs).slice(-2) +"</dl><span></span></div>" + 
                                      "<div style='clear:both'><div id='hours_text'><?php echo __('Hour', 'wpwsc'); ?></div><div id='mins_text'><?php echo __('Minute', 'wpwsc'); ?></div><div id='secs_text'><?php echo __('Second', 'wpwsc'); ?></div></div>";

                                document.getElementById('countbox').innerHTML=out;
                        }
                      setTimeout(GetCounter, 1000);     }
                    }
                  )();  
                  
                </script>
     </div>  
<?php     
}


// Waktu Solat Options Panel

$shortname = "ezws";
$pluginname = "Waktu Solat";

$ezwsoptions = array (

                  array( "name" => $pluginname." Options",
                    "type" => "title"),

                  array( "name" => "General",
                    "type" => "section"),
                  array( "type" => "open"),

                  array( "name" => __('Show Hijri date?','wpwsc'),
                    "desc" => __('Select if you want to show hijri date','wpwsc'),
                    "id" => $shortname."_hijri_enable",
                    "type" => "select",
                    "options" => array(__('Yes','wpwsc'), __('No','wpwsc')),
                    "std" => "Yes"),
                  
                  array( "name" => __('Show Gregorian date?','wpwsc'),
                    "desc" => __('Select if you want to show Gregorian date','wpwsc'),
                    "id" => $shortname."_greg_enable",
                    "type" => "select",
                    "options" => array(__('No','wpwsc'), __('Yes','wpwsc')),
                    "std" => "No"),  
                  
                  array( "name" => __('Text Align','wpwsc'),
                    "desc" => __('Choose text align','wpwsc'),
                    "id" => $shortname."_textalign",
                    "type" => "select",
                    "options" => array("center", "right", "left"),
                    "std" => "center"),  
                  
                  array( "name" => __('Colour Scheme','wpwsc'),
                    "desc" => __( 'Select the colour scheme for the counter', 'wpwsc' ),
                    "id" => $shortname."_color_scheme",
                    "type" => "select",
                    "options" => array("default" , "green", "red", "purple", "black", "blue"),
                    "std" => "default" ),


                  array( "name" => __('Background Color','wpwsc'),
                    "desc" => __('Enter a custom background color','wpwsc'),
                    "id" => $shortname."_bg_scheme",
                    "type" => "colorpicker",
                    "std" => "#FFF"),  
                  
                    

                  array( "name" => __('Custom Background Image','wpwsc'),
                    "type" => "subheader"),
                  array( "type" => "open"),  

                  array( "name" => __('Enable background image?','wpwsc'),
                    "desc" => __('Select if you want to enable custom background image','wpwsc'),
                    "id" => $shortname."_bg_enable",
                    "type" => "select",
                    "options" => array(__('No','wpwsc'), __('Yes','wpwsc')),
                    "std" => "No"), 

                  array( "name" => __('Background image upload','wpwsc'),  
                    "desc" => __('Use this option if you want to use custom background image','wpwsc'),  
                    "id" => $shortname."_bg_image",  
                    "type" => "upload",  
                    "std" => ""),   
                  
                  array( "name" => __('Background Repeat?','wpwsc'),
                    "desc" => __('Choose if you want your background repeat properties','wpwsc'),
                    "id" => $shortname."_bg_repeat",
                    "type" => "select",
                    "options" => array("no-repeat", "repeat", "repeat-x", "repeat-y"),
                    "std" => "no-repeat"),  

                  array( "name" => "Miscellaneous",
                    "type" => "subheader"),
                  array( "type" => "open"),  

                  array( "name" => __('Enable custom css?','wpwsc'),
                    "desc" => __('Select if you want to enable custom css','wpwsc'),
                    "id" => $shortname."_css_enable",
                    "type" => "select",
                    "options" => array(__('No','wpwsc'), __('Yes','wpwsc')),
                    "std" => "No"),  

                  array( "name" => __('Custom CSS','wpwsc'),
                    "desc" => __('Want to add any custom CSS code? Put in here, and the rest is taken care of. This overrides any other stylesheets. eg: a.button{color:green}','wpwsc'),
                    "id" => $shortname."_custom_css",
                    "type" => "textarea",
                    "std" => "#wscontainer {
    background-image:url('".plugins_url('/images/waktu-solat-background.jpg', __FILE__)."');
    background-repeat:no-repeat;
    padding:10px 10px;  
    width: 95%;
    min-height: 180px;
}

.info_message{
    padding-top:10px;
    padding-left: 5px;
    width: 150px;
    font-size: 8pt;
    font-weight: bold;
    text-align: center;
    margin: 0 auto;
}


#countbox{
    color: #fff;
    font-family: Myriad Pro,Helvetica,sans-serif;
    width: 118px;
    height: 35px;
    margin:0 auto;  
    padding: 2px 0 15px 0;
}

#days, #hours, #mins, #secs{
    float: left;
    text-align: center; 
    background-image:url('".plugins_url('/images/flip_default.png', __FILE__)."');
    background-repeat:no-repeat;
    margin: 0 auto;
    height: 33px;
    width: 35px;
}

#days_text,#hours_text, #mins_text,#secs_text{
    float: left;
    text-align: center;
    height: 14px;
    width: 35px;
    color: #000;
    font-size: 8px;
    text-transform: uppercase;
    margin: 0 auto;

}
    
#days span, #hours span, #mins span , #secs span {
    background: url('".plugins_url('/images/flip_gradient_default.png', __FILE__)."');
    background-repeat:no-repeat;
    position: absolute;
    display: block;
    height: 21px;
    width: 35px;
    margin: 0 auto;
    z-index: 1;
}

#days dl, #hours dl, #mins dl , #secs dl {
    width: 35px;
    font-size: 24px;
    line-height: 33px;
    margin: 0 auto 3px auto;
    position: absolute;
    display: block;
    z-index: 0;
}

"),   
                  array( "name" => __( 'Show plugin credit?', 'wpwsc'),
                    "desc" => __( 'Select if you want to enable plugin credit at the bottom of widget', 'wpwsc'),
                    "id" => $shortname."_credit",
                    "type" => "select",
                    "options" => array(__( 'No', 'wpwsc'), __( 'Yes', 'wpwsc')),
                    "std" => "Yes"),
                  array( "name" => __('Enable auto refresh?', 'wpwsc'),
                    "desc" => __( 'Select if you want to enable auto refresh', 'wpwsc'),
                    "id" => $shortname."_auto_refresh",
                    "type" => "select",
                    "options" => array(__( 'No', 'wpwsc'), __( 'Yes', 'wpwsc')),
                    "std" => "No"),   

                  array( "type" => "close")
                  );


function waktusolat_add_admin() {

        global $ezwsoptions;

        if ( $_GET['page'] == plugin_basename ( dirname ( __FILE__ )) ) {

          if ( 'save' == $_REQUEST['action'] ) {

            foreach ($ezwsoptions as $value) { update_option( $value['id'], $_REQUEST[ $value['id'] ] ); 
            }

            foreach ($ezwsoptions as $value) {
                      if( isset( $_REQUEST[ $value['id'] ] ) ) { update_option( $value['id'], $_REQUEST[ $value['id'] ]  ); } else { delete_option( $value['id'] ); } }

                      header("Location: options-general.php?page=".plugin_basename ( dirname ( __FILE__ ))."&saved=true");
                die;

              }
              else if( 'reset' == $_REQUEST['action'] ) {

              foreach ($ezwsoptions as $value) {
                delete_option( $value['id'] ); }

              header("Location: options-general.php?page=".plugin_basename ( dirname ( __FILE__ ))."&reset=true");
              die;

              }
        }

add_options_page("Waktu Solat", "Waktu Solat", 'administrator', plugin_basename ( dirname ( __FILE__ )), 'waktusolat_admin');
}

function waktusolat_admin() {

      global $ezwsoptions;
      $i=0;
      
      if ( $_REQUEST['saved'] ) echo '
      <div id="message" class="updated fade">
        <p>
          <strong>'. __('Waktu Solat settings saved.','wpwsc').'</strong>
        </p>
      </div>';

      if ( $_REQUEST['reset'] ) echo '
      <div id="message" class="updated fade">
        <p>
          <strong>'. __('Waktu Solat settings reset.','wpwsc').'</strong>
        </p>
      </div>';
      
      echo '   
      <div class="wrap ezws_wrap">
        <h2>'. __('Waktu Solat Settings','wpwsc').' </h2>
      
        <div class="ezws_opts">
          <form method="post">';
      
      foreach ($ezwsoptions as $value) {
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
      <p><?php echo __('Please use the options page below to edit the widget settings.','wpwsc'); ?></p>
      
      <?php break;
      
      case 'text':
      ?>
      
      <div class="ezws_input ezws_text">
        <label for="<?php echo $value['id']; ?>">
          <?php echo __($value['name'], 'wpwsc');
          ?>
        </label>
        <input name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>" />
        <small>
          <?php echo __($value['desc'], 'wpwsc');
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
      
      <div class="ezws_input ezws_text">
        <label for="<?php echo $value['id']; ?>">
          <?php echo __($value['name'], 'wpwsc');
          ?>
        </label>
        <input name="<?php echo $value['id']; ?>" id="color_<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>" />
        <small>
          <?php echo __($value['desc'], 'wpwsc');
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
      
      <div class="ezws_input ezws_textarea">
        <label for="<?php echo $value['id']; ?>">
          <?php echo __($value['name'], 'wpwsc'); ?>
        </label>
        <textarea name="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( get_settings( $value['id'] ) != "") { echo trim(get_settings( $value['id'])); } else { echo trim($value['std']); } ?></textarea>
        <small>
          <?php echo __($value['desc'], 'wpwsc');
          ?>
        </small>
        <div class="clearfix">
        </div>
      
      </div>
      
      <?php
      break;
      
      case 'select':
      ?>
      
      <div class="ezws_input ezws_select">
        <label for="<?php echo $value['id']; ?>">
          <?php echo __($value['name'], 'wpwsc');
          ?>
        </label>
      
        <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
          <?php foreach ($value['options'] as $option) {
          ?>
          <option <?php if (get_settings( $value['id'] ) == $option) { echo 'selected="selected"'; } ?>
            ><?php echo __($option, 'wpwsc');
            ?>
          </option><?php }
          ?>
        </select>
      
        <small>
          <?php echo __($value['desc'], 'wpwsc');
          ?>
        </small>
        <div class="clearfix">
        </div>
      </div>
      <?php
      break;
      
      case "checkbox":
      ?>
      
      <div class="ezws_input ezws_checkbox">
        <label for="<?php echo $value['id']; ?>">
          <?php echo __($value['name'], 'wpwsc');
          ?>
        </label>
      
        <?php if(get_option($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = "";}
        ?>
        <input type="checkbox" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?>
        />
      
        <small>
          <?php echo __($value['desc'], 'wpwsc');
          ?>
        </small>
        <div class="clearfix">
        </div>
      </div>
      <?php break;


      // tambahan case untuk handle image upload
      case "upload":

       ?>

       <div class="ezws_input ezws_upload">

        <label for="<?php echo $value['id']; ?>"><?php echo __($value['name'], 'wpwsc'); ?></label>  
        <input id="<?php echo $value['id'] ?>_image" type="text" size="36" name="<?php echo $value['id'] ?>" value="<?php if ( get_settings( $value['id'] ) != "") { echo trim(get_settings( $value['id'])); } else { echo trim($value['std']); } ?>" />
        <input id="<?php echo $value['id'] ?>_button" type="button" value="<?php echo __('Upload Image', 'wpwsc'); ?>" />

        <small><?php echo __($value['desc'], 'wpwsc'); ?></small><div class="clearfix"></div>  

      </div>
      <script language="javascript" type="text/javascript">
      jQuery(document).ready(function() {

      jQuery('#<?php echo $value['id'] ?>_button').click(function() {
       formfield = jQuery('#<?php echo $value['id'] ?>_image').attr('name');
       tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
       return false;
      });

      window.send_to_editor = function(html) {
       imgurl = jQuery('img',html).attr('src');
       jQuery('#<?php echo $value['id'] ?>_image').val(imgurl);
       tb_remove();
      }

      });
      </script>

       <?php  break;
      
      case "subheader":

      $i++;
      
      ?>
      <div class="ezws_title">
    <h3><img src="<?php echo plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) ."/images/dome-th.png"; ?>" width="60" height="22" alt="Projek Waktu Solat" /><?php echo __($value['name'], 'wpwsc'); ?></h3><span class="submit"><input name="save<?php echo $i; ?>" type="submit" value="<?php echo __('Save changes', 'wpwsc'); ?>" />
    </span><div class="clearfix"></div>
        </div>

    <?php break;
       
      case "section":

      $i++;
      
      ?>
<div class="ezws_section">
  <div class="ezws_title">
    <h3><img src="<?php echo plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) ) ."/images/dome-th.png"; ?>" width="60" height="22" alt="Projek Waktu Solat" /><?php echo __($value['name'], 'wpwsc'); ?></h3><span class="submit"><input name="save<?php echo $i; ?>" type="submit" value="<?php echo __('Save changes', 'wpwsc'); ?>" />
    </span><div class="clearfix"></div></div>
    <div class="ezws_options">

    <?php break;

    }
    }
    ?>

    <input type="hidden" name="action" value="save" />
    </form>
    <form method="post">
      <p class="submit">
        <input name="reset" type="submit" value="<?php echo __('Reset', 'wpwsc'); ?>" />
        <input type="hidden" name="action" value="reset" />
      </p>
    </form>
    </div>

    <?php
    }

add_action('admin_init', 'waktusolat_add_init');
add_action('admin_menu', 'waktusolat_add_admin');

function waktusolat_add_init() {
      wp_enqueue_style("functions", plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) )."/style/admin.css", false, "1.1", "all");
      wp_enqueue_style( 'farbtastic' );
      wp_enqueue_script( 'farbtastic' );
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
      wp_enqueue_style('thickbox');
    }
?>