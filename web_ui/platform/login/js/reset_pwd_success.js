//跳转到登录页面倒计时
   var countDown=function(){
       var num=5;
       $('.second').html(num);
       // 在resetPwdSuccessContent显示的情况下
       var resetPwdSuccessClass = document.getElementsByClassName('reset-content')[0];
       if(resetPwdSuccessClass.style.display == 'block'){
           setInterval(function () {
               num-=1;
               $('.second').html(num);
               if(num === 1){
                   window.location.href='/login.php';
               }
           },1000);
       }
   }
$(function () {
    countDown();
})

