<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{



	public function renderDefault($limit = 10,$offset=0,$url=null,$stitek=null,$search=null)
	{
        $query = $this->getHttpRequest()->getQuery();
        if (!empty($query['c_id'])) {
            $clanek = $this->clanky->getByStareId($query['c_id']);
            $this->redirect("Clanek:default",array("id" => $clanek['id'], "nazev"=> Nette\Utils\Strings::webalize($clanek['titulek'])));
        }
        $aktuality = false;
        if (!is_integer($offset) || $offset<0) {
            $offset = 0;
        }
        if (!is_integer($limit) || $limit<1) {
            $limit = 10;
        }
        $this->template->clanky = $this->clanky->getClanky($limit, $offset,$url,$stitek,$aktuality,$search);

        $this->payload->append = 'snippet--clanky';
        $this->template->next_offset = $offset+$limit;
        $this->template->limit = $limit;
        $this->template->url = $url;
        $this->template->stitek = $stitek;
        $this->template->search = $search;
        $this->redrawControl("clanky");
        $this->redrawControl("nacist");

    }

    public function actionStary($id) {
        $clanek = $this->clanky->getByStareId($id);
        $this->redirect("Clanek:default",array("id" => $clanek['id'], "nazev"=> Nette\Utils\Strings::webalize($clanek['titulek'])));
    }



}
