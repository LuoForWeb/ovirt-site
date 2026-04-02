var TemplateList = function () {

    function addListeners () {
        $('#add_template').on('click', function () {
            LOCATION('./content/industry/add_template.php');
        })
    }



    return {
        init: function () {
            addListeners();
        }
    }
}();

jQuery(document).ready(function () {
    TemplateList.init();
});