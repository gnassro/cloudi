function open_folder(param) {
    Notiflix.Loading.Circle("Connecting...");
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "openfold.php",
        data: param,
        success: function(data) {

            if (data["result"] === 207){
                document.getElementById("cl_list").innerHTML = data["output"];
                $('html,body').scrollTop(0);
            }else if (data["result"] === 333){
                window.location.replace(data["output"]);
            }

            Notiflix.Loading.Remove(600);
        },
        error: function(data, exep) {
            Notiflix.Notify.Init({
                messageMaxLength : 600,
                timeout : 60000
            });
            Notiflix.Notify.Failure(data.responseText);
            Notiflix.Loading.Remove(600);
        }
    });

}

function add_cloud() {
    $.showModal({
        title: 'Add Cloud',
        body:
            "<form>\n"+
            "    <div class=\"form-group row\">\n"+
            "        <label for=\"cloudname\" class=\"col-sm-2 col-form-label\">Display name</label>\n"+
            "        <div class=\"col-sm-10\">\n"+
            "            <input type=\"text\" class=\"form-control\" id=\"cloudname\" placeholder=\"Ex: myCloud\">\n"+
            "        </div>\n"+
            "    </div>\n"+
            "    <fieldset class=\"form-group\">\n"+
            "        <div class=\"row\">\n"+
            "            <legend class=\"col-form-label col-sm-2 pt-0\">Cloud type</legend>\n"+
            "            <div class=\"col-sm-10\">\n"+
            "                <div class=\"form-check\">\n"+
            "                    <input class=\"form-check-input\" type=\"radio\" name=\"cl-type\" id=\"gridRadios1\" value=\"ftp\">\n"+
            "                    <label class=\"form-check-label\" for=\"gridRadios1\">\n"+
            "                        FTP\n"+
            "                    </label>\n"+
            "                </div>\n"+
            "                <div class=\"form-check\">\n"+
            "                    <input class=\"form-check-input\" type=\"radio\" name=\"cl-type\" id=\"gridRadios2\" value=\"ggdrive\">\n"+
            "                    <label class=\"form-check-label\" for=\"gridRadios2\">\n"+
            "                        Google Drive\n"+
            "                    </label>\n"+
            "                </div>\n"+
            "            </div>\n"+
            "        </div>\n"+
            "    </fieldset>\n"+
            "</form>",
        modalDialogClass: "modal-lg",
        footer: '<button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button>',
        onCreate: function (modal) {
            // create event handler for form submit and handle values
            $(modal.element).on("click", "button[type='submit']", function (event) {
                event.preventDefault()
                var $form = $(modal.element).find("form");

                if ($form.find("input[class='form-check-input']:checked").val() === "ftp"){
                    modal.hide();
                    var dsname = $form.find("#cloudname").val();
                    var type = "ftp";
                    $.showModal({
                        title: 'Add FTP',
                        body:
                            "<form>\n"+
                            "            <div class=\"form-group row\">\n"+
                            "                <div class=\"col-12\">\n"+
                            "                    <div class=\"row mb-2\">\n"+
                            "                        <div class=\"col-8\">\n"+
                            "                            <div class=\"row\">\n"+
                            "                                <label for=\"dsname\" class=\"col-3 col-form-label\">Display name</label>\n"+
                            "                                <div class=\"col-9\">\n"+
                            "                                    <input type=\"text\" class=\"form-control\" name=\"dsname\" value=\""+dsname +"\">\n"+
                            "                                </div>\n"+
                            "                            </div>\n"+
                            "                        </div>\n"+
                            "                        <div class=\"col-4\">\n"+
                            "                            <select name=\"cl-type\" class=\"form-control\" id=\"sel1\">\n" +
                            "    <option value=\"ftp\">FTP</option>\n" +
                            "    <option value=\"sftp\">SFTP</option>\n" +
                            "    <option value=\"ftps\">FTPS</option>\n" +
                            "  </select>\n"+
                            "                        </div>\n"+
                            "                    </div>\n"+
                            "                    <div class=\"row mb-2\">\n"+
                            "                        <div class=\"col-8\">\n"+
                            "                            <div class=\"row\">\n"+
                            "                                <label for=\"dsname\" class=\"col-3 col-form-label\">Server</label>\n"+
                            "                                <div class=\"col-9\">\n"+
                            "                                    <input type=\"text\" class=\"form-control\" name=\"server\" value=\"\">\n"+
                            "                                </div>\n"+
                            "                            </div>\n"+
                            "                        </div>\n"+
                            "                        <div class=\"col-4\">\n"+
                            "                            <input type=\"text\" class=\"form-control\" name=\"cport\" value=\"21\">\n"+
                            "                        </div>\n"+
                            "                    </div>\n"+
                            "                    <div class=\"row mb-2\">\n"+
                            "                        <div class=\"col-12\">\n"+
                            "                            <div class=\"row\">\n"+
                            "                                <label for=\"dsname\" class=\"col-2 col-form-label\">Username</label>\n"+
                            "                                <div class=\"col-10\">\n"+
                            "                                    <input type=\"text\" class=\"form-control\" name=\"usrname\" >\n"+
                            "                                </div>\n"+
                            "                            </div>\n"+
                            "                        </div>\n"+
                            "                    </div>\n"+
                            "                    <div class=\"row mb-2\">\n"+
                            "                        <div class=\"col-12\">\n"+
                            "                            <div class=\"row\">\n"+
                            "                                <label for=\"dsname\" class=\"col-2 col-form-label\">Password</label>\n"+
                            "                                <div class=\"col-10\">\n"+
                            "                                    <input type=\"password\" class=\"form-control\" name=\"pswd\">\n"+
                            "                                </div>\n"+
                            "                            </div>\n"+
                            "                        </div>\n"+
                            "                    </div>\n"+
                            "                </div>\n"+
                            "            </div>\n"+
                            "        </form>",
                        modalDialogClass: "modal-lg",
                        footer: '<button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button>',
                        onCreate: function (endmodal) {
                            // create event handler for form submit and handle values
                            $(endmodal.element).on("click", "button[type='submit']", function (event) {
                                event.preventDefault();
                                Notiflix.Loading.Circle('Please wait...');
                                var $form = $(endmodal.element).find("form");

                                var dsname = $form.find("input[name='dsname']").val();
                                var server = $form.find("input[name='server']").val();
                                var cport = $form.find("input[name='cport']").val();
                                var usrname = $form.find("input[name='usrname']").val();
                                var pswd = $form.find("input[name='pswd']").val();
                                var type = $form.find("select[name='cl-type']").val();

                                var data = {
                                    "dsname": dsname,
                                    "server": server,
                                    "cport": cport,
                                    "usrname": usrname,
                                    "type": type,
                                    "pswd": pswd
                                };
                                data = $(this).serialize() + "&" + $.param(data);
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: "newcld.php",
                                    data: data,
                                    success: function(data) {
                                        if (data["result"] === 29){
                                            Notiflix.Notify.Success("FTP created successfully");
                                            location.reload();
                                        } else if (data["result"] === 24) {
                                            Notiflix.Notify.Failure("Something wrong, please try again later");
                                        }else if (data["result"] === 22) {
                                            Notiflix.Notify.Warning("Please type FTP user password");
                                            $form.find("input[name='pswd']").focus();
                                        }else if (data["result"] === 21) {
                                            Notiflix.Notify.Warning("Please type FTP user");
                                            $form.find("input[name='usrname']").focus();
                                        }else if (data["result"] === 19) {
                                            Notiflix.Notify.Warning("Verify the port");
                                            $form.find("input[name='cport']").focus();
                                        }else if (data["result"] === 17) {
                                            Notiflix.Notify.Warning("Verify your display name (all caracters must be letters without space)");
                                            $form.find("input[name='dsname']").focus();
                                        }else if (data["result"] === 16) {
                                            Notiflix.Notify.Warning("Verify ftp url server");
                                            $form.find("input[name='server']").focus();
                                        }else {
                                            Notiflix.Notify.Failure("Something wrong !!" + data);
                                            data.forEach(function(entry) {
                                                console.log("ff");
                                                Notiflix.Notify.Failure("Something wrong !!" + entry);
                                            });
                                        }
                                        Notiflix.Loading.Remove(800);
                                    },
                                    error: function(jqXHR,exep) {
                                        Notiflix.Notify.Init({
                                            timeout : 8000,
                                            messageMaxLength : 10000,
                                        });
                                        Notiflix.Notify.Failure("er:" + jqXHR.responseText +"\n exep:" + exep);
                                        Notiflix.Loading.Remove(800);
                                    }
                                });




                            })
                        }
                    })
                }else if ($form.find("input[class='form-check-input']:checked").val() === "ggdrive") {
                    var gg_dsname = $form.find("#cloudname").val();
                    var gg_type = "ggdrive";
                    Notiflix.Loading.Circle('Please wait...');
                    var data = {
                        "gg_dsname": gg_dsname,
                        "gg_type": gg_type
                    };
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "newcld.php",
                        data: $.param(data),
                        success: function(data) {
                            Notiflix.Loading.Remove(800);
                            if (data["result"] === 290){
                                window.location.replace(data["uri_auth"]);
                            }else {
                                Notiflix.Notify.Failure("Something wrong !!");
                            }

                        },
                        error: function(jqXHR,exep) {
                            Notiflix.Notify.Init({
                                timeout : 80000,
                                messageMaxLength : 10000,
                            });
                            Notiflix.Notify.Failure("er:" + jqXHR.responseText +"\n exep:" + exep);
                            Notiflix.Loading.Remove(800);
                        }
                    });
                }

            })
        }
    })

}

function checkbox_toggle() {

    var arrMarkMail = document.getElementsByName("file[]");
    var selectAllitems = document.getElementById("js-select-all-items");

    var copybtn = document.getElementById("cp");
    var mvbtn = document.getElementById("mv");

    if (selectAllitems.checked){
        for (var i = 0; i < arrMarkMail.length; i++) {
            arrMarkMail[i].checked = true;
            copybtn.classList.remove("disabled");
            mvbtn.classList.remove("disabled");
        }
    }else {
        for (var i = 0; i < arrMarkMail.length; i++) {
            arrMarkMail[i].checked = false;
            copybtn.classList.add("disabled");
            mvbtn.classList.add("disabled");
        }
    }


}

function checkbox_items(item) {
    var arrMarkMail = document.getElementsByName("file[]");

    var copybtn = document.getElementById("cp");
    var mvbtn = document.getElementById("mv");
    var selectAllitems = document.getElementById("js-select-all-items");

    if (item.checked){
        copybtn.classList.remove("disabled");
        mvbtn.classList.remove("disabled");
    }else {
        selectAllitems.checked = false;
        var i = 0;
        var isAllunchecked = true;
        while ( i < arrMarkMail.length && isAllunchecked ) {
            if (arrMarkMail[i].checked === true){
                isAllunchecked = false;
            }
            i++
        }
        if(isAllunchecked){
            copybtn.classList.add("disabled");
            mvbtn.classList.add("disabled");
        }
    }
}

function files_to_transfert(elem, clsrc){

    if(!elem.classList.contains("disabled")){
        Notiflix.Loading.Circle("Process...");
        var files_name = [];
        var files_from_page = document.getElementsByName("file[]");
        var c = 0;
        for (var i = 0; i < files_from_page.length; i++) {
            if (files_from_page[i].checked) {
                files_name[c] = files_from_page[i].value;
                c++;
            }
        }


        var data = {
            "clsrc": clsrc,
            "transfert_type": elem.id,
            "files_to_transfert": JSON.stringify(files_name)
        };
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "file_to_transfert.php",
            data: $.param(data),
            success: function(data) {
                if (data["result"] === 107){
                    Notiflix.Notify.Success('Files ready to transfert');

                    var copybtn = document.getElementById("cp");
                    var mvbtn = document.getElementById("mv");

                    var selectAllitems = document.getElementById("js-select-all-items");
                    selectAllitems.checked = false;
                    for (var i = 0; i < files_from_page.length; i++) {
                        files_from_page[i].checked = false;
                        copybtn.classList.add("disabled");
                        mvbtn.classList.add("disabled");
                    }
                } else {
                    Notiflix.Notify.Failure('Something wrong !!' + data["result"]);
                }
                Notiflix.Loading.Remove(600);
            },
            error: function(data, exep) {
                Notiflix.Notify.Init({
                    'timeout' : 34000,
                    'messageMaxLength' : 600
                });
                Notiflix.Notify.Failure('Fail:' + data.responseText);
                Notiflix.Loading.Remove(600);
            }
        });
    }
}

function proceed_transfert(elem, current_dir, clsdest){
    if(!elem.classList.contains("disabled")){

        var data = {
            "current_dir": current_dir,
            "clsdest": clsdest
        };

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "proceed_transfert.php",
            data: $.param(data),
            success: function(data) {
                elem.classList.add("disabled");
            },
            error: function(data, exep) {
                Notiflix.Notify.Failure('Fail:' + data.responseText);
            }
        });
    }
}

var isgoodconnexion = true;
function isready_to_past() {
    var data = {
        "just_param": "E84635EUI637HBN3"
    };
    var pstbtn = document.getElementById("pst");



    $.ajax({
        type: "POST",
        dataType: "json",
        url: "isready_to_past.php",
        data: $.param(data),
        success: function(data) {

            if (!window.isgoodconnexion){
                Notiflix.Notify.Success("Reconnected succeed");
                Notiflix.Loading.Remove(400);
                window.isgoodconnexion = true;
            }

            if (data["result"] === 900){
                if (pstbtn !== null)
                    pstbtn.classList.remove("disabled");
            } else {
                if (pstbtn !== null)
                    pstbtn.classList.add("disabled");
            }
        },
        error: function(data, exep) {
            if (window.isgoodconnexion) {
                Notiflix.Notify.Init({
                    'timeout' : 34000,
                    'messageMaxLength' : 600
                });
                Notiflix.Notify.Failure("There is no connexion with the server!!" + data.responseText);
            }
            window.isgoodconnexion = false;
            Notiflix.Loading.Circle("Connecting...");
            pstbtn.classList.add("disabled");
        }
    });
    var s = setTimeout(function(){
        isready_to_past();
        clearTimeout(s);
    }, 500);

}
isready_to_past();


function is_in_progress() {
    var data = {
        "just_param": "E84635EUI637HBN3"
    };

    var progElem = document.getElementById("show-progress-files");
    var loader = document.getElementsByClassName("loader")[0];
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "read_log_progress.php",
        data: $.param(data),
        success: function(data) {
            if (data["result"] === 666){
                progElem.innerText = data["text"];
                loader.style.display = "block";
            }else if (data["result"] === 667){
                progElem.innerText = "Waiting...";
                loader.style.display = "block";
            }else {
                progElem.innerText = "";
                loader.style.display = "none";
            }
        },
        error: function(data, exep) {
            progElem.innerText = "Somethings Wrong!!!";
            loader.style.display = "none";
        }
    });
    var ss = setTimeout(function(){
        is_in_progress();
        clearTimeout(ss);
    }, 400);

}
is_in_progress();