var DataBackupCenter = function () {

    return {
        //main function to initiate the module
        init: function () {
            const title = document.title;
            History.pushState({url:"./platform/homepage/html/databackup_center_vinchin.php",routeName: 'homepage'}, title, "?homepage");
            requestAnimationFrame(() => {
                document.title = title;
            });
        },

    };

}();

jQuery(document).ready(function() {
    DataBackupCenter.init();
});
