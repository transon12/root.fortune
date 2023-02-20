<?php

namespace DataApi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\PxtAuthentication;
use DataApi\Core\DataApiCore;
use DataApi\Model\News;
use SimpleXMLElement;

class NewsController extends DataApiCore{
    
    private $entityNews;

    public function __construct(PxtAuthentication $entityPxtAuthentication, News $entityNews) {
        parent::__construct($entityPxtAuthentication);
        $this->entityNews = $entityNews;
    }
    
    public function indexAction(){
        $limit = isset($_GET["limit"]) ? $_GET["limit"] : 10;
        $page = isset($_GET["page"]) ? $_GET["page"] : 1;
        $arr = $this->entityNews->fetchAll(["limit" => $limit, "page" => $page]);
        echo json_encode($arr);
		die();
    }
    
    public function updateAction(){
		//include 'https://fortune.vn/feed';
        $feed = file_get_contents('https://fortune.vn/feed');
        //echo $feed; die();
        $feed = str_replace("<content:encoded>", "<contentEncoded>", $feed);
        $feed = str_replace("</content:encoded>", "</contentEncoded>", $feed);
        //echo $feed; die();
        $rss = new SimpleXMLElement($feed);
        //SimpleXMLElement

        //echo $rss->movie[0]->plot;
        /* For each <character> node, we echo a separate <name>. */
        $i = 0;
        $arr = array();
        $this->entityNews->deleteRows();
        foreach ($rss->channel->item as $item) {
            $arr[$i]["title"] = strval($item->title);
            $arr[$i]["description"] = strval($item->description);
            $content = strval($item->contentEncoded);
            $content = str_replace('src="/', 'src="https://fortune500.vn/', $content);
            $arr[$i]["content"] = $content;
            $this->entityNews->addRow($arr[$i]);
            $i++;
        }
        unset($arr);
        //var_dump($arr);
        //echo json_encode($arr);
        die("finish");
    }
}
