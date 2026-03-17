# bootstrap-drawer

Bootstrap addons that provide drawer ( sidebar content ) for bootstrap 4.3.1.

For older bootstrap version, please check other branch.

### 源码地址
https://github.com/clineamb/bootstrap-drawer
### 引入css
<link href="./assets/global/plugins/bootstrap-drawer/css/bootstrap-drawer.min.css" rel="stylesheet" type="text/css"/>
### 引入js
<script type="text/javascript" src="./assets/global/plugins/bootstrap-drawer/js/bootstrap-drawer.min.js"></script>
**注意：tpl目录下已经引入css和js,可直接使用，不用再单独引入**
### 例子
```
<button type="button" class="btn green-haze" data-toggle="drawer" data-target="#drawer-1">排除</button>
<div class="drawer drawer-left slide" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">Drawer Title</h4>
        </div>
        <div class="drawer-body">
            <p>Drawer body</p>
        </div>
        <div class="drawer-footer">Drawer footer</div>
    </div>
</div>
```
+ drawer类名是必须的
+ drawer-left、drawer-right控制弹出方向
+ drawer-content-scrollable控制滚动条显示
+ slide控制弹出的动画，去掉就没有动画了
+ 如果弹出框被遮挡，可通过调整z-index属性使其显示
### 关闭按钮
`<button type="button" class="btn btn-secondary btn-block" data-dismiss="drawer" aria-label="Close">关闭</button>`
### 方法
All API methods are asynchronous and start a transition. They return to the caller as soon as the transition is started but before it ends. In additional, a method call on a transition component will be ignored.

+ .drawer(options)
Activate your content as a drawer. Accept an optional options object.
```
$('#my-drawer').drawer({
    keyboard: false
})
```
+ .drawer('toggle')
Manullay toggles a drawer. Returns to the caller before the drawer has actually been shown or hidden (i.e before shown.bs.drawer or hidden.bs.drawer event occurs).

`$('#my-drawer').drawer('toggle')`
+ .drawer('show')
Manually opens a drawer. Returns to the caller before the drawer has actually been shown (i.e before shown.bs.drawer event occurs).

`$('#my-drawer').drawer('show')`

+ .drawer('hide')
Manually hides a drawer. Returns to the caller before the drawer has actually been hidden (i.e before hidden.bs.drawer event occurs).

`$('#my-drawer').drawer('hide')`
+ .drawer('dispose')
  Destroys an element's drawer.

### 事件
+ show.bs.drawer	
This event fires immediately when the show instance method is called. If caused by a click, the clicked element is available as the relatedTarget property of the event.
+ shown.bs.drawer	
This event is fired when the drawer has been made visible to the user (will wait for CSS transitions to complete). If caused by a click, the clicked element is available as the relatedTarget property of the event.
+ hide.bs.drawer	
This event is fired immediately when the hide instance method has been called.
+ hidden.bs.drawer	
This event is fired when the drawer has finished being hidden from the user (will wait for CSS transitions to complete).
```
$('#my-drawer').on('hidden.bs.drawer', function(e){
    // do something...
})
```

