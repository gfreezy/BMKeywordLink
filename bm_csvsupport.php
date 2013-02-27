<?php

  // bm_cvssupport.php
  //
   // Allows for the import and export of comma seperated files (CVS)
   // handy for importing lists of keywords into Excel and vice versa

function bm_keywordlink_cvsexportvalue($value,$islast=false)
{
  if ($value) echo '1';
  else echo '0';
  if (!$islast) echo ',';
}

function bm_keywordlink_cvsexport()
{
  $links = get_option(BM_KEYWORDLINK_OPTION);

  /* Tell the browser to expect an CVS file */
  header('Content-type: application/csv');
  header('Content-Disposition: attachment; filename="export.csv"');

  /* Generate the header line */
  echo "Keyword,URL,NoFollow,First Only,New Window,Ignore Case,IsAffiliate,Enable In Comments,Chinese Keyword\n";

  foreach ($links as $keyword => $details)
  {
    list($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN) = explode("|",$details);
    $cleankeyword = stripslashes($keyword);
    $cleankeyword = iconv("utf-8", "gb2312", $keyword);  /*by LIUCHENG.NAME (http://www.liucheng.name/),?????????ļ?ʱ???ĵ?????????//for chinese */
    echo "\"$cleankeyword\",";
    echo "\"$link\",";

    bm_keywordlink_cvsexportvalue($nofollow,false);
    bm_keywordlink_cvsexportvalue($firstonly,false);
    bm_keywordlink_cvsexportvalue($newwindow,false);
    bm_keywordlink_cvsexportvalue($ignorecase,false);
    bm_keywordlink_cvsexportvalue($isaffiliate,false);
    bm_keywordlink_cvsexportvalue($docomments,false);
    bm_keywordlink_cvsexportvalue($zh_CN,true);

    echo "\n";
  }

  /* End of the show */
  die(0);
}

function bm_keywordlink_cvsmenu()
{
  ?>
  <h3>Import and Export CSV</h3>
  <p>To allow for easy of editing of your keywords in a spreadsheet you can save to and from a comma seperatated values (CVS) file.</p>
  <form enctype="multipart/form-data" name=cvs_form method="post" action="">
    <input type="radio" name="action" value="exportcvs" checked />Export to CSV
    <input type="submit" value="Submit"><BR/>
    <input type="radio" name="action" value="importcvs" />Import from CSV file
    <input type="file"  name="upload" />
    <input type="submit" value="Submit">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
  </form>
  <?php
}


function bm_keywordlink_cvsimport()
{

  if (is_uploaded_file($_FILES['upload']['tmp_name']))
  {
    $cvscontent = file($_FILES['upload']['tmp_name'],FILE_IGNORE_NEW_LINES);
    $links = get_option(BM_KEYWORDLINK_OPTION);

      // Keep some statistics
    $cnt = 0; $skip = 0; $replace = 0; $added = 0;

    foreach($cvscontent as $row => $value)
    {
        // Skip the first row
      if ($cnt++ == 0)
      {
          // A little check to see if the file we are importing isn't complete garbage
        if (strstr($value,"Keyword")===FALSE)
        {
          bm_keywordlink_topbarmessage("Not a valid CVS file!");
          return;
        }
        continue;
      }

      list($keyword,$link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN) = explode(",",$value);

        // Strip "" from the beginning and end of the keyword and url
      $keyword = trim($keyword, "\"");
      $link    = trim($link, "\"");

        // Ignore empty keywords, or keywords with no link
      if ($keyword == "" || $link == "")
      {
        $skip++;
        continue;
      }

        // Count how many entries we are replacing
      if ($links[$keyword]) $replace++; else $added++;

        // Input validation
      if ($nofollow) $nofollow = 1;
      if ($firstonly) $firstonly = 1;
      if ($newwindow) $newwindow = 1;
      if ($ignorecase) $ignorecase = 1;
      if ($isaffiliate) $isaffiliate = 1;
      if ($docomments) $docomments = 1;
      if ($zh_CN) $zh_CN = 1;

      $newlinks[$keyword] = implode('|',array($link,$nofollow,$firstonly,$newwindow,$ignorecase,$isaffiliate,$docomments,$zh_CN));
    }

      // If we encountered no errors, merge the new keywords with the existing keywords
    foreach($newlinks as $keyword => $parameters)
      $links[$keyword] = $parameters;

      // Update the wordpress database
    update_option(BM_KEYWORDLINK_OPTION,$links);

    bm_keywordlink_topbarmessage("Import complete (replaced $replace keywords, ignored $skip empty, added $added entries)");

  }
  else
    bm_keywordlink_topbarmessage("Error uploading file");

}

  /* bm_keywordlink_checkcvs
   *
    * Tied to the 'init' action to ensure it runs before any headers are sent
   */
  function bm_keywordlink_checkcvs()
  {
    if ($_POST['action']=='exportcvs')
      bm_keywordlink_cvsexport();
  }


  ?>
