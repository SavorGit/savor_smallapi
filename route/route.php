<?php
Route::get('small/api/download/init','small/box/initdata');
Route::get('small/tvList/api/stb/tv_getCommands','small/tvchannel/getchannellist');
Route::get('small/tvListNew/api/stb/tv_getCommands','small/tvchannel/getchannellist');
Route::get('small/tvList/api/stb/tv_commands','small/tvchannel/reportdata');
Route::get('small/api/download/vod/config/v2','small/Program/getmenu');
Route::get('small/api/download/adv/config','small/Program/getadv');
Route::get('small/api/download/ads/config','small/Program/getads');
Route::get('small/api/download/poly/config','small/Program/getpoly');
Route::get('small/api/download/demand/config','small/Demand/getdemand');
Route::get('small/api/download/apk/config','small/Upgrade/boxupgrade');
return [

];
