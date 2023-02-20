<script type="text/javascript"> 
var page = 1;
var route = "<?= $this->url("persons/notifications", ["action" => "index"]) ?>";
  function notification(page, route){
    $.ajax({
      method: 'post',
      url: route,
      data: {page: page},
      success: function(result){
        $("#nav-bar-notify").append(result)
      }
    })
  }
  $(function(){
    $.ajax({
      method: 'post',
      url: "<?= $this->url("persons/notifications", ["action" => "notify-unread"]) ?>",
      success: function(result){
        $("#header-count-notify").text(result);
      }
    });

    notification(page, route);
    
    $("#read-more").on("click",function(){
        page++;
        notification(page, route);
    });

    $("#mark-as-read").on("click", function(){
        var notifyId = [];
        $("#nav-bar-notify").find("div.not-read").each(function(index, notify){
          notifyId.push($(notify).data("nid"));
        });
        if(notifyId.length === 0){
          $.toast({
                heading: 'Cảnh báo',
                text: 'Bạn đã đọc hết thông báo.',
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'warning',
                hideAfter: 3000, 
                stack: 6
              });
          return false
        }
        
        $.ajax({
          url: "<?= $this->url("persons/notifications", ["action" => "mark-read"]) ?>",
          type: "post",
          data: {notifyId: notifyId},
          success: function(result){
            // console.log(result)
            // $("#nav-bar-notify").prepend(result)
            if(result == 'success'){
              notifyId.forEach(function(nid){
                $("#nav-bar-notify").find(`div[data-nid= ${nid}]`).removeClass("bg-blue-grey not-read");
              });
              var currentNotify =  +$("#header-count-notify").text()
              currentNotify -= notifyId.length;
              if(currentNotify === 0){
                  $("#header-count-notify").text("");
              }else{
                  $("#header-count-notify").text(currentNotify);
              }
            }
          }
        })
        return false;
    });
  })
</script>