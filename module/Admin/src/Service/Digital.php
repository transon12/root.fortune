<?php
namespace Admin\Service;

use RuntimeException;
use Zend\Db\Adapter\Adapter;

class Digital{
    
	private $adapter = null;
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
    }

    const PRIZE_TOPUP_0	    = "0";
    const PRIZE_TOPUP_10	= "10000";
    const PRIZE_TOPUP_20	= "20000";
    const PRIZE_TOPUP_30	= "30000";
    const PRIZE_TOPUP_50	= "50000";
    const PRIZE_TOPUP_100	= "100000";
    const PRIZE_TOPUP_200	= "200000";
    const PRIZE_TOPUP_500	= "500000";
    public static function returnPriceTopup(){
        return array(
            self::PRIZE_TOPUP_0 	=> "0 VNĐ",
            self::PRIZE_TOPUP_10 	=> "10.000 VNĐ",
            self::PRIZE_TOPUP_20 	=> "20.000 VNĐ",
            self::PRIZE_TOPUP_30    => "30.000 VNĐ",
            self::PRIZE_TOPUP_50 	=> "50.000 VNĐ",
            self::PRIZE_TOPUP_100 	=> "100.000 VNĐ",
            self::PRIZE_TOPUP_200	=> "200.000 VNĐ",
            self::PRIZE_TOPUP_500	=> "500.000 VNĐ",
        );
    }
}