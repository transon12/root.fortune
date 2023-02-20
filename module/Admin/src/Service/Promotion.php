<?php
namespace Admin\Service;

use RuntimeException;
use Zend\Db\Adapter\Adapter;

class Promotion{
    
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    const SOURCE_1	    = "1";
    const SOURCE_2	    = "2";
    const SOURCE_3	    = "3";
    const SOURCE_4	    = "4";
    const SOURCE_5	    = "5";
    const SOURCE_6	    = "6";
    const SOURCE_7	    = "7";
    const SOURCE_8	    = "8";
    const SOURCE_9	    = "9";
    const SOURCE_10	    = "10";
    public static function returnSource(){
        return array(
            self::SOURCE_1 	    => "OTC - Nhà thuốc",
            self::SOURCE_2 	    => "OTC - NTD",
            self::SOURCE_3 	    => "OTC - TDV",
            self::SOURCE_4 	    => "MT - NTD (tivi, siêu thị, chuỗi hệ thống, ...)",
            self::SOURCE_5 	    => "Social - NTD (Fb, web, Tiktok, Youtube, ...)",
            self::SOURCE_6 	    => "TMĐT - NDT (Lazada, Tiki, Shopee, Sendo, ...)",
            self::SOURCE_7 	    => "CSKH - NTD",
            self::SOURCE_8 	    => "CALL IN - NTD",
            self::SOURCE_9 	    => "KH tổng hợp - NTD",
            self::SOURCE_10 	=> "Chưa xác định",
        );
    }
}