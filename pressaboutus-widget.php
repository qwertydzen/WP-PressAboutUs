<?php 
/*
Plugin Name: PressAboutUs Widget
Plugin URI: http://pressabout.us/widgets/
Description: Display most popular reviews and news about your project
Author: Yauhen Palcheuski
Version: 1
Author URI: http://pressabout.us
Requires at least: 3.0.0
Tested up to: 3.3.1

Copyright 2011-2012 by Yauhen Palcheuski http://pressabout.us

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License,or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not,write to the Free Software
Foundation,Inc.,51 Franklin St,Fifth Floor,Boston,MA 02110-1301 USA
*/ 


// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();
	
class PauWidget extends WP_Widget {
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0
	 */
	function PauWidget() {
		
		ob_start(); //header sent problems
		if(!defined('PAUWIDGET_URL'))
			define('PAUWIDGET_URL',WP_PLUGIN_URL."/".plugin_basename(dirname(__FILE__))."/");
		
		$this->version = "1";
		$this->domain  = "pau-wid";
		$this->load_text_domain();
		
		$this->functions = new PressAboutUsWidgetFunctions();
		add_action('wp_enqueue_scripts',array(&$this,'load_scripts_styles'));

		$widget_ops = array('classname' => 'pressaboutus-widget','description' => __("Display most popular reviews about your project",$this->domain));
		$this->WP_Widget('pressaboutus-widget',__('PressAboutUs Widget',$this->domain),$widget_ops);
	}
	
	/**
	* Register localization/language file
	*
	* @return void
	* @since 1
	*/
	function load_text_domain(){
		if(function_exists('load_plugin_textdomain')){
			$plugin_dir = basename(dirname(__FILE__)).'/langs/';
			load_plugin_textdomain($this->domain,WP_CONTENT_DIR.'/plugins/'.$plugin_dir,$plugin_dir);
		}
	}
	
	/**
	 * Load frontend js/css
	 *
	 * @return void
	 * @since 1
	 */
	function load_scripts_styles(){
		if(is_admin()) return;
		//wp_enqueue_style('pressaboutus-widget',PAUWIDGET_URL.'_css/pau-widget.css',NULL,$this->version);
		//wp_enqueue_script('pressaboutus-widget',PAUWIDGET_URL.'_js/pau-widget.js',array('jquery'),$this->version,true);
	}
	
	
	/**
	 * Display widget.
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 * @since 1
	 */
	function widget( $args, $instance ) {
		global $wpdb;

		extract($args); extract($instance);

		$instance['limit']	= ($limit) ? $limit : 5;
        $instance['delay']	= ($delay) ? $delay : 0.01;
        $instance['width']	= ($width) ? $width : 0;
        $instance['style']	= ($style) ? $style : 'default';

        $width = ($width=='auto') ? '' : (int)$width;

        // start widget //
		$output  = $before_widget."\n";
        $output .= '<link rel="stylesheet" href="/wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/_css/pau-'.$style.'.css?ver='.$this->version .'" media="all"/>
<div id="pressaboutus" '.($width>0 ? 'style="width:'.$width.'px;"' : '').'>
<div class="pressaboutus-header">';
        if( isset( $title ) ){
            $output .= $before_title. $title . $after_title . "\n";
        }else{
            $output .= "Press About Us";
        }
        $output .= '</div>
    <div class="pressaboutus-content">';
        $output .= $this->functions->get_reviews( $instance );
        $output .= '</div>
    <div class="pressaboutus-footer">
        Powered by <a href="http://pressabout.us"><b>pressabout.us</b></a>
    </div>
</div>
<script type="text/javascript">
    //tracking code of load widget, just for stats
    (function() {
        var loader = new Image();
        loader.src = "http://hits.twittweb.com/hits/send/58?r=" + Math.round(100000 * Math.random());
    })();
</script>
';
		echo $output .=  $after_widget."\n";
		// end widget //
	}

	
	/**
	 * Configuration form.
	 *
	 * @param array $instance
	 * @return void
	 * @since 1
	 */
	function form( $instance ) {
		$default = array(
            'tlength' => '60',
            'dlength' => '100',
            'title' => 'About Us',
            'limit'=> 5,
            'delay'=>0.01,
            'style' => 'default',
            'width' => '250',
			'Domain'
		); $instance = wp_parse_args( $instance, $default );

        $widget_styles = array('tiny','horizontal','default'    );

        $widget_tooltips = array(
                    'default'=>array(
                                    'tooltip_msg'=>'<img src=\'http://i.imm.io/epGx.png\' width=\'200px\'/>',
                                    'sub_msg'=>'Shows title and description'),
                    'tiny'=>array(
                                    'tooltip_msg'=>'<img src=\'http://i.imm.io/epEw.png\' width=\'230px\'/>',
                                    'sub_msg'=>'Shows domain names'),
                    'horizontal'=>array(
                                    'tooltip_msg'=>'<img src=\'http://i.imm.io/epEf.png\' width=\'230px\'/>',
                                    'sub_msg'=>'Shows favicons and domain names'),
        );

        $post_types = get_post_types(array('public'=>true),'names','and');

        extract( $instance );
	 	?>
		<p>
	 		<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title', $this->domain ) ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title') ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo $title ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('width')?>"><?php _e('Width of the widget',$this->domain)?> <input id="<?php echo $this->get_field_id('width')?>" name="<?php echo $this->get_field_name('width')?>" size="4" type="text" value="<?php echo $width?>"/></label>
		</p>
        <p>
            <label for="<?php echo $this->get_field_id('limit')?>"><?php _e('Show how many posts?',$this->domain)?> <input id="<?php echo $this->get_field_id('limit')?>" name="<?php echo $this->get_field_name('limit')?>" size="4" type="text" value="<?php echo $limit?>"/></label>
        </p>
<!--        <p>-->
<!--            <label for="--><?php //echo $this->get_field_id('delay')?><!--">--><?php //_e('How fast renew posts?',$this->domain)?><!-- <input id="--><?php //echo $this->get_field_id('delay')?><!--" name="--><?php //echo $this->get_field_name('delay')?><!--" size="4" type="text" value="--><?php //echo $delay?><!--"/></label>-->
<!--        </p>-->
        <p>
			<label for="<?php echo $this->get_field_id('tlength')?>"><?php _e('Title length',$this->domain)?> <input id="<?php echo $this->get_field_id('tlength')?>" name="<?php echo $this->get_field_name('tlength')?>" size="4" type="text" value="<?php echo $tlength?>"/> <?php _e('chars',$this->domain)?></label>
		</p>
        <p>
            <label for="<?php echo $this->get_field_id('dlength')?>"><?php _e('Description length',$this->domain)?> <input id="<?php echo $this->get_field_id('dlength')?>" name="<?php echo $this->get_field_name('dlength')?>" size="4" type="text" value="<?php echo $dlength?>"/> <?php _e('chars',$this->domain)?></label>
        </p>

        <style>
            .tooltip{
                position:absolute;
                z-index:999;
                left:-9999px;
                background-color:#dedede;
                padding:5px;
                border:1px solid #fff;
                width:250px;
            }

            .tooltip p{
                margin:0;
                padding:0;
                color:#fff;
                background-color:#222;
                padding:2px 7px;
            }
        </style>

        <script>

//            jQuery(document).ready(function($){

                function simple_tooltip(target_items, name){
                    jQuery.noConflict();
                    jQuery(target_items).each(function(i){
                        jQuery("body").append("<div class='"+name+"' id='"+name+i+"'><p>"+jQuery(this).attr('title')+"</p></div>");
                        var my_tooltip = jQuery("#"+name+i);
                        if(jQuery(this).attr("title") != "" && jQuery(this).attr("title") != "undefined" ){

                            jQuery(this).removeAttr("title").mouseover(function(){
                                my_tooltip.css({display:"none"}).fadeIn(400);
                            }).mousemove(function(kmouse){
                                    var border_top = jQuery(window).scrollTop();
                                    var border_right = jQuery(window).width();
                                    var left_pos;
                                    var top_pos;
                                    var offset = 15;
                                    if(border_right - (offset *2) >= my_tooltip.width() + kmouse.pageX){
                                        left_pos = kmouse.pageX+offset;
                                    } else{
                                        left_pos = border_right-my_tooltip.width()-offset;
                                    }

                                    if(border_top + (offset *2)>= kmouse.pageY - my_tooltip.height()){
                                        top_pos = border_top +offset;
                                    } else{
                                        top_pos = kmouse.pageY-my_tooltip.height()-offset;
                                    }

                                    my_tooltip.css({left:left_pos, top:top_pos});
                                }).mouseout(function(){
                                    my_tooltip.css({left:"-9999px"});
                                });

                        }

                    });
                }


//            });
        </script>

		<p>
            <label><?php _e('Widget Style:',$this->domain)?></label><br />
            <?php foreach ( $widget_styles as $value ) : ?>

                <label for="<?php echo $this->get_field_id($value)?>">
                    <input id="<?php echo $this->get_field_id($value)?>"
                           name="<?php echo $this->get_field_name('style')?>"
                           value="<?= $value?>"
                           type="radio"
                           <? if($style == $value){
                                echo 'checked';
                            }
                            ?> />
                        <abbr onmouseover='simple_tooltip("abbr","tooltip");return true;' title="<?php _e($widget_tooltips[$value]['tooltip_msg'], $this->domain) ?>" style="text-decoration: none; border-bottom:1px dotted;">
                            <?php _e($value, $this->domain)?>
                        </abbr>
                </label><br /><small><?php _e($widget_tooltips[$value]['sub_msg'],$this->domain ) ?></small><br />
            <?php endforeach;?>
        </p>

        <a href="http://pressabout.us/widgets/" target="_blank"><?php _e('Create more widgets',$this->domain)?></a> | <a href="http://pressabout.us/static/contacts" target="_blank"><?php _e('Contact us',$this->domain)?></a>
        <?php
	}
	
}
include(dirname(__FILE__)."/include.php");
add_action('widgets_init',create_function('','return register_widget("PauWidget");'));
?>
