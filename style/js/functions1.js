
$(document).ready(function() {

    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    });
});

$(function () {
    $('.progress-popover').popover({
        container: 'body'
    })
});