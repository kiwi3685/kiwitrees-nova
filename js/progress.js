/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
 *
 * Derived from http://talkerscode.com/webtricks/display-progress-bar-while-page-loads-using-jquery.php
 *
 */

document.onreadystatechange = function(e)
{
  if(document.readyState=="interactive")
  {
    var all = document.getElementsByTagName("*");
    for (var i=0, max=all.length; i < max; i++) 
    {
      set_ele(all[i]);
    }
  }
}

function check_element(ele)
{
  var all = document.getElementsByTagName("*");
  var totalele=all.length;
  var per_inc=100/all.length;

  if(jQuery(ele).on())
  {
    var prog_width=per_inc+Number(document.getElementById("progress_width").value);
    document.getElementById("progress_width").value=prog_width;
    jQuery("#bar1").animate({width:prog_width+"%"},10,function(){
      if(document.getElementById("bar1").style.width=="100%")
      {
        jQuery(".progress").fadeOut("slow");
      }			
    });
  }

  else	
  {
    set_ele(ele);
  }
}

function set_ele(set_element)
{
  check_element(set_element);
}

/* HTML CODE
      <!-- page load progress bar -->
      <div class="progress" id="progress_div">
        <div class="bar warning" id="bar1"></div>
        <div class="percent" id="percent1"></div>
        <input type="hidden" id="progress_width" value="0">
      </div>
*/