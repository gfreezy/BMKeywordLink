<?php
/*
Plugin Name: Keyword Link Plugin
Plugin URI: http://www.dijksterhuis.org/wordpress-plugins/keyword-link-plugin/
Description: A SEO plugin that helps you to automatically link keywords to articles.
Author: Martijn Dijksterhuis
Version: 0.7.1
Author URI: http://www.dijksterhuis.org/#

*/

/* The idea for this plugin comes from a website I designed. It has lots of touristic
 * information and it would be great if every important keyword would automatically be linked
 * to its relevant articles instead of me having to do this by hand.
 *
 * Example:  "A visit to the UK is not complete without a visit to stonehenge."
 *
 * Would become:  "A visit to the UK is not complete without a visit to <A href="link">stonehenge</a>."
 * update by LiuCheng.Name (http://www.liucheng.name/)
 * ֧???????ַ???????,?ֱ?????Ӣ???????Ĺؼ???
 * ?????????ļ?ʱ?????ַ???????????
 * ?????༭?ؼ???ʱ?????ַ???????????  //ע??,????ȥ????ԭ???߶Թؼ??ʵļ????????ܡ???Ϊ?˹???ֻ??Ӣ????Ч??
 * ?????滻?ؼ??????????ӵ?????.?????????е????ӽ?????ƥ??.
*/

/* Constants */
define(BM_KEYWORDLINK_OPTION,'bm_keywordlinkoption');
define(BM_KEYWORDLINK_QUOTES,'1');

include_once("bm_csvsupport.php");

function bm_keywordlink_admininit()
{
	 // Add a page to the options section of the website
	if (current_user_can('manage_options'))
		add_options_page("BM Keywordlink","BM Keywordlink", 8, __FILE__, 'bm_keywordlink_optionpage');
}

function bm_keywordlink_topbarmessage($msg)
{
	echo '<div class="updated fade" id="message"><p>' . $msg . '</p></div>';
}

function bm_keywordlink_showdefinitions()
{
	/* Retrieve the keyword definitions */
	$links = get_option(BM_KEYWORDLINK_OPTION);

	echo "<h3>Links</h3>";

	if ($links)
	{
		echo "<table class='widefat'>\n";
		echo "<thead><tr><th>#</th><th>Keyword</th><th>Link</th><th>Attributes</th><th>Action</th></tr></thead>\n";
		$cnt = 0;
		foreach ($links as $keyword => $details)
		{
			list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN) = explode('|',$details);
			$cleankeyword = stripslashes($keyword);

			if ($cnt++ % 2) echo '<tr class=alternate>'; else echo '<tr>';
			echo "<td>$cnt</td><td>$cleankeyword</td><td><a href='$link'>$link</a></td>";

			/* show attributes */
			echo "<td>";
			if ($nofollow) echo "[nofollow] ";
			if ($firstonly) echo "[first only] ";
			if ($newwindow) echo "[new window] ";
			if ($ignorecase) echo "[ignore case] ";
			if ($isaffiliate) echo "[affiliate] ";
			if ($docomments) echo "[comments] ";
			if ($zh_CN) echo "[Chinese Keyword]";
			echo "</td>";

			$urlsave_keyword = $keyword;
			$urlsave_url 	  = $link;

			echo "<td>";
			echo "<input type=button value=Edit onClick=\"javascript:BMEditKeyword('$urlsave_keyword','$urlsave_url',";
				echo (($nofollow=="0")?"0":"1") . "," . (($firstonly=="0")?"0":"1") . "," .(($newwindow=="0")?"0":"1"). "," .(($ignorecase=="0")?"0":"1") . "," . (($isaffiliate=="0")?"0":"1") . "," . (($docomments=="0")?"0":"1") . "," . (($zh_CN=="0")?"0":"1") . ");\"/>";
echo "<input type=button value=Delete onClick=\"javascript:BMDeleteKeyword('$urlsave_keyword');\" />\n";
echo "</td></tr>\n";
}
echo "</table>";
}
else
	echo "<p>No links have been defined!</p>";

?>

<!-- Support for the delete button , we use Javascript here -->
<form name=delete_form method="post" action="">
	<input type=hidden name=action value=delete />
	<input type=hidden name=keyword value="" />
</form>
<script type="text/javascript">

function BMDeleteKeyword(keyword)
{
	if (confirm('Are you sure you want to delete this keyword?'))
	{
		document.delete_form.keyword.value = keyword;
		document.delete_form.submit();
	}
}

function BMEditKeyword(keyword,url,nofollow,firstonly,newwindow,ignorecase,isaffiliate,docomments,zh_CN)
{
	document.bm_keywordadd.keyword.value      = (keyword);
	document.bm_keywordadd.link.value         = (url);
	document.bm_keywordadd.nofollow.checked   = (nofollow==1);
	document.bm_keywordadd.firstonly.checked  = (firstonly==1);
	document.bm_keywordadd.newwindow.checked  = (newwindow==1);
	document.bm_keywordadd.ignorecase.checked = (ignorecase==1);
	document.bm_keywordadd.isaffiliate.checked= (isaffiliate==1);
	document.bm_keywordadd.docomments.checked= (docomments==1);
	document.bm_keywordadd.zh_CN.checked= (zh_CN==1);
	window.location.hash = "keywordeditor";
}

	  // Encoding things in Base64 avoids many tricky issues with ' " \ / etc in strings
	  // Source: http://ntt.cc/2008/01/19/base64-encoder-decoder-with-javascript.html
	  /*
 	  var keyStr = "ABCDEFGHIJKLMNOP" +
               	"QRSTUVWXYZabcdef" +
               	"ghijklmnopqrstuv" +
               	"wxyz0123456789+/" +
               	"=";

	  function decode64(input)
	  {
     var output = "";
     var chr1, chr2, chr3 = "";
     var enc1, enc2, enc3, enc4 = "";
     var i = 0;

     // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
     var base64test = /[^A-Za-z0-9\+\/\=]/g;
     if (base64test.exec(input)) {
        alert("There were invalid base64 characters in the input text.\n" +
              "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
              "Expect errors in decoding.");
     }
     input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

     do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
           output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
           output = output + String.fromCharCode(chr3);
        }

        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";

     } while (i < input.length);

     return unescape(output);
   }*/


   </script>
   <?php


 }

 function bm_keywordlink_addnew()
 {
 	?>
 	<h3>Edit / Add a new link</h3>
 	<a name="keywordeditor"></a><form name=bm_keywordadd method="post" action="">
 	<input type=hidden name=action value=save />
 	<table>
 		<tr>
 			<td><label for=keyword>Keyword</label></td><td><input type=text name=keyword /></td></tr>
 			<tr><td><label for=link>Link</label></td><td><input type=text size=50 maxlength=200 name=link /></td></tr>
 			<tr><td><label for=nofollow>No Follow</label></td><td><input type=checkbox id=nofollow name=nofollow value="1">
 				| <label for=firstonly>First Match Only</label>&nbsp;<input type=checkbox id=firstonly name=firstonly value="1">
 				| <label for=newwindow>New Window</label>&nbsp;<input type=checkbox id=newwindow name=newwindow value="1">
 				| <label for=ignorecase>Ignore case</label>&nbsp;<input type=checkbox id=ignorecase name=ignorecase value="1">
 				| <label for=isaffiliate>Is Affiliate</label>&nbsp;<input type=checkbox id=isaffiliate name=isaffiliate value="1">
 				| <label for=docomments>Filter in comments?</label>&nbsp;<input type=checkbox id=docomments name=docomments value="1">
 				| <label for=zh_CN>For zh_CN?</label>&nbsp;<input type=checkbox id=zh_CN name=zh_CN value="1">
 			</td></tr>
 			<tr><td><input type=submit value="Save" /></td></tr></table>
 		</form>
 		<?php
 	}

 	function bm_keywordlink_help()
 	{
 		?>
 		<h3>Help</h3>
 		<p>
 			The Keyword link plugin searches the contents of each of your posts for the above listed keywords. Each keyword found is
 			automatically linked to the link you have specified. For each link you can also specify the following options:
 		</p>
 		<ul>
 			<li>No Follow - This adds a <em>rel='no follow'</em> to the link.</li>
 			<li>First Match Only - Only replace the first match of the word, ignore further mentions.</li>
 			<li>New Window - This adds a <em>target='_blank'</em> to the link, forcing a new browser window on clicking.</li>
 			<li>Ignore Case - "Google", "google" and "gooGLE" are all fine.</li>
 			<li>Is affiliate - Allows you to tell your visitors that the link is an affiliate.</li>
 			<li>Filter in comments - Also replace this keyword in post comments.</li>
 			<li>*For zh_CN - It work for Chinese Keyword. by <a href="http://www.liucheng.name/?p=299">LIUCHENG.NAME</a></li>
 		</ul>
 		<p>
 			Each link created by the plugin is contained in an &lt;span class='bm_keywordlink'&gt; .. &lt;/span&gt; wrapper. This allows you
 			to modify the links by adding a style to your themes style.css file.</p>
 			<p>Affiliate links work a little different, they use &lt;span class='bm_keywordlink_affiliate'&gt; .. &lt;/span&gt; allowing you
 				to differentiate those paid for links from your internal links. </p>
 				<p>
 					Example style.css:
 					<pre>
 						.bm_keywordlink { text-decoration: underline; }
 						.bm_keywordlink_affiliate { font-weight: bold; }
 					</pre>
 				</p>
 				<?php
 			}

 			function bm_keywordlink_savenew()
 			{
 				$links = get_option(BM_KEYWORDLINK_OPTION);

 				$keyword = $_POST['keyword'];
 				$link = $_POST['link'];
 				$nofollow = ($_POST['nofollow']=="1") ? "1" : "0";
 				$firstonly = ($_POST['firstonly']=="1") ? "1" : "0";
 				$newwindow = ($_POST['newwindow']=="1") ? "1" : "0";
 				$ignorecase = ($_POST['ignorecase']=="1") ? "1" : "0";
 				$isaffiliate = ($_POST['isaffiliate']=="1") ? "1" : "0";
 				$docomments = ($_POST['docomments']=="1") ? "1" : "0";
 				$zh_CN = ($_POST['zh_CN']=="1") ? "1" : "0";

 				if ($keyword == '' || $link == '')
 				{
 					bm_keywordlink_topbarmessage(__('Please enter both a keyword and URL'));
 					return;
 				}

 				if (isset($links[$keyword]))
 				{
 					bm_keywordlink_topbarmessage(__('Existing keyword has been updated'));
 				}

 				/* Store the link */
 				$links[$keyword] = implode('|',array($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN));

 				update_option(BM_KEYWORDLINK_OPTION,$links);
 			}

 			function bm_keywordlink_deletekeyword()
 			{
 				$links = get_option(BM_KEYWORDLINK_OPTION);
 				$keyword = $_POST['keyword'];

 				if (!isset($links[$keyword]))
 				{
 					bm_keywordlink_topbarmessage(__('No such keyword, bizarre error!'));
 					return;
 				}

 				unset($links[$keyword]);
 				update_option(BM_KEYWORDLINK_OPTION,$links);
 			}


 			function bm_keywordlink_optionpage()
 			{
 				/* Perform any action */
 				if ($_POST['action']=='save')
 					bm_keywordlink_savenew();
 				if ($_POST['action']=='delete')
 					bm_keywordlink_deletekeyword();
 				if ($_POST['action']=='importcvs')
 					bm_keywordlink_cvsimport();
 				/*Note: exportcvs is called from the init action linked below */

 				/* Definition */
 				echo '<div class="wrap">';
 				echo '<h2>BlogMechanics KeywordLink</h2>';

 				/* Introduction */
 				echo '<p>This plugin automatically links keywords in your posts to their definition pages.</p>';

 				/* Show the existing options */
 				bm_keywordlink_showdefinitions();

 				/* Allow adding a new link */
 				bm_keywordlink_addnew();

 				/* Allow important and exporting to CVS */
 				bm_keywordlink_cvsmenu();

 				/* Show help information */
 				bm_keywordlink_help();

 				echo '</div>';
 			}

/* bm_keywordlink_replace
 *
 * This is where everything happens... search the content and search for our set of keywords
 * and add the links in the right places.
*/

function bm_keywordlink_replace($content,$iscomments)
{
	global $bm_keywordlinks;
	$links = $bm_keywordlinks;

	if ($links)
		foreach ($links as $keyword => $details)
		{
			list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN) = explode("|",$details);

				// If this keyword is not tagged for replacement in comments we continue
			if ($iscomments && $docomments==0)
				continue;

			$cleankeyword = stripslashes($keyword);
			if ($isaffiliate)
				$url  = "<span class='bm_keywordlink_affiliate'>";
			else
				$url  = "<span class='bm_keywordlink'>";

			$url .= "<a href=\"$link\"";

			if ($nofollow) $url .= ' rel="nofollow"';
			if ($newwindow) $url .= ' target="_blank"';

			$url .= ">$cleankeyword</a>";
			$url .= "</span>";

			if ($firstonly) $limit = 1; else $limit=-1;
			if ($ignorecase) $case = "i"; else $case="";

				// The regular expression comes from an older
				// auto link plugin by Sean Hickey. It fixed the autolinking inside a link
				// problem. Thanks to [Steph] for the code.

		// we don't want to link the keyword if it is already linked.
		// so let's find all instances where the keyword is in a link and change it to &&&&&, which will be sufficient to avoid linking it. We use //&&&&&, since WP would pass that
        // the idea is come from 'kb-linker'

			$content = preg_replace( '|(<a[^>]+?>)([^<]*)('.$cleankeyword.')([^<]*)(</a[^>]*>)|Ui', '$1$2&&&&&$4$5', $content);

				// For keywords with quotes (') to work, we need to disable word boundary matching
			$cleankeyword = preg_quote($cleankeyword,'\'');

			if (BM_KEYWORDLINK_QUOTES && $zh_CN)
				$regEx = '\'(?!((<.*?)|(<a.*?)))('. $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
			elseif (BM_KEYWORDLINK_QUOTES && strpos( $cleankeyword  , '\'')>0)
				$regEx = '\'(?!((<.*?)|(<a.*?)))(' . $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
			else
				$regEx = '\'(?!((<.*?)|(<a.*?)))(\b'. $cleankeyword . '\b)(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;

			$content = preg_replace($regEx,$url,$content,$limit);

	//change our '&&&&&' things to $cleankeyword.
			$content = str_replace( '&&&&&', $cleankeyword, $content);

		}
		return $content;
	}

	function bm_keywordlink_replace_content($content)
	{
		return bm_keywordlink_replace($content,false);
	}

	function bm_keywordlink_replace_comments($content)
	{
		return bm_keywordlink_replace($content,true);
	}

/* bm_keywordlink_init
 *
 * As we are now called for both the content and comments we will be
 * doing some steps several times. To avoid slowing down things too
 * much the repetative bits are cached here.
*/

function cmp($a, $b)
{
	if (strlen($a) == strlen($b))
		return 0;
	if (strlen($a) > strlen($b))
		return -1;
	return 1;
}

function bm_keywordlink_init()
{
	global $bm_keywordlinks;
	$bm_keywordlinks = get_option(BM_KEYWORDLINK_OPTION);
	if ($bm_keywordlinks)
		uksort($bm_keywordlinks, "cmp");
}

/* Tie the module into Wordpress */
add_action('admin_menu','bm_keywordlink_admininit');
add_filter('the_content','bm_keywordlink_replace_content',1);
add_filter('comment_text','bm_keywordlink_replace_comments',1);
add_action('init','bm_keywordlink_checkcvs');
add_action('init','bm_keywordlink_init');

?>
