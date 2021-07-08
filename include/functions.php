<?php
/**
 * Created by PhpStorm.
 * User: GNassro
 * Date: 29/02/2020
 * Time: 23:33
 */

function check_connexion (&$user_id = null){

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $con = new MysqlConnect();
    $ret = false;
    if (isset($_SESSION['session_key'])) {

        $con->prepareAndBind('SELECT * from session_account WHERE session_id = ?','s',$_SESSION['session_key']);
        if($con->secureExcecute()){
            if ($con->get_num_rows_stm() == 1){
                $row = $con->fetch_assoc_stm();
                $con->close_stm();
                $user_id = $row['user_id'];
                $ret = true;
            }
        }
    }elseif (isset($_COOKIE["session_key"])){

        $con->prepareAndBind('SELECT * from session_account WHERE session_id = ?','s',$_COOKIE["session_key"]);
        if($con->secureExcecute()){
            if ($con->get_num_rows_stm() == 1){
                $row = $con->fetch_assoc_stm();
                $con->close_stm();
                $user_id = $row['user_id'];
                $ret = true;
                $_SESSION['session_key'] = $_COOKIE["session_key"];
            }
        }
    }

    $con->close_Mysql_stm();
    return $ret;
}

function show_user_content_empty(){
    ?>
<style>

.add-btn {
  height: 140px;
  line-height: 140px;
  width: 140px;
  font-size: 2em;
  font-weight: bold;
  border-radius: 50%;
  background-color: rgba(0,91,192,0.75);
  color: white;
  text-align: center;
  cursor: pointer;
}
.ctayfc{
    color: #3f464c;
}
.fcd{
color: #8aa0a7;
}
</style>

<div class="container h-100">
    <h2 class="text-center ctayfc">Click to add your first cloud</h2>
    <p class="text-center fcd">FTP - Google Drive</p><br>
  <div class="row h-100 justify-content-center align-items-center">

    <div class="col-md-6 text-center d-flex" onclick="add_cloud()">
        <div class="add-btn mx-auto my-auto" >+</div>
    </div>
  </div>
</div>

    <?php
}

function show_user_content_cloud_list(){
    $con1 = new MysqlConnect();

    if (check_connexion($user_id)){

        $con1->prepareAndBind('SELECT * from cloud WHERE user_id = ?','i',$user_id);
        if($con1->secureExcecute()){
            if ($con1->get_num_rows_stm() >= 0){
                echo '<div class="container h-100"><div id="cl_list">';
                $i= 3;
                $s = $i + $con1->get_num_rows_stm();
                while ($row = $con1->fetch_assoc_stm()){
                    $con2 = new MysqlConnect();
                    if ($row["cloud_type"] == 'ftp' || $row["cloud_type"] == 'sftp' || $row["cloud_type"] == 'ftps'){

                    $con2->prepareAndBind('SELECT * from ftp WHERE cloud_id = ?','i',$row['cloud_id']);
                    if($con2->secureExcecute()){
                        $ftp_row = $con2->fetch_assoc_stm();
                        $con2->close_stm();
                    if (($i % 3) == 0)
                        echo '<div class="card-deck">';
                ?>
                <form action="#" method="POST" class="card mb-4 cl-item"">
                    <img class="card-img-top" src="style/img/ftp2.png" >
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['cloud_name']; ?></h5>
                                <p class="card-text"><?php echo $ftp_row['server']; ?></p>

                                <input type="hidden" name="clitem" value="<?php echo $ftp_row["cloud_id"]; ?>">
                                <input type="hidden" name="fold_to_open" value="<?php echo '.'; ?>">
                                <input type="hidden" name="currect-dir" value="<?php echo '/'; ?>">
                                <input type="submit" class="btn btn-primary" value="Open"/>
                            </div>
                </form>
                <?php

                if (($i % 3) == 2)
                        echo '</div>';

                $i++;
                }
                @$con2->close_Mysql_stm();
                    } else if ($row["cloud_type"] == 'ggdrive'){
                        $con3 = new MysqlConnect();

                        $con3->prepareAndBind('SELECT * from gg_drive WHERE cloud_id = ?','i',$row['cloud_id']);
                        if($con3->secureExcecute()){
                        $ggdrive_row = $con3->fetch_assoc_stm();
                    if (($i % 3) == 0)
                        echo '<div class="card-deck">';
                ?>
                <form action="#" method="POST" class="card mb-4 cl-item"">
                    <img class="card-img-top" src="style/img/ggdrive.png" >
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $row['cloud_name']; ?></h5>
                                <p class="card-text"><?php echo $ggdrive_row['email']; ?></p>

                                <input type="hidden" name="gg_clitem" value="<?php echo $ggdrive_row["cloud_id"]; ?>">
                                <input type="hidden" name="gg_fold_to_open_id" value="<?php echo 'root'; ?>">
                                <input type="submit" class="btn btn-primary" value="Open"/>
                            </div>
                </form>
                <?php

                if (($i % 3) == 2)
                        echo '</div>';

                $i++;
                }
                        @$con3->close_stm();
                        @$con3->close_Mysql_stm();
                }
                }
                while (($i % 3) != 0){
                    ?>
                    <div class="card mb-4 d-none d-md-block no-cl-item">
                     <div class="card-body"></div>
                    </div>


                    <?php
                    if (($i % 3) == 2)
                        echo '</div>';
                    $i++;
                }
                echo '</div></div>';
            }
        }
    }
    @$con1->close_stm();
    @$con1->close_Mysql_stm();
}
function show_user_content(){
    $con = new MysqlConnect();

    if (check_connexion($user_id)){

        $con->prepareAndBind('SELECT * from cloud WHERE user_id = ?','i',$user_id);
        if($con->secureExcecute()){
            if ($con->get_num_rows_stm() == 0){
                show_user_content_empty();
            }else{
                show_user_content_cloud_list();
            }
        }
        @$con->close_stm();
        @$con->close_Mysql_stm();
    }
}
function show_header (){

    ?>
    <!-- head start-->
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8"/>
        <title>Cloudi | Created by Elghozi Nasreddine</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <!-- Bootstrap CSS CDN -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">        <!-- bootstrap icons fa -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


        <!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
        <!-- Scrollbar Custom CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">
        <link rel="stylesheet" href="style/css/style.css">
        <!-- Font Awesome JS -->
        <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
        <script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

        <!-- Notiflix -->
        <script src="style/notiflix/notiflix-aio-2.1.2.min.js"></script>

        <!-- msg-box -->
        <script src="style/msg-box/bootstrap-show-modal.js"></script>

<script type="text/javascript" src="style/js/functions1.js"></script>
        <script type="text/javascript">
        $(document).ready(function () {
            $(document).on('submit','.cl-item', function (e) {
                e.preventDefault();
                var theForm = $(this);
                var param = theForm.serialize();
                open_folder(param);
            });
        });
        </script>



    <?php
    if (check_connexion()){
        ?>
        <script>

</script>
        </head>
        <body>
        <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3><a href="<?php echo "http://".$_SERVER['HTTP_HOST']; ?>" >Cloudi</a></h3>
            </div>
            <ul class="list-unstyled CTAs">
                <li>
                    <a href="#" onclick="add_cloud()" class="article">Add new cloud</a>
                </li>
            </ul>
            <ul class="list-unstyled components">
                <li>
                    <a href="<?php echo "http://".$_SERVER['HTTP_HOST']; ?>" >Home</a>
                </li>

            </ul>


        </nav>


        <?php
    }else{
        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#singnupFrom").submit(function(e){
                    e.preventDefault();
                    Notiflix.Notify.Init({
                        position: 'right-bottom',
                    });
                    if(document.getElementById('signuppassword').value === document.getElementById('signupcpassword').value){
                        var data = {
                            "signupemail": document.getElementById('signupemail').value,
                            "signupfirstname": document.getElementById('signupfirstname').value,
                            "signuplastname": document.getElementById('signuplastname').value,
                            "signuppassword": document.getElementById('signuppassword').value
                        };
                        data = $(this).serialize() + "&" + $.param(data);
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: "signup.php",
                            data: data,
                            success: function(data) {
                                if (data["result"] === 101){
                                    // must complete email verify in future (nchallah ma nansahech)
                                    Notiflix.Notify.Success('Account created successfully');
                                    document.getElementById('signupemail').value = "";
                                    document.getElementById('signupfirstname').value = "";
                                    document.getElementById('signuplastname').value = "";
                                    document.getElementById('signuppassword').value = "";
                                    document.getElementById('signupcpassword').value = "";

                                    document.getElementById("pills-signin-tab").click();
                                }else if (data["result"] === 99){
                                    Notiflix.Notify.Warning('There is no connexion with server');
                                }else if (data["result"] === 75){
                                    Notiflix.Notify.Warning('Email already used');
                                    document.getElementById("signupemail").focus();
                                }else if (data["result"] === 35){
                                    Notiflix.Notify.Warning('Please verify your last name (must be letters only)');
                                    document.getElementById("signuplastname").focus();
                                }else if (data["result"] === 31){
                                    Notiflix.Notify.Warning('Please verify your firstname (must be letters only)');
                                    document.getElementById("signupfirstname").focus();
                                }else if (data["result"] === 26){
                                    Notiflix.Notify.Warning('Please verify the password (must be at least 6 caracters)');
                                    document.getElementById("signuppassword").focus();
                                }else if (data["result"] === 19){
                                    Notiflix.Notify.Warning('Please verify your email');
                                    document.getElementById("signupemail").focus();
                                }else if (data["result"] === 105){
                                    location.reload(true);
                                }else {
                                    Notiflix.Notify.Warning('Something wrong !! please try again later');
                                }
                            },
                            error: function(data, exep) {
                                Notiflix.Notify.Failure('There is no connexion with server:' + data.responseText);
                                Notiflix.Notify.Failure('There is no connexion with server' + exep);
                            }
                        });
                    }else {
                        Notiflix.Notify.Warning('Please confirm your password');
                        document.getElementById("signupcpassword").focus();
                    }

                    return false;
                });

                $("#singninFrom").submit(function(e){
                    e.preventDefault();
                    var data = {
                        "signinemail": document.getElementById('signinemail').value,
                        "signinpassword": document.getElementById('signinpassword').value,
                        "signincondition": document.getElementById('signincondition').checked
                    };
                    data = $(this).serialize() + "&" + $.param(data);
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "signin.php",
                        data: data,
                        success: function(data) {
                            Notiflix.Notify.Init({
                                position: 'right-bottom',
                            });
                            if (data["result"] === 1024){
                                Notiflix.Notify.Success('Account login successfully');
                                location.reload(true);
                            }else if (data["result"] === 404){
                                Notiflix.Notify.Warning('Please verify your email or password');
                            }else if (data["result"] === 190){
                                Notiflix.Notify.Warning('Please enter a valid email');
                                document.getElementById("signinemail").focus();
                            }else if (data["result"] === 260){
                                Notiflix.Notify.Warning('Please verify the password (must be at least 6 caracters)');
                                document.getElementById("signinpassword").focus();
                            }else if (data["result"] === 990){
                                Notiflix.Notify.Warning('There is no connexion with server');
                            }else if (data["result"] === 105){
                                location.reload(true);
                            }else {
                                Notiflix.Notify.Warning('Something wrong !! please try again later');
                            }
                        },
                        error: function(data, exep) {
                            Notiflix.Notify.Init({
                                position: 'right-bottom',
                                messageMaxLength: 5000,
                                timeout: 60000,
                            });
                            Notiflix.Notify.Failure('There is no connexion with server:' + data.responseText);
                                Notiflix.Notify.Failure('There is no connexion with server' + exep);
                        }
                    });
                    return false;
                });
            });
        </script>

        </head>
        <body>
        <div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3><a href="<?php echo "http://".$_SERVER['HTTP_HOST']; ?>" >Cloudi</a></h3>
            </div>
        </nav>


        <?php
    }
}

function show_body (){
    if (check_connexion()){
        ?>
                <!-- Page Content  -->
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-light">

                <div class="container-fluid">

                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="nav navbar-nav ml-auto">

                        <ul class="nav navbar-nav>
                        <li class="nav-item active">
                                <a class="nav-link" href="#">Logout</a>
                            </li>
                        </ul>

                        </div>
                    </div>
                </div>
            </nav>
            <div><div class="loader"></div> <span id="show-progress-files"></span>
            </div>
            <?php
            show_user_content();
            ?>
        </div>
    </div>


        <?php
    }else{
        ?>
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">

                    <button type="button" id="sidebarCollapse" class="btn btn-primary">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>
                </div>
            </nav>
            <div class="col main pt-5 mt-3">
            <div class="col-sm-8 ml-auto mr-auto">
                <ul class="nav nav-pills nav-fill mb-1" id="pills-tab" role="tablist">
                    <li class="nav-item"> <a class="nav-link active" id="pills-signin-tab" data-toggle="pill" href="#pills-signin" role="tab" aria-controls="pills-signin" aria-selected="true">Sign In</a> </li>
                    <li class="nav-item"> <a class="nav-link" id="pills-signup-tab" data-toggle="pill" href="#pills-signup" role="tab" aria-controls="pills-signup" aria-selected="false">Sign Up</a> </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-signin" role="tabpanel" aria-labelledby="pills-signin-tab">
                        <div class="col-sm-12 border border-primary shadow rounded pt-2">
                            <div class="text-center"><img src="style/img/logo-80x80.png" class="rounded-circle border p-1"></div>
                            <form method="post" id="singninFrom">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="signinemail" id="signinemail" class="form-control" placeholder="Enter valid email" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="signinpassword" id="signinpassword" class="form-control" placeholder="***********" required>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col">
                                            <label><input type="checkbox" name="signincondition" id="signincondition"> Remember me.</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="signinsubmit" value="Sign In" class="btn btn-block btn-primary" onclick="">
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-signup" role="tabpanel" aria-labelledby="pills-signup-tab">
                        <div class="col-sm-12 border border-primary shadow rounded pt-2">
                            <div class="text-center"><img src="style/img/logo-80x80.png" class="rounded-circle border p-1"></div>
                            <form method="post" id="singnupFrom">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="signupemail" id="signupemail" class="form-control" placeholder="Enter valid email" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">First name <span class="text-danger">*</span></label>
                                    <input type="text" name="signupfirstname" id="signupfirstname" class="form-control" placeholder="Choose your first name" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Last name <span class="text-danger">*</span></label>
                                    <input type="text" name="signuplastname" id="signuplastname" class="form-control" placeholder="Choose your last name" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="signuppassword" id="signuppassword" class="form-control" placeholder="***********" pattern="^\S{6,}$" onchange="this.setCustomValidity(this.validity.patternMismatch ? 'Must have at least 6 characters' : ''); if(this.checkValidity()) form.password_two.pattern = this.value;"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="signupcpassword" id="signupcpassword" class="form-control" pattern="^\S{6,}$" onchange="this.setCustomValidity(this.validity.patternMismatch ? 'Please enter the same Password as above' : '');" placeholder="***********" required>
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="signupsubmit" value="Sign Up" class="btn btn-block btn-primary" onclick="">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            </div>
            </div>
    </div>



        <?php
    }
    ?>
<script type="text/javascript" src="style/js/functions.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $("#sidebar").mCustomScrollbar({
                theme: "minimal"
            });

            $('#sidebarCollapse').on('click', function () {
                $('#sidebar, #content').toggleClass('active');
                $('.collapse.in').toggleClass('in');
                $('a[aria-expanded=true]').attr('aria-expanded', 'false');
            });
        });
    </script>
</body>

</html>
<?php
}

function check_mail($email){
    $r = false;
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if(strlen($email)<=60)
            $r = true;
    }
    return $r;
}

function is_url($url){
    /**$r = false;
    /**if(filter_var($url,FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || filter_var($url,FILTER_VALIDATE_IP)){
        $r = true;
    }
    if (preg_match('%^(?:(?:ftps?)://)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$%uiS', $url)) {

    return true;
    }
    return $r;*/
    return true;
}
?>