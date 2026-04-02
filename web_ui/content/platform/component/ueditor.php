<script src="./assets/global/plugins/ueditor/ueditor.config.js?v=20240729"></script>
<script src="./assets/global/plugins/ueditor/ueditor.all.js?v=20240729"></script>
<script type="text/javascript">
    $(function(){
        $(".myueditor").each(function(index, element) {
            var id=$(element).attr("id");
            var config=$(element).attr("data-config");
            config=((config==""||config==undefined)?$ueconfig:config);
            UE.getEditor(id,config);
        });
    });
</script>
