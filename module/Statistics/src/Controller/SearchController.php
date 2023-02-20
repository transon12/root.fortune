<?php

namespace Statistics\Controller;

use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

use Admin\Core\AdminCore;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Products;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Settings\Model\Messages;
use Statistics\Form\Search\SearchForm;
use Storehouses\Model\Agents;
use Storehouses\Model\Storehouses;

class SearchController extends AdminCore{
    
    public $entitySettings;
    public $entityCodes;
    public $entityProducts;
    public $entityMessages;
    public $entityAgents;
    public $entityStorehouses;
    
    public function __construct(PxtAuthentication $entityPxtAuthentication, Settings $entitySettings, 
        Codes $entityCodes, Products $entityProducts, Messages $entityMessages, Agents $entityAgents, Storehouses $entityStorehouses) {
        parent::__construct($entityPxtAuthentication);
        $this->entitySettings = $entitySettings;
        $this->entityCodes = $entityCodes;
        $this->entityProducts = $entityProducts;
        $this->entityMessages = $entityMessages;
        $this->entityAgents = $entityAgents;
        $this->entityStorehouses = $entityStorehouses;
    }
    
    public function indexAction(){
        $formSearch = new SearchForm();
        $queries = $this->params()->fromQuery();
        $formSearch->setData($queries);
        // $a = $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]);
        // \Zend\Debug\Debug::dump($a); die();
        $arrCodes = new Paginator(new ArrayAdapter( $this->entityCodes->fetchAlls(COMPANY_ID, $queries) ));
        // get setting paginator
        $contentPaginator = $this->entitySettings->fetchPaginator($this->defineCompanyId);
        // set page
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        // set per page
        $perPage = (int) $this->params()->fromQuery('per_page', $contentPaginator['per_page']);
        $perPage = ($perPage < 1) ? $contentPaginator['per_page'] : $perPage;
        
        $arrCodes->setCurrentPageNumber($page);
        $arrCodes->setItemCountPerPage($perPage);
        $arrCodes->setPageRange($contentPaginator['page_range']);
        return new ViewModel([
            'arrCodes'          => $arrCodes, 
            'contentPaginator'  => $contentPaginator,
            'formSearch'        => $formSearch,
            'queries'           => $queries,
            'optionProducts'    => $this->entityProducts->fetchAllOptions01(['company_id' => COMPANY_ID]),
            'optionAgents'      => $this->entityAgents->fetchAllToOptions(["company_id" => COMPANY_ID]),
            'optionStorehouses' => $this->entityStorehouses->fetchAllOptions()
        ]);
    }
    
    public function iframeAction(){
        $this->layout()->setTemplate('empty/layout');
        $view = new ViewModel();
        $id = $this->params()->fromRoute('id', 0);
        $view->setVariable('id', $id);
        return $view;
    }
}
