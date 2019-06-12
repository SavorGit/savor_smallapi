<?php
Route::rule('small/api/download/init','small/box/initdata');
Route::rule('small/tvList/api/stb/tv_getCommands','small/tvchannel/getchannellist');
Route::rule('small/tvListNew/api/stb/tv_getCommands','small/tvchannel/getchannellist');
Route::rule('small/tvList/api/stb/tv_commands','small/tvchannel/reportdata');
Route::rule('small/tvListNew/api/stb/tv_commands','small/tvchannel/reportdata');
Route::rule('small/api/download/vod/config/v2','small/Program/getmenu');
Route::rule('small/api/download/adv/config','small/Program/getadv');
Route::rule('small/api/download/ads/config','small/Program/getads');
Route::rule('small/api/download/poly/config','small/Program/getpoly');
Route::rule('small/api/download/demand/config','small/Demand/getdemand');
Route::rule('small/api/download/apk/config','small/Upgrade/boxupgrade');
return [

];
