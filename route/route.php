<?php
Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');
Route::get('small/api/download/vod/config/v2','small/Program/getmenu');
Route::get('small/api/download/adv/config','small/Program/getadv');
Route::get('small/api/download/ads/config','small/Program/getads');

return [

];
