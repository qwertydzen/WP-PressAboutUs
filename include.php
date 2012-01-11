<?php 
/**
*PopularWidgetFunctions
*
*@Popular Widget
*@author Yauhen palcheuski
*@copyright 20011-2012
*@since 1
*/

class PressAboutUsWidgetFunctions {
	
	 function PressAboutUsWidgetProFunctions() {
    	// with no instructions, does nothing
	 }
	
	/**
	 *get reviews results
	 *
	 *@return void
	 *@since 1
	*/
	function get_reviews( $instance ){
		
		global $wpdb; 
		extract($instance);
		
		$join = '';
		$output  = '';

        $domain = str_replace(array('http://', 'https://'), '',get_bloginfo('wpurl'));
		$reviews = wp_cache_get("pau_reviews", 'pau_cache');
		if ( $reviews == false ) {
			$json = file_get_contents('http://pressabout.us/widgets/'.$domain.'.json?num=1000&entity_value=title&title_length=150&description_length=300');
			$reviews = json_decode($json);
			wp_cache_set("pau_reviews", $reviews, 'pau_cache', 86400);
		}
        $totalReviews = count($reviews);

        $offset = 0;
        if ($totalReviews > $limit) {
            $offset = round((date('H')*3600 + date('i')*60 + date('s')) * $delay) % $totalReviews;
        }

        $revs = array_slice($reviews, $offset, $limit);
        $cntRevs = count($revs);
        if ($limit > $cntRevs) {
            $result = array_merge($revs, array_slice($reviews, 0, $limit - $cntRevs));
            $revs = $result;
        }

        if(count($revs)>0){
            foreach ($revs as $post) {
                $post->title = ($tlength && (strlen($post->title) > $tlength))
                                            ? substr($post->title, 0, $tlength) . " ..."
                                            : $post->title;
                $post->description = ($dlength && (strlen($post->description) > $dlength))
                    ? substr($post->description, 0, $dlength) . " ..."
                    : $post->description;
                $post->description = htmlspecialchars($post->description);
                $post->title = htmlspecialchars($post->title);
                $func = $style . 'Item';
                $output .= $this->$func($post);
                $count++;
            }
        }else{
            $output .= 'Not found news. Check your project <a href="http://pressabout.us/widgets">here</a>';
        }
		return $output;
	}


    function tinyItem($item){
        $domain = str_replace(array('http://', 'https://'), '',get_bloginfo('wpurl'));
        $out = '
<div class="pressaboutus-item">
    <div class="pressaboutus-domain">
       <a onmousedown="this.href=\'http://pressabout.us/url?ref=\'+document.location.href+\'&domain='.$domain.'&orig_url='.$item->url.'\';return true;" href="'.$item->original_url.'" target="_blank" class="pressaboutus-link" alt="'.$item->description.'">'.$item->domain_name.'</a>
    </div>
</div>';
        return $out;
    }

    function defaultItem($item){
        $domain = str_replace(array('http://', 'https://'), '',get_bloginfo('wpurl'));
        $out = '
<div class="pressaboutus-item">
    <div class="pressaboutus-domain">
       <a onmousedown="this.href=\'http://pressabout.us/url?ref=\'+document.location.href+\'&domain='.$domain.'&orig_url='.$item->url.'\';return true;" href="'.$item->original_url.'" target="_blank"  href="'.$item->url.'" class="pressaboutus-link" alt="'.$item->domain_name.' write about">'.$item->title.'</a>
       <p>'.$item->description.'</p>
    </div>
</div>';
        return $out;
    }

    function horizontalItem($item){
        $domain = str_replace(array('http://', 'https://'), '',get_bloginfo('wpurl'));
        $out = '
<div class="pressaboutus-item">
    <div class="pressaboutus-domain">
        <img alt="" height="16" src="http://www.google.com/s2/favicons?domain_url=http://'.$item->domain_name.'" width="16" />
        <a onmousedown="this.href=\'http://pressabout.us/url?ref=\'+document.location.href+\'&domain='.$domain.'&orig_url='.$item->url.'\';return true;"
            href="'.$item->original_url.'"
            target="_blank"
            href="'.$item->url.'"
            class="pressaboutus-link"
            alt="'.$item->description.'">'.$item->domain_name.'</a>
    </div>
</div>';
        return $out;
    }
}
?>