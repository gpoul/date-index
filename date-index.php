<?php
/*
Plugin Name: date-index
Description: Display a date-index of postings.
Version: 20090101
Author: Gerhard Poul
Author URI: http://gpoul.strain.at/
*/

/*
 Copyright (c) 2008, 2009 Gerhard Poul. All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions
 are met:
 1. Redistributions of source code must retain the above copyright
    notice, this list of conditions and the following disclaimer.
 2. Redistributions in binary form must reproduce the above copyright
    notice, this list of conditions and the following disclaimer in the
    documentation and/or other materials provided with the distribution.
 3. Neither the name of the Organization nor the names of its contributors
    may be used to endorse or promote products derived from this software
    without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS''
 AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 ARE DISCLAIMED.  IN NO EVENT SHALL THE COPYRIGHT HOLDERS OR CONTRIBUTORS BE
 LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 SUCH DAMAGE.
*/

function date_index ($content) {
  $marker = "<!-- date-index -->";
  $stext = "";
  if (strstr($content, $marker) != FALSE) {
    global $wpdb;
    $statsql = "select month(post_date),year(post_date),count(*) from wp_posts where post_date!=0 and post_type='post' and post_date<date_add(current_date(),interval - day(current_date())+1 day) group by year(post_date),month(post_date);";
    $googledata = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));

    $statslist = $wpdb->get_results($statsql, ARRAY_N);
    $chartdata = "";
    $years = "";
    $currentmonth = 1; // Track the month we currently have
    // Pad everything in that year before the start of the first month with posts
    for ($i = 1; $i < $statslist[0][0]; $i++) {
      $chartdata .= '_';
      $currentmonth++;
    }
    // Determine the highest amount of posts in a month
    $maxposts = 0;
    foreach ($statslist as $monthlystats) {
      $maxposts = max($maxposts, $monthlystats[2]);
    }
    // Maximum Google Charts API will graph in relation to our posts
    $graphfactor = 61 / $maxposts;
    foreach ($statslist as $monthlystats) {
      while ($currentmonth < $monthlystats[0]) {
        $chartdata .= $googledata[0]; // Add a null value into the data stream
        $currentmonth++;
      }
      $chartdata .= $googledata[$graphfactor * $monthlystats[2]];
      $years[$monthlystats[1]] = 1; // Collect associative array of years for year list
      $currentmonth++;
      if ($currentmonth > 12) $currentmonth = 1;
    }
    // Pad everything in the last year to the end of the current year
    for ($i = 12; $i > $statslist[count($statslist)-1][0]; $i--) {
      $chartdata .= '_';
    }
    $yearlist = "";
    foreach ($years as $year => $value) {
      $yearlist .= "|$year";
    }
    $grapharea = 1/count($years); // 1 / Number of Years
    $stext .= "<img src='http://chart.apis.google.com/chart?chs=400x125&cht=lc&chf=c,ls,0,ffffff,$grapharea,f8f8f8,$grapharea&chxt=x,y&chxl=0:$yearlist&chxs=0,666666,11,-1&chxr=1,0,$maxposts&chd=s:$chartdata'/>";
    $content = str_replace($marker, $stext, $content);
  }
  return $content;
}

add_filter('the_content', 'date_index');

?>
