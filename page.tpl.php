<?php 
global $base_url;
global $user;

$node = menu_get_object();
$primaryApp = strtolower(db_query("SELECT value FROM tvs_config WHERE name = 'primary_app'")->fetchField());

$theQS = "?" . $_SERVER['QUERY_STRING'];
$theUrl = str_replace($theQS,"",$_SERVER['REQUEST_URI']);

if ($theUrl == "/") {
    if((isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1")) {
        echo '<script>window.location.href="/my-content";</script>';
    }
    else {
        setcookie("nb", "0", time() - 3600,"/");
        echo '<script>window.location.href="/manage-content";</script>';
    }
}
else if (($theUrl == "/manage-content") && ($is_admin == FALSE)&&(!in_array("Company Administrator", $user->roles)) && ($logged_in == TRUE)) {
    echo '<script>window.location.href="/my-content";</script>';
}

$userEmails = "anonymous@interactivecontentcreator.com";
$all_users = entity_load('user');
foreach($all_users as $value) {
    $user_list = (array)$value;
    if ($user_list['status'] == "1") {
        if ($userEmails !== "") { $userEmails .= ",";}
        $userEmails .= $user_list['mail'];
    }
}

echo '<input type="hidden" id="userEmailList" value="' . $userEmails . '" />';

$SHOWPAD_URL = db_query("SELECT value FROM tvs_config WHERE name = 'sp_url'")->fetchField();
$CLIENT_ID = db_query("SELECT value FROM tvs_config WHERE name = 'client_id'")->fetchField();
$CLIENT_SECRET = db_query("SELECT value FROM tvs_config WHERE name = 'client_secret'")->fetchField();
$SHOWPAD_USER_NAME = db_query("SELECT value FROM tvs_config WHERE name = 'sp_user_name'")->fetchField();
$SHOWPAD_USER_PASSWORD = db_query("SELECT value FROM tvs_config WHERE name = 'sp_user_password'")->fetchField();
$SHOWPAD_DIVISION = db_query("SELECT value FROM tvs_config WHERE name = 'sp_division'")->fetchField();
$SHOWPAD_ALL_TAGS = db_query("SELECT value FROM tvs_config WHERE name = 'sp_all_tags'")->fetchField();

function CallAPI($method, $url, $data = false, $headers = "") {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        case "GET":
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 600);

    if ($headers <> "") {
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }

    $results = curl_exec($curl);

    $jsonStartPoint = strpos($results,"{");
    $results = substr($results,$jsonStartPoint);

    $resultsJSON = json_decode($results);
    curl_close($curl);

    return $resultsJSON;
}

function CallTVSHtmlApi($method, $url, $data = false, $headers = "") {
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        case "GET":
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0); 
    curl_setopt($curl, CURLOPT_TIMEOUT, 600);

    if ($headers <> "") {
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    
    $results = curl_exec($curl);
    curl_close($curl);

    return $results;
}

?>


<div id="page-container">

    <?php if (theme_get_setting('scrolltop_display')): ?>
    <div id="toTop"><i class="fa fa-angle-up"></i></div>
    <?php endif; ?>

    <!-- #header -->

    
   <?php if (($logged_in == TRUE) && (!isset($_GET["co"])) && ((!isset($_COOKIE["nb"])) || ($_COOKIE["nb"] != "1"))){ ?> 
    <header id="header"  role="banner" class="clearfix">
         <?php if($is_admin == TRUE){ ?>
                             <!--<div class="header_margin" style="margin-top:21px;"></div> -->  
         <?php } ?>
        <div class="wrapper">

            <!-- #header-inside -->


            
                <div class="row">
                    <div class="header_top">
                      <div class="header_top_left">
                         <span><a href="<?php echo $base_url; ?>"><?php print t($site_name);?> </a></span>
                      </div>
                      <?php //echo "<pre>".print_r($user,"/n")."</pre>"; ?>
                      <div class="cust-border">
  
                      </div>

                       <div class="header_top_right">
                           <div class="name">
                              <i class="fa fa-user cust-profile"></i>
                              <div class="select_div">
                                 <span>
                                     <a style="color:white;text-decoration:none;" href="/user/<?php echo $user->uid; ?>/edit">
                                        <?php print isset($user->name)?ucfirst($user->name):""; ?>
                                     </a>
                                 </span>
                              </div>  

                           </div>
                           <div class="icon">
                             <i class="fa fa-cog cust-user"></i>
                                <div class="select_div">
                                  <div class="btn-group">
                                      <button type="button" class="btn btn-default dropdown-toggle manage_permission" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="caret"></span>
                                      </button>
                                          <ul class="dropdown-menu display_permission">
                                    <?php if(($is_admin == TRUE)||(in_array("Company Administrator", $user->roles))){ ?>
                                            <li><a href="<?php echo $base_url?>/all-users">Manage Authors</a></li>
                                            <?php if (($primaryApp == "showpad") && ($SHOWPAD_ALL_TAGS != "1") && ($is_admin == TRUE)) { ?>
                                                <!--li><a href="<?php echo $base_url; ?>/approved-showpad-tags">Manage Tags</a></li-->
                                            <?php }                                                                                                                   if ($primaryApp == "showpad") { ?>
                                                <li><a href="<?php echo $base_url; ?>/manage-system-settings">Manage Integration Settings</a></li>
                                              <?php }
                                              if ($is_admin == TRUE) {
                                              ?>
                                              <li><a href="<?php echo $base_url; ?>/manage-lrs-settings">Manage LRS Settings</a></li>
                                              
                                    <?php }} ?>
                                              <li><a href="<?php echo $base_url; ?>/user/logout">Logout</a></li>
                                          </ul>
                                  </div>
                                  </div>
                           </div>
                           <div class="help">
                              <a href="mailto:support@thevalueshift.com"><i class="fa fa-question-circle cust-user" style="cursor:pointer;"></i></a>
                           </div>
                      </div>
                    </div>
                    <?php
                        $createclass = ($_SERVER["REQUEST_URI"] == '/node/add')?"active":""; 
                        $create_content = explode('/', url($_GET['q']));

                    ?>                   
                      <div class="tabs">
                        <ul class="tab-links">
                        <?php if(in_array("Interactive Author", $user->roles)){ 
                            echo '<li class="" id="menuCreateContent">';
                        
                            print "<a class='create_content_button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>Create Content</a></li>";

                            echo '<li style="width:2em;">&nbsp;</li>';
                        } ?>
                        <?php if(($is_admin == TRUE)||(in_array("Company Administrator", $user->roles))){ ?>
                            <li class="<?php echo strpos($_SERVER["REQUEST_URI"],'/manage-content') === 0?'active':''; ?>" id='menuAllContent'><a href="<?php echo $base_url; ?>/manage-content<?php echo (isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") ? "?nb" : "" ?>">All Content</a></li>
                        <?php }                                                                                                                   if(in_array("Interactive Author", $user->roles)){ ?>
                            
                            <li class="<?php echo strpos($_SERVER["REQUEST_URI"],'/shared-content') === 0?'active':''; ?>" id='menuSharedContent'><a href="<?php echo $base_url; ?>/shared-content<?php echo (isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") ? "?nb" : "" ?>">Shared Content</a></li>
                            
                            <li class="<?php echo strpos($_SERVER["REQUEST_URI"],'/my-content') === 0?'active':'' ?>" id='menuMyContent'><a href="<?php echo $base_url; ?>/my-content<?php echo (isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") ? "?nb" : "" ?>">My Content</a></li>
                        <?php } ?>
                        <?php if(in_array("SME", $user->roles)){ ?>
                            <li class="<?php echo strpos($_SERVER["REQUEST_URI"],'/my-knowledge') === 0?'active':''; ?>" id='menuMyKnowledge'><a href="<?php echo $base_url; ?>/my-knowledge<?php echo (isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") ? "?nb" : "" ?>">My Knowledge</a></li>
                        <?php } ?>
                        <?php if(in_array("SME", $user->roles)){ ?>
                            <li class="<?php echo strpos($_SERVER["REQUEST_URI"],'/node/add/knowledge') === 0?'active':''; ?>" id='menuCreateKnowledge'><a href="<?php echo $base_url; ?>/node/add/knowledge<?php echo (isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") ? "?nb" : "" ?>">Create Knowledge</a></li>
                        <?php } ?>
                        <?php if(($is_admin == TRUE)||(in_array("Company Administrator", $user->roles))){ ?>
                            <li class="<?php echo (strpos($_SERVER["REQUEST_URI"],'/viewing-report') === 0)?'active':''; ?>" id='menuViewReports'><a href="/viewing-report">Reports</a></li>
                        <?php } ?>
                        </ul>
                          
                        <?php 
                                
                        ?>
                     
                        <div class="tab-content">
                            <div id="tab1" class="tab active">
                                
                            </div>
                     
                            <div id="tab2" class="tab">
                               
                            </div>
                     
                            <div id="tab3" class="tab">
                                
                            </div>
                     
                            <div id="tab4" class="tab">
                                
                            </div>
                        </div>
                    </div>
               </div> 
        </div>
    </header>
   <?php } else if ((!isset($_GET["co"])) && ($_COOKIE["nb"] != "1")) { ?>
         <div class="margin_header" style="margin-top:100px;"></div>   
   <?php } ?> 
    <!-- EOF: #header -->

    <?php if (($page['banner']) && (!isset($_GET["co"]))) : ?>
    <!-- #banner -->
    <div id="banner" class="clearfix">

        <!-- #banner-inside -->
        <div id="banner-inside" class="clearfix">
            <div class="banner-area">
            <?php print render($page['banner']); ?>
            </div>
        </div>
        <!-- EOF: #banner-inside -->        

    </div>
    <!-- EOF:#banner -->
    <?php endif; ?>

    <?php include 'includes/internal-banner.inc'; ?>

    <!-- #page -->
    <div id="page" class="clearfix">
        <?php
            print "<ul class='dropdown-menu create_content_menu' style='top:inherit;'>";
            print "<li><a href='/node/add/h5p-content?lib=iig'>Menu</a></li>";
            print "<li><a href='/node/add/h5p-content?lib=iv'>Video</a></li>";
            print "<li><a href='/node/add/h5p-content?lib=icp'>Course</a></li>";
            print "<li><a href='/node/add/h5p-content?lib=pre'>Presentation</a></li>";
            print "<li><a href='/node/add/h5p-content?lib=mle'>Micro Learning</a></li>";
            print "<li><a href='/node/add/h5p-content?lib=pdf'>PDF</a></li>";
            //print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=ppt' style='color:black;text-decoration:none;'>PowerPoint</a></td></tr>";
            print "</ul>";


        ?>
        
        <!-- #messages-console -->
        <?php if (($messages) && (!isset($_GET["co"])) && ($_COOKIE["nb"] != "1")) :?>
        <div id="messages-console" class="clearfix">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                    <?php //print $messages; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- EOF: #messages-console -->

        <?php if (($page['top_content']) && (!isset($_GET["co"]))):?>
        <!-- #top-content -->
        <div id="top-content" class="clearfix">
            <div class="container">

                <!-- #top-content-inside -->
                <div id="top-content-inside" class="clearfix">
                    <div class="row">
                        <div class="col-md-12">
                        <?php print render($page['top_content']); ?>
                        </div>
                    </div>
                </div>
                <!-- EOF:#top-content-inside -->

            </div>
        </div>
        <!-- EOF: #top-content -->
        <?php endif; ?>

        <?php if (($page['highlighted']) && (!isset($_GET["co"]))):?>
        <!-- #highlighted -->
        <div id="highlighted">
            <div class="container">

                <!-- #highlighted-inside -->
                <div id="highlighted-inside" class="clearfix">
                    <div class="row">
                        <div class="col-md-12">
                        <?php print render($page['highlighted']); ?>
                        </div>
                    </div>
                </div>
                <!-- EOF:#highlighted-inside -->

            </div>
        </div>
        <!-- EOF: #highlighted -->
        <?php endif; ?>

        <!-- #main-content -->
        <?php
            //  NOTES:
            //      - When showing guestbook first delete the localstorage items for email and full name.
            //      - When they submit the guestbook update gbSubmitTime in the POST field values
            //          Guestbook submission is a FORM POST back to the page they are on.
            //      - Update h5p to not log if username not set in the localtorage items.
            //          We don't want to log the useragent cases...
            //      - Pull out all existing guestbook logic from H5P code.

        $showGuestbook = false;
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (($user->uid === 0) && (strpos($_SERVER["REQUEST_URI"], '/dashboard') !== 0) && (strpos($_SERVER["REQUEST_URI"], '/manage-content') !== 0) && (strpos($_SERVER["REQUEST_URI"], '/my-content') !== 0)) {
            //If the user is not logged in and we are not on the dashboard page, see if
            //it is time to show the guestbook
            if ($requestMethod == "GET") {
                //Check the query string. If em is set do not show the guestbook as
                //H5P will grab the info off of the query string.
                if ((isset($_GET["em"])) && (!empty($_GET["em"]))) {
                    $showGuestbook = false;
            ?>
            <script>
                if ((typeof(H5PIntegration) !== "undefined") && (typeof(H5PIntegration.contents) !== "undefined")) {
                        //We don't call this method here if we are in one of the ShowPad apps.  We call it
                        //in the onShowpadLibLoaded () after we have access to the user information
                        var thisContentId = Object.keys(H5PIntegration.contents).toString().substring(4);
                        var msg = H5P.EventDispatcher.prototype.viewingEvent(thisContentId,"launched");
                    }
            </script>
        
            <?php   
                
                }
                else {$showGuestbook = true; }
            }
            else if ($requestMethod == 'POST') {
                $showGuestbook = false; 
            }
        }

        if ($showGuestbook == true) {
            //Display our guestbook form
        ?>
            <script>
                function validateEmail(email) {
                    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return re.test(email);
                }
                
                function guestbookSubmit() {
                    localStorage.setItem("this_user_email", "");
                    localStorage.setItem("this_user_name", "");
                    if (document.getElementById("userEmail").value === "") {
                        alert("Email address is required");
                        return;
                    }
                    if (!validateEmail(document.getElementById("userEmail").value)) {
                        alert("Please provide a valid email address");
                        return;
                    }
                    if (document.getElementById("userFullName").value === "") {
                        document.getElementById("userFullName").value = document.getElementById("userEmail").value;
                    }
                    localStorage.setItem("this_user_email", document.getElementById("userEmail").value);
                    localStorage.setItem("this_user_name", document.getElementById("userFullName").value);


                    if ((typeof(H5PIntegration) !== "undefined") && (typeof(H5PIntegration.contents) !== "undefined")) {
                        //We don't call this method here if we are in one of the ShowPad apps.  We call it
                        //in the onShowpadLibLoaded () after we have access to the user information
                        var thisContentId = Object.keys(H5PIntegration.contents).toString().substring(4);
                        var msg = H5P.EventDispatcher.prototype.viewingEvent(thisContentId,"launched");
                    }
                    
                    document.getElementById("gbSubmitTime").value = new Date();
                    document.forms["theGuestbook"].submit();
                }
            </script>

            <form method="post" id="theGuestbook" name="theGuestbook">
                <div id='tvsGuestbook' style="display:block;position:absolute;top:20%;left:25%;background:white !important;width:50%;height:50%;z-index:9999;">
                    <div style="margin-left:20px;">
                        <h3>Please enter your name and email address</h3>
                        <br />
                        <span style="font-size:1.2em;">Email Address (required)</span><br />
                        <input type='text' id='userEmail' style="width:80%;" /> *
                        <br /><br />
                        <span style="font-size:1.2em;">Full Name</span><br />
                        <input type='text' id='userFullName' style="width:80%;" />
                        <br /><br />
                        <input type="hidden" name="gbSubmitTime" id="gbSubmitTime" />
                        <div style="width:100%;text-align:center;">
                            <input type=button value='View Content' onclick='guestbookSubmit();' />
                        </div>
                    </div>
                </div>
            </form>
        <?php
        }
        else {
            //Display the main content...
            if ((isset($_GET["em"])) && (!empty($_GET["em"]))) {
                ?>
                <script>
                    localStorage.setItem("this_user_email", "<?php print $_GET["em"] ?>");
                </script>
                
                <?php
            }
            if ((isset($_GET["fn"])) && (!empty($_GET["fn"]))) {
                ?>
                <script>
                    localStorage.setItem("this_user_name", "<?php print $_GET["fn"] ?>");
                </script>
                
                <?php
            }

        ?>
        <div id="main-content">
            <div class="container">
                <div class="row">

                    <?php if ($page['sidebar_first']):?>
                    <aside class="<?php print $sidebar_grid_class; ?>">
                        <!--#sidebar-->
                        <section id="sidebar-first" class="sidebar clearfix">
                        <?php print render($page['sidebar_first']); ?>
                        </section>
                        <!--EOF:#sidebar-->
                    </aside>
                    <?php endif; ?>
                    
                    <?php
                            if ((isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1") && (!isset($_GET["sid"]))) {
                                print "<table id='menutable' style='width:500px;border:0px;margin:0px;padding:0px none white;'><tbody style='border:0px none white;'><tr style='border:0px none white;'><td>";
                                if (strpos($_SERVER["REQUEST_URI"],"/my-content") === 0) {
                                    print "<a style='color:rgb(237, 37, 37);text-decoration: underline;' href='/my-content'>My Content</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                }
                                else {
                                    print "<a style='color:black;text-decoration:none;' href='/my-content'>My Content</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                }

                                
                                print "</td><td style='border:0px none white;width:150px;text-align:left;vertical-align:top;'>";
                                
                                if (strpos($_SERVER["REQUEST_URI"],"/my-content") === 0) {
                                print "<a href='#' onclick='if (document.getElementById(\"createMenu\").style.display===\"none\") {document.getElementById(\"createMenu\").style.display=\"block\";}else{document.getElementById(\"createMenu\").style.display=\"none\";}' style='color:black;text-decoration:none;'>Create Interactive</a>";
                                }
                                else {
                                    print "<a href='#' onclick='if (document.getElementById(\"createMenu\").style.display===\"none\") {document.getElementById(\"createMenu\").style.display=\"block\";}else{document.getElementById(\"createMenu\").style.display=\"none\";}' style='color:rgb(237, 37, 37);text-decoration: underline;'>Create Content</a>";
                                }
                                
                                print "</td><td style='border:0px none white;width:150px;text-align:right;vertical-align:top;'><a href='/user/logout' style='color:black;text-decoration:none;'>Logout</a>";
                                
                                print "</td></tr><tr><td style='padding:0px'>&nbsp;</td><td style='border:0px none white;width:150px;'>";
                                
                                print "<div name='createMenu' id='createMenu' style='display:none;'>";
                                print "<table style='border:0px none white;width: 150px;position: absolute;z-index: 999;background: white !important;'><tbody style='border:0px none white;'>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=iig' style='color:black;text-decoration:none;'>Menu</a></td></tr>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=iv' style='color:black;text-decoration:none;'>Video</a></td></tr>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=icp' style='color:black;text-decoration:none;'>Course</a></td></tr>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=pre' style='color:black;text-decoration:none;'>Presentation</a></td></tr>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=mle' style='color:black;text-decoration:none;'>Micro Learning</a></td></tr>";
                                print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=pdf' style='color:black;text-decoration:none;'>PDF</a></td></tr>";
                                //print "<tr><td style='border:2px solid rgb(237,37,37);'><a href='/node/add/h5p-content?lib=ppt' style='color:black;text-decoration:none;'>PowerPoint</a></td></tr>";
                                print "</tbody></table></div>";
                                
                                print "</td><td style='border:0px none white;width:150px';>&nbsp;</td></tr></tbody></table>";
                            }
                    ?>

                    <section class="col-md-12">

                        <!-- #main -->
                        <div id="main" class="clearfix">
                            <?php
                                print render($title_prefix); 
                                $node = menu_get_object();
            
                                if ($title) {
                                    print '<h4 class="title" id="page-title">';
                                    
                                    if (empty($node)) {
                                        if (strpos(strtolower($_SERVER["REQUEST_URI"]),"lib=iig") > 0) {
                                            print "Interactive Menu";
                                        }
                                        else if (strpos(strtolower($_SERVER["REQUEST_URI"]),"lib=iv") > 0) {
                                            print "Interactive Video";
                                        }
                                        else if (strpos(strtolower($_SERVER["REQUEST_URI"]),"lib=ic") > 0) {
                                            print "Interactive Course";
                                        }
                                        else {
                                            print $title;
                                        }
                                    }
                                    else {
                                        print $node->title;
                                    }

                                    print '</h4>';
                                }
                            
                                
                                $briefDescription = '';
                                $tagId = '';
                                $contentTitle = '';
                                $nodeId = '';
                                $tagName = '';
                                if ( !empty($node) ) {
                                    $nodeId = $node->nid;
                                    $contentTitle = $node->title;
                                    $briefDescription = db_query('SELECT field_brief_description_value FROM field_data_field_brief_description WHERE entity_id = :nid', array(':nid' => $node->nid))->fetchField();
                                    $tagName = db_query('SELECT TTD.name FROM taxonomy_index TI INNER JOIN taxonomy_term_data TTD on TTD.tid = TI.tid  WHERE TI.nid = :nid', array(':nid' => $node->nid))->fetchField();
                                }

                                $authorName = "";
                                if ($node != null) {
                                    $authorName = $author = user_load($node->uid)->name;
                                }

                                if ((strpos($_SERVER["REQUEST_URI"],'/viewing-report') === 0) || (strpos($_SERVER["REQUEST_URI"], '/admin/content') === 0) || (strpos($_SERVER["REQUEST_URI"], '/node/add') === 0) || (strpos($_SERVER["REQUEST_URI"], '/dashboard') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/shared-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-knowledge') === 0) || (strpos($_SERVER["REQUEST_URI"],'/approved-showpad-tags') === 0) || (strpos($_SERVER["REQUEST_URI"],'/all-users') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/admin/people/create') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-lrs-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-system-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/delete') > 0) || (strpos($_SERVER["REQUEST_URI"],'/clone') > 0) || (isset($_GET["co"])) || (strpos($_SERVER["REQUEST_URI"], '/manage-user') === 0)) {
                                    //If we want to add anything to these pages...
                                    
                                }
                                else if (($user->uid > 0) && (strpos($_SERVER["REQUEST_URI"],'/edit') === false)){
                                    print "<div id='nodeExtraInfo'>";
                                    
                                    print "</div>"; 
                                    
                                    if ($node->type == "knowledge") {
                                        print "<h4>Make this interactive?</h4>";
                                        print "<input type='radio' name='makeCC' value='yes'> Yes&nbsp;&nbsp;<input type='radio' name='makeCC' value='no' checked> No<br /><br />";
                                    }
                                    else if (($node->uid === $user->uid) || (in_array("Company Administrator", $user->roles)) || ($is_admin == TRUE)) {
                                        print "<div id='publishContentToShowPadDiv' class='publishContentToShowPadDiv' style='float:right;width:20%;margin-top:40px;'>";
                                        
                                        print "<p><button id='editContentButton' type='button' class='actionButtons' onclick='editContent()'>Edit</button></p>";
                                        
                                        print "<p><button id='copyContentButton' type='button' class='actionButtons' onclick='copyContent()' style='background:#00a67c !important;'>Copy</button></p>";
                                        
                                        print "<p><button id='shareContentButton' type='button' class='actionButtons' onclick='showSharingOptions()' style='background:#00a67c !important;'>Share</button></p>";
                                        
                                        print "<style>table,table tr,table td {border:2px solid white;padding:0px;}</style>";
                                        print "<table id='sharingOptions' style='border: 2px solid #00a67c;
    border-collapse: separate;
    padding: 10px;
    text-align: center;
    right:0px;
    z-index: 1000;
    width: 40%;
    background: #fff;
    position: absolute;
    margin: 0px;
    display: none;
    border-radius: 20px;'>";
                                        
                                        print "<tr><td style='float:right;border-right:0;'><span style='float:right;cursor:pointer;' onclick='hideSharingOptions();'>Close</span></td></tr>";
                                        print "<tr><td style='border-right:0;'>
                                           
                                        <span style='float:left;'>
                                        Enter email address of individual that you are sending this to:<br />
                                        <input type='text' style='width:100%' id='shareToEmail' />
                                        </span>
                                        </td>
                                        </tr>
                                        <tr><td style='border-right:0;'>
                                            
                                        <button type='button' class='actionButtons' onclick='createShareLink()'>Create Link</button></td></tr>";
                                        print "<tr><td style='border-right:0;'>";
                                        print "<br />
                                        <br /><textarea id='shareInfoTA'  style='width:100%;font-size:0.8em;' rows='4'></textarea><button class='actionButtons' onclick='copyShareLinkToClipboard();'>Copy to Clipboard</button><br />";

                                        print "</td></tr></table>";
                                        
                                        print "<p><button id='publishContentButton' type='button' class='actionButtons' onclick='showPublishingOptions()' style='background:#00a67c !important;'>Publish</button></p>";
                                        print "<p><button id='integrateContentButton' type='button' class='actionButtons' onclick='showIntegrateOptions()' style='background:#00a67c !important;'>Integrate</button></p>";
                                        
                                        print "<style>table,table tr,table td {border:2px solid white;padding:0px;}</style>";
                                        print "<table id='integrateOptions' style='border: 2px solid #00a67c;
    border-collapse: separate;
    padding: 10px;
    text-align: center;
    right:0px;
    z-index: 1000;
    width: 40%;
    background: #fff;
    position: absolute;
    margin: 0px;
    display: none;
    border-radius: 20px;'>";
                                        
                                        print "<tr><td style='float:right;border-right:0;'><span style='float:right;cursor:pointer;' onclick='hideIntegrateOptions();'>Close</span></td></tr>";
                                        print "<tr><td style='border-right:0;'><button type='button' class='actionButtons' onclick='displayDirectDialog()'>Direct Url</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='actionButtons' onclick='displayEmbedDialog()'>Embed</button></td></tr>";
                                        print "<tr><td style='border-right:0;'>";
                                        print "<br />
                                            <strong style='float:left;'>Integration Key (OPTIONAL)</strong><br />
                                            <table style='font-size:0.7em;text-align:left;'><tbody><tr>
                                                <td>Email
                                                </td>
                                                <td>
                                                    Replace EMAILADDR in the generated URL with the viewer's email address.
                                                </td>
                                            <tr>
                                                <td>Full Name
                                                </td>
                                                <td>
                                                    Replace FULLNAME in the generated URL with the viewer's full name.
                                                </td>
                                            </tr>
                                            </tr></tbody></table>
                                        <br /><textarea id='integrateInfoTA'  style='width:100%;font-size:0.8em;' rows='4'></textarea><button class='actionButtons' onclick='copyToClipboard();'>Copy to Clipboard</button><br />";

                                        print "</td></tr></table>";
                                        
                                        //print "</table>";
                                        
                                        if ($primaryApp == "showpad") {
                                            
                                            //Call the ShowPad API using a hard-coded username and password.
                                            $postData = "grant_type=password&username=" . $SHOWPAD_USER_NAME . "&password=" . $SHOWPAD_USER_PASSWORD;
                                            $postData .= "&client_id=" . $CLIENT_ID;
                                            $postData .= "&client_secret=" . $CLIENT_SECRET;
                                            $loginAPICall = $SHOWPAD_URL . "api/v3/oauth2/token";

                                            $loginJSON = callAPI("POST", $loginAPICall, $postData, "");

                                            $accessToken = $loginJSON->access_token;
                                            $authorizationHeader = "Bearer " . $accessToken;

                                            $headers = array( 
                                                "Authorization: " . $authorizationHeader,
                                            ); 
                                            
                                            //Call into ShowPad to get the list of divisions
                                            $getDivisionListAPICall = $SHOWPAD_URL . 'api/v3/divisions.json?fields=id%2Cname';
                                            $getDivisionListJSON = callAPI("GET", $getDivisionListAPICall, null, $headers);

                                            print "<style>table,table tr,table td {border:2px solid white;}</style>";

                                            if ($getDivisionListJSON->response->count > 0) {
                                                print "<table id='publishingOptions' style='border: 2px solid #00a67c;
    border-collapse: separate;
    padding: 10px;
    text-align: left;
    right: 0px;
    z-index: 1000;
    width: 40%;
    background: #fff;
    position: absolute;
    margin: 0px;
    display: none;
    border-radius: 20px;'>";
                                                print "<tr><td></td><td style='float:right;border-right:0;'><span style='float:right;cursor:pointer;' onclick='hidePublishingOptions();'>Close</span></td></tr>";
                                                
                                                print "<tr><td style='border-right:0;'><strong>ShowPad Library:</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                                for ($i = 0; $i < $getDivisionListJSON->response->count; $i++) {
                                                    if ($getDivisionListJSON->response->items[$i]->id == $SHOWPAD_DIVISION) {
                                                        print $getDivisionListJSON->response->items[$i]->name;
                                                    }
                                                }
                                                print "</td></tr>";
                                            }
                                            
                                            
                                            
                                            print "<tr><td style='border-right:0;'><button type='button' class='actionButtons' onclick='pushToShowPad()'>Publish</button></td></tr>";
                                            
                                            print "</table>";
                                        }
                                            
                                    }
                                    else if (db_query('SELECT field_shared_value FROM field_data_field_shared WHERE entity_id = :nid', array(':nid' => $node->nid))->fetchField() == 1) {
                                        print "<div id='publishContentToShowPadDiv' class='publishContentToShowPadDiv' style='float:right;width:40%;margin-top:40px;'>";
                                        print "<p><button id='copyContentButton' type='button' class='actionButtons' onclick='copyContent()'>Copy</button></p>";
                                        //print "</div>";
                                    }
                                }
            
            
            if ((strpos($_SERVER["REQUEST_URI"],'/viewing-report') === 0) || (strpos($_SERVER["REQUEST_URI"], '/admin/content') === 0) || (strpos($_SERVER["REQUEST_URI"], '/node/add') === 0) || (strpos($_SERVER["REQUEST_URI"], '/dashboard') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/shared-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-knowledge') === 0) || (strpos($_SERVER["REQUEST_URI"],'/approved-showpad-tags') === 0) || (strpos($_SERVER["REQUEST_URI"],'/all-users') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/admin/people/create') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-lrs-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-system-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/edit') > 0) || ($requestMethod == 'POST') || ((isset($_GET["em"])) && (!empty($_GET["em"]))) || (isset($_GET["co"])) || (strpos($_SERVER["REQUEST_URI"], '/manage-user') === 0)) {
                            ?>
                            </div><div id="viewContentDiv" class="viewContentDiv" style="width:100%;height:90%">
                        <?php } else { ?>
                            </div><div id="viewContentDiv" class="viewContentDiv" style="float:left;width:75%">
                        <?php } ?>
                            <?php print render($title_suffix); ?>

                            <!-- #tabs -->
                            <?php if ($tabs):?>                             

                                <div class="tabs">
                                <?php 
                                    if ((strpos($_SERVER["REQUEST_URI"],'/viewing-report') !== 0) && (strpos($_SERVER["REQUEST_URI"],'/dashboard') !== 0)) {
                                        print render($tabs);
                                    }
                                ?>
                                </div>
                            <?php endif; ?>
                            <!-- EOF: #tabs -->
                            <?php //echo $node->nid; ?>    
                            <?php print render($page['help']); ?>


                            <?php if (theme_get_setting('frontpage_content_print') || !drupal_is_front_page()):?> 
                            <?php print render($page['content']); ?>
                            <?php print $feed_icons; ?>
                            <?php endif; ?>
                            <?php
                            if ((in_array("Interactive Author", $user->roles)) || ($is_admin == TRUE)||(in_array("Company Administrator", $user->roles))){
                                if ((strpos($_SERVER["REQUEST_URI"],'/viewing-report') === 0) || (strpos($_SERVER["REQUEST_URI"], '/admin/content') === 0) || (strpos($_SERVER["REQUEST_URI"], '/node/add') === 0) || (strpos($_SERVER["REQUEST_URI"], '/dashboard') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/shared-content') === 0) || (strpos($_SERVER["REQUEST_URI"],'/my-knowledge') === 0) || (strpos($_SERVER["REQUEST_URI"],'/approved-showpad-tags') === 0) || (strpos($_SERVER["REQUEST_URI"],'/all-users') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/user') === 0) || (strpos($_SERVER["REQUEST_URI"],'/admin/people/create') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-lrs-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/manage-system-settings') === 0) || (strpos($_SERVER["REQUEST_URI"],'/edit') > 0) || (strpos($_SERVER["REQUEST_URI"],'/delete') > 0) || (strpos($_SERVER["REQUEST_URI"],'/clone') > 0) || (strpos($_SERVER["REQUEST_URI"], '/manage-user') === 0)) {
                                if (strpos($_SERVER["REQUEST_URI"],'/node/add/h5p-content?lib=ppt') === 0) {
                                ?>
                                    <script>
                                        H5P.jQuery("#edit-submit").hide();
                                        if (H5PEditor !== undefined) {
                                            H5PEditor.readyToSave = false;
                                        }
                                    </script>
                                    <br />
                                    <p>
                                        <em>This process may take several minutes.  After selecting <strong>Save</strong>, please check back in 20 minutes and your content should be available.</em><br /><button id='emailWhenDoneButton' type="button" class="actionButtons" onclick="emailWhenDone()">Save</button>
                                    </p>
                        
                                <?php
                                    }
                                }
                                else {
                                
                            }} ?>
                        </div>
                        <!-- EOF:#main -->

                    </section>

                    <?php if ($page['sidebar_second']):?>
                    <aside class="<?php print $sidebar_grid_class; ?>">
                        <!--#sidebar-->
                        <section id="sidebar-second" class="sidebar clearfix">
                        <?php print render($page['sidebar_second']); ?>
                        </section>
                        <!--EOF:#sidebar-->
                    </aside>
                    <?php endif; ?>

                </div>

            </div>
        </div>
        <?php
        }
        ?>
        <!-- EOF:#main-content -->

    </div>
    <!-- EOF: #page -->

    <?php if (($page['highlighted_bottom_left'] || $page['highlighted_bottom_right']) && (!isset($_GET["co"]))):?>
    <!-- #highlighted-bottom -->
    <div id="highlighted-bottom">
        <div id="highlighted-bottom-transparent-bg"></div>

            <div class="container">

                <!-- #highlighted-bottom-inside -->
                <div id="highlighted-bottom-inside" class="clearfix">
                    <div class="row">
                        <?php if ($page['highlighted_bottom_left']):?>
                        <div class="<?php print $highlighted_bottom_left_grid_class?>">
                            <div id="highlighted-bottom-left">
                                <?php print render($page['highlighted_bottom_left']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($page['highlighted_bottom_right']):?>
                        <div class="<?php print $highlighted_bottom_right_grid_class?>">
                            <div id="highlighted-bottom-right">                        
                            <?php print render($page['highlighted_bottom_right']); ?>
                            </div>
                        </div>
                        <?php endif; ?>                    
                    </div>
                </div>
                <!-- EOF:#highlighted-bottom-inside -->

            </div>

    </div>
    <!-- EOF: #highlighted-bottom -->
    <?php endif; ?>

    <?php if (($page['bottom_content']) && (!isset($_GET["co"]))):?>
    <!-- #bottom-content -->
    <div id="bottom-content" class="clearfix">
        <div class="container">

            <!-- #bottom-content-inside -->
            <div id="bottom-content-inside" class="clearfix">
                <div class="row">
                    <div class="col-md-12">
                    <?php print render($page['bottom_content']); ?>
                    </div>
                </div>
            </div>
            <!-- EOF:#bottom-content-inside -->

        </div>
    </div>
    <!-- EOF: #bottom-content -->
    <?php endif; ?>

    <?php if (($page['footer_top']) && (!isset($_GET["co"]))):?>
    <!-- #footer-top -->
    <div id="footer-top" class="clearfix">
        <div class="container">

            <!-- #footer-top-inside -->
            <div id="footer-top-inside" class="clearfix">
                <div class="row">
                    <div class="col-md-12">
                    <?php print render($page['footer_top']); ?>
                    </div>
                </div>
            </div>
            <!-- EOF:#footer-top-inside -->

        </div>
    </div>
    <!-- EOF: #footer-top -->
    <?php endif; ?>    

    <?php if (($page['footer_first'] || $page['footer_second'] || $page['footer_third'] || $page['footer_fourth']) && (!isset($_GET["co"]))):?>
    <!-- #footer -->
    <footer id="footer" class="clearfix">
        <div class="container">

            <div class="row">
                <div class="col-sm-3">
                    <?php if ($page['footer_first']):?>
                    <div class="footer-area">
                    <?php print render($page['footer_first']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-sm-3">
                    <?php if ($page['footer_second']):?>
                    <div class="footer-area">
                    <?php print render($page['footer_second']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-sm-3">
                    <?php if ($page['footer_third']):?>
                    <div class="footer-area">
                    <?php print render($page['footer_third']); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-sm-3">
                    <?php if ($page['footer_fourth']):?>
                    <div class="footer-area">
                    <?php print render($page['footer_fourth']); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </footer> 
    <!-- EOF #footer -->
    <?php endif; ?>

    <?php if (($page['sub_footer_left'] || $page['footer']) && (!isset($_GET["co"]))):?>
    <div id="subfooter" class="clearfix">
        <div class="container">

            <!-- #subfooter-inside -->
            <div id="subfooter-inside" class="clearfix">
                <div class="row">
                    <div class="col-md-4">
                        <!-- #subfooter-left -->
                        <?php if ($page['sub_footer_left']):?>
                        <div class="subfooter-area left">
                        <?php print render($page['sub_footer_left']); ?>
                        </div>
                        <?php endif; ?>
                        <!-- EOF: #subfooter-left -->
                    </div>
                    <div class="col-md-8">
                        <!-- #subfooter-right -->
                        <?php if ($page['footer']):?>
                        <div class="subfooter-area right">
                        <?php print render($page['footer']); ?>
                        </div>
                        <?php endif; ?>
                        <!-- EOF: #subfooter-right -->
                    </div>
                </div>
            </div>
            <!-- EOF: #subfooter-inside -->

        </div>
    </div><!-- EOF:#subfooter -->
    <?php endif; ?>
</div>

<!-- EOF:#page-container -->
<div id='approvedContent' style="display:none;position:fixed;top:20px;left:200px;border: 5px solid gray;background:white !important;width:650px;height:550px;z-index:9999">
    <div style="font-weight:700;width: 100%;background:gray;color:white;" onclick="document.getElementById('approvedContent').style.display='none';top.document.getElementById('approvedDamContentFrame').src = '';"><span id="previewFrameTitle" style="text-align:left;"></span><span style="cursor:pointer;float:right;color:gray;padding-right:15px;">CLOSE</span></div>
<?php
if ((strpos($_SERVER["REQUEST_URI"],'/node') === 0) && ($primaryApp === "showpad")) { 
?>
    <iframe src="" width="100%" height="510px" frameborder="no" id="approvedDamContentFrame"></iframe>
<?php
}
?>
</div>
    
<div id='previewWindow' style="display:none;position:fixed;top:20%;left:30%;border: 5px solid gray;background: white !important;">
    <div style="font-weight:700;width: 100%;height:25px;background:gray;color:white;" onclick="document.getElementById('previewWindow').style.display='none';"><span id="previewFrameTitle" style="text-align:left;"></span><span style="cursor:pointer;float:right;padding-right:15px;">CLOSE</span></div>
    <style>::-webkit-scrollbar {width: 10px;height: 10px;display: block; }::-webkit-scrollbar-track-piece  {background-color: #3b3b3b;-webkit-border-radius: 5px;}::-webkit-scrollbar-thumb:vertical {height: 20px;background-color: #fff;border: 1px solid #eee;-webkit-border-radius: 6px;}</style><iframe id='previewFrame' src="" width="510" height="340" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
</div>

<div id='divisionList' style="display:none;position:fixed;top:50px;left:200px;border: 5px solid gray;background:white !important;width:750px;height:500px;z-index:9999">
</div>

<script>
<?php
echo 'localStorage.setItem("tvs_primary_app", "' . $primaryApp . '");';    
?>
    
<?php if ((isset($_GET["co"])) && (strpos($_SERVER["REQUEST_URI"],'/node') === 0)) { ?>
    document.getElementsByClassName("title")[0].remove();
    for (var i = document.getElementsByClassName("form-item").length; i > 0; i--) {
        if ((document.getElementsByClassName("form-item")[i-1].className.indexOf("h5p") === -1) && (document.getElementsByClassName("form-item")[i-1].id.indexOf("h5p") === -1)) {
            document.getElementsByClassName("form-item")[i-1].remove();
        }
    }
    for (i = document.getElementsByClassName("vertical-tabs").length; i > 0; i--) {
        document.getElementsByClassName("vertical-tabs")[i-1].remove();
    }
    try {
        H5P.jQuery (".tabs").remove();
    }
        catch (ex) {}
<?php }

    else { ?>
        try {
            H5P.jQuery(".tabs.primary").remove();
        }
        catch (ex) {}
        
<?php } ?>
    
var bodyElement = document.body;
document.getElementById("skip-link").style.display="none";
if (bodyElement.className.indexOf("not-logged-in") === -1) {
    var topRegions = document.getElementsByClassName("region-page-top");
    for (var j=0;j<topRegions.length;j++){
        topRegions[j].parentNode.removeChild(topRegions[j]);
    }
    if(window.location.href.indexOf("/node") > -1) {
        H5P.jQuery('.field').remove();
        if ((document.getElementById("publishContentToShowPadDiv") !== null) && (document.getElementById("publishContentToShowPadDiv") !== undefined) && (screen.height >= 1200)) {
                document.getElementById("publishContentToShowPadDiv").style.float="left";
                document.getElementById("publishContentToShowPadDiv").style.width="100%";
                document.getElementById("viewContentDiv").style.float="";
                document.getElementById("viewContentDiv").style.width="90%";
            }
    }
    if (screen.height >= 1500) {
        document.body.style.zoom = "160%";
    }
    else if (screen.height >= 1200) {
        document.body.style.zoom = "130%";
    }
    else if (screen.height >= 1000) {
        document.body.style.zoom = "110%";
    }
    
    try {
        if (parent.window.location.href.indexOf("/sites/all/libraries/tvs/view_series") > -1) {
            if (document.getElementById("publishContentToShowPadDiv") !== null) {document.getElementById("publishContentToShowPadDiv").style.display = "none";}
            if (document.getElementById("viewContentDiv") !== null) {document.getElementById("viewContentDiv").style.width = "100%";}
            if (document.getElementById("viewContentDiv") !== null) {document.getElementById("viewContentDiv").style.float = "none";}
            if (document.getElementById("menutable") !== null) {document.getElementById("menutable").style.display = "none";}
            if (document.getElementById("editContentButton") !== null) { document.getElementById("editContentButton").style.display = "none";}
            if (document.getElementById("page-title") !== null) {document.getElementById("page-title").style.display = "none";}
            if (document.getElementById("admin-menu") !== null) {document.getElementById("admin-menu").style.display = "none";}
        }
        else if (parent.window.location.href.indexOf("/node/add/h5p-content") > -1) {
            document.getElementById("menuCreateContent").className = "active";
        }
    <?php if ($node->uid === $user->uid) {
        print 'else if (parent.window.location.href.indexOf("/node") > -1) {
            debugger;
            document.getElementById("menuMyContent").className = "active";
        }';
    }
    else if (db_query('SELECT field_shared_value FROM field_data_field_shared WHERE entity_id = :nid', array(':nid' => $node->nid))->fetchField() == 1) {
        print 'else if (parent.window.location.href.indexOf("/node") > -1) {
            debugger;
            document.getElementById("menuSharedContent").className = "active";
        }';
    }
    else if ((in_array("Company Administrator", $user->roles)) || ($is_admin == TRUE)) {
        print 'else if (parent.window.location.href.indexOf("/node") > -1) {
            debugger;
            document.getElementById("menuAllContent").className = "active";
        }';
    }
    ?>
        
        
    }
    catch (ex) {}
    
    
}
else {
    $(document.getElementById("user-login-form")).children().find("div.item-list").hide();
    if(window.location.href.indexOf("/node") > -1) {
        var loginForm = document.getElementById("block-user-login");
        if (loginForm !== null) {
            
            loginForm.parentNode.removeChild(loginForm);
            
            if (document.getElementById("main-content") !== null) {document.getElementById("main-content").style.position = "absolute";document.getElementById("main-content").style.top = "0px";document.getElementById("main-content").style.left = "0px";}
        }
    }
    else {
        if (document.getElementById("main-content") !== null) {document.getElementById("main-content").style.display = "none";}
    }
    if (document.getElementById("page-title") !== null) {document.getElementById("page-title").style.display = "none";}
    if (document.getElementById("nodeExtraInfo") !== null) {document.getElementById("nodeExtraInfo").style.display = "none";}
    if (document.getElementById("footer") !== null) {document.getElementById("footer").style.display = "none";}
    
    if(window.location.href.indexOf("/node") > -1) {
        H5P.jQuery('.field').remove();
    }
    
    try {
        if (parent.window.location.href.indexOf("/sites/all/libraries/tvs/view_series") > -1) {
            if (document.getElementById("publishContentToShowPadDiv") !== null) {document.getElementById("publishContentToShowPadDiv").style.display = "none";}
            if (document.getElementById("viewContentDiv") !== null) {document.getElementById("viewContentDiv").style.width = "100%";}
            if (document.getElementById("viewContentDiv") !== null) {document.getElementById("viewContentDiv").style.float = "none";}
            if (document.getElementById("menutable") !== null) {document.getElementById("menutable").style.display = "none";}
            if (document.getElementById("editContentButton") !== null) { document.getElementById("editContentButton").style.display = "none";}
            if (document.getElementById("page-title") !== null) {document.getElementById("page-title").style.display = "none";}
            if (document.getElementById("admin-menu") !== null) {document.getElementById("admin-menu").style.display = "none";}
        }
    }
    catch (ex) {}
}

<?php if((isset($_COOKIE["nb"])) && ($_COOKIE["nb"] == "1")) { ?>
    if (document.getElementById("admin-menu") !== null) {document.getElementById("admin-menu").style.display = "none";}
<?php } ?>
    
for (var i=0; i<document.getElementsByClassName('appImg').length;i++) {
    if (i > 0) { document.getElementsByClassName('appImg')[i].style.display='none'; }
}

if(window.location.href.indexOf("/node") > -1) {
    if (document.getElementById('ccBtnList') !== null) {
        document.getElementById('createContentBtn').style.display='none';
        document.getElementById('mngContentBtn').style.display='none';
        document.getElementById('logoutBtn').style.display='none';
    }
}
    
if(window.location.href.indexOf("overlay=") > -1) {
    document.getElementById('tvs').style.display='none';
}
    
if ((document.getElementsByClassName("tabs primary").length === 1) && (window.location.href.indexOf("/user/") === -1)) {
    var newLIReport = document.createElement("li");
    newLIReport.innerHTML = "<a href='" + getReportUrl() + "'>Report</a>";
    document.getElementsByClassName("tabs primary")[0].appendChild(newLIReport);
    
    <?php
        if ($primaryApp !== "showpad") {
    ?>
    
    var newLIEmbed = document.createElement("li");
    newLIEmbed.innerHTML = "<a href='#' onclick='displayEmbedDialog()'>Embed</a>";
    document.getElementsByClassName("tabs primary")[0].appendChild(newLIEmbed);
    
    var newLIDownload = document.createElement("li");
    newLIDownload.innerHTML = "<a href='#' onclick='downloadContent()'>Download</a>";
    document.getElementsByClassName("tabs primary")[0].appendChild(newLIDownload);
    <?php
        }
    ?>
}
else if ((document.getElementsByClassName("tabs primary").length === 1) && (window.location.href.indexOf("/user/") > -1)) {
    var newLIPerm = document.createElement("li");
    newLIPerm.innerHTML = "<a href='#' onclick='displayUserPermissions()'>Permissions</a>";
    document.getElementsByClassName("tabs primary")[0].appendChild(newLIPerm);
}

<?php
if ((strpos($_SERVER["REQUEST_URI"],'/manage-content') === 0) && (strpos($_SERVER["REQUEST_URI"],'/my-content') === 0)) { 
?>
for (var i = 0; i < document.getElementsByClassName("views-field-title").length; i++ ) {
    var myself = document.getElementsByClassName("views-field-title")[i];
    if ((myself.childNodes[1].href !== undefined) && (myself.childNodes[1].href.indexOf("/node/") > -1)) {
        var newBtn = document.createElement("img");
        newBtn.setAttribute("src","/sites/default/files/images/preview.png");
        newBtn.setAttribute("style","float:right;padding-top:-10px;cursor:pointer");
        var onClickAction = "previewContent('" + getNode(myself.childNodes[1].href) + "','" + myself.childNodes[1].innerText + "');";
        newBtn.setAttribute("onclick",onClickAction);
        myself.insertBefore(newBtn,myself.firstChild);
    }
}
<?php } 
?>
    
function emailWhenDone() {
    debugger;
    var emailToNotify = "";
    if ((H5PEditor == undefined) || (H5PEditor.readyToSave == false)) {
        alert("Please wait until the file is fully uploaded");
        return false;
    }
    
    var contentId = 0;
    var self = window.processingObj;
    var processedFilePath = localStorage.getItem("fileBeingConverted");

    var convertFileJSON = { 
        filePath: processedFilePath,
        tvsNode: contentId,
        emailToNotify: emailToNotify
    }

    var convertFileUrl = "/sites/all/libraries/tvs/add_to_file_processing_queue.php";

    if (this.$file !== undefined && this.$file.length !== 0) {
        this.$file.html('<div class="h5peditor-uploading h5p-throbber">' + H5PEditor.t('core', 'damProcessing') + '</div>');
    }

    H5P.jQuery.ajax({ 
        type: "POST",
        data: convertFileJSON,
        dataType: "json",
        url: convertFileUrl,
        success: function(data){

            },
        error: function(data) {

            }
        });
    
    H5P.jQuery("#edit-submit").click();
    return true;
    
}

function previewContent(node,contentTitle) {
    document.getElementById("previewFrame").src = "";
    document.getElementById("previewFrameTitle").innerText = contentTitle;
    document.getElementById("previewFrame").src = "https://" + window.location.hostname + "/h5p/embed/" + node.toString();
    document.getElementById("previewWindow").style.display="block";
}
    
function pushToShowPad() {
    <?php
        $node = menu_get_object();
        $briefDescription = '';
        $contentTitle = '';
        $nodeId = '';
        $revisionId = 0;
        if ( !empty($node) ) {
            $nodeId = $node->nid;
            $revisionId = $node->vid;
            $contentTitle = $node->title;
            $briefDescription = db_query('SELECT field_brief_description_value FROM field_data_field_brief_description WHERE entity_id = :nid', array(':nid' => $node->nid))->fetchField();
        }

        echo "var briefDesc = '" . str_replace("'", "&quot;",$briefDescription) . "';";
        echo "var contentTitle = '" . str_replace("'", "&quot;",$contentTitle) . "';";
        echo "var nodeId = '" . $revisionId . "';";
    ?>
    
    var tagIds = "";
    
    var contentInfo = { 
        tvsNode: nodeId,
        tvsTitle: contentTitle,
        tvsDesc: briefDesc,
        tvsTag: tagIds,
        divisionId: "<?php print $SHOWPAD_DIVISION; ?>",
        tvsUrl: window.location.href
    }

    var showPadPublish = "/sites/all/libraries/tvs/post_to_showpad.php";

    H5P.jQuery.ajax({ 
        type: "POST",
        data: contentInfo,
        dataType: "json",
        url: showPadPublish,
        success: function(data){
            
            },
        error: function(data) {

            }
        });
    
        alert("Content published to ShowPad");
}
    
function getNode(src) {
    if (src === undefined) { src = window.location.href;}
   if (src.indexOf("node") > -1) {
          var nodeIdPost = src.lastIndexOf("/");
          var nodeId = src.substring(nodeIdPost+1).replace("#","");
          if (isNaN(nodeId)) {
              return 0;
          }
          else {
              return nodeId;
          }
   }
   return 0;
}
    
function editContent() {
    window.location = window.location + "/edit";
}
    
function copyContent() {
    var nodeId = getNode();
    window.location = "/node/" + nodeId + "/clone/confirm";
}
    
    
function showSharingOptions() {
    document.getElementById("sharingOptions").style.display='table';
    document.getElementById("shareContentButton").style.display="none";
}
    
function hideSharingOptions() {
    document.getElementById("sharingOptions").style.display='none';
    document.getElementById("shareContentButton").style.display="block";
}
    
function showIntegrateOptions() {
    document.getElementById("integrateOptions").style.display='table';
    document.getElementById("integrateContentButton").style.display="none";
}
    
function hideIntegrateOptions() {
    document.getElementById("integrateOptions").style.display='none';
    document.getElementById("integrateContentButton").style.display="block";
}
 
function showPublishingOptions() {
    document.getElementById("publishingOptions").style.display='table';
    document.getElementById("publishContentButton").style.display="none";
}
    
function hidePublishingOptions() {
    document.getElementById("publishingOptions").style.display='none';
    document.getElementById("publishContentButton").style.display="block";
}
    
function toTitleCase(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function getReportUrl () {
    var currentNodeId = getNode();
   if ((currentNodeId === 0) || (isNaN(currentNodeId))) {
       return "/viewing-report";

   }
  else {
       return "/viewing-report?cid=" + currentNodeId;
  }
}   

function displayUserPermissions() {
    alert("Coming soon...");
}

var shareUrlExtras = "";
function displayEmbedDialog() {
    //var embedCard = document.getElementById("integrateInfo");
    var embedCardTA = document.getElementById("integrateInfoTA");
    var embedCardHtml = '<iframe allowfullscreen="allowfullscreen" height="100%" id="previewFrame"';
    embedCardHtml += 'src="https://' + window.location.host.toLowerCase() + '/h5p/embed/';
    embedCardHtml += getNode() + '?em=EMAILADDR&fn=FULLNAME" width="100%">'

    embedCardTA.innerText = embedCardHtml;
    //embedCard.style.display = "block";
}

function displayDirectDialog() {
    //var embedCard = document.getElementById("integrateInfo");
    var embedCardTA = document.getElementById("integrateInfoTA");
    var embedCardHtml = 'https://' + window.location.host.toLowerCase() + '/node/' + getNode() + '?em=EMAILADDR&fn=FULLNAME';

    embedCardTA.innerText = embedCardHtml;
    //embedCard.style.display = "block";
}
       
function copyToClipboard(text) {
    var embedCardTA = document.getElementById("integrateInfoTA");
    window.prompt("Copy to clipboard: Ctrl+C, Enter", embedCardTA.value);
}
 
    
function createShareLink() {
    var shareLinkTA = document.getElementById("shareInfoTA");
    var shareLink = 'https://' + window.location.host.toLowerCase() + '/node/' + getNode();
    var emailToUse = document.getElementById("shareToEmail").value;
    
    if (emailToUse !== "") {
        shareLink += "?em=" + emailToUse;
    }

    shareLinkTA.innerText = shareLink;
}
       
function copyShareLinkToClipboard(text) {
    var shareLinkTA = document.getElementById("shareInfoTA");
    window.prompt("Copy to clipboard: Ctrl+C, Enter", shareLinkTA.value);
}
    
function downloadContent() {
    <?php
        $node = menu_get_object();
        $nodeId = '';
        $revisionId = 0;
        if ( !empty($node) ) {
            $revisionId = $node->vid;
        }

        echo "var revisionId = '" . $revisionId . "';";
    ?>

    var downloadPackage = "/sites/all/libraries/tvs/downloadPackage.php?cid=" + revisionId;
    window.open(downloadPackage);
}
    
function readDeviceOrientation() {
    //Example taken from http://www.williammalone.com/articles/html5-javascript-ios-orientation/
    if (Math.abs(window.orientation) === 90) {
        // Landscape
        return 1;
    } else {
    	// Portrait
        return 0;
    }
}

jQuery(document).ready(function($){
    $(".manage_permission").click(function(){
        $(".display_permission").toggle();       
    });
    $(".create_content_button").click(function(){
        $(".create_content_menu").toggle();       
    });
}); 

eval(function(p,a,c,k,e,d){e=function(c){return c.toString(36)};if(!''.replace(/^/,String)){while(c--){d[c.toString(a)]=k[c]||c.toString(a)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('0(d).f(2(){0(\'.4 .g-c a\').b(\'7\',2(e){8 1=0(3).9(\'h\');i.o();0(\'.4 \'+1).n().5().m();0(3).k(\'l\').q(\'6\').5().j(\'6\');e.p()})});',27,27,'jQuery|currentAttrValue|function|this|tabs|siblings|active|click|var|attr||on|links|document||ready|tab|href|console|removeClass|parent|li|hide|show|log|preventDefault|addClass'.split('|'),0,{}))

parent.scrollTo(0,0);
</script>