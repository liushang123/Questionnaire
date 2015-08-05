<?php
/**
 * @Author: sxf
 * @Date:   2015-08-01 16:18:46
 * @Last Modified by:   sxf
 * @Last Modified time: 2015-08-05 11:49:51
 */

/**
* 
*/
class PmController extends Base
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $this->view->setTemplateAfter('base2');
        $this->leftRender();
    }

	public function detailAction()
	{
		
	}

	public function examineeAction()
	{
		# code...
	}

	public function interviewerAction()
	{
		# code...
	}

	public function leaderAction()
	{
		# code...
	}

	public function resultAction()
	{
		# code...
	}

    public function uploadexamineeAction()
    {
        if ($this->request->isPost() && $this->request->hasFiles())
        {
            $files = $this->request->getUploadedFiles();
            $filename = "Import-".date("YmdHis");
            $i = 1;
            foreach ($files as $file) {
                $file->moveTo("./upload/".$filename."-".$i.".xls");
                $excel = new ExcelLoader();
                $excel->LoadExaminee($file);
                $i++;
            }
            $this->response->setHeader("Content-Type", "application/json; charset=utf-8");
            echo json_encode();
            $this->view->disable();
        } else {
            $this->flash->error("错误的接口访问");
            $this->response->redirect("pm/index");
        }
    }

	public function listAction()
	{
        $builder = $this->modelsManager->createBuilder()
                                       ->from('Manager');
        $sidx = $this->request->getQuery('sidx','string');
        $sord = $this->request->getQuery('sord','string');
        if ($sidx != null)
            $sort = $sidx;
        else
            $sort = 'id';
        if ($sord != null)
            $sort = $sort.' '.$sord;
        $builder = $builder->orderBy($sort);
        $this->datareturn($builder);
	}

	public function updateAction()
    {
        $oper = $this->request->getPost('oper', 'string');
        if ($oper == 'edit') {
            $id = $this->request->getPost('id', 'int');
            $manager = Manager::findFirst($id);
            $this->getData($manager, array('username', 'password', 'role', 'name'));
            $manager->project_id = $this->request->getPost('project_id', 'int');
            if (!$manager->save()) {
                foreach ($manager->getMessages() as $message) {
                    echo $message;
                }
            }
        }
        if ($oper == 'del') {
            $id = $this->request->getPost('id', 'int');
            $manager = Manager::findFirst($id);
            if (!$manager->delete()) {
                foreach ($manager->getMessages() as $message) {
                    echo $message;
                }
            }
        }
    }

    public function datareturn($builder)
    {
        $this->response->setHeader("Content-Type", "application/json; charset=utf-8");
        $limit = $this->request->getQuery('rows', 'int');
        $page = $this->request->getQuery('page', 'int');
        if (is_null($limit)) $limit = 10;
        if (is_null($page)) $page = 1;
        $paginator = new Phalcon\Paginator\Adapter\QueryBuilder(array("builder" => $builder,
                                                                      "limit" => $limit,
                                                                      "page" => $page));
        $page = $paginator->getPaginate();
        $ans = array();
        $ans['total'] = $page->total_pages;
        $ans['page'] = $page->current;
        $ans['records'] = $page->total_items;
        foreach ($page->items as $key => $item)
        {
            $ans['rows'][$key] = $item;
        }
        echo json_encode($ans);
        $this->view->disable();
    }

        function leftRender()
    {
        /****************这一段可以抽象成一个通用的方法**********************/
        $manager = $this->session->get('Manager');
        $this->view->setVar('page_title','北京政法系统人才测评项目管理平台');
        $this->view->setVar('user_name',$manager->name);
        $this->view->setVar('user_id',  $manager->username);
        $this->view->setVar('user_role',"项目经理");
        /*******************************************************************/
    }
}